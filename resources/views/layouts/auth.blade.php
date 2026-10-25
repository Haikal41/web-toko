<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Masuk') — {{ store_name() }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <div class="auth-wrap">
        <aside class="auth-side">
            <a href="{{ route('home') }}" class="brand" style="color:#fff">
                <span class="brand-mark" style="background:#fff;color:#5b3df5">M</span>
                <span>{{ store_name() }}</span>
            </a>

            <div>
                <span class="eyebrow" style="background:rgba(255,255,255,.15);color:#fff">Aksesoris Custom</span>
                <h2>Hadiah kecil,<br>kenangan besar.</h2>
                <p>Piala ukir, pigura personal, dan gantungan kunci lucu — semua bisa kamu kustom sesuai cerita.</p>
            </div>

            <div class="auth-quote">
                &ldquo;Setiap aksesoris dibuat dengan detail, karena momen berharga layak dirayakan dengan cara yang istimewa.&rdquo;
                <div style="margin-top:.6rem;font-weight:700;color:#fff">&mdash; {{ store_name() }}</div>
            </div>
        </aside>

        <div class="auth-form">
            <div class="auth-card">
                <a href="{{ route('home') }}" class="tiny muted" style="display:inline-block;margin-bottom:1.2rem">&larr; Kembali ke toko</a>

                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
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

                @yield('content')
            </div>
        </div>
    </div>

    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
