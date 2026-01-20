<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title','Kepegawaian')</title>

    {{-- Font Poppins --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

    {{-- ================= CSS Khusus Kepegawaian ================= --}}
    <link rel="stylesheet" href="{{ asset('css/kepegawaian/base.css') }}?v={{ filemtime(public_path('css/kepegawaian/base.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/kepegawaian/dashboard.css') }}?v={{ filemtime(public_path('css/kepegawaian/dashboard.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/kepegawaian/detail-pegawai.css') }}?v={{ filemtime(public_path('css/kepegawaian/detail-pegawai.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/kepegawaian/detail-riwayat.css') }}?v={{ filemtime(public_path('css/kepegawaian/detail-riwayat.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/kepegawaian/laporan.css') }}?v={{ filemtime(public_path('css/kepegawaian/laporan.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/kepegawaian/riwayat.css') }}?v={{ filemtime(public_path('css/kepegawaian/riwayat.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/kepegawaian/dokter-pemeriksa.css') }}?v={{ filemtime(public_path('css/kepegawaian/dokter-pemeriksa.css')) }}">



    @stack('styles')

    
</head>

<style>
    .btn-back {
        display: flex;
        align-items: center;
        text-decoration: none;
        color: inherit;
        margin-right: 16px;
    }

    .btn-back img {
        width: 24px;
        height: 24px;
    }

    /* ============================= */
    /* SIDEBAR */
    /* ============================= */
    .sidebar {
        width: 240px;
        height: 100vh;
        position: fixed;
        left: 0;
        top: 0;
        background: #316BA1;
        padding: 28px 20px;
        color: white;
        display: flex;
        flex-direction: column;
    }

    .sidebar h2 {
        font-size: 18px;
        color: #B4F2F4;
        margin-bottom: 4px;
    }

    .sidebar small {
        font-size: 12px;
        opacity: 0.85;
    }

</style>
<body>

    {{-- ================= SIDEBAR ================= --}}
    <div class="sidebar">
        <h2>SISTEM POLIKLINIK</h2>
        <small>ADMIN KEPEGAWAIAN PANEL</small>

        <div class="menu">

            <a href="{{ route('kepegawaian.dashboard') }}"
               class="{{ request()->routeIs('kepegawaian.dashboard') ? 'active' : '' }}">
                <img src="{{ asset('assets/adminPoli/dashboard.png') }}">
                <span>Dashboard</span>
            </a>

            <a href="{{ route('kepegawaian.pegawai') }}"
               class="{{ request()->routeIs('kepegawaian.pegawai*') ? 'active' : '' }}">
                <img src="{{ asset('assets/adminPoli/saran.png') }}">
                <span>Data Pegawai</span>
            </a>

            <a href="{{ route('kepegawaian.riwayat') }}"
               class="{{ request()->routeIs('kepegawaian.riwayat*') ? 'active' : '' }}">
                <img src="{{ asset('assets/adminPoli/artikel.png') }}">
                <span>Riwayat Pemeriksaan</span>
            </a>

            <a href="{{ route('kepegawaian.laporan') }}"
               class="{{ request()->routeIs('kepegawaian.laporan*') ? 'active' : '' }}">
                <img src="{{ asset('assets/adminPoli/laporan.png') }}">
                <span>Laporan</span>
            </a>

            <a href="{{ route('kepegawaian.dokter_pemeriksa.index') }}"
            class="{{ request()->is('kepegawaian/dokter_pemeriksa*') ? 'active' : '' }}">
                <span>Dokter & Pemeriksa</span>
            </a>

        </div>

        <form action="{{ route('logout') }}" method="POST" class="logout-form">
            @csrf
            <button type="submit" class="logout-btn">
                <img src="{{ asset('assets/adminPoli/logout.png') }}">
                Logout
            </button>
        </form>
    </div>

    {{-- ================= MAIN CONTENT ================= --}}
    <div class="main">
        @yield('content')
    </div>

</body>
</html>
