<!DOCTYPE html>
<html>
<head>
<title>@yield('title')</title>
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>

<div class="admin-layout">

<aside class="sidebar">
    <h3>SISTEM POLIKLINIK</h3>
    <p class="role">ADMIN KEPEGAWAIAN PANEL</p>

    <a href="/kepegawaian">Dashboard</a>
    <a href="/kepegawaian/pegawai">Data Pegawai</a>
    <a href="/kepegawaian/riwayat">Riwayat Pemeriksaan</a>
    <a href="/kepegawaian/laporan">Laporan</a>

    <div class="logout">Logout</div>
</aside>

<main class="content">
    @yield('content')
</main>

</div>

</body>
</html>
