<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class PosController extends Controller
{
    public function create()
    {
        $products = Product::active()->with('variants')->orderBy('name')->get();
        $shipping = (float) setting('shipping_flat', 15000);

        $posProducts = $products->map(fn (Product $product) => [
            'id' => $product->id,
            'name' => $product->name,
            'price' => (float) $product->price,
            'stock' => (int) $product->stock,
            'requires_personalization' => (bool) $product->requires_personalization,
            'variants' => $product->variants->map(fn ($variant) => [
                'id' => $variant->id,
                'name' => $variant->name,
                'adjustment' => (float) $variant->price_adjustment,
                'stock' => (int) $variant->stock,
            ])->values()->all(),
        ])->values()->all();

        return view('admin.pos.create', compact('products', 'shipping', 'posProducts'));
    }

    public function store(Request $request, OrderService $orders)
    {
        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:100'],
            'customer_phone' => ['nullable', 'string', 'max:20'],
            'delivery' => ['nullable', 'boolean'],
            'customer_address' => ['nullable', 'string', 'max:500'],
            'payment_method' => ['required', 'in:cash,qris'],
            'cash_received' => ['required_if:payment_method,cash', 'nullable', 'numeric', 'min:0'],
            'coupon_code' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:999'],
            'items.*.personalization_text' => ['nullable', 'string', 'max:60'],
            'items.*.personalization_notes' => ['nullable', 'string', 'max:200'],
        ]);

        $delivery = $request->boolean('delivery');

        if ($delivery && blank($data['customer_address'] ?? null)) {
            throw ValidationException::withMessages([
                'customer_address' => 'Alamat wajib diisi untuk pesanan yang dikirim.',
            ]);
        }

        $products = Product::with('variants')
            ->whereIn('id', collect($data['items'])->pluck('product_id'))
            ->get()
            ->keyBy('id');

        $items = new Collection();

        foreach ($data['items'] as $index => $row) {
            $product = $products->get($row['product_id']);

            if (! $product || ! $product->is_active) {
                throw ValidationException::withMessages([
                    "items.{$index}.product_id" => 'Produk tidak tersedia.',
                ]);
            }

            $variant = null;

            if (! empty($row['variant_id'])) {
                $variant = $product->variants->firstWhere('id', (int) $row['variant_id']);

                if (! $variant) {
                    throw ValidationException::withMessages([
                        "items.{$index}.variant_id" => 'Varian tidak sesuai dengan produk.',
                    ]);
                }
            }

            if ($product->requires_personalization && blank($row['personalization_text'] ?? null)) {
                throw ValidationException::withMessages([
                    "items.{$index}.personalization_text" => "Personalisasi untuk {$product->name} wajib diisi.",
                ]);
            }

            $unitPrice = (float) $product->price + (float) ($variant?->price_adjustment ?? 0);
            $quantity = (int) $row['quantity'];

            $items->push([
                'product' => $product,
                'variant' => $variant,
                'quantity' => $quantity,
                'personalization_text' => $row['personalization_text'] ?? null,
                'personalization_notes' => $row['personalization_notes'] ?? null,
                'unit_price' => $unitPrice,
                'line_total' => $unitPrice * $quantity,
            ]);
        }

        $cashReceived = $data['payment_method'] === 'cash'
            ? (float) $data['cash_received']
            : null;

        try {
            $order = $orders->create(null, [
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'] ?? null,
                'customer_address' => $delivery
                    ? $data['customer_address']
                    : 'Ambil di toko',
                'shipping_cost' => $delivery ? (float) setting('shipping_flat', 15000) : 0,
                'coupon_code' => $data['coupon_code'] ?? null,
                'notes' => $data['notes'] ?? null,
                'channel' => 'pos',
                'fulfillment' => $delivery ? 'delivery' : 'pickup',
                'cash_received' => $cashReceived,
                'created_by' => $request->user()->id,
            ], $items);
        } catch (RuntimeException $e) {
            return back()->withInput()->withErrors(['items' => $e->getMessage()]);
        }

        if ($data['payment_method'] === 'cash') {
            $orders->markPaid($order, [
                'transaction_id' => 'POS-'.strtoupper(uniqid()),
                'payment_type' => 'cash',
                'gross_amount' => $order->total,
                'status' => 'settlement',
                'cash_received' => $cashReceived,
                'raw_response' => ['source' => 'pos-cash'],
            ], $request->user());

            return redirect()
                ->route('admin.orders.receipt', ['order' => $order, 'auto' => 1])
                ->with('success', 'Pesanan tunai '.$order->order_number.' berhasil dibuat dan ditandai lunas.');
        }

        $paymentUrl = URL::temporarySignedRoute('checkout.payment', now()->addDays(7), [
            'orderNumber' => $order->order_number,
        ]);

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('success', 'Pesanan '.$order->order_number.' dibuat. Kirim tautan pembayaran ke pembeli.')
            ->with('payment_link', $paymentUrl);
    }
}
