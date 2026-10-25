@extends('layouts.app')

@section('title', store_name().' — Aksesoris untuk Momen Berharga')

@section('content')
<section class="hero">
    <div class="container hero-grid">
        <div>
            <span class="eyebrow">&#10022; Personalized accessories</span>
            <h1>Buat hadiahmu <span class="accent-text">beda dari yang lain</span>.</h1>
            <p class="hero-lead">
                {{ setting('store_description', 'Gantungan kunci, boneka, pigura, dan piala yang bisa dipersonalisasi dengan nama atau pesan spesial. Cocok untuk wisuda, ulang tahun, pernikahan, dan momen berharga lainnya.') }}
            </p>
            <div class="hero-actions">
                <a href="{{ route('products.index') }}" class="btn btn-lg">Lihat Koleksi &rarr;</a>
                <a href="{{ wa_link(store_phone(), 'Halo, saya mau tanya soal custom aksesoris.') }}" target="_blank" rel="noopener" class="btn btn-outline btn-lg">Tanya via WA</a>
            </div>
            <div class="hero-stats">
                <div class="hero-stat"><strong>500+</strong><span>Hadiah terkirim</span></div>
                <div class="hero-stat"><strong>4.9/5</strong><span>Rating pembeli</span></div>
                <div class="hero-stat"><strong>Custom</strong><span>Nama & ucapan</span></div>
            </div>
        </div>

        <div class="hero-visual">
            <div class="hero-card-main">
                <div class="hero-emoji">&#127873;</div>
            </div>
            <div class="float-card one">
                <span class="float-icon">&#9998;</span>
                <div>Nama diukir<span>Personalisasi gratis</span></div>
            </div>
            <div class="float-card two">
                <span class="float-icon">&#128666;</span>
                <div>Dikirim cepat<span>Packing aman</span></div>
            </div>
        </div>
    </div>
</section>

<section class="section-sm" id="kategori">
    <div class="container">
        <div class="section-head">
            <div>
                <span class="eyebrow">Kategori</span>
                <h2>Pilih sesuai momennya</h2>
            </div>
            <a href="{{ route('products.index') }}" class="btn btn-outline btn-sm">Semua produk &rarr;</a>
        </div>

        <div class="category-row">
            @foreach($categories as $category)
                <a href="{{ route('products.index', ['category' => $category->slug]) }}" class="category-chip">
                    <span class="emoji">
                        {{ match($category->slug) {
                            'gantungan-kunci' => '🔑',
                            'boneka' => '🧸',
                            'pigura' => '🖼️',
                            'piala' => '🏆',
                            default => '🎁',
                        } }}
                    </span>
                    <span>{{ $category->name }}<br><small>{{ $category->products_count }} produk</small></span>
                </a>
            @endforeach
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head">
            <div>
                <span class="eyebrow">Best picks</span>
                <h2>Produk favorit pelanggan</h2>
                <p>Aksesoris paling sering dipesan untuk hadiah spesial.</p>
            </div>
        </div>

        @if($featured->isEmpty())
            <div class="card empty-state">
                <div class="emoji">&#128230;</div>
                <p>Belum ada produk yang ditampilkan. Tambahkan produk melalui dashboard admin.</p>
            </div>
        @else
            <div class="grid grid-4">
                @foreach($featured as $product)
                    @include('partials.product-card', ['product' => $product])
                @endforeach
            </div>
        @endif
    </div>
</section>

<section class="section" id="kenapa-kami" style="background:#fff;border-block:1px solid var(--line)">
    <div class="container">
        <div class="section-head">
            <div>
                <span class="eyebrow">Kenapa kami</span>
                <h2>Detail kecil yang bikin berkesan</h2>
            </div>
        </div>
        <div class="grid grid-3">
            <div class="panel card">
                <div class="stat-icon purple" style="margin-bottom:.8rem">&#9998;</div>
                <h3 style="font-size:1rem">Personalisasi nama</h3>
                <p class="muted small mb-0">Tambah nama, tanggal, atau ucapan pada piala dan pigura. Kami kerjakan rapi sesuai permintaan.</p>
            </div>
            <div class="panel card">
                <div class="stat-icon green" style="margin-bottom:.8rem">&#128230;</div>
                <h3 style="font-size:1rem">Packing aman</h3>
                <p class="muted small mb-0">Setiap produk dibungkus berlapis agar sampai di tanganmu dalam kondisi sempurna.</p>
            </div>
            <div class="panel card">
                <div class="stat-icon amber" style="margin-bottom:.8rem">&#128172;</div>
                <h3 style="font-size:1rem">Konfirmasi WhatsApp</h3>
                <p class="muted small mb-0">Setelah pembayaran, kamu langsung dapat notifikasi WhatsApp berisi detail pesanan.</p>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="card" style="padding:2.5rem;background:linear-gradient(130deg,#231a55,#5b3df5 70%,#9a6bff);color:#fff;display:flex;justify-content:space-between;gap:2rem;align-items:center;flex-wrap:wrap">
            <div>
                <span class="eyebrow" style="background:rgba(255,255,255,.16);color:#fff">Butuh yang lebih spesial?</span>
                <h2 class="display" style="font-size:2rem;margin:.7rem 0 .5rem">Custom desain sesuai ceritamu</h2>
                <p style="color:rgba(255,255,255,.82);max-width:52ch;margin:0">Ceritakan ide hadiahmu, kami bantu wujudkan dari pemilihan bahan sampai ukiran nama.</p>
            </div>
            <a href="{{ wa_link(store_phone(), 'Halo, saya ingin konsultasi custom aksesoris.') }}" target="_blank" rel="noopener" class="btn btn-lg" style="background:#fff;color:var(--primary)">Konsultasi Gratis</a>
        </div>
    </div>
</section>
@endsection
