<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappService
{
    public function normalizeNumber(string $number): string
    {
        $digits = preg_replace('/[^0-9]/', '', $number);

        if (str_starts_with($digits, '0')) {
            return '62'.substr($digits, 1);
        }

        if (str_starts_with($digits, '8')) {
            return '62'.$digits;
        }

        return $digits;
    }

    /**
     * @return array{ok:bool, response:string}
     */
    public function send(string $target, string $message): array
    {
        $target = $this->normalizeNumber($target);

        if ($target === '') {
            return ['ok' => false, 'response' => 'Nomor tujuan kosong'];
        }

        $apiKey = config('whatsapp.api_key');

        if (config('whatsapp.provider') === 'log' || blank($apiKey)) {
            Log::info('WhatsApp (simulasi) ke '.$target.': '.$message);

            return ['ok' => true, 'response' => 'SIMULASI: WA_API_KEY belum diisi, pesan hanya dicatat di log.'];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $apiKey,
            ])->timeout(20)->post(config('whatsapp.fonnte_url'), [
                'target' => $target,
                'message' => $message,
                'countryCode' => '62',
            ]);

            return [
                'ok' => $response->successful(),
                'response' => trim($response->status().' '.$response->body()),
            ];
        } catch (\Throwable $e) {
            return ['ok' => false, 'response' => 'Exception: '.$e->getMessage()];
        }
    }
}
