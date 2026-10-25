<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\CreatesShopData;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use CreatesShopData;
    use RefreshDatabase;

    public function test_customer_can_view_account_page(): void
    {
        $customer = $this->customer();

        $this->actingAs($customer)
            ->get(route('account.index'))
            ->assertOk()
            ->assertSee($customer->name);
    }

    public function test_customer_can_update_profile(): void
    {
        $customer = $this->customer();

        $this->actingAs($customer)->put(route('account.profile.update'), [
            'name' => 'Nama Baru',
            'email' => $customer->email,
            'phone' => '081299999111',
        ])->assertRedirect();

        $customer->refresh();

        $this->assertSame('Nama Baru', $customer->name);
        $this->assertSame('081299999111', $customer->phone);
    }

    public function test_customer_can_add_set_default_and_delete_address(): void
    {
        $customer = $this->customer();

        $this->actingAs($customer)->post(route('account.address.store'), [
            'recipient_name' => 'Pembeli Toko',
            'phone' => '081200000003',
            'address' => 'Jl. Melati No. 12',
            'city' => 'Bandung',
            'postal_code' => '40162',
        ])->assertRedirect();

        $address = $customer->addresses()->first();

        $this->assertNotNull($address);
        $this->assertTrue((bool) $address->is_default);

        $this->actingAs($customer)->post(route('account.address.store'), [
            'recipient_name' => 'Pembeli Toko',
            'phone' => '081200000003',
            'address' => 'Jl. Kenanga No. 3',
            'city' => 'Bandung',
        ])->assertRedirect();

        $second = $customer->addresses()->where('id', '!=', $address->id)->first();

        $this->actingAs($customer)->post(route('account.address.default', $second))->assertRedirect();

        $this->assertTrue((bool) $second->fresh()->is_default);
        $this->assertFalse((bool) $address->fresh()->is_default);

        $this->actingAs($customer)->delete(route('account.address.destroy', $second))->assertRedirect();

        $this->assertNull(Address::find($second->id));
    }

    public function test_customer_cannot_touch_address_of_another_user(): void
    {
        $customer = $this->customer();
        $other = $this->customer(['email' => 'other@test.local']);

        $address = $other->addresses()->create([
            'recipient_name' => 'Other',
            'phone' => '081200000099',
            'address' => 'Jl. Elsewhere',
            'city' => 'Jakarta',
            'is_default' => true,
        ]);

        $this->actingAs($customer)
            ->delete(route('account.address.destroy', $address))
            ->assertForbidden();

        $this->assertNotNull(Address::find($address->id));
    }

    public function test_customer_can_open_own_order_detail_only(): void
    {
        $customer = $this->customer();
        $other = $this->customer(['email' => 'other@test.local']);

        $order = Order::create([
            'order_number' => 'MVA-20260101-0010',
            'user_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_phone' => $customer->phone,
            'customer_address' => 'Jl. Melati No. 12',
            'subtotal' => 50000,
            'discount' => 0,
            'shipping_cost' => 15000,
            'total' => 65000,
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $this->actingAs($customer)
            ->get(route('account.order.show', $order->order_number))
            ->assertOk()
            ->assertSee('MVA-20260101-0010');

        $this->actingAs($other)
            ->get(route('account.order.show', $order->order_number))
            ->assertNotFound();
    }
}
