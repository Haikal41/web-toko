<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\MidtransService;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MidtransWebhookController extends Controller
{
    public function handle(Request $request, MidtransService $midtrans, OrderService $orders)
    {
        $payload = $request->all();

        Log::info('Midtrans webhook received', $payload);

        if (! $midtrans->verifySignature($payload)) {
            Log::warning('Midtrans signature invalid', ['order_id' => $payload['order_id'] ?? null]);

            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $order = Order::where('order_number', $payload['order_id'] ?? '')->first();

        if (! $order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $status = $payload['transaction_status'] ?? '';

        if (in_array($status, ['settlement', 'capture'], true)) {
            $orders->markPaid($order, [
                'transaction_id' => $payload['transaction_id'] ?? null,
                'payment_type' => $payload['payment_type'] ?? null,
                'gross_amount' => $payload['gross_amount'] ?? $order->total,
                'status' => $status,
                'raw_response' => $payload,
            ]);
        } elseif (in_array($status, ['expire', 'deny', 'cancel'], true)) {
            $orders->cancel($order, 'Midtrans: '.$status);
        }

        return response()->json(['message' => 'OK']);
    }
}
