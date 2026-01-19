<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>@yield('title','Sistem Poliklinik')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Font Poppins --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

    {{-- CSS khusus Pasien  --}}
    <link rel="stylesheet" href="{{ asset('css/pasien/base.css') }}?v={{ filemtime(public_path('css/pasien/base.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/pasien/artikel.css') }}?v={{ filemtime(public_path('css/pasien/artikel.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/pasien/artikel-detail.css') }}?v={{ filemtime(public_path('css/pasien/artikel-detail.css')) }}">
    
    <link rel="stylesheet" href="{{ asset('css/pasien/detail-riwayat.css') }}?v={{ filemtime(public_path('css/pasien/detail-riwayat.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/pasien/dashboard.css') }}?v={{ filemtime(public_path('css/pasien/dashboard.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/pasien/riwayat.css') }}?v={{ filemtime(public_path('css/pasien/riwayat.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/pasien/tentang.css') }}?v={{ filemtime(public_path('css/pasien/tentang.css')) }}">

    @stack('styles')
</head>
<body>

<nav class="navbar-pasien">
    <a href="{{ route('home') }}" class="brand">
        <img src="{{ asset('images/logo-poliklinik.png') }}">
        <span>SISTEM POLIKLINIK</span>
    </a>

    <div class="nav-menu">
        <a href="{{ route('tentang') }}">Tentang Kami</a>
        <a href="{{ route('pasien.riwayat') }}">Riwayat Pemeriksaan</a>
        <a href="{{ route('artikel.index.public') }}">Artikel Kesehatan</a>

        @auth
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button class="logout-btn">Logout</button>
        </form>
        @endauth
    </div>
</nav>

@yield('content')

</body>
</html>
