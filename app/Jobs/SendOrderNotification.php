<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\WhatsappLog;
use App\Services\WhatsappService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RuntimeException;

class SendOrderNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [60, 300, 900];

    public function __construct(public Order $order) {}

    public function handle(WhatsappService $whatsapp): void
    {
        $order = $this->order->fresh(['items']);

        if (! $order || $order->status === 'pending' || $order->status === 'batal') {
            return;
        }

        $messages = [
            'customer' => [
                'recipient' => $order->customer_phone,
                'message' => $this->customerMessage($order),
            ],
            'admin' => [
                'recipient' => setting('wa_admin_number') ?: config('whatsapp.admin_number'),
                'message' => $this->adminMessage($order),
            ],
        ];

        $failed = false;

        foreach ($messages as $type => $payload) {
            if (blank($payload['recipient'])) {
                continue;
            }

            $log = WhatsappLog::firstOrCreate(
                [
                    'order_id' => $order->id,
                    'recipient_type' => $type,
                ],
                [
                    'recipient' => $whatsapp->normalizeNumber($payload['recipient']),
                    'message' => $payload['message'],
                    'status' => 'queued',
                ]
            );

            if ($log->status === 'sent') {
                continue;
            }

            $result = $whatsapp->send($payload['recipient'], $payload['message']);

            $log->update([
                'recipient' => $whatsapp->normalizeNumber($payload['recipient']),
                'message' => $payload['message'],
                'status' => $result['ok'] ? 'sent' : 'failed',
                'provider_response' => $result['response'],
                'attempts' => $log->attempts + 1,
                'sent_at' => $result['ok'] ? now() : null,
            ]);

            if (! $result['ok']) {
                $failed = true;
            }
        }

        if ($failed) {
            throw new RuntimeException('Sebagian notifikasi WhatsApp gagal dikirim.');
        }
    }

    public function failed(?\Throwable $exception): void
    {
        $order = $this->order->fresh();

        if (! $order) {
            return;
        }

        WhatsappLog::where('order_id', $order->id)
            ->where('status', 'queued')
            ->update(['status' => 'failed']);

        try {
            Mail::raw(
                'Notifikasi WhatsApp untuk pesanan '.$order->order_number.' gagal dikirim setelah beberapa percobaan. Silakan cek dashboard dan hubungi pembeli secara manual.',
                fn ($message) => $message->to(config('mail.from.address'))->subject('WA gagal: '.$order->order_number)
            );
        } catch (\Throwable $e) {
            Log::warning('Fallback email gagal: '.$e->getMessage());
        }
    }

    private function customerMessage(Order $order): string
    {
        $lines = $order->items->map(function ($item) {
            $line = '- '.$item->quantity.'x '.$item->product_name;

            if ($item->variant_name) {
                $line .= ' ('.$item->variant_name.')';
            }

            if ($item->personalization_text) {
                $line .= ' | Personalisasi: "'.$item->personalization_text.'"';
            }

            return $line.' — Rp'.number_format((float) $item->subtotal, 0, ',', '.');
        })->implode("\n");

        return "Halo {$order->customer_name}, pembayaran pesanan {$order->order_number} di Multiverse Aksesoris sudah kami terima.\n\n"
            ."Detail pesanan:\n{$lines}\n\n"
            .'Total: Rp'.number_format((float) $order->total, 0, ',', '.')."\n\n"
            .'Pesanan akan segera kami proses. Terima kasih sudah berbelanja!';
    }

    private function adminMessage(Order $order): string
    {
        return "Pesanan baru LUNAS {$order->order_number}\n"
            ."Pembeli: {$order->customer_name} ({$order->customer_phone})\n"
            .'Jumlah item: '.$order->items->sum('quantity')."\n"
            .'Total: Rp'.number_format((float) $order->total, 0, ',', '.')."\n\n"
            .'Buka dashboard: '.route('admin.dashboard');
    }
}
