<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MidtransService
{
    public function isConfigured(): bool
    {
        return filled(config('midtrans.server_key'));
    }

    public function createSnapToken(Order $order): ?string
    {
        if (! $this->isConfigured()) {
            return null;
        }

        $items = $order->items->map(fn ($item) => [
            'id' => (string) $item->product_id,
            'price' => (int) round((float) $item->price),
            'quantity' => $item->quantity,
            'name' => mb_substr($item->product_name, 0, 50),
        ])->values()->all();

        if ((float) $order->shipping_cost > 0) {
            $items[] = [
                'id' => 'SHIPPING',
                'price' => (int) round((float) $order->shipping_cost),
                'quantity' => 1,
                'name' => 'Ongkos Kirim',
            ];
        }

        if ((float) $order->discount > 0) {
            $items[] = [
                'id' => 'DISCOUNT',
                'price' => -(int) round((float) $order->discount),
                'quantity' => 1,
                'name' => 'Diskon Kupon',
            ];
        }

        $payload = [
            'transaction_details' => [
                'order_id' => $order->order_number,
                'gross_amount' => (int) round((float) $order->total),
            ],
            'customer_details' => [
                'first_name' => $order->customer_name,
                'phone' => $order->customer_phone,
                'email' => $order->user?->email,
                'billing_address' => ['address' => $order->customer_address],
                'shipping_address' => ['address' => $order->customer_address],
            ],
            'item_details' => $items,
            'callbacks' => [
                'finish' => \Illuminate\Support\Facades\URL::temporarySignedRoute(
                    'checkout.finish',
                    now()->addDays(7),
                    ['orderNumber' => $order->order_number]
                ),
            ],
        ];

        try {
            $response = Http::withBasicAuth(config('midtrans.server_key'), '')
                ->acceptJson()
                ->timeout(20)
                ->post(config('midtrans.api_url'), $payload);

            if ($response->successful()) {
                return $response->json('token');
            }

            Log::error('Midtrans snap error', ['status' => $response->status(), 'body' => $response->body()]);
        } catch (\Throwable $e) {
            Log::error('Midtrans exception: '.$e->getMessage());
        }

        return null;
    }

    public function verifySignature(array $payload): bool
    {
        $serverKey = config('midtrans.server_key');

        if (blank($serverKey)) {
            return false;
        }

        $expected = hash('sha512', ($payload['order_id'] ?? '').($payload['status_code'] ?? '').($payload['gross_amount'] ?? '').$serverKey);

        return hash_equals($expected, $payload['signature_key'] ?? '');
    }
}
