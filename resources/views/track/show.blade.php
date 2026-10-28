@extends('layouts.app')

@section('title', 'Pesanan '.$order->order_number.' — '.store_name())

@section('content')
<div class="page-head">
    <div class="container">
        <div class="breadcrumb"><a href="{{ route('home') }}">Beranda</a><span>/</span><a href="{{ route('track.index') }}">Lacak Pesanan</a><span>/</span><span>{{ $order->order_number }}</span></div>
        <h1>Pesanan {{ $order->order_number }}</h1>
    </div>
</div>

<div class="container" style="padding-bottom:3rem;max-width:760px">
    <div class="card panel">
        <div class="flex-between flex-wrap gap-sm">
            <div>
                <div class="tiny muted">Status pesanan</div>
                <span class="pill pill-{{ $order->statusColor() }}" style="margin-top:.35rem">{{ $order->statusLabel() }}</span>
            </div>
            <div style="text-align:right">
                <div class="tiny muted">Total</div>
                <div class="bold" style="font-size:1.3rem">{{ rupiah($order->total) }}</div>
            </div>
        </div>

        @if($order->status === 'pending' && $paymentUrl)
            <a href="{{ $paymentUrl }}" class="btn btn-lg btn-block mt-3">Lanjutkan pembayaran</a>
        @endif

        @if($receiptUrl)
            <a href="{{ $receiptUrl }}" class="btn btn-outline btn-block mt-3" target="_blank" rel="noopener">Cetak / simpan struk pembayaran</a>
        @endif

        @if($order->status === 'batal' && $order->cancel_reason)
            <div class="alert alert-error small mt-3" style="margin-bottom:0">Alasan pembatalan: {{ $order->cancel_reason }}</div>
        @endif

        <div class="divider"></div>

        <dl class="detail-list">
            <dt>Tanggal</dt><dd>{{ $order->created_at->format('d M Y H:i') }}</dd>
            <dt>Nama</dt><dd>{{ $order->customer_name }}</dd>
            <dt>WhatsApp</dt><dd>{{ $order->customer_phone ?: '-' }}</dd>
            <dt>Alamat</dt><dd>{{ $order->customer_address ?: '-' }}</dd>
            <dt>Sumber</dt><dd>{{ $order->channelLabel() }}</dd>
        </dl>

        <div class="divider"></div>

        <h3 style="font-size:1rem">Item pesanan</h3>
        @foreach($order->items as $item)
            <div class="flex-between" style="font-size:.86rem;margin-bottom:.6rem;gap:.5rem">
                <span>
                    {{ $item->quantity }}x {{ $item->product_name }}
                    @if($item->variant_name)<span class="tiny muted">({{ $item->variant_name }})</span>@endif
                    @if($item->personalization_text)<br><span class="tiny" style="color:var(--primary)">&#9998; {{ $item->personalization_text }}</span>@endif
                </span>
                <span class="nowrap bold">{{ rupiah($item->subtotal) }}</span>
            </div>
        @endforeach

        <div class="summary-row"><span class="muted">Subtotal</span><span>{{ rupiah($order->subtotal) }}</span></div>
        @if((float) $order->discount > 0)
            <div class="summary-row"><span class="muted">Diskon</span><span>-{{ rupiah($order->discount) }}</span></div>
        @endif
        <div class="summary-row"><span class="muted">Ongkir</span><span>{{ rupiah($order->shipping_cost) }}</span></div>
        <div class="summary-row total"><span>Total</span><span>{{ rupiah($order->total) }}</span></div>

        <div class="divider"></div>

        <h3 style="font-size:1rem">Riwayat status</h3>
        @forelse($order->statusLogs as $log)
            <div class="flex-between" style="font-size:.82rem;padding:.45rem 0;border-bottom:1px dashed var(--border, #eee);gap:.6rem">
                <span>
                    <span class="pill pill-{{ \App\Models\Order::colorFor($log->to_status) }}">{{ \App\Models\Order::labelFor($log->to_status) }}</span>
                    @if($log->note)<span class="tiny muted">— {{ $log->note }}</span>@endif
                </span>
                <span class="tiny muted nowrap">{{ $log->created_at->format('d M Y H:i') }}</span>
            </div>
        @empty
            <p class="small muted">Pesanan sedang menunggu pembayaran.</p>
        @endforelse

        <div class="flex gap-sm mt-3" style="flex-wrap:wrap">
            <a href="{{ route('track.index') }}" class="btn btn-outline">&larr; Lacak pesanan lain</a>
            <a href="{{ wa_link(store_phone(), 'Halo admin, saya ingin menanyakan pesanan '.$order->order_number.'.') }}" target="_blank" rel="noopener" class="btn">Chat admin</a>
        </div>
    </div>
</div>
@endsection
