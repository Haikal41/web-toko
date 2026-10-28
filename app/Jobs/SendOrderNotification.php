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

    public function __construct(public Order $order, public bool $resend = false) {}

    public function handle(WhatsappService $whatsapp): void
    {
        $order = $this->order->fresh(['items', 'payments']);

        if (! $order || in_array($order->status, ['pending', 'batal'], true)) {
            return;
        }

        $messageType = $this->resend
            ? WhatsappLog::TYPE_MANUAL_RESEND
            : WhatsappLog::TYPE_PAYMENT_RECEIPT;

        $messages = [
            'customer' => [
                'recipient' => $order->customer_phone,
                'message' => $this->customerMessage($order),
            ],
        ];

        if (! $this->resend) {
            $messages['admin'] = [
                'recipient' => setting('wa_admin_number') ?: config('whatsapp.admin_number'),
                'message' => $this->adminMessage($order),
            ];
        }

        $failed = false;

        foreach ($messages as $type => $payload) {
            if (blank($payload['recipient'])) {
                continue;
            }

            // Notifikasi otomatis hanya sekali per pesanan. Kirim ulang manual
            // selalu membuat log baru agar setiap aksi tercatat.
            $log = $this->resend
                ? new WhatsappLog([
                    'order_id' => $order->id,
                    'recipient_type' => $type,
                    'message_type' => $messageType,
                    'recipient' => $whatsapp->normalizeNumber($payload['recipient']),
                    'message' => $payload['message'],
                    'status' => 'queued',
                ])
                : WhatsappLog::firstOrCreate(
                    [
                        'order_id' => $order->id,
                        'recipient_type' => $type,
                        'message_type' => $messageType,
                    ],
                    [
                        'recipient' => $whatsapp->normalizeNumber($payload['recipient']),
                        'message' => $payload['message'],
                        'status' => 'queued',
                    ]
                );

            if ($log->exists && $log->status === 'sent') {
                continue;
            }

            $result = $whatsapp->send($payload['recipient'], $payload['message']);

            $log->fill([
                'recipient' => $whatsapp->normalizeNumber($payload['recipient']),
                'message_type' => $messageType,
                'message' => $payload['message'],
                'status' => $result['ok'] ? 'sent' : 'failed',
                'provider_response' => $result['response'],
                'attempts' => $log->attempts + 1,
                'sent_at' => $result['ok'] ? now() : null,
            ])->save();

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

        $message = "Halo {$order->customer_name}, pembayaran pesanan {$order->order_number} di ".store_name()." sudah kami terima.\n\n"
            ."Detail pesanan:\n{$lines}\n\n"
            .'Total: Rp'.number_format((float) $order->total, 0, ',', '.')."\n"
            .'Pembayaran: '.$this->paymentLabel($order)."\n";

        if ($order->cash_received !== null) {
            $message .= 'Tunai diterima: Rp'.number_format((float) $order->cash_received, 0, ',', '.')."\n"
                .'Kembalian: Rp'.number_format((float) $order->change_amount, 0, ',', '.')."\n";
        }

        $message .= 'Pemenuhan: '.$order->fulfillmentLabel()."\n\n"
            .'Struk pembayaran (cetak/simpan): '.$order->receiptUrl()."\n\n"
            .'Pesanan akan segera kami proses. Terima kasih sudah berbelanja!';

        return $message;
    }

    private function adminMessage(Order $order): string
    {
        $message = "Pesanan baru LUNAS {$order->order_number}\n"
            ."Pembeli: {$order->customer_name} ({$order->customer_phone})\n"
            .'Jumlah item: '.$order->items->sum('quantity')."\n"
            .'Total: Rp'.number_format((float) $order->total, 0, ',', '.')."\n"
            .'Pembayaran: '.$this->paymentLabel($order)."\n"
            .'Pemenuhan: '.$order->fulfillmentLabel();

        if ($order->cash_received !== null) {
            $message .= "\n".'Tunai diterima: Rp'.number_format((float) $order->cash_received, 0, ',', '.')
                .' | Kembalian: Rp'.number_format((float) $order->change_amount, 0, ',', '.');
        }

        return $message."\n\n".'Buka pesanan: '.route('admin.orders.show', $order);
    }

    private function paymentLabel(Order $order): string
    {
        return match ($order->payment()?->payment_type) {
            'cash' => 'Tunai',
            'qris' => 'QRIS',
            'demo' => 'Pembayaran demo',
            'bank_transfer' => 'Transfer bank',
            'ewallet' => 'E-Wallet',
            null, '' => '-',
            default => ucfirst(str_replace('_', ' ', (string) $order->payment()?->payment_type)),
        };
    }
}
