<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>@yield('title','Kepegawaian')</title>

    <link rel="stylesheet" href="{{ asset('css/kepegawaian.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body>

<div class="sidebar">
    <h2>SISTEM POLIKLINIK</h2>
    <small>ADMIN KEPEGAWAIAN PANEL</small>

    <div class="menu">

        {{-- DASHBOARD --}}
        <a href="{{ route('kepegawaian.dashboard') }}"
           class="{{ request()->routeIs('kepegawaian.dashboard') ? 'active' : '' }}">
            <img src="{{ asset('assets/adminPoli/dashboard.png') }}" alt="Dashboard">
            <span>Dashboard</span>
        </a>

        {{-- DATA PEGAWAI --}}
        <a href="{{ route('kepegawaian.pegawai') }}"
           class="{{ request()->routeIs('kepegawaian.pegawai*') ? 'active' : '' }}">
            <img src="{{ asset('assets/adminPoli/saran.png') }}" alt="Pegawai">
            <span>Data Pegawai</span>
        </a>

        {{-- RIWAYAT PEMERIKSAAN --}}
        <a href="{{ route('kepegawaian.riwayat') }}"
           class="{{ request()->routeIs('kepegawaian.riwayat*') ? 'active' : '' }}">
            <img src="{{ asset('assets/adminPoli/artikel.png') }}" alt="Riwayat">
            <span>Riwayat Pemeriksaan</span>
        </a>

        {{-- LAPORAN --}}
        <a href="{{ route('kepegawaian.laporan') }}"
           class="{{ request()->routeIs('kepegawaian.laporan*') ? 'active' : '' }}">
            <img src="{{ asset('assets/adminPoli/laporan.png') }}" alt="Laporan">
            <span>Laporan</span>
        </a>

    </div>

    {{-- LOGOUT --}}
    <form action="{{ route('logout') }}" method="POST" class="logout-form">
        @csrf
        <button type="submit" class="logout-btn">
            <img src="{{ asset('assets/adminPoli/logout.png') }}" alt="Logout">
            Logout
        </button>
    </form>
</div>

<div class="main">
    @yield('content')
</div>

</body>
</html>
