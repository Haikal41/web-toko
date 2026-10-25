<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\CreatesShopData;
use Tests\TestCase;

class MidtransWebhookTest extends TestCase
{
    use CreatesShopData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['midtrans.server_key' => 'kunci-uji-123']);
    }

    private function signature(array $payload): string
    {
        return hash(
            'sha512',
            ($payload['order_id'] ?? '').($payload['status_code'] ?? '').($payload['gross_amount'] ?? '').config('midtrans.server_key')
        );
    }

    private function pendingOrder(Product $product): Order
    {
        $customer = $this->customer();

        $order = Order::create([
            'order_number' => 'MVA-20260101-0009',
            'user_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_phone' => $customer->phone,
            'customer_address' => 'Jl. Mawar No. 1',
            'subtotal' => (float) $product->price,
            'discount' => 0,
            'shipping_cost' => 15000,
            'total' => (float) $product->price + 15000,
            'status' => 'pending',
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => (float) $product->price,
            'quantity' => 1,
            'subtotal' => (float) $product->price,
        ]);

        return $order;
    }

    public function test_valid_settlement_webhook_marks_order_as_paid(): void
    {
        Queue::fake();

        $product = $this->product(['stock' => 5]);
        $order = $this->pendingOrder($product);

        $payload = [
            'order_id' => $order->order_number,
            'status_code' => '200',
            'gross_amount' => (string) $order->total,
            'transaction_status' => 'settlement',
            'transaction_id' => 'trx-abc-123',
            'payment_type' => 'qris',
        ];

        $payload['signature_key'] = $this->signature($payload);

        $response = $this->postJson(route('webhooks.midtrans'), $payload);

        $response->assertOk()->assertJson(['message' => 'OK']);

        $order->refresh();

        $this->assertSame('paid', $order->status);
        $this->assertNotNull($order->paid_at);
        $this->assertSame('trx-abc-123', $order->payment()->transaction_id);
        $this->assertSame(4, Product::find($product->id)->stock);
    }

    public function test_webhook_with_invalid_signature_is_rejected(): void
    {
        $product = $this->product();
        $order = $this->pendingOrder($product);

        $response = $this->postJson(route('webhooks.midtrans'), [
            'order_id' => $order->order_number,
            'status_code' => '200',
            'gross_amount' => (string) $order->total,
            'transaction_status' => 'settlement',
            'signature_key' => 'signature-palsu',
        ]);

        $response->assertStatus(403);
        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_expire_webhook_cancels_pending_order_without_touching_stock(): void
    {
        $product = $this->product(['stock' => 5]);
        $order = $this->pendingOrder($product);

        $payload = [
            'order_id' => $order->order_number,
            'status_code' => '200',
            'gross_amount' => (string) $order->total,
            'transaction_status' => 'expire',
        ];

        $payload['signature_key'] = $this->signature($payload);

        $this->postJson(route('webhooks.midtrans'), $payload)->assertOk();

        $this->assertSame('batal', $order->fresh()->status);
        $this->assertSame(5, Product::find($product->id)->stock);
    }

    public function test_webhook_for_unknown_order_returns_404(): void
    {
        $payload = [
            'order_id' => 'MVA-TIDAK-ADA',
            'status_code' => '200',
            'gross_amount' => '10000',
            'transaction_status' => 'settlement',
        ];

        $payload['signature_key'] = $this->signature($payload);

        $this->postJson(route('webhooks.midtrans'), $payload)->assertNotFound();
    }
}
