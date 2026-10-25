<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\CreatesShopData;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use CreatesShopData;
    use RefreshDatabase;

    public function test_guest_can_register_and_is_logged_in(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Pembeli Baru',
            'email' => 'baru@test.local',
            'phone' => '081288888888',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticated();

        $user = User::where('email', 'baru@test.local')->first();

        $this->assertNotNull($user);
        $this->assertSame('customer', $user->role);
    }

    public function test_registration_rejects_duplicate_email(): void
    {
        $this->customer(['email' => 'ada@test.local']);

        $this->post(route('register.store'), [
            'name' => 'Pembeli Baru',
            'email' => 'ada@test.local',
            'phone' => '081288888888',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])->assertSessionHasErrors('email');
    }

    public function test_admin_can_login_and_is_redirected_to_dashboard(): void
    {
        $admin = $this->admin(['email' => 'boss@test.local']);

        $response = $this->post(route('login.store'), [
            'email' => 'boss@test.local',
            'password' => 'rahasia123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_employee_login_redirects_to_attendance(): void
    {
        $this->employee(['email' => 'staff@test.local']);

        $this->post(route('login.store'), [
            'email' => 'staff@test.local',
            'password' => 'rahasia123',
        ])->assertRedirect(route('attendance.index'));
    }

    public function test_login_with_wrong_password_fails(): void
    {
        $this->admin(['email' => 'boss@test.local']);

        $this->post(route('login.store'), [
            'email' => 'boss@test.local',
            'password' => 'salah-sekali',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_inactive_account_cannot_login(): void
    {
        $this->admin(['email' => 'matih@test.local', 'is_active' => false]);

        $this->post(route('login.store'), [
            'email' => 'matih@test.local',
            'password' => 'rahasia123',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_user_can_logout(): void
    {
        $customer = $this->customer();

        $this->actingAs($customer)
            ->post(route('logout'))
            ->assertRedirect(route('home'));

        $this->assertGuest();
    }

    public function test_password_is_hashed(): void
    {
        $user = $this->customer();

        $this->assertNotSame('rahasia123', $user->password);
        $this->assertTrue(Hash::check('rahasia123', $user->password));
    }
}
