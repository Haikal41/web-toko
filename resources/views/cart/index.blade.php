@extends('layouts.app')

@section('title', 'Keranjang Belanja — '.store_name())

@section('content')
<div class="page-head">
    <div class="container">
        <div class="breadcrumb"><a href="{{ route('home') }}">Beranda</a><span>/</span><span>Keranjang</span></div>
        <h1>Keranjang belanja</h1>
    </div>
</div>

<div class="container" style="padding-bottom:3rem">
    @if($items->isEmpty())
        <div class="card empty-state">
            <div class="emoji">&#128717;</div>
            <h3>Keranjangmu masih kosong</h3>
            <p>Yuk pilih aksesoris favoritmu dan buat hadiah yang berkesan.</p>
            <a href="{{ route('products.index') }}" class="btn btn-lg">Mulai Belanja</a>
        </div>
    @else
        <div class="cart-layout">
            <div class="card">
                @foreach($items as $item)
                    <div class="cart-item">
                        <div class="cart-thumb">
                            <img src="{{ $item['product']->imageUrl() }}" alt="{{ $item['product']->name }}">
                        </div>
                        <div>
                            <a href="{{ route('products.show', $item['product']->slug) }}" class="cart-title">{{ $item['product']->name }}</a>
                            <div class="cart-sub">{{ rupiah($item['unit_price']) }} / item
                                @if($item['variant']) &middot; {{ $item['variant']->name }} @endif
                            </div>
                            @if($item['personalization_text'])
                                <div class="cart-persona">&#9998; {{ $item['personalization_text'] }}</div>
                            @endif
                        </div>
                        <div class="cart-actions" style="text-align:right">
                            <div class="bold mb-2">{{ rupiah($item['line_total']) }}</div>
                            <div class="flex gap-sm" style="justify-content:flex-end;align-items:center;flex-wrap:wrap">
                                <form action="{{ route('cart.update', $item['key']) }}" method="POST" class="flex gap-sm" style="align-items:center">
                                    @csrf
                                    @method('PATCH')
                                    <div class="qty-control" data-qty>
                                        <button type="button" data-qty-minus aria-label="Kurangi">&minus;</button>
                                        <input type="number" name="quantity" value="{{ $item['quantity'] }}" min="1" max="99" style="width:46px">
                                        <button type="button" data-qty-plus aria-label="Tambah">+</button>
                                    </div>
                                    <button class="btn btn-outline btn-sm" type="submit">Update</button>
                                </form>
                                <form action="{{ route('cart.destroy', $item['key']) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-ghost btn-sm" type="submit" style="color:var(--red)" data-confirm="Hapus item ini dari keranjang?">Hapus</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="card panel" style="position:sticky;top:95px">
                <h3 style="font-size:1.05rem">Ringkasan</h3>
                <div class="divider"></div>
                <div class="summary-row"><span class="muted">Subtotal</span><span>{{ rupiah($subtotal) }}</span></div>
                <div class="summary-row"><span class="muted">Ongkos kirim</span><span>{{ rupiah($shipping) }}</span></div>
                <div class="summary-row total"><span>Total</span><span>{{ rupiah($subtotal + $shipping) }}</span></div>

                <a href="{{ route('checkout.index') }}" class="btn btn-lg btn-block mt-2">Lanjut ke Checkout &rarr;</a>
                <a href="{{ route('products.index') }}" class="btn btn-ghost btn-block mt-1">Tambah produk lain</a>

                <div class="hero-note mt-2" style="justify-content:center">&#128274; Pembayaran aman via Midtrans</div>
            </div>
        </div>
    @endif
</div>
@endsection
