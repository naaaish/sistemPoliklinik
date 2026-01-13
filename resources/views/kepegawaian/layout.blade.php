<!DOCTYPE html>
<html>
<head>
    <title>@yield('title')</title>
    <link rel="stylesheet" href="{{ asset('css/kepegawaian.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body>

<div class="sidebar">
    <h2>SISTEM POLIKLINIK</h2>
    <small>ADMIN KEPEGAWAIAN PANEL</small>
    <div class="menu">
        <a href="{{ route('kepegawaian.dashboard') }}"
        class="{{ request()->routeIs('kepegawaian.dashboard') ? 'active' : '' }}">
            Dashboard
        </a>

        <a href="{{ route('kepegawaian.pegawai') }}"
        class="{{ request()->routeIs('kepegawaian.pegawai') ? 'active' : '' }}">
            Data Pegawai
        </a>

        <a href="{{ route('kepegawaian.riwayat') }}"
        class="{{ request()->routeIs('kepegawaian.riwayat') ? 'active' : '' }}">
            Riwayat Pemeriksaan
        </a>

        <a href="{{ route('kepegawaian.laporan') }}"
        class="{{ request()->routeIs('kepegawaian.laporan') ? 'active' : '' }}">
            Laporan
        </a>
    </div>

    <!-- LOGOUT -->
    <form action="{{ route('logout') }}" method="POST" class="logout-form">
        @csrf
        <button type="submit" class="logout-btn">Logout</button>
    </form>
</div>

<div class="main">
    @yield('content')
</div>

</body>
</html>
