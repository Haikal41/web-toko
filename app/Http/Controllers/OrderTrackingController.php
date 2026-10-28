<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class OrderTrackingController extends Controller
{
    public function index()
    {
        return view('track.index');
    }

    public function lookup(Request $request)
    {
        $data = $request->validate([
            'order_number' => ['required', 'string', 'max:30'],
            'phone' => ['required', 'string', 'max:20'],
        ]);

        $order = Order::with(['items', 'statusLogs', 'payments'])
            ->where('order_number', strtoupper(trim($data['order_number'])))
            ->first();

        if (! $order || ! $this->phoneMatches($order->customer_phone, $data['phone'])) {
            return back()
                ->withErrors(['order_number' => 'Pesanan tidak ditemukan. Periksa nomor pesanan dan nomor WhatsApp Anda.'])
                ->withInput();
        }

        $paymentUrl = null;

        if ($order->status === 'pending') {
            $paymentUrl = URL::temporarySignedRoute('checkout.payment', now()->addDays(7), [
                'orderNumber' => $order->order_number,
            ]);
        }

        $receiptUrl = $order->hasReceipt() ? $order->receiptUrl() : null;

        return view('track.show', compact('order', 'paymentUrl', 'receiptUrl'));
    }

    private function phoneMatches(?string $stored, string $input): bool
    {
        $stored = $this->normalizePhone($stored);
        $input = $this->normalizePhone($input);

        if ($stored === '' || $input === '') {
            return false;
        }

        return $stored === $input
            || str_ends_with($stored, substr($input, -9))
            || str_ends_with($input, substr($stored, -9));
    }

    private function normalizePhone(?string $phone): string
    {
        $digits = preg_replace('/\D/', '', (string) $phone);

        if (str_starts_with($digits, '62')) {
            $digits = '0'.substr($digits, 2);
        }

        return $digits;
    }
}
