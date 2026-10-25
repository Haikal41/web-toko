<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Order;
use App\Services\CartService;
use App\Services\MidtransService;
use App\Services\OrderService;
use Illuminate\Http\Request;
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

        $addresses = auth()->user()->addresses()->orderByDesc('is_default')->get();
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

        if (! empty($data['address_id'])) {
            $address = $user->addresses()->findOrFail($data['address_id']);
        } else {
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
                'address_id' => $address->id,
                'customer_name' => $address->recipient_name,
                'customer_phone' => $address->phone,
                'customer_address' => trim($address->address.', '.$address->city.' '.$address->postal_code),
                'shipping_cost' => (float) setting('shipping_flat', 15000),
                'coupon_code' => $data['coupon_code'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);
        } catch (RuntimeException $e) {
            return back()->withErrors(['checkout' => $e->getMessage()])->withInput();
        }

        return redirect()->route('checkout.payment', $order->order_number);
    }

    public function payment(string $orderNumber)
    {
        $order = Order::with('items')
            ->where('user_id', auth()->id())
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        if ($order->status !== 'pending') {
            return redirect()->route('checkout.finish', $order->order_number);
        }

        $snapToken = null;

        if ($this->midtrans->isConfigured() && ! $order->payments()->whereNotNull('transaction_id')->exists()) {
            $snapToken = $this->midtrans->createSnapToken($order);
        }

        return view('checkout.payment', compact('order', 'snapToken'));
    }

    public function demoPay(Request $request, string $orderNumber)
    {
        $order = Order::where('user_id', $request->user()->id)
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        if ($this->midtrans->isConfigured() && ! app()->environment('local')) {
            abort(403, 'Pembayaran demo tidak tersedia.');
        }

        $this->orders->markPaid($order, [
            'transaction_id' => 'DEMO-'.strtoupper(uniqid()),
            'payment_type' => 'demo',
            'gross_amount' => $order->total,
            'status' => 'settlement',
            'raw_response' => ['source' => 'demo-payment'],
        ]);

        return redirect()->route('checkout.finish', $order->order_number)
            ->with('success', 'Pembayaran demo berhasil. Notifikasi WhatsApp diproses.');
    }

    public function finish(string $orderNumber)
    {
        $order = Order::with(['items', 'payments'])
            ->where('user_id', auth()->id())
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        return view('checkout.finish', compact('order'));
    }
}
