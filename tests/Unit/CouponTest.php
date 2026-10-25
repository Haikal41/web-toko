<?php

namespace Tests\Unit;

use App\Models\Coupon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponTest extends TestCase
{
    use RefreshDatabase;

    private function coupon(array $attributes = []): Coupon
    {
        return Coupon::create(array_merge([
            'code' => 'HEMAT',
            'type' => 'percentage',
            'value' => 10,
            'min_purchase' => 50000,
            'max_uses' => 10,
            'used_count' => 0,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addMonth(),
            'is_active' => true,
        ], $attributes));
    }

    public function test_percentage_discount(): void
    {
        $coupon = $this->coupon();

        $this->assertEquals(10000, $coupon->discountFor(100000));
    }

    public function test_fixed_discount_is_capped_at_subtotal(): void
    {
        $coupon = $this->coupon(['type' => 'fixed', 'value' => 50000]);

        $this->assertEquals(50000, $coupon->discountFor(100000));
        $this->assertEquals(20000, $coupon->discountFor(20000));
    }

    public function test_minimum_purchase_is_enforced(): void
    {
        $coupon = $this->coupon();

        $this->assertFalse($coupon->isValidFor(49999));
        $this->assertTrue($coupon->isValidFor(50000));
    }

    public function test_inactive_expired_and_exhausted_coupons_are_invalid(): void
    {
        $this->assertFalse($this->coupon(['code' => 'A', 'is_active' => false])->isValidFor(100000));
        $this->assertFalse($this->coupon(['code' => 'B', 'expires_at' => now()->subDay()])->isValidFor(100000));
        $this->assertFalse($this->coupon(['code' => 'C', 'starts_at' => now()->addDay()])->isValidFor(100000));
        $this->assertFalse($this->coupon(['code' => 'D', 'max_uses' => 1, 'used_count' => 1])->isValidFor(100000));
    }
}
