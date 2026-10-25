<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HelpersTest extends TestCase
{
    use RefreshDatabase;

    public function test_rupiah_formats_amount(): void
    {
        $this->assertSame('Rp25.000', rupiah(25000));
        $this->assertSame('Rp1.250.000', rupiah(1250000));
        $this->assertSame('Rp0', rupiah(null));
    }

    public function test_wa_link_normalizes_number(): void
    {
        $this->assertSame('https://wa.me/6281234567890', wa_link('081234567890'));
        $this->assertSame('https://wa.me/6281234567890', wa_link('+62 812-3456-7890'));
        $this->assertStringContainsString('text=', wa_link('081234567890', 'Halo dunia'));
    }

    public function test_store_name_falls_back_to_default(): void
    {
        $this->assertSame('Multiverse Aksesoris', store_name());
    }
}
