@extends('layouts.auth')

@section('title', 'Daftar Akun')

@section('content')
    <h1>Buat akun baru</h1>
    <p class="muted">Daftar agar bisa menyimpan alamat, memantau pesanan, dan menerima notifikasi WhatsApp.</p>

    <form action="{{ route('register.store') }}" method="POST" class="mt-2">
        @csrf
        <div class="field">
            <label class="label" for="name">Nama lengkap</label>
            <input id="name" type="text" name="name" class="input" value="{{ old('name') }}" required autofocus placeholder="Nama kamu">
        </div>
        <div class="form-grid">
            <div class="field">
                <label class="label" for="email">Email</label>
                <input id="email" type="email" name="email" class="input" value="{{ old('email') }}" required placeholder="nama@email.com">
            </div>
            <div class="field">
                <label class="label" for="phone">Nomor WhatsApp</label>
                <input id="phone" type="text" name="phone" class="input" value="{{ old('phone') }}" required placeholder="08xxxxxxxxxx">
            </div>
        </div>
        <div class="field">
            <label class="label" for="password">Password</label>
            <input id="password" type="password" name="password" class="input" required placeholder="Minimal 6 karakter">
        </div>
        <div class="field">
            <label class="label" for="password_confirmation">Konfirmasi password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" class="input" required placeholder="Ulangi password">
        </div>
        <button type="submit" class="btn btn-lg btn-block">Daftar</button>
    </form>

    <div class="auth-switch">
        Sudah punya akun? <a href="{{ route('login') }}">Masuk di sini</a>
    </div>
@endsection
