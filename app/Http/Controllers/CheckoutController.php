<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Services\CartService;
use App\Services\MidtransService;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use RuntimeException;

class CheckoutController extends Controller
{
    public function __construct(
        private CartService $cart,
        private OrderService $orders,
        private MidtransService $midtrans,
    ) {}

    public function index()
    {
        $items = $this->cart->items();

        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->withErrors(['cart' => 'Keranjang Anda masih kosong.']);
        }

        $addresses = auth()->check()
            ? auth()->user()->addresses()->orderByDesc('is_default')->get()
            : collect();

        $shipping = (float) setting('shipping_flat', 15000);

        return view('checkout.index', [
            'items' => $items,
            'subtotal' => $items->sum('line_total'),
            'shipping' => $shipping,
            'addresses' => $addresses,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'address_id' => ['nullable', 'integer'],
            'recipient_name' => ['required_without:address_id', 'nullable', 'string', 'max:100'],
            'phone' => ['required_without:address_id', 'nullable', 'string', 'max:20'],
            'address' => ['required_without:address_id', 'nullable', 'string', 'max:500'],
            'city' => ['required_without:address_id', 'nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'notes' => ['nullable', 'string', 'max:500'],
            'coupon_code' => ['nullable', 'string', 'max:30'],
        ]);

        $address = null;

        if (! empty($data['address_id'])) {
            if (! $user) {
                return back()->withErrors(['checkout' => 'Silakan isi alamat pengiriman.'])->withInput();
            }

            $address = $user->addresses()->findOrFail($data['address_id']);
        } elseif ($user) {
            $isFirst = $user->addresses()->count() === 0;

            $address = $user->addresses()->create([
                'recipient_name' => $data['recipient_name'],
                'phone' => $data['phone'],
                'address' => $data['address'],
                'city' => $data['city'],
                'postal_code' => $data['postal_code'] ?? null,
                'is_default' => $isFirst,
            ]);
        }

        try {
            $order = $this->orders->create($user, [
                'address_id' => $address?->id,
                'customer_name' => $address?->recipient_name ?? $data['recipient_name'],
                'customer_phone' => $address?->phone ?? $data['phone'],
                'customer_address' => $address
                    ? trim($address->address.', '.$address->city.' '.$address->postal_code)
                    : trim($data['address'].', '.$data['city'].' '.($data['postal_code'] ?? '')),
                'shipping_cost' => (float) setting('shipping_flat', 15000),
                'coupon_code' => $data['coupon_code'] ?? null,
                'notes' => $data['notes'] ?? null,
                'channel' => 'online',
            ]);
        } catch (RuntimeException $e) {
            return back()->withErrors(['checkout' => $e->getMessage()])->withInput();
        }

        return redirect()->to($this->paymentUrl($order));
    }

    public function payment(Request $request, string $orderNumber)
    {
        $order = $this->findAccessibleOrder($request, $orderNumber)->load('items');

        if ($order->status !== 'pending') {
            return redirect()->to($this->finishUrl($order));
        }

        $snapToken = null;

        if ($this->midtrans->isConfigured() && ! $order->payments()->whereNotNull('transaction_id')->exists()) {
            $snapToken = $this->midtrans->createSnapToken($order);
        }

        return view('checkout.payment', [
            'order' => $order,
            'snapToken' => $snapToken,
            'finishUrl' => $this->finishUrl($order),
            'demoUrl' => URL::temporarySignedRoute('checkout.demo', now()->addDays(7), ['orderNumber' => $order->order_number]),
        ]);
    }

    public function demoPay(Request $request, string $orderNumber)
    {
        $order = $this->findAccessibleOrder($request, $orderNumber);

        if ($this->midtrans->isConfigured() && ! app()->environment('local')) {
            abort(403, 'Pembayaran demo tidak tersedia.');
        }

        $this->orders->markPaid($order, [
            'transaction_id' => 'DEMO-'.strtoupper(uniqid()),
            'payment_type' => 'demo',
            'gross_amount' => $order->total,
            'status' => 'settlement',
            'raw_response' => ['source' => 'demo-payment'],
        ], $request->user());

        return redirect()->to($this->finishUrl($order))
            ->with('success', 'Pembayaran demo berhasil. Notifikasi WhatsApp diproses.');
    }

    public function finish(Request $request, string $orderNumber)
    {
        $order = $this->findAccessibleOrder($request, $orderNumber)
            ->load(['items', 'payments', 'statusLogs']);

        return view('checkout.finish', [
            'order' => $order,
            'paymentUrl' => $order->status === 'pending' ? $this->paymentUrl($order) : null,
            'receiptUrl' => $order->hasReceipt() ? $order->receiptUrl() : null,
        ]);
    }

    /**
     * Tamu mengakses pesanan lewat signed URL. Pembeli lama yang login tetap
     * bisa membuka pesanannya sendiri tanpa signature.
     */
    private function findAccessibleOrder(Request $request, string $orderNumber): Order
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();

        $user = $request->user();

        if ($user && ($order->user_id === $user->id || $user->isAdmin())) {
            return $order;
        }

        if ($request->hasValidSignature()) {
            return $order;
        }

        abort(403, 'Tautan pesanan tidak valid atau sudah kedaluwarsa.');
    }

    private function paymentUrl(Order $order): string
    {
        return URL::temporarySignedRoute('checkout.payment', now()->addDays(7), [
            'orderNumber' => $order->order_number,
        ]);
    }

    private function finishUrl(Order $order): string
    {
        return URL::temporarySignedRoute('checkout.finish', now()->addDays(7), [
            'orderNumber' => $order->order_number,
        ]);
    }
}
