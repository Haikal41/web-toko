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

    public function test_registration_page_is_not_available(): void
    {
        $this->get('/register')->assertNotFound();
    }

    public function test_owner_can_login_and_is_redirected_to_dashboard(): void
    {
        $owner = $this->owner(['email' => 'boss@test.local']);

        $response = $this->post(route('login.store'), [
            'email' => 'boss@test.local',
            'password' => 'rahasia123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($owner);
    }

    public function test_kasir_login_redirects_to_admin_dashboard(): void
    {
        $kasir = $this->kasir(['email' => 'kasir@test.local']);

        $this->post(route('login.store'), [
            'email' => 'kasir@test.local',
            'password' => 'rahasia123',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($kasir);
    }

    public function test_login_with_wrong_password_fails(): void
    {
        $this->owner(['email' => 'boss@test.local']);

        $this->post(route('login.store'), [
            'email' => 'boss@test.local',
            'password' => 'salah-sekali',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_inactive_account_cannot_login(): void
    {
        $this->owner(['email' => 'matih@test.local', 'is_active' => false]);

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
