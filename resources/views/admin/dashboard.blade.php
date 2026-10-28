@extends('layouts.admin')

@section('title', 'Dashboard')
@section('crumb', 'Ringkasan toko')

@section('content')
<div class="stat-grid">
@if($isOwner)
    <div class="stat-card">
        <div class="stat-icon green">&#128176;</div>
        <div>
            <div class="stat-label">Omzet hari ini</div>
            <div class="stat-value">{{ rupiah($todayRevenue) }}</div>
            <div class="stat-note">{{ $todayOrders }} pesanan dibuat hari ini</div>
        </div>
    </div>
@else
    <div class="stat-card">
        <div class="stat-icon green">&#128230;</div>
        <div>
            <div class="stat-label">Pesanan hari ini</div>
            <div class="stat-value">{{ $todayOrders }}</div>
            <div class="stat-note">Transaksi masuk hari ini</div>
        </div>
    </div>
@endif
    @if($isOwner)
    <div class="stat-card">
        <div class="stat-icon purple">&#128200;</div>
        <div>
            <div class="stat-label">Omzet bulan ini</div>
            <div class="stat-value">{{ rupiah($monthRevenue) }}</div>
            <div class="stat-note">{{ now()->translatedFormat('F Y') }}</div>
        </div>
    </div>
    @endif
    <div class="stat-card">
        <div class="stat-icon amber">&#9203;</div>
        <div>
            <div class="stat-label">Pesanan pending</div>
            <div class="stat-value">{{ $pendingOrders }}</div>
            <div class="stat-note">Menunggu pembayaran</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red">&#9888;</div>
        <div>
            <div class="stat-label">Stok menipis</div>
            <div class="stat-value">{{ $lowStock }}</div>
            <div class="stat-note">Produk perlu restock</div>
        </div>
    </div>
</div>

@if($isOwner)
<div class="admin-grid-2">
    <div class="card">
        <div class="panel-head">
            <div>
                <h3>Tren penjualan</h3>
                <p>Omzet pesanan terbayar 30 hari terakhir</p>
            </div>
            <span class="pill pill-purple" style="background:var(--primary-soft);color:var(--primary)">30 hari</span>
        </div>
        <div class="panel-body">
            <div class="chart-box"><canvas id="revenueChart"></canvas></div>
        </div>
    </div>

    <div class="card">
        <div class="panel-head">
            <div>
                <h3>Produk terlaris</h3>
                <p>Top 10 berdasarkan unit terjual</p>
            </div>
        </div>
        <div class="panel-body">
            <div class="chart-box"><canvas id="topProductChart"></canvas></div>
        </div>
    </div>
</div>

<div class="admin-grid-2">
    <div class="card">
        <div class="panel-head">
            <div>
                <h3>Volume pesanan</h3>
                <p>Jumlah pesanan terbayar per hari</p>
            </div>
        </div>
        <div class="panel-body">
            <div class="chart-box small"><canvas id="ordersChart"></canvas></div>
        </div>
    </div>

    <div class="card">
        <div class="panel-head">
            <div>
                <h3>Status pesanan</h3>
                <p>Distribusi seluruh pesanan</p>
            </div>
        </div>
        <div class="panel-body">
            <div class="chart-box small"><canvas id="statusChart"></canvas></div>
        </div>
    </div>
</div>
@endif

<div class="card">
    <div class="panel-head">
        <div>
            <h3>Pesanan terbaru</h3>
            <p>8 pesanan terakhir yang masuk</p>
        </div>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline btn-sm">Semua pesanan</a>
        <a href="{{ route('admin.pos.create') }}" class="btn btn-sm">+ Buat Pesanan</a>
    </div>
    <div class="table-wrap" style="border:0;border-radius:0">
        <table class="table">
            <thead>
                <tr>
                    <th>No. Pesanan</th>
                    <th>Pembeli</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Waktu</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentOrders as $order)
                    <tr>
                        <td class="bold">{{ $order->order_number }}</td>
                        <td>{{ $order->customer_name }}</td>
                        <td class="bold nowrap">{{ rupiah($order->total) }}</td>
                        <td><span class="pill pill-{{ $order->statusColor() }}">{{ $order->statusLabel() }}</span></td>
                        <td class="small muted">{{ $order->created_at->diffForHumans() }}</td>
                        <td><a href="{{ route('admin.orders.show', $order) }}" class="btn btn-outline btn-sm">Detail</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center muted" style="padding:2rem">Belum ada pesanan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
@if($isOwner)
<script>
    const chartFont = { family: "'Plus Jakarta Sans', sans-serif" };
    const currency = (value) => 'Rp' + new Intl.NumberFormat('id-ID').format(value);

    const revenueCtx = document.getElementById('revenueChart');
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: @json($chart['labels']),
            datasets: [{
                label: 'Omzet',
                data: @json($chart['revenue']),
                borderColor: '#5b3df5',
                backgroundColor: 'rgba(91,61,245,.12)',
                fill: true,
                tension: .38,
                pointRadius: 2,
                borderWidth: 2.5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: (ctx) => currency(ctx.parsed.y) } }
            },
            scales: {
                y: { beginAtZero: true, ticks: { callback: (v) => 'Rp' + (v / 1000) + 'k', font: chartFont }, grid: { color: '#f0eef6' } },
                x: { ticks: { font: chartFont, maxTicksLimit: 8 }, grid: { display: false } }
            }
        }
    });

    const topCtx = document.getElementById('topProductChart');
    new Chart(topCtx, {
        type: 'bar',
        data: {
            labels: @json($topProducts->pluck('product_name')),
            datasets: [{
                label: 'Unit terjual',
                data: @json($topProducts->pluck('sold')),
                backgroundColor: 'rgba(91,61,245,.75)',
                borderRadius: 6,
                barThickness: 14
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { beginAtZero: true, ticks: { precision: 0, font: chartFont }, grid: { color: '#f0eef6' } },
                y: { ticks: { font: chartFont, callback: function (value) { const label = this.getLabelForValue(value); return label.length > 22 ? label.slice(0, 22) + '…' : label; } }, grid: { display: false } }
            }
        }
    });

    const ordersCtx = document.getElementById('ordersChart');
    new Chart(ordersCtx, {
        type: 'bar',
        data: {
            labels: @json($chart['labels']),
            datasets: [{
                label: 'Pesanan',
                data: @json($chart['orders']),
                backgroundColor: 'rgba(255,92,138,.7)',
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0, font: chartFont }, grid: { color: '#f0eef6' } },
                x: { ticks: { font: chartFont, maxTicksLimit: 8 }, grid: { display: false } }
            }
        }
    });

    const statusLabels = @json($statusCounts->keys());
    const statusValues = @json($statusCounts->values());
    const statusColors = { pending: '#f4b63f', paid: '#12976a', diproses: '#2f6fed', dikirim: '#4c4fce', selesai: '#0e8a8a', batal: '#d64545' };
    const statusNames = { pending: 'Menunggu', paid: 'Dibayar', diproses: 'Diproses', dikirim: 'Dikirim', selesai: 'Selesai', batal: 'Batal' };

    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: statusLabels.map((s) => statusNames[s] || s),
            datasets: [{
                data: statusValues,
                backgroundColor: statusLabels.map((s) => statusColors[s] || '#c9c3dd'),
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '64%',
            plugins: { legend: { position: 'bottom', labels: { font: chartFont, boxWidth: 10, padding: 14 } } }
        }
    });
</script>
@endif
@endpush
