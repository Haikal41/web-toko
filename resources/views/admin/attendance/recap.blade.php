@extends('layouts.admin')

@section('title', 'Rekap Absensi')
@section('crumb', 'Karyawan / Rekap Absensi')

@section('content')
<div class="card panel">
    <form method="GET" class="toolbar" style="justify-content:space-between">
        <div class="flex gap" style="align-items:flex-end;flex-wrap:wrap">
            <div class="field">
                <label class="label">Bulan</label>
                <input type="month" class="input" name="month" value="{{ $month }}">
            </div>
            <button class="btn btn-outline">Tampilkan</button>
        </div>
        <a href="{{ route('admin.attendance.export', ['month' => $month]) }}" class="btn">&#8681; Export CSV</a>
    </form>
</div>

<div class="card">
    <div class="panel-head">
        <div>
            <h3>Rekap {{ $date->translatedFormat('F Y') }}</h3>
            <p>H = Hadir, T = Telat, A = Alpa/Tidak hadir, - = belum ada data</p>
        </div>
    </div>

    <div class="table-wrap" style="border:0;border-radius:0">
        <table class="table recap-table">
            <thead>
                <tr>
                    <th style="min-width:150px">Karyawan</th>
                    @foreach($days as $day)
                        <th>{{ $day }}</th>
                    @endforeach
                    <th>H</th>
                    <th>T</th>
                    <th>A</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $employee)
                    @php($records = ($attendances[$employee->id] ?? collect())->keyBy(fn ($row) => $row->date->format('j')))
                    @php($counts = ['hadir' => 0, 'telat' => 0, 'tidak_hadir' => 0])
                    <tr>
                        <td class="bold small">{{ $employee->name }}</td>
                        @foreach($days as $day)
                            @php($record = $records->get($day))
                            @if($record)
                                @php($counts[$record->status] = ($counts[$record->status] ?? 0) + 1)
                                <td><span class="attendance-code code-{{ strtolower(substr($record->status, 0, 1)) }}">{{ $record->statusCode() }}</span></td>
                            @else
                                <td><span class="attendance-code code--">-</span></td>
                            @endif
                        @endforeach
                        <td class="bold" style="color:var(--green)">{{ $counts['hadir'] }}</td>
                        <td class="bold" style="color:var(--amber)">{{ $counts['telat'] }}</td>
                        <td class="bold" style="color:var(--red)">{{ $counts['tidak_hadir'] }}</td>
                    </tr>
                @empty
                    <tr><td colspan="{{ $days->count() + 4 }}" class="text-center muted" style="padding:2rem">Belum ada karyawan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
