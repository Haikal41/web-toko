@extends('layouts.app')

@section('title', 'Absensi Karyawan — '.store_name())

@section('content')
<div class="page-head">
    <div class="container">
        <div class="breadcrumb"><a href="{{ route('home') }}">Beranda</a><span>/</span><span>Absensi</span></div>
        <h1>Absensi hari ini</h1>
        <p class="muted">{{ now()->translatedFormat('l, d F Y') }} &middot; Jam kerja {{ substr($setting->work_start, 0, 5) }}–{{ substr($setting->work_end, 0, 5) }}</p>
    </div>
</div>

<div class="container" style="padding-bottom:3rem">
    <div class="grid" style="grid-template-columns:1fr 1fr;align-items:start">
        <div class="card panel">
            <h3 style="font-size:1.05rem">Status kamu hari ini</h3>
            @if($today)
                <div class="mt-2" style="display:grid;gap:.8rem">
                    <div class="flex-between card" style="padding:.9rem">
                        <span class="small muted">Absen masuk</span>
                        <strong>{{ substr($today->check_in, 0, 5) }}</strong>
                    </div>
                    <div class="flex-between card" style="padding:.9rem">
                        <span class="small muted">Absen pulang</span>
                        <strong>{{ $today->check_out ? substr($today->check_out, 0, 5) : '—' }}</strong>
                    </div>
                    <div class="flex-between card" style="padding:.9rem">
                        <span class="small muted">Status</span>
                        <span class="pill pill-{{ $today->status === 'hadir' ? 'green' : ($today->status === 'telat' ? 'amber' : 'red') }}">{{ $today->statusLabel() }}</span>
                    </div>
                </div>
            @else
                <p class="small muted mt-2">Belum ada absensi hari ini. Scan QR dari admin untuk mencatat kehadiran.</p>
            @endif

            <div class="alert alert-info small mt-2">
                Scan pertama = absen masuk, scan berikutnya = absen pulang. Waktu mengikuti server.
            </div>

            <a href="{{ route('attendance.history') }}" class="btn btn-outline btn-block">Lihat riwayat kehadiran</a>
        </div>

        <div class="card panel">
            <h3 style="font-size:1.05rem">Scan QR absensi</h3>
            <p class="small muted">Arahkan kamera ke QR yang ditampilkan admin di toko.</p>

            <div class="scan-box">
                <div id="reader"></div>
                <div id="scan-result" class="scan-result"></div>

                <details class="mt-2">
                    <summary class="small bold" style="cursor:pointer;color:var(--primary)">Kamera tidak bisa? Masukkan kode manual</summary>
                    <form id="manual-form" class="mt-2" action="{{ route('attendance.scan') }}" method="POST">
                        @csrf
                        <div class="field">
                            <label class="label">Kode QR</label>
                            <input class="input" name="token" placeholder="Tempel kode dari admin" required>
                        </div>
                        <button class="btn btn-sm" type="submit">Kirim kode</button>
                    </form>
                </details>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
    const resultBox = document.getElementById('scan-result');
    let scanning = false;

    async function submitToken(token) {
        if (scanning) return;
        scanning = true;

        resultBox.innerHTML = '<div class="alert alert-info">Memproses absensi...</div>';

        try {
            const response = await fetch(@json(route('attendance.scan')), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ token: token })
            });

            const data = await response.json();
            resultBox.innerHTML = '<div class="alert ' + (data.ok ? 'alert-success' : 'alert-error') + '">' + data.message + '</div>';

            if (data.ok) {
                setTimeout(() => window.location.reload(), 1600);
            } else {
                scanning = false;
            }
        } catch (error) {
            resultBox.innerHTML = '<div class="alert alert-error">Terjadi kesalahan. Coba lagi.</div>';
            scanning = false;
        }
    }

    if (window.Html5Qrcode) {
        const scanner = new Html5Qrcode('reader');

        Html5Qrcode.getCameras().then(cameras => {
            if (!cameras.length) return;

            scanner.start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: { width: 220, height: 220 } },
                (decodedText) => {
                    scanner.pause();
                    submitToken(decodedText);
                    setTimeout(() => { if (!scanning) scanner.resume(); }, 3000);
                },
                () => {}
            ).catch(() => {
                resultBox.innerHTML = '<div class="alert alert-warning">Kamera tidak dapat diakses. Gunakan input kode manual di bawah.</div>';
            });
        }).catch(() => {
            resultBox.innerHTML = '<div class="alert alert-warning">Izin kamera ditolak. Gunakan input kode manual di bawah.</div>';
        });
    }
</script>
@endpush
