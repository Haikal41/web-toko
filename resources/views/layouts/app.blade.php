<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', store_name().' — Aksesoris untuk Momen Berharga')</title>
    <meta name="description" content="@yield('meta_description', setting('store_description', 'Gantungan kunci, boneka, pigura, dan piala custom untuk hadiah yang berkesan.'))">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('head')
</head>
<body>
    <header class="site-header">
        <div class="container header-inner">
            <a href="{{ route('home') }}" class="brand">
                @if(store_logo())
                    <img src="{{ store_logo() }}" alt="{{ store_name() }}" class="brand-logo">
                @else
                    <span class="brand-mark">M</span>
                @endif
                <span>{{ store_name() }}</span>
            </a>

            <nav class="nav-links">
                <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Beranda</a>
                <a href="{{ route('products.index') }}" class="{{ request()->routeIs('products.*') ? 'active' : '' }}">Produk</a>
                <a href="{{ route('home') }}#kategori">Kategori</a>
                <a href="{{ route('home') }}#kenapa-kami">Kenapa Kami</a>
                @auth
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                    @endif
                    @if(auth()->user()->isKaryawan())
                        <a href="{{ route('attendance.index') }}">Absensi</a>
                    @endif
                @endauth
            </nav>

            <div class="header-actions">
                <button class="icon-btn mobile-toggle" type="button" data-mobile-toggle aria-label="Menu">&#9776;</button>

                @auth
                    <a href="{{ route('account.index') }}" class="icon-btn" title="Akun saya" aria-label="Akun saya">&#128100;</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline btn-sm">Masuk</a>
                @endauth

                <a href="{{ route('cart.index') }}" class="icon-btn" title="Keranjang" aria-label="Keranjang">
                    &#128717;
                    @if($cartCount > 0)
                        <span class="badge-count">{{ $cartCount }}</span>
                    @endif
                </a>
            </div>
        </div>
    </header>

    <main>
        @if(session('success') || session('error') || $errors->any())
            <div class="container" style="padding-top:1.2rem">
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
    </main>

    <footer class="site-footer">
        <div class="container">
            <div class="footer-grid">
                <div>
                    <div class="brand" style="color:#fff;margin-bottom:.8rem">
                        <span class="brand-mark">M</span>
                        <span>{{ store_name() }}</span>
                    </div>
                    <p style="font-size:.86rem;max-width:34ch">
                        {{ setting('store_tagline', 'Aksesoris custom untuk hadiah yang tak terlupakan.') }}
                    </p>
                </div>
                <div>
                    <h4>Belanja</h4>
                    <a href="{{ route('products.index') }}">Semua Produk</a>
                    <a href="{{ route('products.index', ['category' => 'piala']) }}">Piala & Penghargaan</a>
                    <a href="{{ route('products.index', ['category' => 'pigura']) }}">Pigura Custom</a>
                    <a href="{{ route('products.index', ['category' => 'gantungan-kunci']) }}">Gantungan Kunci</a>
                </div>
                <div>
                    <h4>Bantuan</h4>
                    <a href="{{ wa_link(store_phone(), 'Halo, saya ingin bertanya tentang produk.') }}" target="_blank" rel="noopener">Chat WhatsApp</a>
                    <a href="{{ route('account.index') }}">Pesanan Saya</a>
                    <a href="{{ route('cart.index') }}">Keranjang</a>
                </div>
                <div>
                    <h4>Kontak</h4>
                    <p style="font-size:.86rem;margin-bottom:.4rem">{{ setting('store_address', 'Indonesia') }}</p>
                    <p style="font-size:.86rem;margin-bottom:.4rem">
                        <a href="{{ wa_link(store_phone()) }}" target="_blank" rel="noopener">{{ store_phone() }}</a>
                    </p>
                    <p style="font-size:.86rem">
                        <a href="mailto:{{ store_email() }}">{{ store_email() }}</a>
                    </p>
                </div>
            </div>
            <div class="footer-bottom">
                <span>&copy; {{ date('Y') }} {{ store_name() }}. Semua hak dilindungi.</span>
                <span>Dibuat dengan sepenuh hati untuk hadiah terbaik Anda.</span>
            </div>
        </div>
    </footer>

    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>
