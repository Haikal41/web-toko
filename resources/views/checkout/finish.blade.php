@extends('layouts.app')

@section('title', 'Pesanan '.$order->order_number.' — '.store_name())

@section('content')
<div class="page-head">
    <div class="container">
        <div class="steps">
            <span class="step"><span class="num">1</span> Keranjang</span>
            <span class="sep"></span>
            <span class="step"><span class="num">2</span> Alamat</span>
            <span class="sep"></span>
            <span class="step active"><span class="num">3</span> Selesai</span>
        </div>
        <h1>Detail pesanan</h1>
    </div>
</div>

<div class="container" style="padding-bottom:3rem">
    <div class="card panel pay-card">
        <div class="text-center">
            @if($order->status === 'pending')
                <div class="status-icon wait">&#8987;</div>
                <h2 class="display" style="font-size:1.8rem">Menunggu pembayaran</h2>
                <p class="muted">Pesanan {{ $order->order_number }} sudah dibuat. Selesaikan pembayaran agar segera diproses.</p>
                <a href="{{ $paymentUrl }}" class="btn btn-lg">Lanjutkan Pembayaran</a>
            @elseif($order->status === 'batal')
                <div class="status-icon" style="background:var(--red-soft)">&#10005;</div>
                <h2 class="display" style="font-size:1.8rem">Pesanan dibatalkan</h2>
                <div class="alert alert-info small mt-2" style="margin-bottom:0">
                    @if($order->cancel_reason)
                        Alasan pembatalan: {{ $order->cancel_reason }}
                    @else
                        Hubungi admin bila ada pertanyaan.
                    @endif
                </div>
                <a href="{{ route('products.index') }}" class="btn">Belanja lagi</a>
            @else
                <div class="status-icon ok">&#10003;</div>
                <h2 class="display" style="font-size:1.8rem">Terima kasih, {{ $order->customer_name }}!</h2>
                <p class="muted">Pembayaran pesanan <strong>{{ $order->order_number }}</strong> sudah kami terima.</p>
                <span class="pill pill-{{ $order->statusColor() }}">{{ $order->statusLabel() }}</span>
            @endif
        </div>

        <div class="divider"></div>

        <div class="detail-list" style="margin-bottom:1.2rem">
            <dt>Nomor pesanan</dt><dd>{{ $order->order_number }}</dd>
            <dt>Tanggal</dt><dd>{{ $order->created_at->format('d M Y H:i') }}</dd>
            <dt>WhatsApp</dt><dd>{{ $order->customer_phone }}</dd>
            <dt>Alamat</dt><dd>{{ $order->customer_address }}</dd>
        </div>

        <div class="divider"></div>

        @foreach($order->items as $item)
            <div class="flex-between" style="font-size:.85rem;margin-bottom:.6rem;gap:.5rem">
                <span>
                    {{ $item->quantity }}x {{ $item->product_name }}
                    @if($item->variant_name)<span class="tiny muted">({{ $item->variant_name }})</span>@endif
                    @if($item->personalization_text)<br><span class="tiny" style="color:var(--primary)">&#9998; {{ $item->personalization_text }}</span>@endif
                </span>
                <span class="nowrap bold">{{ rupiah($item->subtotal) }}</span>
            </div>
        @endforeach

        <div class="summary-row total"><span>Total</span><span>{{ rupiah($order->total) }}</span></div>

        @if($order->status !== 'pending' && $order->status !== 'batal')
            <div class="alert alert-success mt-2" style="margin-bottom:0">
                Notifikasi WhatsApp berisi detail pesanan dikirim ke {{ $order->customer_phone }}.
            </div>
        @endif

        <div class="flex gap-sm mt-3" style="justify-content:center;flex-wrap:wrap">
            @if($receiptUrl)
                <a href="{{ $receiptUrl }}" class="btn btn-outline" target="_blank" rel="noopener">Cetak / simpan struk</a>
            @endif
            @auth
                <a href="{{ route('account.order.show', $order->order_number) }}" class="btn btn-outline">Lihat di akun</a>
            @else
                <a href="{{ route('track.index') }}" class="btn btn-outline">Lacak pesanan</a>
            @endauth
            <a href="{{ route('products.index') }}" class="btn">Belanja lagi</a>
        </div>

        @guest
            <div class="alert alert-info small mt-2" style="margin-bottom:0">
                Simpan nomor pesanan <strong>{{ $order->order_number }}</strong> dan nomor WhatsApp yang kamu gunakan untuk melacak pesanan kapan saja.
            </div>
        @endguest
    </div>
</div>
@endsection
