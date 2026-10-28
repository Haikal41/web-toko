<?php

namespace Tests\Feature;

use App\Jobs\SendOrderNotification;
use App\Models\Order;
use App\Models\Product;
use App\Models\WhatsappLog;
use App\Services\WhatsappService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\CreatesShopData;
use Tests\TestCase;

class ReceiptNotificationTest extends TestCase
{
    use CreatesShopData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'whatsapp.provider' => 'log',
            'whatsapp.api_key' => null,
            'whatsapp.admin_number' => null,
        ]);
    }

    private function order(array $attributes = [], ?Product $product = null): Order
    {
        $product ??= $this->product(['price' => 50000]);

        $order = Order::create(array_merge([
            'order_number' => 'MVA-20260101-0001',
            'user_id' => null,
            'customer_name' => 'Pembeli Tunai',
            'customer_phone' => '081200000003',
            'customer_address' => 'Ambil di toko',
            'subtotal' => 100000,
            'discount' => 0,
            'shipping_cost' => 0,
            'total' => 100000,
            'status' => 'paid',
            'channel' => 'pos',
            'fulfillment' => 'pickup',
            'cash_received' => 100000,
            'change_amount' => 0,
            'paid_at' => now(),
        ], $attributes));

        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => 50000,
            'quantity' => 2,
            'subtotal' => 100000,
        ]);

        $order->payments()->create([
            'transaction_id' => 'POS-1',
            'payment_type' => 'cash',
            'gross_amount' => 100000,
            'status' => 'settlement',
            'paid_at' => now(),
        ]);

        return $order;
    }

    public function test_automatic_notification_contains_receipt_link_and_cash_details(): void
    {
        $order = $this->order([
            'cash_received' => 150000,
            'change_amount' => 50000,
        ]);

        (new SendOrderNotification($order))->handle(app(WhatsappService::class));

        $log = WhatsappLog::where('order_id', $order->id)->firstOrFail();

        $this->assertSame('customer', $log->recipient_type);
        $this->assertSame(WhatsappLog::TYPE_PAYMENT_RECEIPT, $log->message_type);
        $this->assertSame('sent', $log->status);
        $this->assertStringContainsString('Tunai diterima: Rp150.000', $log->message);
        $this->assertStringContainsString('Kembalian: Rp50.000', $log->message);
        $this->assertStringContainsString('Ambil di toko', $log->message);

        preg_match('/https?:\/\/\S+struk\/\S+/', $log->message, $matches);

        $this->assertNotEmpty($matches, 'Pesan WhatsApp harus memuat tautan struk.');
        $this->get($matches[0])->assertOk()->assertSee($order->order_number)->assertSee('LUNAS');
    }

    public function test_automatic_notification_is_not_duplicated(): void
    {
        $order = $this->order();

        (new SendOrderNotification($order))->handle(app(WhatsappService::class));
        (new SendOrderNotification($order))->handle(app(WhatsappService::class));

        $this->assertSame(1, WhatsappLog::where('order_id', $order->id)->count());
    }

    public function test_manual_resend_creates_new_log_each_time(): void
    {
        $order = $this->order();

        (new SendOrderNotification($order, true))->handle(app(WhatsappService::class));
        (new SendOrderNotification($order, true))->handle(app(WhatsappService::class));

        $logs = WhatsappLog::where('order_id', $order->id)->get();

        $this->assertCount(2, $logs);
        $this->assertSame(
            [WhatsappLog::TYPE_MANUAL_RESEND, WhatsappLog::TYPE_MANUAL_RESEND],
            $logs->pluck('message_type')->all()
        );
    }

    public function test_pending_and_cancelled_orders_get_no_notification(): void
    {
        $product = $this->product(['price' => 50000]);

        $pending = $this->order(['status' => 'pending', 'paid_at' => null], $product);
        $cancelled = $this->order(['order_number' => 'MVA-20260101-0002', 'status' => 'batal'], $product);

        (new SendOrderNotification($pending))->handle(app(WhatsappService::class));
        (new SendOrderNotification($cancelled))->handle(app(WhatsappService::class));

        $this->assertSame(0, WhatsappLog::count());
    }
}
