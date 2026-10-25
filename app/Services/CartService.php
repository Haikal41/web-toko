<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;

class CartService
{
    private const SESSION_KEY = 'cart';

    public function raw(): array
    {
        return session()->get(self::SESSION_KEY, []);
    }

    public function key(int $productId, ?int $variantId, ?string $text, ?string $notes): string
    {
        return implode(':', [$productId, $variantId ?? 0, md5(($text ?? '').'|'.($notes ?? ''))]);
    }

    public function add(Product $product, ?ProductVariant $variant, int $quantity, ?string $text, ?string $notes): void
    {
        $items = $this->raw();
        $key = $this->key($product->id, $variant?->id, $text, $notes);

        if (isset($items[$key])) {
            $items[$key]['quantity'] += $quantity;
        } else {
            $items[$key] = [
                'product_id' => $product->id,
                'variant_id' => $variant?->id,
                'quantity' => $quantity,
                'personalization_text' => $text,
                'personalization_notes' => $notes,
            ];
        }

        session()->put(self::SESSION_KEY, $items);
    }

    public function update(string $key, int $quantity): void
    {
        $items = $this->raw();

        if (! isset($items[$key])) {
            return;
        }

        if ($quantity <= 0) {
            unset($items[$key]);
        } else {
            $items[$key]['quantity'] = $quantity;
        }

        session()->put(self::SESSION_KEY, $items);
    }

    public function remove(string $key): void
    {
        $items = $this->raw();
        unset($items[$key]);
        session()->put(self::SESSION_KEY, $items);
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    /**
     * @return Collection<int, array{key:string, product:Product, variant:?ProductVariant, quantity:int, personalization_text:?string, personalization_notes:?string, unit_price:float, line_total:float}>
     */
    public function items(): Collection
    {
        $items = collect();

        foreach ($this->raw() as $key => $item) {
            $product = Product::with('variants')->find($item['product_id']);

            if (! $product || ! $product->is_active) {
                continue;
            }

            $variant = $item['variant_id']
                ? $product->variants->firstWhere('id', $item['variant_id'])
                : null;

            $unitPrice = (float) $product->price + (float) ($variant?->price_adjustment ?? 0);
            $quantity = (int) $item['quantity'];

            $items->push([
                'key' => $key,
                'product' => $product,
                'variant' => $variant,
                'quantity' => $quantity,
                'personalization_text' => $item['personalization_text'] ?? null,
                'personalization_notes' => $item['personalization_notes'] ?? null,
                'unit_price' => $unitPrice,
                'line_total' => $unitPrice * $quantity,
            ]);
        }

        return $items;
    }

    public function count(): int
    {
        return array_sum(array_column($this->raw(), 'quantity'));
    }

    public function subtotal(): float
    {
        return (float) $this->items()->sum('line_total');
    }

    public function isEmpty(): bool
    {
        return $this->items()->isEmpty();
    }
}
