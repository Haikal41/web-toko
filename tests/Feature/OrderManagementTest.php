<?php

namespace Tests\Feature;

use App\Jobs\SendOrderNotification;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\URL;
use Tests\CreatesShopData;
use Tests\TestCase;

class OrderManagementTest extends TestCase
{
    use CreatesShopData;
    use RefreshDatabase;

    private function posPayload(Product $product, array $overrides = []): array
    {
        return array_merge([
            'customer_name' => 'Pembeli Walk-in',
            'payment_method' => 'cash',
            'cash_received' => 200000,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
        ], $overrides);
    }

    private function makeOrder(array $attributes = [], ?Product $product = null): Order
    {
        $order = Order::create(array_merge([
            'order_number' => 'MVA-20260101-0001',
            'user_id' => null,
            'customer_name' => 'Pembeli Toko',
            'customer_phone' => '081200000003',
            'customer_address' => 'Ambil di toko',
            'subtotal' => 50000,
            'discount' => 0,
            'shipping_cost' => 0,
            'total' => 50000,
            'status' => 'paid',
            'channel' => 'pos',
        ], $attributes));

        if ($product) {
            $order->items()->create([
                'product_id' => $product->id,
                'product_name' => $product->name,
                'price' => 25000,
                'quantity' => 2,
                'subtotal' => 50000,
            ]);
        }

        return $order;
    }

    public function test_kasir_can_create_cash_pos_order_and_stock_is_reduced(): void
    {
        Queue::fake();

        $kasir = $this->kasir();
        $product = $this->product(['price' => 50000, 'stock' => 10]);

        $response = $this->actingAs($kasir)->post(route('admin.pos.store'), $this->posPayload($product));

        $order = Order::first();

        $this->assertNotNull($order);
        $response->assertRedirect(route('admin.orders.receipt', ['order' => $order, 'auto' => 1]));

        $this->assertSame('pos', $order->channel);
        $this->assertSame('paid', $order->status);
        $this->assertSame('pickup', $order->fulfillment);
        $this->assertSame($kasir->id, $order->created_by);
        $this->assertNull($order->user_id);
        $this->assertEquals(100000, (float) $order->total);
        $this->assertEquals(0, (float) $order->shipping_cost);
        $this->assertEquals(200000, (float) $order->cash_received);
        $this->assertEquals(100000, (float) $order->change_amount);
        $this->assertSame(8, Product::find($product->id)->stock);
        $this->assertSame('cash', $order->payment()->payment_type);
        $this->assertDatabaseHas('order_status_logs', [
            'order_id' => $order->id,
            'user_id' => $kasir->id,
            'to_status' => 'paid',
        ]);
    }

    public function test_qris_pos_order_is_pending_with_payment_link(): void
    {
        $kasir = $this->kasir();
        $product = $this->product(['price' => 50000, 'stock' => 10]);

        $response = $this->actingAs($kasir)->post(route('admin.pos.store'), $this->posPayload($product, [
            'customer_phone' => '081298765432',
            'payment_method' => 'qris',
        ]));

        $order = Order::first();

        $this->assertNotNull($order);
        $response->assertRedirect(route('admin.orders.show', $order));
        $response->assertSessionHas('payment_link');

        $this->assertSame('pending', $order->status);
        $this->assertSame('pickup', $order->fulfillment);
        $this->assertNull($order->cash_received);
        $this->assertSame(10, Product::find($product->id)->stock);
        $this->assertSame(0, $order->statusLogs()->count());
    }

    public function test_cash_pos_order_requires_enough_cash_and_does_not_create_order(): void
    {
        $kasir = $this->kasir();
        $product = $this->product(['price' => 50000]);

        $this->actingAs($kasir)
            ->post(route('admin.pos.store'), $this->posPayload($product, ['cash_received' => 90000]))
            ->assertSessionHasErrors('items');

        $this->assertSame(0, Order::count());
        $this->assertSame(10, Product::find($product->id)->stock);
    }

    public function test_delivery_pos_order_stores_fulfillment_and_has_shipping_label(): void
    {
        Queue::fake();

        $kasir = $this->kasir();
        $product = $this->product(['price' => 50000]);

        $this->actingAs($kasir)->post(route('admin.pos.store'), $this->posPayload($product, [
            'customer_name' => 'Rina Kirim',
            'customer_phone' => '081277788899',
            'delivery' => 1,
            'customer_address' => 'Jl. Kenanga No. 8, Bandung 40211',
            'cash_received' => 200000,
        ]));

        $order = Order::firstOrFail();

        $this->assertSame('delivery', $order->fulfillment);
        $this->assertEquals(15000, (float) $order->shipping_cost);
        $this->assertEquals(115000, (float) $order->total);
        $this->assertEquals(85000, (float) $order->change_amount);

        $this->actingAs($kasir)
            ->get(route('admin.orders.shipping-label', $order))
            ->assertOk()
            ->assertSee('LABEL PENGIRIMAN INTERNAL')
            ->assertSee('Rina Kirim')
            ->assertSee('Jl. Kenanga No. 8')
            ->assertSee($order->order_number)
            ->assertDontSee('Rp');
    }

    public function test_shipping_label_is_not_available_for_pickup_orders(): void
    {
        $kasir = $this->kasir();
        $order = $this->makeOrder(['fulfillment' => 'pickup'], $this->product());

        $this->actingAs($kasir)
            ->get(route('admin.orders.shipping-label', $order))
            ->assertNotFound();
    }

    public function test_cash_receipt_shows_cash_received_and_change(): void
    {
        $kasir = $this->kasir();
        $order = $this->makeOrder([
            'cash_received' => 60000,
            'change_amount' => 10000,
        ], $this->product());

        $this->actingAs($kasir)
            ->get(route('admin.orders.receipt', ['order' => $order, 'auto' => 1]))
            ->assertOk()
            ->assertSee('Tunai diterima')
            ->assertSee('Kembalian')
            ->assertSee('Rp60.000')
            ->assertSee('Rp10.000')
            ->assertSee('window.print()', false);
    }

    public function test_public_receipt_requires_signed_url(): void
    {
        $order = $this->makeOrder([], $this->product());

        $this->get(route('receipt.show', $order->order_number))->assertForbidden();

        $signedUrl = URL::temporarySignedRoute('receipt.show', now()->addDays(30), [
            'orderNumber' => $order->order_number,
        ]);

        $this->get($signedUrl)
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee('LUNAS');
    }

    public function test_cashier_can_resend_receipt_via_whatsapp(): void
    {
        Queue::fake();

        $kasir = $this->kasir();
        $order = $this->makeOrder(['status' => 'paid'], $this->product());

        $this->actingAs($kasir)
            ->post(route('admin.orders.resend-receipt', $order))
            ->assertRedirect()
            ->assertSessionHas('success');

        Queue::assertPushed(SendOrderNotification::class, function (SendOrderNotification $job) use ($order) {
            return $job->order->is($order) && $job->resend === true;
        });
    }

    public function test_receipt_cannot_be_resent_for_pending_order(): void
    {
        Queue::fake();

        $kasir = $this->kasir();
        $order = $this->makeOrder(['status' => 'pending'], $this->product());

        $this->actingAs($kasir)
            ->post(route('admin.orders.resend-receipt', $order))
            ->assertSessionHas('error');

        Queue::assertNothingPushed();
    }

    public function test_pos_order_requires_personalization_text_when_needed(): void
    {
        $kasir = $this->kasir();
        $product = $this->product(['requires_personalization' => true]);

        $this->actingAs($kasir)
            ->post(route('admin.pos.store'), $this->posPayload($product))
            ->assertSessionHasErrors('items.0.personalization_text');

        $this->assertSame(0, Order::count());
    }

    public function test_kasir_can_cancel_paid_order_and_stock_is_restored(): void
    {
        $kasir = $this->kasir();
        $product = $this->product(['stock' => 5]);
        $order = $this->makeOrder(['status' => 'paid'], $product);

        $this->actingAs($kasir)
            ->post(route('admin.orders.cancel', $order), ['reason' => 'Salah input varian'])
            ->assertRedirect();

        $order->refresh();

        $this->assertSame('batal', $order->status);
        $this->assertSame($kasir->id, $order->cancelled_by);
        $this->assertNotNull($order->cancelled_at);
        $this->assertSame('Salah input varian', $order->cancel_reason);
        $this->assertSame(7, Product::find($product->id)->stock);
        $this->assertDatabaseHas('order_status_logs', [
            'order_id' => $order->id,
            'user_id' => $kasir->id,
            'from_status' => 'paid',
            'to_status' => 'batal',
        ]);
    }

    public function test_kasir_cannot_cancel_order_that_is_already_shipped(): void
    {
        $kasir = $this->kasir();
        $product = $this->product();
        $order = $this->makeOrder(['status' => 'dikirim'], $product);

        $this->actingAs($kasir)
            ->post(route('admin.orders.cancel', $order), ['reason' => 'Coba batalkan'])
            ->assertForbidden();

        $this->assertSame('dikirim', $order->fresh()->status);
    }

    public function test_owner_can_cancel_order_beyond_kasir_limit(): void
    {
        $owner = $this->owner();
        $product = $this->product(['stock' => 5]);
        $order = $this->makeOrder(['status' => 'dikirim'], $product);

        $this->actingAs($owner)
            ->post(route('admin.orders.cancel', $order), ['reason' => 'Barang retur'])
            ->assertRedirect();

        $this->assertSame('batal', $order->fresh()->status);
        $this->assertSame($owner->id, $order->fresh()->cancelled_by);
        $this->assertSame(7, Product::find($product->id)->stock);
    }

    public function test_cancel_requires_reason(): void
    {
        $kasir = $this->kasir();
        $order = $this->makeOrder(['status' => 'pending'], $this->product());

        $this->actingAs($kasir)
            ->post(route('admin.orders.cancel', $order), [])
            ->assertSessionHasErrors('reason');

        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_kasir_can_view_order_receipt(): void
    {
        $kasir = $this->kasir();
        $order = $this->makeOrder([], $this->product());

        $this->actingAs($kasir)
            ->get(route('admin.orders.receipt', $order))
            ->assertOk()
            ->assertSee($order->order_number);
    }

    public function test_guest_can_track_order_by_number_and_phone(): void
    {
        $order = $this->makeOrder([
            'status' => 'diproses',
            'channel' => 'online',
            'customer_phone' => '081298765432',
        ]);

        $this->post(route('track.lookup'), [
            'order_number' => $order->order_number,
            'phone' => '081298765432',
        ])->assertOk()
            ->assertSee($order->order_number)
            ->assertSee('Cetak / simpan struk pembayaran');

        $this->post(route('track.lookup'), [
            'order_number' => $order->order_number,
            'phone' => '+62 812-9876-5432',
        ])->assertOk()->assertSee($order->order_number);
    }

    public function test_tracking_rejects_wrong_phone(): void
    {
        $order = $this->makeOrder([
            'channel' => 'online',
            'customer_phone' => '081298765432',
        ]);

        $this->post(route('track.lookup'), [
            'order_number' => $order->order_number,
            'phone' => '081200000000',
        ])->assertSessionHasErrors('order_number');
    }

    public function test_orders_can_be_filtered_by_channel(): void
    {
        $owner = $this->owner();

        $this->makeOrder(['order_number' => 'MVA-20260101-0001', 'channel' => 'online']);
        $this->makeOrder(['order_number' => 'MVA-20260101-0002', 'channel' => 'pos']);

        $this->actingAs($owner)
            ->get(route('admin.orders.index', ['channel' => 'pos']))
            ->assertOk()
            ->assertSee('MVA-20260101-0002')
            ->assertDontSee('MVA-20260101-0001');
    }

    public function test_kasir_cannot_cancel_shipped_order_via_status_update(): void
    {
        $kasir = $this->kasir();
        $order = $this->makeOrder(['status' => 'dikirim'], $this->product());

        $this->actingAs($kasir)
            ->patch(route('admin.orders.status', $order), ['status' => 'batal'])
            ->assertForbidden();

        $this->assertSame('dikirim', $order->fresh()->status);
    }

    public function test_manual_paid_status_reduces_stock_and_sets_paid_at(): void
    {
        Queue::fake();

        $kasir = $this->kasir();
        $product = $this->product(['stock' => 5]);
        $order = $this->makeOrder(['status' => 'pending'], $product);

        $this->actingAs($kasir)
            ->patch(route('admin.orders.status', $order), ['status' => 'paid'])
            ->assertRedirect();

        $order->refresh();

        $this->assertSame('paid', $order->status);
        $this->assertNotNull($order->paid_at);
        $this->assertSame(3, Product::find($product->id)->stock);
    }

    public function test_status_update_is_recorded_in_audit_log(): void
    {
        $kasir = $this->kasir();
        $order = $this->makeOrder(['status' => 'paid'], $this->product());

        $this->actingAs($kasir)
            ->patch(route('admin.orders.status', $order), [
                'status' => 'diproses',
                'note' => 'Sedang dikerjakan',
            ])
            ->assertRedirect();

        $this->assertSame('diproses', $order->fresh()->status);
        $this->assertDatabaseHas('order_status_logs', [
            'order_id' => $order->id,
            'user_id' => $kasir->id,
            'from_status' => 'paid',
            'to_status' => 'diproses',
            'note' => 'Sedang dikerjakan',
        ]);
    }
}
