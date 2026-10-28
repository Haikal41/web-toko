<?php

namespace App\Services;

use App\Jobs\SendOrderNotification;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OrderService
{
    public function __construct(private CartService $cart) {}

    /**
     * @param  Collection<int, array<string, mixed>>|null  $items
     */
    public function create(?User $user, array $data, ?Collection $items = null): Order
    {
        $fromCart = $items === null;
        $items = $items ?? $this->cart->items();

        if ($items->isEmpty()) {
            throw new RuntimeException('Keranjang belanja masih kosong.');
        }

        foreach ($items as $item) {
            $stock = $item['variant']?->stock ?? $item['product']->stock;

            if ($stock < $item['quantity']) {
                throw new RuntimeException("Stok {$item['product']->name} tidak mencukupi.");
            }
        }

        $subtotal = $items->sum('line_total');
        $discount = 0.0;
        $coupon = null;

        if (! empty($data['coupon_code'])) {
            $coupon = Coupon::where('code', strtoupper($data['coupon_code']))->first();

            if (! $coupon || ! $coupon->isValidFor($subtotal)) {
                throw new RuntimeException('Kode kupon tidak valid atau tidak memenuhi syarat.');
            }

            $discount = $coupon->discountFor($subtotal);
        }

        $shipping = (float) ($data['shipping_cost'] ?? 0);
        $total = max(0, $subtotal - $discount + $shipping);

        $fulfillment = $data['fulfillment'] ?? 'delivery';

        if (! in_array($fulfillment, Order::FULFILLMENTS, true)) {
            $fulfillment = 'delivery';
        }

        $cashReceived = isset($data['cash_received']) ? (float) $data['cash_received'] : null;

        if ($cashReceived !== null && $cashReceived < $total) {
            throw new RuntimeException('Uang tunai yang diterima kurang dari total pesanan.');
        }

        $order = DB::transaction(function () use ($user, $data, $items, $subtotal, $discount, $shipping, $total, $coupon, $fulfillment) {
            $order = Order::create([
                'order_number' => Order::generateNumber(),
                'user_id' => $user?->id,
                'address_id' => $data['address_id'] ?? null,
                'coupon_id' => $coupon?->id,
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'] ?? null,
                'customer_address' => $data['customer_address'] ?? '',
                'subtotal' => $subtotal,
                'discount' => $discount,
                'shipping_cost' => $shipping,
                'total' => $total,
                'status' => 'pending',
                'channel' => $data['channel'] ?? 'online',
                'fulfillment' => $fulfillment,
                'created_by' => $data['created_by'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($items as $item) {
                $order->items()->create([
                    'product_id' => $item['product']->id,
                    'product_name' => $item['product']->name,
                    'variant_id' => $item['variant']?->id,
                    'variant_name' => $item['variant']?->name,
                    'price' => $item['unit_price'],
                    'quantity' => $item['quantity'],
                    'personalization_text' => $item['personalization_text'] ?? null,
                    'personalization_notes' => $item['personalization_notes'] ?? null,
                    'subtotal' => $item['line_total'],
                ]);
            }

            $order->payments()->create([
                'gross_amount' => $total,
                'status' => 'pending',
            ]);

            return $order;
        });

        if ($fromCart) {
            $this->cart->clear();
        }

        return $order;
    }

    public function markPaid(Order $order, array $paymentData = [], ?User $actor = null): bool
    {
        $updated = DB::transaction(function () use ($order, $paymentData, $actor) {
            $order = Order::whereKey($order->id)->lockForUpdate()->first();

            if (! $order || $order->status !== 'pending') {
                return false;
            }

            foreach ($order->items as $item) {
                if ($item->product_id) {
                    Product::whereKey($item->product_id)->decrement('stock', $item->quantity);
                }

                if ($item->variant_id) {
                    \App\Models\ProductVariant::whereKey($item->variant_id)->decrement('stock', $item->quantity);
                }
            }

            $fromStatus = $order->status;

            $cashReceived = array_key_exists('cash_received', $paymentData) && $paymentData['cash_received'] !== null
                ? (float) $paymentData['cash_received']
                : null;

            if ($cashReceived !== null && $cashReceived < (float) $order->total) {
                throw new RuntimeException('Uang tunai yang diterima kurang dari total pesanan.');
            }

            $order->update([
                'status' => 'paid',
                'paid_at' => now(),
                'cash_received' => $cashReceived,
                'change_amount' => $cashReceived !== null ? $cashReceived - (float) $order->total : null,
            ]);

            $payment = $order->payments()->latest()->first() ?? $order->payments()->make();

            $payment->fill([
                'transaction_id' => $paymentData['transaction_id'] ?? $payment->transaction_id,
                'payment_type' => $paymentData['payment_type'] ?? $payment->payment_type,
                'gross_amount' => $paymentData['gross_amount'] ?? $order->total,
                'status' => $paymentData['status'] ?? 'settlement',
                'raw_response' => $paymentData['raw_response'] ?? $payment->raw_response,
                'paid_at' => now(),
            ])->save();

            if ($order->coupon_id) {
                Coupon::whereKey($order->coupon_id)->increment('used_count');
            }

            $this->logStatus($order, $fromStatus, 'paid', $actor, 'Pembayaran diterima'.(! empty($paymentData['payment_type']) ? ' ('.$paymentData['payment_type'].')' : ''));

            return true;
        });

        if ($updated) {
            SendOrderNotification::dispatch($order->fresh());
        }

        return $updated;
    }

    public function cancel(Order $order, ?string $reason = null, ?User $actor = null): void
    {
        DB::transaction(function () use ($order, $reason, $actor) {
            $order = Order::whereKey($order->id)->lockForUpdate()->first();

            if (! $order || $order->status === 'batal') {
                return;
            }

            if (in_array($order->status, Order::REVENUE_STATUSES, true)) {
                foreach ($order->items as $item) {
                    if ($item->product_id) {
                        Product::whereKey($item->product_id)->increment('stock', $item->quantity);
                    }

                    if ($item->variant_id) {
                        \App\Models\ProductVariant::whereKey($item->variant_id)->increment('stock', $item->quantity);
                    }
                }
            }

            $fromStatus = $order->status;

            $order->update([
                'status' => 'batal',
                'cancelled_by' => $actor?->id,
                'cancelled_at' => now(),
                'cancel_reason' => $reason,
                'notes' => $reason ? trim(($order->notes ?? '')."\nDibatalkan: ".$reason) : $order->notes,
            ]);

            $this->logStatus($order, $fromStatus, 'batal', $actor, $reason);
        });
    }

    public function updateStatus(Order $order, string $status, ?User $actor = null, ?string $note = null): void
    {
        if (! in_array($status, Order::STATUSES, true)) {
            throw new RuntimeException('Status tidak valid.');
        }

        if ($status === 'batal') {
            $this->cancel($order, $note, $actor);

            return;
        }

        if ($status === 'paid' && $order->status === 'pending') {
            $this->markPaid($order, [], $actor);

            return;
        }

        if ($order->status === $status) {
            return;
        }

        $fromStatus = $order->status;
        $order->update(['status' => $status]);

        $this->logStatus($order, $fromStatus, $status, $actor, $note);
    }

    private function logStatus(Order $order, ?string $from, string $to, ?User $actor, ?string $note = null): void
    {
        OrderStatusLog::create([
            'order_id' => $order->id,
            'user_id' => $actor?->id,
            'from_status' => $from,
            'to_status' => $to,
            'note' => $note,
        ]);
    }
}
