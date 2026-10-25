@extends('layouts.app')

@section('title', 'Riwayat Absensi — '.store_name())

@section('content')
<div class="page-head">
    <div class="container">
        <div class="breadcrumb"><a href="{{ route('home') }}">Beranda</a><span>/</span><a href="{{ route('attendance.index') }}">Absensi</a><span>/</span><span>Riwayat</span></div>
        <h1>Riwayat kehadiran</h1>
    </div>
</div>

<div class="container" style="padding-bottom:3rem">
    <div class="card">
        <div class="table-wrap" style="border:0">
            <table class="table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Masuk</th>
                        <th>Pulang</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances as $attendance)
                        <tr>
                            <td>{{ $attendance->date->translatedFormat('l, d M Y') }}</td>
                            <td>{{ $attendance->check_in ? substr($attendance->check_in, 0, 5) : '-' }}</td>
                            <td>{{ $attendance->check_out ? substr($attendance->check_out, 0, 5) : '-' }}</td>
                            <td><span class="pill pill-{{ $attendance->status === 'hadir' ? 'green' : ($attendance->status === 'telat' ? 'amber' : 'red') }}">{{ $attendance->statusLabel() }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center muted" style="padding:2rem">Belum ada riwayat absensi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="panel">{{ $attendances->links() }}</div>
    </div>

    <a href="{{ route('attendance.index') }}" class="btn btn-outline mt-2">&larr; Kembali ke absensi</a>
</div>
@endsection
