<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\CreatesShopData;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use CreatesShopData;
    use RefreshDatabase;

    public function test_guest_can_browse_home_and_catalog(): void
    {
        $product = $this->product(['name' => 'Pigura Akrilik Custom']);

        $this->get(route('home'))->assertOk();
        $this->get(route('products.index'))->assertOk()->assertSee('Pigura Akrilik Custom');
        $this->get(route('products.show', $product->slug))->assertOk()->assertSee('Pigura Akrilik Custom');
    }

    public function test_catalog_can_be_filtered_by_keyword_and_category(): void
    {
        $category = $this->category(['name' => 'Piala', 'slug' => 'piala']);
        $trophy = $this->product(['name' => 'Piala Emas Wahana', 'category' => $category]);
        $other = $this->product(['name' => 'Boneka Beruang', 'slug' => 'boneka-beruang']);

        $this->get(route('products.index', ['q' => 'Piala']))
            ->assertOk()
            ->assertSee('Piala Emas Wahana')
            ->assertDontSee('Boneka Beruang');

        $this->get(route('products.index', ['category' => 'piala']))
            ->assertOk()
            ->assertSee('Piala Emas Wahana')
            ->assertDontSee('Boneka Beruang');

        $this->assertNotNull($trophy->fresh());
        $this->assertNotNull($other->fresh());
    }

    public function test_catalog_can_be_sorted_by_price(): void
    {
        $this->product(['name' => 'Produk Mahal', 'slug' => 'produk-mahal', 'price' => 200000]);
        $this->product(['name' => 'Produk Murah', 'slug' => 'produk-murah', 'price' => 10000]);

        $response = $this->get(route('products.index', ['sort' => 'price_asc']));

        $response->assertOk();
        $this->assertLessThan(
            strpos($response->getContent(), 'Produk Mahal'),
            strpos($response->getContent(), 'Produk Murah')
        );
    }

    public function test_inactive_product_is_not_reachable(): void
    {
        $product = $this->product(['is_active' => false]);

        $this->get(route('products.show', $product->slug))->assertNotFound();
    }

    public function test_guest_is_redirected_to_login_for_protected_pages(): void
    {
        $this->get(route('account.index'))->assertRedirect(route('login'));
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }
}
