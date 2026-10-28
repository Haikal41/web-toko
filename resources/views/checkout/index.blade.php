@extends('layouts.app')

@section('title', 'Checkout — '.store_name())

@section('content')
<div class="page-head">
    <div class="container">
        <div class="breadcrumb"><a href="{{ route('home') }}">Beranda</a><span>/</span><a href="{{ route('cart.index') }}">Keranjang</a><span>/</span><span>Checkout</span></div>
        <div class="steps">
            <span class="step"><span class="num">1</span> Keranjang</span>
            <span class="sep"></span>
            <span class="step active"><span class="num">2</span> Alamat & Pembayaran</span>
            <span class="sep"></span>
            <span class="step"><span class="num">3</span> Selesai</span>
        </div>
        <h1>Checkout</h1>
    </div>
</div>

<div class="container" style="padding-bottom:3rem">
    <form action="{{ route('checkout.store') }}" method="POST">
        @csrf
        <div class="cart-layout">
            <div style="display:flex;flex-direction:column;gap:1.2rem">
                <div class="card panel">
                    <h3 style="font-size:1.05rem">Data pengiriman</h3>
                    <p class="small muted">Tidak perlu membuat akun. Isi nama dan nomor WhatsApp agar kami bisa mengirim notifikasi pesanan.</p>

                    @if($addresses->isNotEmpty())
                        <div style="display:grid;gap:.7rem;margin:1rem 0">
                            @foreach($addresses as $address)
                                <label class="card" style="padding:1rem;cursor:pointer;display:flex;gap:.8rem;align-items:flex-start">
                                    <input type="radio" name="address_id" value="{{ $address->id }}" @checked(old('address_id', $addresses->first()->id) == $address->id) style="margin-top:.3rem;accent-color:var(--primary)">
                                    <span>
                                        <span class="bold">{{ $address->recipient_name }}</span>
                                        @if($address->is_default) <span class="pill pill-green" style="margin-left:.3rem">Utama</span> @endif
                                        <br>
                                        <span class="small muted">{{ $address->phone }} &middot; {{ $address->address }}, {{ $address->city }} {{ $address->postal_code }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>

                        <details class="mt-2">
                            <summary class="bold small" style="cursor:pointer;color:var(--primary)">+ Gunakan alamat baru</summary>
                            <div class="form-grid mt-2">
                                <div class="field">
                                    <label class="label">Nama penerima</label>
                                    <input class="input" name="recipient_name" value="{{ old('recipient_name') }}" placeholder="Nama lengkap">
                                </div>
                                <div class="field">
                                    <label class="label">Nomor WhatsApp</label>
                                    <input class="input" name="phone" value="{{ old('phone') }}" placeholder="08xxxxxxxxxx">
                                </div>
                                <div class="field full">
                                    <label class="label">Alamat lengkap</label>
                                    <textarea class="textarea" name="address" placeholder="Jalan, nomor rumah, RT/RW, kecamatan">{{ old('address') }}</textarea>
                                </div>
                                <div class="field">
                                    <label class="label">Kota</label>
                                    <input class="input" name="city" value="{{ old('city') }}">
                                </div>
                                <div class="field">
                                    <label class="label">Kode pos</label>
                                    <input class="input" name="postal_code" value="{{ old('postal_code') }}">
                                </div>
                            </div>
                            <p class="help">Alamat ini akan disimpan ke akunmu untuk pesanan berikutnya.</p>
                        </details>
                    @else
                        <div class="form-grid mt-2">
                            <div class="field">
                                <label class="label">Nama penerima</label>
                                <input class="input" name="recipient_name" required value="{{ old('recipient_name', auth()->user()?->name) }}">
                            </div>
                            <div class="field">
                                <label class="label">Nomor WhatsApp</label>
                                <input class="input" name="phone" required value="{{ old('phone', auth()->user()?->phone) }}" placeholder="08xxxxxxxxxx">
                                <div class="help">Notifikasi dan tautan lacak pesanan dikirim ke nomor ini.</div>
                            </div>
                            <div class="field full">
                                <label class="label">Alamat lengkap</label>
                                <textarea class="textarea" name="address" required placeholder="Jalan, nomor rumah, RT/RW, kecamatan">{{ old('address') }}</textarea>
                            </div>
                            <div class="field">
                                <label class="label">Kota</label>
                                <input class="input" name="city" required value="{{ old('city') }}">
                            </div>
                            <div class="field">
                                <label class="label">Kode pos</label>
                                <input class="input" name="postal_code" value="{{ old('postal_code') }}">
                            </div>
                        </div>
                    @endif
                </div>

                <div class="card panel">
                    <h3 style="font-size:1.05rem">Catatan pesanan</h3>
                    <div class="field" style="margin:.8rem 0 0">
                        <textarea class="textarea" name="notes" placeholder="Contoh: mohon dikirim sebelum tanggal 20, atau detail tambahan personalisasi.">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="card panel" style="position:sticky;top:95px">
                <h3 style="font-size:1.05rem">Ringkasan pesanan</h3>
                <div class="divider"></div>

                @foreach($items as $item)
                    <div class="flex-between" style="font-size:.84rem;margin-bottom:.6rem;gap:.5rem">
                        <span>{{ $item['quantity'] }}x {{ $item['product']->name }}
                            @if($item['personalization_text'])<br><span class="tiny muted">&#9998; {{ $item['personalization_text'] }}</span>@endif
                        </span>
                        <span class="nowrap bold">{{ rupiah($item['line_total']) }}</span>
                    </div>
                @endforeach

                <div class="divider"></div>
                <div class="summary-row"><span class="muted">Subtotal</span><span>{{ rupiah($subtotal) }}</span></div>
                <div class="summary-row"><span class="muted">Ongkos kirim</span><span>{{ rupiah($shipping) }}</span></div>

                <div class="field mt-2">
                    <label class="label" for="coupon_code">Kode kupon <span>(opsional)</span></label>
                    <input id="coupon_code" class="input" name="coupon_code" value="{{ old('coupon_code') }}" placeholder="Mis. HADIAH10">
                </div>

                <div class="summary-row total"><span>Total</span><span>{{ rupiah($subtotal + $shipping) }}</span></div>

                <button type="submit" class="btn btn-lg btn-block mt-2">Buat Pesanan &amp; Bayar</button>
                <p class="help text-center mt-1">Kamu akan diarahkan ke halaman pembayaran Midtrans.</p>
            </div>
        </div>
    </form>
</div>
@endsection
