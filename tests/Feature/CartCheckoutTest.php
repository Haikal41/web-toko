<?php

namespace Tests\Feature;

use App\Jobs\SendOrderNotification;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\CreatesShopData;
use Tests\TestCase;

class CartCheckoutTest extends TestCase
{
    use CreatesShopData;
    use RefreshDatabase;

    public function test_product_can_be_added_to_cart(): void
    {
        $product = $this->product(['name' => 'Gantungan Kunci Custom']);
        $customer = $this->customer();

        $response = $this->actingAs($customer)->post(route('cart.store'), [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response->assertRedirect(route('cart.index'));

        $this->actingAs($customer)
            ->get(route('cart.index'))
            ->assertOk()
            ->assertSee('Gantungan Kunci Custom');
    }

    public function test_product_requiring_personalization_rejects_empty_text(): void
    {
        $product = $this->product(['requires_personalization' => true]);
        $customer = $this->customer();

        $this->actingAs($customer)
            ->post(route('cart.store'), [
                'product_id' => $product->id,
                'quantity' => 1,
            ])
            ->assertSessionHasErrors('personalization_text');

        $this->actingAs($customer)
            ->post(route('cart.store'), [
                'product_id' => $product->id,
                'quantity' => 1,
                'personalization_text' => 'Untuk Sahabat',
            ])
            ->assertSessionHasNoErrors();
    }

    public function test_quantity_beyond_stock_is_rejected(): void
    {
        $product = $this->product(['stock' => 2]);
        $customer = $this->customer();

        $this->actingAs($customer)
            ->post(route('cart.store'), [
                'product_id' => $product->id,
                'quantity' => 5,
            ])
            ->assertSessionHasErrors('quantity');
    }

    public function test_cart_item_can_be_updated_and_removed(): void
    {
        $product = $this->product();
        $customer = $this->customer();

        $this->actingAs($customer)->post(route('cart.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $key = array_key_first(session('cart'));

        $this->actingAs($customer)
            ->patch(route('cart.update', $key), ['quantity' => 4])
            ->assertRedirect(route('cart.index'));

        $this->assertSame(4, session('cart')[$key]['quantity']);

        $this->actingAs($customer)
            ->delete(route('cart.destroy', $key))
            ->assertRedirect(route('cart.index'));

        $this->assertSame([], session('cart'));
    }

    public function test_checkout_creates_pending_order_with_shipping_and_coupon(): void
    {
        $product = $this->product(['price' => 200000]);
        $customer = $this->customer();

        Coupon::create([
            'code' => 'HEMAT10',
            'type' => 'percentage',
            'value' => 10,
            'min_purchase' => 100000,
            'max_uses' => 10,
            'used_count' => 0,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'is_active' => true,
        ]);

        $this->actingAs($customer)->post(route('cart.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($customer)->post(route('checkout.store'), [
            'recipient_name' => 'Pembeli Toko',
            'phone' => '081200000003',
            'address' => 'Jl. Mawar No. 1',
            'city' => 'Bandung',
            'postal_code' => '40111',
            'coupon_code' => 'HEMAT10',
        ]);

        $order = Order::first();

        $this->assertNotNull($order);
        $response->assertRedirect(route('checkout.payment', $order->order_number));

        $this->assertSame('pending', $order->status);
        $this->assertEquals(200000, (float) $order->subtotal);
        $this->assertEquals(20000, (float) $order->discount);
        $this->assertEquals(15000, (float) $order->shipping_cost);
        $this->assertEquals(195000, (float) $order->total);
        $this->assertSame(1, $order->items()->count());
        $this->assertEmpty(session('cart'));
        $this->assertSame(1, $customer->addresses()->count());
    }

    public function test_checkout_with_invalid_coupon_is_rejected(): void
    {
        $product = $this->product(['price' => 50000]);
        $customer = $this->customer();

        $this->actingAs($customer)->post(route('cart.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $this->actingAs($customer)
            ->post(route('checkout.store'), [
                'recipient_name' => 'Pembeli Toko',
                'phone' => '081200000003',
                'address' => 'Jl. Mawar No. 1',
                'city' => 'Bandung',
                'coupon_code' => 'TIDAKADA',
            ])
            ->assertSessionHasErrors('checkout');

        $this->assertSame(0, Order::count());
    }

    public function test_demo_payment_marks_order_paid_and_reduces_stock(): void
    {
        Queue::fake();

        $product = $this->product(['stock' => 10]);
        $customer = $this->customer();

        $this->actingAs($customer)->post(route('cart.store'), [
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        $this->actingAs($customer)->post(route('checkout.store'), [
            'recipient_name' => 'Pembeli Toko',
            'phone' => '081200000003',
            'address' => 'Jl. Mawar No. 1',
            'city' => 'Bandung',
        ]);

        $order = Order::first();

        $response = $this->actingAs($customer)->post(route('checkout.demo', $order->order_number));

        $response->assertRedirect(route('checkout.finish', $order->order_number));

        $order->refresh();

        $this->assertSame('paid', $order->status);
        $this->assertNotNull($order->paid_at);
        $this->assertSame(7, Product::find($product->id)->stock);
        $this->assertSame('settlement', $order->payment()->status);

        Queue::assertPushed(SendOrderNotification::class);
    }

    public function test_customer_cannot_pay_someone_else_order(): void
    {
        $owner = $this->customer(['email' => 'owner@test.local']);
        $other = $this->customer(['email' => 'other@test.local']);

        $order = Order::create([
            'order_number' => 'MVA-20260101-0001',
            'user_id' => $owner->id,
            'customer_name' => $owner->name,
            'customer_phone' => $owner->phone,
            'customer_address' => 'Jl. Mawar No. 1',
            'subtotal' => 10000,
            'discount' => 0,
            'shipping_cost' => 15000,
            'total' => 25000,
            'status' => 'pending',
        ]);

        $this->actingAs($other)
            ->post(route('checkout.demo', $order->order_number))
            ->assertNotFound();

        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_cancelled_order_returns_stock(): void
    {
        $product = $this->product(['stock' => 10]);
        $customer = $this->customer();

        $this->actingAs($customer)->post(route('cart.store'), [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $this->actingAs($customer)->post(route('checkout.store'), [
            'recipient_name' => 'Pembeli Toko',
            'phone' => '081200000003',
            'address' => 'Jl. Mawar No. 1',
            'city' => 'Bandung',
        ]);

        $order = Order::first();

        $this->actingAs($customer)->post(route('checkout.demo', $order->order_number));

        $this->assertSame(8, Product::find($product->id)->stock);

        app(\App\Services\OrderService::class)->cancel($order->fresh(), 'Pembeli membatalkan');

        $this->assertSame('batal', $order->fresh()->status);
        $this->assertSame(10, Product::find($product->id)->stock);
    }
}
