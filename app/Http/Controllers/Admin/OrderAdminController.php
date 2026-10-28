<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendOrderNotification;
use App\Models\Order;
use App\Services\OrderService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OrderAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::withCount('items')->latest();

        if ($request->filled('status') && in_array($request->status, Order::STATUSES, true)) {
            $query->where('status', $request->status);
        }

        if ($request->filled('channel') && in_array($request->channel, Order::CHANNELS, true)) {
            $query->where('channel', $request->channel);
        }

        if ($request->filled('q')) {
            $q = $request->string('q')->toString();
            $query->where(function ($builder) use ($q) {
                $builder->where('order_number', 'like', "%{$q}%")
                    ->orWhere('customer_name', 'like', "%{$q}%")
                    ->orWhere('customer_phone', 'like', "%{$q}%");
            });
        }

        if ($request->filled('start')) {
            $query->whereDate('created_at', '>=', Carbon::parse($request->input('start')));
        }

        if ($request->filled('end')) {
            $query->whereDate('created_at', '<=', Carbon::parse($request->input('end')));
        }

        $orders = $query->paginate(15)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load(['items.product', 'items.variant', 'payments', 'whatsappLogs', 'user', 'creator', 'canceller', 'statusLogs.user']);

        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order, OrderService $orders)
    {
        $data = $request->validate([
            'status' => ['required', 'in:'.implode(',', Order::STATUSES)],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        if ($data['status'] === 'batal' && ! $order->canBeCancelledBy($request->user())) {
            abort(403, 'Pesanan ini tidak dapat dibatalkan oleh kasir.');
        }

        $orders->updateStatus($order, $data['status'], $request->user(), $data['note'] ?? null);

        return back()->with('success', 'Status pesanan diperbarui menjadi '.$order->fresh()->statusLabel().'.');
    }

    public function cancel(Request $request, Order $order, OrderService $orders)
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        if (! $order->canBeCancelledBy($request->user())) {
            abort(403, 'Pesanan ini tidak dapat dibatalkan oleh kasir.');
        }

        $orders->cancel($order, $data['reason'], $request->user());

        return back()->with('success', 'Pesanan '.$order->order_number.' dibatalkan dan stok dikembalikan bila sudah lunas.');
    }

    public function receipt(Order $order)
    {
        $order->load(['items', 'creator', 'payments']);

        return view('admin.orders.receipt', compact('order'));
    }

    public function shippingLabel(Order $order)
    {
        abort_unless($order->isDelivery(), 404, 'Label pengiriman hanya tersedia untuk pesanan yang dikirim.');

        $order->load(['items', 'creator']);

        return view('admin.orders.shipping-label', compact('order'));
    }

    public function resendReceipt(Request $request, Order $order)
    {
        if (! $order->hasReceipt()) {
            return back()->with('error', 'Struk hanya dapat dikirim untuk pesanan yang sudah lunas.');
        }

        if (blank($order->customer_phone)) {
            return back()->with('error', 'Pesanan ini tidak memiliki nomor WhatsApp pembeli.');
        }

        SendOrderNotification::dispatch($order->fresh(['items']), true);

        return back()->with('success', 'Struk pembayaran '.$order->order_number.' dijadwalkan dikirim ulang ke WhatsApp pembeli.');
    }
}
