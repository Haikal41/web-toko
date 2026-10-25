<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private CartService $cart) {}

    public function index()
    {
        return view('cart.index', [
            'items' => $this->cart->items(),
            'subtotal' => $this->cart->subtotal(),
            'shipping' => (float) setting('shipping_flat', 15000),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'variant_id' => ['nullable', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
            'personalization_text' => ['nullable', 'string', 'max:60'],
            'personalization_notes' => ['nullable', 'string', 'max:200'],
        ]);

        $product = Product::active()->findOrFail($data['product_id']);

        $variant = null;

        if (! empty($data['variant_id'])) {
            $variant = ProductVariant::where('product_id', $product->id)->findOrFail($data['variant_id']);
        }

        if ($product->requires_personalization && blank($data['personalization_text'] ?? null)) {
            return back()->withErrors(['personalization_text' => 'Teks personalisasi wajib diisi untuk produk ini.'])->withInput();
        }

        $stock = $variant?->stock ?? $product->stock;

        if ($stock < $data['quantity']) {
            return back()->withErrors(['quantity' => 'Stok tidak mencukupi.'])->withInput();
        }

        $this->cart->add(
            $product,
            $variant,
            (int) $data['quantity'],
            $data['personalization_text'] ?? null,
            $data['personalization_notes'] ?? null,
        );

        return redirect()->route('cart.index')->with('success', 'Produk ditambahkan ke keranjang.');
    }

    public function update(Request $request, string $key)
    {
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1', 'max:99']]);
        $this->cart->update($key, (int) $data['quantity']);

        return redirect()->route('cart.index')->with('success', 'Keranjang diperbarui.');
    }

    public function destroy(string $key)
    {
        $this->cart->remove($key);

        return redirect()->route('cart.index')->with('success', 'Item dihapus dari keranjang.');
    }
}
