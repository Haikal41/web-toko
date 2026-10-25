@extends('layouts.app')

@section('title', 'Pesanan '.$order->order_number.' — '.store_name())

@section('content')
<div class="page-head">
    <div class="container">
        <div class="breadcrumb"><a href="{{ route('home') }}">Beranda</a><span>/</span><a href="{{ route('account.index') }}">Akun</a><span>/</span><span>{{ $order->order_number }}</span></div>
        <div class="flex-between flex-wrap">
            <h1>{{ $order->order_number }}</h1>
            <span class="pill pill-{{ $order->statusColor() }}">{{ $order->statusLabel() }}</span>
        </div>
    </div>
</div>

<div class="container" style="padding-bottom:3rem">
    <div class="grid" style="grid-template-columns:1.5fr 1fr;align-items:start">
        <div class="card">
            <div class="panel-head">
                <div>
                    <h3>Detail pesanan</h3>
                    <p>Dibuat {{ $order->created_at->format('d M Y H:i') }}</p>
                </div>
            </div>

            @foreach($order->items as $item)
                <div class="cart-item">
                    <div class="cart-thumb">
                        <img src="{{ $item->product?->imageUrl() ?? asset('images/products/placeholder.svg') }}" alt="{{ $item->product_name }}">
                    </div>
                    <div>
                        <div class="cart-title">{{ $item->product_name }}</div>
                        <div class="cart-sub">
                            {{ $item->quantity }} x {{ rupiah($item->price) }}
                            @if($item->variant_name) &middot; {{ $item->variant_name }} @endif
                        </div>
                        @if($item->personalization_text)
                            <div class="cart-persona">&#9998; {{ $item->personalization_text }}</div>
                        @endif
                    </div>
                    <div class="bold nowrap">{{ rupiah($item->subtotal) }}</div>
                </div>
            @endforeach

            <div class="panel">
                <div class="summary-row"><span class="muted">Subtotal</span><span>{{ rupiah($order->subtotal) }}</span></div>
                @if((float) $order->discount > 0)
                    <div class="summary-row"><span class="muted">Diskon</span><span>-{{ rupiah($order->discount) }}</span></div>
                @endif
                <div class="summary-row"><span class="muted">Ongkos kirim</span><span>{{ rupiah($order->shipping_cost) }}</span></div>
                <div class="summary-row total"><span>Total</span><span>{{ rupiah($order->total) }}</span></div>
            </div>
        </div>

        <div style="display:flex;flex-direction:column;gap:1.2rem">
            <div class="card panel">
                <h3 style="font-size:1.02rem">Pengiriman</h3>
                <div class="small" style="margin-top:.6rem">
                    <div class="bold">{{ $order->customer_name }}</div>
                    <div class="muted">{{ $order->customer_phone }}</div>
                    <div class="muted">{{ $order->customer_address }}</div>
                </div>
                @if($order->notes)
                    <div class="alert alert-info small mt-2 mb-0">{{ $order->notes }}</div>
                @endif
            </div>

            <div class="card panel">
                <h3 style="font-size:1.02rem">Pembayaran</h3>
                @php($payment = $order->payments->sortByDesc('id')->first())
                <dl class="detail-list mt-2">
                    <dt>Status</dt><dd>{{ $payment?->status ?? 'Belum ada' }}</dd>
                    <dt>Metode</dt><dd>{{ $payment?->payment_type ?? '-' }}</dd>
                    <dt>Dibayar</dt><dd>{{ $payment?->paid_at?->format('d M Y H:i') ?? '-' }}</dd>
                </dl>

                @if($order->status === 'pending')
                    <a href="{{ route('checkout.payment', $order->order_number) }}" class="btn btn-block mt-2">Bayar sekarang</a>
                @endif
            </div>

            <a href="{{ wa_link(store_phone(), 'Halo admin, saya ingin menanyakan pesanan '.$order->order_number.'.') }}" target="_blank" rel="noopener" class="btn btn-outline btn-block">Tanya admin via WhatsApp</a>
        </div>
    </div>
</div>
@endsection
