<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\CreatesShopData;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use CreatesShopData;
    use RefreshDatabase;

    public function test_admin_dashboard_is_accessible(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard');
    }

    public function test_non_admin_is_forbidden_from_admin_area(): void
    {
        $employee = $this->employee();

        $this->actingAs($employee)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($this->customer())->get(route('admin.products.index'))->assertForbidden();
    }

    public function test_admin_can_create_update_and_delete_product(): void
    {
        $admin = $this->admin();
        $category = $this->category(['name' => 'Piala']);

        $this->actingAs($admin)->post(route('admin.products.store'), [
            'name' => 'Piala Emas Baru',
            'category_id' => $category->id,
            'description' => 'Piala untuk lomba.',
            'price' => 120000,
            'stock' => 15,
            'low_stock_threshold' => 3,
            'requires_personalization' => '1',
            'is_active' => '1',
        ])->assertRedirect(route('admin.products.index'));

        $product = Product::where('name', 'Piala Emas Baru')->first();

        $this->assertNotNull($product);
        $this->assertTrue($product->requires_personalization);

        $this->actingAs($admin)->put(route('admin.products.update', $product), [
            'name' => 'Piala Emas Diperbarui',
            'category_id' => $category->id,
            'price' => 130000,
            'stock' => 20,
            'low_stock_threshold' => 3,
        ])->assertRedirect(route('admin.products.index'));

        $product->refresh();

        $this->assertSame('Piala Emas Diperbarui', $product->name);
        $this->assertEquals(130000, (float) $product->price);
        $this->assertFalse($product->requires_personalization);

        $this->actingAs($admin)
            ->delete(route('admin.products.destroy', $product))
            ->assertRedirect(route('admin.products.index'));

        $this->assertNull(Product::find($product->id));
    }

    public function test_admin_can_manage_categories(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('admin.categories.store'), ['name' => 'Boneka'])
            ->assertRedirect();

        $category = Category::where('slug', 'boneka')->first();

        $this->assertNotNull($category);

        $this->actingAs($admin)
            ->put(route('admin.categories.update', $category), ['name' => 'Boneka Lucu'])
            ->assertRedirect();

        $this->assertSame('Boneka Lucu', $category->fresh()->name);

        $this->actingAs($admin)
            ->delete(route('admin.categories.destroy', $category))
            ->assertRedirect();

        $this->assertNull(Category::find($category->id));
    }

    public function test_category_with_products_cannot_be_deleted(): void
    {
        $admin = $this->admin();
        $category = $this->category();
        $this->product(['category' => $category]);

        $this->actingAs($admin)
            ->delete(route('admin.categories.destroy', $category))
            ->assertSessionHasErrors('category');

        $this->assertNotNull(Category::find($category->id));
    }

    public function test_admin_can_manage_coupons(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.coupons.store'), [
            'code' => 'diskon10',
            'type' => 'percentage',
            'value' => 10,
            'min_purchase' => 50000,
            'max_uses' => 50,
            'is_active' => '1',
        ])->assertRedirect();

        $coupon = Coupon::where('code', 'DISKON10')->first();

        $this->assertNotNull($coupon);
        $this->assertEquals(10, (float) $coupon->value);

        $this->actingAs($admin)->put(route('admin.coupons.update', $coupon), [
            'code' => 'DISKON20',
            'type' => 'percentage',
            'value' => 20,
            'min_purchase' => 50000,
            'is_active' => '1',
        ])->assertRedirect();

        $this->assertEquals(20, (float) $coupon->fresh()->value);

        $this->actingAs($admin)
            ->delete(route('admin.coupons.destroy', $coupon))
            ->assertRedirect();

        $this->assertNull(Coupon::find($coupon->id));
    }

    public function test_admin_can_manage_employees(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('admin.employees.store'), [
            'name' => 'Budi',
            'email' => 'budi@test.local',
            'phone' => '081299999999',
            'password' => 'rahasia123',
        ])->assertRedirect();

        $employee = User::where('email', 'budi@test.local')->first();

        $this->assertNotNull($employee);
        $this->assertSame('karyawan', $employee->role);

        $this->actingAs($admin)->put(route('admin.employees.update', $employee), [
            'name' => 'Budi Santoso',
            'email' => 'budi@test.local',
            'phone' => '081299999999',
            'is_active' => '1',
        ])->assertRedirect();

        $this->assertSame('Budi Santoso', $employee->fresh()->name);

        $this->actingAs($admin)
            ->delete(route('admin.employees.destroy', $employee))
            ->assertRedirect();

        $this->assertNull(User::find($employee->id));
    }

    public function test_admin_can_update_order_status(): void
    {
        $admin = $this->admin();
        $customer = $this->customer();

        $order = Order::create([
            'order_number' => 'MVA-20260101-0002',
            'user_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_phone' => $customer->phone,
            'customer_address' => 'Jl. Mawar No. 1',
            'subtotal' => 50000,
            'discount' => 0,
            'shipping_cost' => 15000,
            'total' => 65000,
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.orders.status', $order), ['status' => 'diproses'])
            ->assertRedirect();

        $this->assertSame('diproses', $order->fresh()->status);

        $this->actingAs($admin)
            ->patch(route('admin.orders.status', $order), ['status' => 'status-ngawur'])
            ->assertSessionHasErrors('status');
    }

    public function test_admin_order_list_and_detail_are_readable(): void
    {
        $admin = $this->admin();
        $customer = $this->customer();

        $order = Order::create([
            'order_number' => 'MVA-20260101-0003',
            'user_id' => $customer->id,
            'customer_name' => 'Pembeli Toko',
            'customer_phone' => '081200000003',
            'customer_address' => 'Jl. Mawar No. 1',
            'subtotal' => 50000,
            'discount' => 0,
            'shipping_cost' => 15000,
            'total' => 65000,
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('admin.orders.index'))
            ->assertOk()
            ->assertSee('MVA-20260101-0003');

        $this->actingAs($admin)
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('MVA-20260101-0003');
    }

    public function test_sales_report_and_export_are_available(): void
    {
        $admin = $this->admin();
        $customer = $this->customer();

        Order::create([
            'order_number' => 'MVA-20260101-0004',
            'user_id' => $customer->id,
            'customer_name' => 'Pembeli Toko',
            'customer_phone' => '081200000003',
            'customer_address' => 'Jl. Mawar No. 1',
            'subtotal' => 50000,
            'discount' => 0,
            'shipping_cost' => 15000,
            'total' => 65000,
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $this->actingAs($admin)->get(route('admin.reports.index'))->assertOk();

        $response = $this->actingAs($admin)->get(route('admin.reports.export'));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('Content-Type'));
        $this->assertStringContainsString('MVA-20260101-0004', $response->streamedContent());
    }

    public function test_admin_can_update_store_settings(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->put(route('admin.settings.update'), [
            'store_name' => 'Multiverse Aksesoris Test',
            'store_tagline' => 'Tagline uji',
            'shipping_flat' => 20000,
            'low_stock_threshold' => 4,
            'wa_admin_number' => '081200000999',
        ])->assertRedirect();

        $this->assertSame('Multiverse Aksesoris Test', setting('store_name'));
        $this->assertEquals(20000, (float) setting('shipping_flat'));
        $this->assertSame('081200000999', setting('wa_admin_number'));
    }

    public function test_store_logo_can_be_uploaded(): void
    {
        $admin = $this->admin();
        Storage::fake('public');

        $file = UploadedFile::fake()->image('logo.png', 200, 200);

        $this->actingAs($admin)->put(route('admin.settings.update'), [
            'store_name' => 'Multiverse Aksesoris',
            'shipping_flat' => 15000,
            'low_stock_threshold' => 5,
            'store_logo' => $file,
        ])->assertRedirect();

        $path = setting('store_logo');

        $this->assertNotNull($path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_order_list_can_be_filtered_by_date_range(): void
    {
        $admin = $this->admin();
        $customer = $this->customer();

        $old = Order::create([
            'order_number' => 'MVA-LAMA-0001',
            'user_id' => $customer->id,
            'customer_name' => 'Pembeli Toko',
            'customer_phone' => '081200000003',
            'customer_address' => 'Jl. Mawar No. 1',
            'subtotal' => 50000,
            'discount' => 0,
            'shipping_cost' => 15000,
            'total' => 65000,
            'status' => 'paid',
            'paid_at' => now()->subMonths(3),
        ]);

        $old->forceFill(['created_at' => now()->subMonths(3)])->save();

        $new = Order::create([
            'order_number' => 'MVA-BARU-0002',
            'user_id' => $customer->id,
            'customer_name' => 'Pembeli Toko',
            'customer_phone' => '081200000003',
            'customer_address' => 'Jl. Mawar No. 1',
            'subtotal' => 50000,
            'discount' => 0,
            'shipping_cost' => 15000,
            'total' => 65000,
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.orders.index', [
            'start' => now()->subDays(7)->format('Y-m-d'),
            'end' => now()->format('Y-m-d'),
        ]));

        $response->assertOk()->assertSee('MVA-BARU-0002')->assertDontSee('MVA-LAMA-0001');

        $this->assertNotNull($old->fresh());
        $this->assertNotNull($new->fresh());
    }

    public function test_product_image_can_be_set_as_primary(): void
    {
        $admin = $this->admin();
        $product = $this->product();

        $first = $product->images()->create(['path' => 'products/a.svg', 'is_primary' => true, 'sort_order' => 0]);
        $second = $product->images()->create(['path' => 'products/b.svg', 'is_primary' => false, 'sort_order' => 1]);

        $this->actingAs($admin)
            ->patch(route('admin.products.image.primary', [$product, $second]))
            ->assertRedirect();

        $this->assertFalse((bool) $first->fresh()->is_primary);
        $this->assertTrue((bool) $second->fresh()->is_primary);
    }

    public function test_report_period_switches_between_daily_monthly_and_yearly(): void
    {
        $admin = $this->admin();
        $customer = $this->customer();

        Order::create([
            'order_number' => 'MVA-20260101-0005',
            'user_id' => $customer->id,
            'customer_name' => 'Pembeli Toko',
            'customer_phone' => '081200000003',
            'customer_address' => 'Jl. Mawar No. 1',
            'subtotal' => 50000,
            'discount' => 0,
            'shipping_cost' => 15000,
            'total' => 65000,
            'status' => 'paid',
            'paid_at' => now(),
            'created_at' => now(),
        ]);

        foreach (['harian', 'bulanan', 'tahunan'] as $period) {
            $this->actingAs($admin)
                ->get(route('admin.reports.index', ['period' => $period]))
                ->assertOk()
                ->assertSee('Tren penjualan');
        }
    }
}
