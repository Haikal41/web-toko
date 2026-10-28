@extends('layouts.admin')

@section('title', 'Pesanan')
@section('crumb', 'Transaksi / Pesanan')

@section('content')
<div class="card">
    <div class="panel-head">
        <div>
            <h3>Daftar pesanan</h3>
            <p>{{ $orders->total() }} pesanan</p>
        </div>
        <a href="{{ route('admin.pos.create') }}" class="btn">+ Buat Pesanan</a>
    </div>

    <div class="panel-body" style="padding-bottom:.6rem">
        <form method="GET" class="toolbar">
            <div class="field">
                <label class="label">Cari</label>
                <input class="input" name="q" value="{{ request('q') }}" placeholder="No. pesanan / nama / WA">
            </div>
            <div class="field">
                <label class="label">Status</label>
                <select class="select" name="status">
                    <option value="">Semua status</option>
                    @foreach(\App\Models\Order::STATUSES as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label class="label">Sumber</label>
                <select class="select" name="channel">
                    <option value="">Semua sumber</option>
                    <option value="online" @selected(request('channel') === 'online')>Online</option>
                    <option value="pos" @selected(request('channel') === 'pos')>Kasir / POS</option>
                </select>
            </div>
            <div class="field">
                <label class="label">Dari</label>
                <input type="date" class="input" name="start" value="{{ request('start') }}">
            </div>
            <div class="field">
                <label class="label">Sampai</label>
                <input type="date" class="input" name="end" value="{{ request('end') }}">
            </div>
            <button class="btn btn-outline">Filter</button>
            @if(request()->hasAny(['q', 'status', 'channel', 'start', 'end']))
                <a href="{{ route('admin.orders.index') }}" class="btn btn-ghost">Reset</a>
            @endif
        </form>
    </div>

    <div class="table-wrap" style="border:0;border-radius:0">
        <table class="table">
            <thead>
                <tr>
                    <th>No. Pesanan</th>
                    <th>Pembeli</th>
                    <th>WA</th>
                    <th>Sumber</th>
                    <th>Item</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Tanggal</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td class="bold">{{ $order->order_number }}</td>
                        <td>{{ $order->customer_name }}</td>
                        <td class="small">{{ $order->customer_phone }}</td>
                        <td><span class="pill pill-{{ $order->isPos() ? 'blue' : 'gray' }}">{{ $order->isPos() ? 'POS' : 'Online' }}</span></td>
                        <td class="small">{{ $order->items_count }}</td>
                        <td class="bold nowrap">{{ rupiah($order->total) }}</td>
                        <td><span class="pill pill-{{ $order->statusColor() }}">{{ $order->statusLabel() }}</span></td>
                        <td class="small muted nowrap">{{ $order->created_at->format('d M Y H:i') }}</td>
                        <td class="nowrap">
                            <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-outline btn-sm">Detail</a>
                            <a href="{{ route('admin.orders.receipt', $order) }}" class="btn btn-ghost btn-sm" target="_blank">Struk</a>
                            @if($order->isDelivery())
                                <a href="{{ route('admin.orders.shipping-label', $order) }}" class="btn btn-ghost btn-sm" target="_blank">Resi</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center muted" style="padding:2.5rem">Tidak ada pesanan yang cocok.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="panel">{{ $orders->links() }}</div>
</div>
@endsection
