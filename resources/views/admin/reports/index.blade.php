@extends('layouts.admin')

@section('title', 'Laporan Penjualan')
@section('crumb', 'Laporan / Penjualan')

@section('content')
<div class="card panel">
    <form method="GET" class="toolbar" style="justify-content:space-between">
        <div class="flex gap" style="flex-wrap:wrap;align-items:flex-end">
            <div class="field">
                <label class="label">Dari tanggal</label>
                <input type="date" class="input" name="start" value="{{ $start->format('Y-m-d') }}">
            </div>
            <div class="field">
                <label class="label">Sampai tanggal</label>
                <input type="date" class="input" name="end" value="{{ $end->format('Y-m-d') }}">
            </div>
            <div class="field">
                <label class="label">Periode grafik</label>
                <select class="select" name="period">
                    <option value="harian" @selected($period === 'harian')>Harian</option>
                    <option value="bulanan" @selected($period === 'bulanan')>Bulanan</option>
                    <option value="tahunan" @selected($period === 'tahunan')>Tahunan</option>
                </select>
            </div>
            <button class="btn btn-outline">Terapkan</button>
        </div>
        <a href="{{ route('admin.reports.export', ['start' => $start->format('Y-m-d'), 'end' => $end->format('Y-m-d')]) }}" class="btn">&#8681; Export CSV</a>
    </form>
</div>

<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-icon purple">&#128176;</div>
        <div><div class="stat-label">Total omzet</div><div class="stat-value">{{ rupiah($summary['revenue']) }}</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green">&#128230;</div>
        <div><div class="stat-label">Pesanan lunas</div><div class="stat-value">{{ $summary['orders'] }}</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon amber">&#127873;</div>
        <div><div class="stat-label">Item terjual</div><div class="stat-value">{{ $summary['items'] }}</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red">&#128202;</div>
        <div><div class="stat-label">Rata-rata pesanan</div><div class="stat-value">{{ rupiah($summary['average']) }}</div></div>
    </div>
</div>

<div class="card" style="margin-bottom:1.2rem">
    <div class="panel-head">
        <div>
            <h3>Tren penjualan</h3>
            <p>{{ ucfirst($period) }} &mdash; {{ $start->format('d M Y') }} s.d. {{ $end->format('d M Y') }}</p>
        </div>
    </div>
    <div class="panel-body">
        <div class="chart-box"><canvas id="trendChart"></canvas></div>
    </div>
</div>

<div class="admin-grid-2">
    <div class="card">
        <div class="panel-head">
            <div>
                <h3>Rincian {{ $period }}</h3>
                <p>{{ $start->format('d M Y') }} &mdash; {{ $end->format('d M Y') }}</p>
            </div>
        </div>
        <div class="table-wrap" style="border:0;border-radius:0">
            <table class="table">
                <thead><tr><th>Periode</th><th>Pesanan</th><th>Omzet</th></tr></thead>
                <tbody>
                    @forelse($trend as $row)
                        <tr>
                            <td>{{ $row->label }}</td>
                            <td>{{ $row->total_orders }}</td>
                            <td class="bold">{{ rupiah($row->revenue) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center muted" style="padding:2rem">Belum ada penjualan pada periode ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="panel-head">
            <div>
                <h3>Produk terlaris</h3>
                <p>Berdasarkan unit terjual</p>
            </div>
        </div>
        <div class="table-wrap" style="border:0;border-radius:0">
            <table class="table">
                <thead><tr><th>Produk</th><th>Terjual</th><th>Omzet</th></tr></thead>
                <tbody>
                    @forelse($topProducts as $product)
                        <tr>
                            <td class="small bold">{{ $product->product_name }}</td>
                            <td>{{ $product->sold }}</td>
                            <td class="nowrap">{{ rupiah($product->revenue) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center muted" style="padding:2rem">Belum ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const chartFont = { family: "'Instrument Sans', sans-serif" };
    const trendCtx = document.getElementById('trendChart');

    if (trendCtx) {
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: @json($chart['labels']),
                datasets: [
                    {
                        label: 'Omzet',
                        data: @json($chart['revenue']),
                        borderColor: '#5b3df5',
                        backgroundColor: 'rgba(91, 61, 245, .12)',
                        fill: true,
                        tension: .35,
                        pointRadius: 3,
                        pointBackgroundColor: '#5b3df5',
                        borderWidth: 2
                    },
                    {
                        label: 'Pesanan',
                        data: @json($chart['orders']),
                        borderColor: '#f59e0b',
                        backgroundColor: 'rgba(245, 158, 11, .10)',
                        fill: false,
                        tension: .35,
                        pointRadius: 3,
                        pointBackgroundColor: '#f59e0b',
                        borderWidth: 2,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'bottom', labels: { font: chartFont, boxWidth: 10, padding: 14 } },
                    tooltip: {
                        callbacks: {
                            label: (item) => item.dataset.label === 'Omzet'
                                ? 'Omzet: Rp' + Number(item.raw).toLocaleString('id-ID')
                                : 'Pesanan: ' + item.raw
                        }
                    }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { callback: (v) => 'Rp' + (v / 1000) + 'k', font: chartFont }, grid: { color: '#f0eef6' } },
                    y1: { beginAtZero: true, position: 'right', ticks: { precision: 0, font: chartFont }, grid: { display: false } },
                    x: { ticks: { font: chartFont, maxTicksLimit: 10 }, grid: { display: false } }
                }
            }
        });
    }
</script>
@endpush
