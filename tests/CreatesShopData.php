<?php

namespace Tests;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Str;

trait CreatesShopData
{
    protected function owner(array $attributes = []): User
    {
        return User::create(array_merge([
            'name' => 'Owner Toko',
            'email' => 'owner@test.local',
            'phone' => '081200000001',
            'password' => 'rahasia123',
            'role' => 'owner',
            'is_active' => true,
        ], $attributes));
    }

    protected function admin(array $attributes = []): User
    {
        return $this->owner($attributes);
    }

    protected function kasir(array $attributes = []): User
    {
        return User::create(array_merge([
            'name' => 'Kasir Toko',
            'email' => 'kasir@test.local',
            'phone' => '081200000002',
            'password' => 'rahasia123',
            'role' => 'kasir',
            'is_active' => true,
        ], $attributes));
    }

    protected function employee(array $attributes = []): User
    {
        return $this->kasir($attributes);
    }

    protected function customer(array $attributes = []): User
    {
        return User::create(array_merge([
            'name' => 'Pembeli Toko',
            'email' => 'pembeli@test.local',
            'phone' => '081200000003',
            'password' => 'rahasia123',
            'role' => 'customer',
            'is_active' => true,
        ], $attributes));
    }

    protected function category(array $attributes = []): Category
    {
        $name = $attributes['name'] ?? 'Gantungan Kunci';
        $slug = $attributes['slug'] ?? Str::slug($name);

        unset($attributes['name'], $attributes['slug']);

        return Category::firstOrCreate(
            ['slug' => $slug],
            array_merge(['name' => $name, 'is_active' => true], $attributes)
        );
    }

    protected function product(array $attributes = []): Product
    {
        $category = $attributes['category'] ?? $this->category();
        unset($attributes['category']);

        $name = $attributes['name'] ?? 'Gantungan Kunci Custom';

        return Product::create(array_merge([
            'category_id' => $category->id,
            'name' => $name,
            'slug' => $attributes['slug'] ?? Str::slug($name),
            'description' => $attributes['description'] ?? 'Produk uji coba untuk pengujian otomatis.',
            'price' => 25000,
            'stock' => 10,
            'requires_personalization' => false,
            'is_active' => true,
            'low_stock_threshold' => 5,
        ], $attributes));
    }

    protected function variant(Product $product, array $attributes = []): ProductVariant
    {
        return $product->variants()->create(array_merge([
            'name' => 'Warna Merah',
            'price_adjustment' => 0,
            'stock' => 5,
        ], $attributes));
    }
}
