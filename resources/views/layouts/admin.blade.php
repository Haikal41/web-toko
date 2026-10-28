<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ store_name() }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    @stack('head')
</head>
<body class="admin-body">
    <div class="admin-shell">
        <aside class="admin-sidebar">
            <div class="admin-brand">
                @if(store_logo())
                    <img src="{{ store_logo() }}" alt="{{ store_name() }}" class="brand-logo">
                @else
                    <span class="brand-mark">M</span>
                @endif
                <span>{{ store_name() }}<small>Admin Panel</small></span>
            </div>

            <nav class="admin-nav">
                <div class="label">Utama</div>
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"><span class="ico">&#9636;</span> Dashboard</a>
                <a href="{{ route('admin.orders.index') }}" class="{{ request()->routeIs('admin.orders.*') ? 'active' : '' }}"><span class="ico">&#128230;</span> Pesanan @if(($adminPendingCount ?? 0) > 0)<span class="nav-badge">{{ $adminPendingCount }}</span>@endif</a>
                <a href="{{ route('admin.pos.create') }}" class="{{ request()->routeIs('admin.pos.*') ? 'active' : '' }}"><span class="ico">&#10133;</span> Buat Pesanan</a>
                <a href="{{ route('admin.products.index') }}" class="{{ request()->routeIs('admin.products.index') ? 'active' : '' }}"><span class="ico">&#127873;</span> Produk</a>

                @if(auth()->user()->isOwner())
                    <div class="label">Owner</div>
                    <a href="{{ route('admin.categories.index') }}" class="{{ request()->routeIs('admin.categories.*') ? 'active' : '' }}"><span class="ico">&#128193;</span> Kategori</a>
                    <a href="{{ route('admin.reports.index') }}" class="{{ request()->routeIs('admin.reports.*') ? 'active' : '' }}"><span class="ico">&#128202;</span> Laporan Penjualan</a>
                    <a href="{{ route('admin.coupons.index') }}" class="{{ request()->routeIs('admin.coupons.*') ? 'active' : '' }}"><span class="ico">&#127991;</span> Kupon</a>
                    <a href="{{ route('admin.employees.index') }}" class="{{ request()->routeIs('admin.employees.*') ? 'active' : '' }}"><span class="ico">&#128101;</span> Kelola Kasir</a>

                    <div class="label">Sistem</div>
                    <a href="{{ route('admin.whatsapp.index') }}" class="{{ request()->routeIs('admin.whatsapp.*') ? 'active' : '' }}"><span class="ico">&#128172;</span> WhatsApp</a>
                    <a href="{{ route('admin.settings.index') }}" class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}"><span class="ico">&#9881;</span> Pengaturan</a>
                @endif
            </nav>

            <div class="admin-sidebar-footer">
                <a href="{{ route('home') }}"><span class="ico">&#8599;</span> Lihat Toko</a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" style="background:none;border:0;color:#b9b2d6;padding:.35rem .5rem;cursor:pointer;font-size:.78rem;display:flex;gap:.5rem;align-items:center;width:100%">&#9099; Keluar</button>
                </form>
            </div>
        </aside>

        <div class="admin-main">
            <div class="admin-topbar">
                <div>
                    <div class="crumb">Admin / @yield('crumb', 'Dashboard')</div>
                    <h1>@yield('title', 'Dashboard')</h1>
                </div>
                <div class="admin-user">
                    <span class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                    <div>
                        <div>{{ auth()->user()->name }}</div>
                        <div class="tiny muted" style="font-weight:500">{{ auth()->user()->roleLabel() }}</div>
                    </div>
                </div>
            </div>

            <div class="admin-content">
                @if(session('success') || session('error') || $errors->any())
                    <div>
                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        @if(session('error'))
                            <div class="alert alert-error">{{ session('error') }}</div>
                        @endif
                        @if($errors->any())
                            <div class="alert alert-error">
                                <strong>Periksa kembali:</strong>
                                <ul style="margin:.35rem 0 0 1rem">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>

    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>
