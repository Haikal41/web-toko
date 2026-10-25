@extends('layouts.auth')

@section('title', 'Masuk')

@section('content')
    <h1>Selamat datang kembali</h1>
    <p class="muted">Masuk untuk melanjutkan belanja dan melihat pesananmu.</p>

    <form action="{{ route('login.store') }}" method="POST" class="mt-2">
        @csrf
        <div class="field">
            <label class="label" for="email">Email</label>
            <input id="email" type="email" name="email" class="input" value="{{ old('email') }}" required autofocus placeholder="nama@email.com">
        </div>
        <div class="field">
            <label class="label" for="password">Password</label>
            <input id="password" type="password" name="password" class="input" required placeholder="Password kamu">
        </div>
        <div class="flex-between mb-2">
            <label class="checkbox-row"><input type="checkbox" name="remember"> Ingat saya</label>
        </div>
        <button type="submit" class="btn btn-lg btn-block">Masuk</button>
    </form>

    <div class="alert alert-info small mt-2" style="margin-bottom:0">
        <strong>Akun demo:</strong><br>
        Admin: admin@multiverse.test / password<br>
        Karyawan: karyawan@multiverse.test / password<br>
        Pembeli: pembeli@multiverse.test / password
    </div>

    <div class="auth-switch">
        Belum punya akun? <a href="{{ route('register') }}">Daftar sekarang</a>
    </div>
@endsection
