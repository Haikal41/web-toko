<?php

namespace Tests\Feature;

use App\Jobs\SendOrderNotification;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\URL;
use Tests\CreatesShopData;
use Tests\TestCase;

class CartCheckoutTest extends TestCase
{
    use CreatesShopData;
    use RefreshDatabase;

    private function addToCart(Product $product, int $quantity = 1, array $extra = []): void
    {
        $this->post(route('cart.store'), array_merge([
            'product_id' => $product->id,
            'quantity' => $quantity,
        ], $extra))->assertRedirect(route('cart.index'));
    }

    private function guestCheckout(array $overrides = []): \Illuminate\Testing\TestResponse
    {
        return $this->post(route('checkout.store'), array_merge([
            'recipient_name' => 'Pembeli Tamu',
            'phone' => '081200000003',
            'address' => 'Jl. Mawar No. 1',
            'city' => 'Bandung',
            'postal_code' => '40111',
        ], $overrides));
    }

    public function test_guest_can_add_product_to_cart(): void
    {
        $product = $this->product(['name' => 'Gantungan Kunci Custom']);

        $this->addToCart($product, 2);

        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee('Gantungan Kunci Custom');
    }

    public function test_product_requiring_personalization_rejects_empty_text(): void
    {
        $product = $this->product(['requires_personalization' => true]);

        $this->post(route('cart.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertSessionHasErrors('personalization_text');

        $this->post(route('cart.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
            'personalization_text' => 'Untuk Sahabat',
        ])->assertSessionHasNoErrors();
    }

    public function test_quantity_beyond_stock_is_rejected(): void
    {
        $product = $this->product(['stock' => 2]);

        $this->post(route('cart.store'), [
            'product_id' => $product->id,
            'quantity' => 5,
        ])->assertSessionHasErrors('quantity');
    }

    public function test_cart_item_can_be_updated_and_removed(): void
    {
        $product = $this->product();

        $this->addToCart($product);

        $key = array_key_first(session('cart'));

        $this->patch(route('cart.update', $key), ['quantity' => 4])
            ->assertRedirect(route('cart.index'));

        $this->assertSame(4, session('cart')[$key]['quantity']);

        $this->delete(route('cart.destroy', $key))
            ->assertRedirect(route('cart.index'));

        $this->assertSame([], session('cart'));
    }

    public function test_guest_can_open_checkout_page(): void
    {
        $this->addToCart($this->product());

        $this->get(route('checkout.index'))
            ->assertOk()
            ->assertSee('Checkout')
            ->assertSee('Tidak perlu membuat akun');
    }

    public function test_checkout_with_empty_cart_redirects_back_to_cart(): void
    {
        $this->get(route('checkout.index'))
            ->assertRedirect(route('cart.index'));
    }

    public function test_tracking_page_is_public(): void
    {
        $this->get(route('track.index'))
            ->assertOk()
            ->assertSee('Lacak pesanan');
    }

    public function test_guest_checkout_creates_pending_order_with_shipping_and_coupon(): void
    {
        $product = $this->product(['price' => 200000]);

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

        $this->addToCart($product);

        $response = $this->guestCheckout(['coupon_code' => 'HEMAT10']);

        $order = Order::first();

        $this->assertNotNull($order);
        $this->assertNull($order->user_id);
        $this->assertSame('online', $order->channel);
        $this->assertTrue(str_starts_with(
            $response->headers->get('Location'),
            route('checkout.payment', $order->order_number)
        ));

        $this->assertSame('pending', $order->status);
        $this->assertEquals(200000, (float) $order->subtotal);
        $this->assertEquals(20000, (float) $order->discount);
        $this->assertEquals(15000, (float) $order->shipping_cost);
        $this->assertEquals(195000, (float) $order->total);
        $this->assertSame(1, $order->items()->count());
        $this->assertEmpty(session('cart'));

        $this->get($response->headers->get('Location'))
            ->assertOk()
            ->assertSee($order->order_number);
    }

    public function test_checkout_with_invalid_coupon_is_rejected(): void
    {
        $product = $this->product(['price' => 50000]);

        $this->addToCart($product);

        $this->guestCheckout(['coupon_code' => 'TIDAKADA'])
            ->assertSessionHasErrors('checkout');

        $this->assertSame(0, Order::count());
    }

    public function test_payment_url_requires_valid_signature(): void
    {
        $product = $this->product();
        $this->addToCart($product);
        $this->guestCheckout();

        $order = Order::first();

        $this->get(route('checkout.payment', $order->order_number))->assertForbidden();
        $this->get(route('checkout.finish', $order->order_number))->assertForbidden();
    }

    public function test_demo_payment_marks_order_paid_and_reduces_stock(): void
    {
        Queue::fake();

        $product = $this->product(['stock' => 10]);

        $this->addToCart($product, 3);
        $this->guestCheckout();

        $order = Order::first();
        $demoUrl = URL::temporarySignedRoute('checkout.demo', now()->addDays(7), [
            'orderNumber' => $order->order_number,
        ]);

        $response = $this->post($demoUrl);

        $this->assertTrue(str_starts_with(
            $response->headers->get('Location'),
            route('checkout.finish', $order->order_number)
        ));

        $order->refresh();

        $this->assertSame('paid', $order->status);
        $this->assertNotNull($order->paid_at);
        $this->assertSame(7, Product::find($product->id)->stock);
        $this->assertSame('settlement', $order->payment()->status);
        $this->assertDatabaseHas('order_status_logs', [
            'order_id' => $order->id,
            'from_status' => 'pending',
            'to_status' => 'paid',
        ]);

        Queue::assertPushed(SendOrderNotification::class);
    }

    public function test_paid_order_receipt_is_available_to_guest_via_signed_link(): void
    {
        Queue::fake();

        $this->addToCart($this->product(['price' => 200000]));
        $this->guestCheckout();

        $order = Order::firstOrFail();

        app(\App\Services\OrderService::class)->markPaid($order, [
            'transaction_id' => 'TEST-RECEIPT',
            'payment_type' => 'qris',
            'status' => 'settlement',
        ]);

        $order->refresh();

        $this->get(route('receipt.show', $order->order_number))->assertForbidden();

        $this->get($order->receiptUrl())
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee('LUNAS')
            ->assertSee('QRIS');

        $finishUrl = URL::temporarySignedRoute('checkout.finish', now()->addDays(7), [
            'orderNumber' => $order->order_number,
        ]);

        $this->get($finishUrl)->assertOk()->assertSee('Cetak / simpan struk');
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
            ->assertForbidden();

        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_cancelled_order_returns_stock(): void
    {
        Queue::fake();

        $product = $this->product(['stock' => 10]);

        $this->addToCart($product, 2);
        $this->guestCheckout();

        $order = Order::first();

        app(\App\Services\OrderService::class)->markPaid($order, [
            'transaction_id' => 'TEST-1',
            'payment_type' => 'qris',
            'status' => 'settlement',
        ]);

        $this->assertSame(8, Product::find($product->id)->stock);

        app(\App\Services\OrderService::class)->cancel($order->fresh(), 'Pembeli membatalkan');

        $this->assertSame('batal', $order->fresh()->status);
        $this->assertSame(10, Product::find($product->id)->stock);
        $this->assertSame('Pembeli membatalkan', $order->fresh()->cancel_reason);
    }
}
