@extends('layouts.app')

@section('title', 'Lacak Pesanan — '.store_name())

@section('content')
<div class="page-head">
    <div class="container">
        <div class="breadcrumb"><a href="{{ route('home') }}">Beranda</a><span>/</span><span>Lacak Pesanan</span></div>
        <h1>Lacak pesananmu</h1>
        <p class="muted" style="margin:.4rem 0 0">Masukkan nomor pesanan dan nomor WhatsApp yang kamu gunakan saat checkout.</p>
    </div>
</div>

<div class="container" style="padding-bottom:3rem;max-width:620px">
    <div class="card panel">
        <form action="{{ route('track.lookup') }}" method="POST">
            @csrf
            <div class="field">
                <label class="label" for="order_number">Nomor pesanan</label>
                <input id="order_number" class="input" name="order_number" value="{{ old('order_number') }}" placeholder="MVA-20261028-0001" required autofocus>
            </div>
            <div class="field">
                <label class="label" for="phone">Nomor WhatsApp</label>
                <input id="phone" class="input" name="phone" value="{{ old('phone') }}" placeholder="08xxxxxxxxxx" required>
                <div class="help">Gunakan nomor yang sama seperti saat checkout.</div>
            </div>
            <button class="btn btn-lg btn-block" type="submit">Cari pesanan</button>
        </form>

        <div class="alert alert-info small mt-3" style="margin-bottom:0">
            Tidak perlu akun. Nomor pesanan ada di halaman selesai checkout dan notifikasi WhatsApp.
        </div>
    </div>

    <p class="text-center small muted mt-2">
        Butuh bantuan? <a href="{{ wa_link(store_phone(), 'Halo admin, saya ingin menanyakan pesanan saya.') }}" target="_blank" rel="noopener">Chat WhatsApp admin</a>
    </p>
</div>
@endsection
