@extends('layouts.admin')

@section('title', 'Absensi & QR')
@section('crumb', 'Karyawan / Absensi')

@section('content')
<div class="admin-grid-2">
    <div class="card">
        <div class="panel-head">
            <div>
                <h3>QR absensi</h3>
                <p>Tampilkan QR ini di toko agar karyawan dapat memindainya</p>
            </div>
            <form action="{{ route('admin.attendance.generate') }}" method="POST">
                @csrf
                <button class="btn" type="submit">Buat QR baru</button>
            </form>
        </div>

        <div class="qr-box">
            @if($currentToken)
                <div class="qr-frame" id="qr-frame" data-token="{{ $currentToken->token }}"></div>
                <div class="qr-timer">
                    Berlaku <strong id="qr-countdown">60</strong> detik &middot;
                    kedaluwarsa {{ $currentToken->expires_at->format('H:i:s') }}
                </div>
                <div class="mt-2">
                    <div class="tiny muted">Kode manual (jika kamera tidak tersedia):</div>
                    <code style="font-size:.7rem;word-break:break-all;display:block;background:#faf8fe;padding:.5rem;border-radius:8px;margin-top:.3rem">{{ $currentToken->token }}</code>
                </div>
            @else
                <div class="status-icon wait" style="margin-bottom:.8rem">&#128247;</div>
                <p class="muted">Belum ada QR aktif. Klik <strong>Buat QR baru</strong> untuk menampilkan QR absensi.</p>
                <p class="tiny muted">QR hanya berlaku 60 detik untuk mencegah penyalahgunaan.</p>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="panel-head">
            <div>
                <h3>Pengaturan jam kerja</h3>
                <p>Dipakai untuk menghitung status hadir/telat</p>
            </div>
        </div>
        <form action="{{ route('admin.attendance.settings') }}" method="POST" class="panel">
            @csrf
            @method('PUT')
            <div class="form-grid">
                <div class="field">
                    <label class="label">Jam masuk</label>
                    <input type="time" class="input" name="work_start" value="{{ substr($settings->work_start, 0, 5) }}" required>
                </div>
                <div class="field">
                    <label class="label">Jam pulang</label>
                    <input type="time" class="input" name="work_end" value="{{ substr($settings->work_end, 0, 5) }}" required>
                </div>
                <div class="field">
                    <label class="label">Toleransi telat (menit)</label>
                    <input type="number" class="input" name="late_tolerance_minutes" min="0" max="120" value="{{ $settings->late_tolerance_minutes }}" required>
                </div>
                <div class="field">
                    <label class="label">Hari kerja</label>
                    <input class="input" name="work_days" value="{{ $settings->work_days }}" required>
                    <div class="help">1=Senin ... 7=Minggu, pisahkan dengan koma. Default 1,2,3,4,5,6.</div>
                </div>
            </div>
            <button class="btn" type="submit">Simpan pengaturan</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="panel-head">
        <div>
            <h3>Kehadiran hari ini</h3>
            <p>{{ now()->translatedFormat('l, d F Y') }} &middot; {{ $employees->count() }} karyawan aktif</p>
        </div>
        <a href="{{ route('admin.attendance.recap') }}" class="btn btn-outline btn-sm">Lihat rekap</a>
    </div>
    <div class="table-wrap" style="border:0;border-radius:0">
        <table class="table">
            <thead>
                <tr><th>Karyawan</th><th>Masuk</th><th>Pulang</th><th>Status</th><th>IP</th></tr>
            </thead>
            <tbody>
                @forelse($employees as $employee)
                    @php($record = $todayAttendances->get($employee->id))
                    <tr>
                        <td>
                            <div class="bold small">{{ $employee->name }}</div>
                            <div class="tiny muted">{{ $employee->email }}</div>
                        </td>
                        <td>{{ $record?->check_in ? substr($record->check_in, 0, 5) : '—' }}</td>
                        <td>{{ $record?->check_out ? substr($record->check_out, 0, 5) : '—' }}</td>
                        <td>
                            @if($record)
                                <span class="pill pill-{{ $record->status === 'hadir' ? 'green' : ($record->status === 'telat' ? 'amber' : 'red') }}">{{ $record->statusLabel() }}</span>
                            @else
                                <span class="pill pill-gray">Belum absen</span>
                            @endif
                        </td>
                        <td class="tiny muted">{{ $record?->ip_address ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center muted" style="padding:2rem">Belum ada karyawan aktif.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
    const frame = document.getElementById('qr-frame');

    if (frame) {
        new QRCode(frame, {
            text: frame.dataset.token,
            width: 240,
            height: 240,
            colorDark: '#1c1633',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.M
        });
    }

    const countdown = document.getElementById('qr-countdown');
    const expiresAt = @json($currentToken?->expires_at?->toIso8601String());

    if (countdown && expiresAt) {
        const end = new Date(expiresAt).getTime();

        const tick = () => {
            const remaining = Math.max(0, Math.round((end - Date.now()) / 1000));
            countdown.textContent = remaining;
            if (remaining <= 0) {
                countdown.textContent = '0';
                clearInterval(timer);
                const box = document.getElementById('qr-frame');
                if (box) box.style.opacity = '.35';
            }
        };

        const timer = setInterval(tick, 1000);
        tick();
    }
</script>
@endpush
