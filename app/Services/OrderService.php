<?php

namespace App\Services;

use App\Jobs\SendOrderNotification;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OrderService
{
    public function __construct(private CartService $cart) {}

    public function create(User $user, array $data): Order
    {
        $items = $this->cart->items();

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

        $order = DB::transaction(function () use ($user, $data, $items, $subtotal, $discount, $shipping, $total, $coupon) {
            $order = Order::create([
                'order_number' => Order::generateNumber(),
                'user_id' => $user->id,
                'address_id' => $data['address_id'] ?? null,
                'coupon_id' => $coupon?->id,
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'customer_address' => $data['customer_address'],
                'subtotal' => $subtotal,
                'discount' => $discount,
                'shipping_cost' => $shipping,
                'total' => $total,
                'status' => 'pending',
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
                    'personalization_text' => $item['personalization_text'],
                    'personalization_notes' => $item['personalization_notes'],
                    'subtotal' => $item['line_total'],
                ]);
            }

            $order->payments()->create([
                'gross_amount' => $total,
                'status' => 'pending',
            ]);

            return $order;
        });

        $this->cart->clear();

        return $order;
    }

    public function markPaid(Order $order, array $paymentData = []): bool
    {
        $updated = DB::transaction(function () use ($order, $paymentData) {
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

            $order->update([
                'status' => 'paid',
                'paid_at' => now(),
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

            return true;
        });

        if ($updated) {
            SendOrderNotification::dispatch($order->fresh());
        }

        return $updated;
    }

    public function cancel(Order $order, ?string $reason = null): void
    {
        DB::transaction(function () use ($order, $reason) {
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

            $order->update([
                'status' => 'batal',
                'notes' => $reason ? trim(($order->notes ?? '')."\n".$reason) : $order->notes,
            ]);
        });
    }

    public function updateStatus(Order $order, string $status): void
    {
        if (! in_array($status, Order::STATUSES, true)) {
            throw new RuntimeException('Status tidak valid.');
        }

        if ($status === 'batal') {
            $this->cancel($order);

            return;
        }

        $order->update(['status' => $status]);
    }
}
