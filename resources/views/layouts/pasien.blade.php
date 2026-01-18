<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>@yield('title','Sistem Poliklinik')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="{{ asset('css/pasien.css') }}">
    <link rel="stylesheet" href="{{ asset('css/artikel-detail.css') }}">

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
        <a href="{{ route('artikel.index') }}">Artikel Kesehatan</a>

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
