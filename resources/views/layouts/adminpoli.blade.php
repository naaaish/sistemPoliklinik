<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin Poliklinik')</title>

    {{-- Font Poppins --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

    {{-- CSS khusus AdminPoli --}}
    <link rel="stylesheet" href="{{ asset('css/adminpoli/base.css') }}?v={{ filemtime(public_path('css/adminpoli/base.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/adminpoli/obat.css') }}?v={{ filemtime(public_path('css/adminpoli/obat.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/adminpoli/modal.css') }}?v={{ filemtime(public_path('css/adminpoli/modal.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/adminpoli/diagnosa.css') }}?v={{ filemtime(public_path('css/adminpoli/diagnosa.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/adminpoli/diagnosak3.css') }}?v={{ filemtime(public_path('css/adminpoli/diagnosak3.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/adminpoli/pemeriksaan.css') }}?v={{ filemtime(public_path('css/adminpoli/pemeriksaan.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/adminpoli/pemeriksaan-detail.css') }}?v={{ filemtime(public_path('css/adminpoli/pemeriksaan-detail.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/adminpoli/saran.css') }}?v={{ filemtime(public_path('css/adminpoli/saran.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/adminpoli/artikel.css') }}?v={{ filemtime(public_path('css/adminpoli/artikel.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/adminpoli/laporan.css') }}?v={{ filemtime(public_path('css/adminpoli/laporan.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/adminpoli/laporan_index.css') }}?v={{ filemtime(public_path('css/adminpoli/laporan_index.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/adminpoli/dokter-pemeriksa.css') }}?v={{ filemtime(public_path('css/adminpoli/dokter-pemeriksa.css')) }}">

    {{-- Sweet Alert --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- Tom Select --}}
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
</head>
@stack('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<body>
<div class="ap-wrap">

    {{-- Sidebar --}}
    <aside class="ap-sidebar">
        <div class="ap-brand">
            <div class="ap-brand__title">SISTEM POLIKLINIK</div>
            <div class="ap-brand__subtitle">ADMIN POLIKLINIK PANEL</div>
        </div>
        <div class="ap-sep"></div>

        <div class="ap-menu-title">Menu</div>

        <nav class="ap-nav">
           <a class="ap-nav-item {{ request()->routeIs('adminpoli.dashboard') ? 'active' : '' }}"
            href="{{ route('adminpoli.dashboard') }}">
                <img src="{{ asset('assets/adminPoli/dashboard.png') }}" alt="dashboard">
                <span>Dashboard</span>
            </a>

            <a class="ap-nav-item {{ request()->routeIs('adminpoli.obat.*') ? 'active' : '' }}"
            href="{{ route('adminpoli.obat.index') }}">
                <img src="{{ asset('assets/adminPoli/obat.png') }}" alt="obat">
                <span>Obat</span>
            </a>

            <a class="ap-nav-item {{ request()->routeIs('adminpoli.saran.*') ? 'active' : '' }}"
            href="{{ route('adminpoli.saran.index') }}">
                <img src="{{ asset('assets/adminPoli/saran.png') }}" alt="saran">
                <span>Saran</span>
            </a>

            <a class="ap-nav-item {{ request()->routeIs('adminpoli.diagnosa.*') ? 'active' : '' }}"
            href="{{ route('adminpoli.diagnosa.index') }}">
            <img src="{{ asset('assets/adminPoli/diagnosa.png') }}" alt="Diagnosa">
            <span>Diagnosa</span>
            </a>

            <a class="ap-nav-item {{ request()->routeIs('adminpoli.pemeriksaan.*') ? 'active' : '' }}"
            href="{{ route('adminpoli.pemeriksaan.index') }}">
                <img src="{{ asset('assets/adminPoli/pemeriksaan.png') }}" alt="pemeriksaan">
                <span>Pemeriksaan Pasien</span>
            </a>

            <a class="ap-nav-item {{ request()->routeIs('adminpoli.laporan.*') ? 'active' : '' }}"
            href="{{ route('adminpoli.laporan.index') }}">
                <img src="{{ asset('assets/adminPoli/laporan.png') }}" alt="laporan">
                <span>Laporan</span>
            </a>

            <a class="ap-nav-item {{ request()->routeIs('adminpoli.dokter_pemeriksa.*') ? 'active' : '' }}"
            href="{{ route('adminpoli.dokter_pemeriksa.index') }}">
                <img src="{{ asset('assets/adminPoli/doctor.png') }}" alt="dokter">
                <span>Dokter/Pemeriksa</span>
            </a>

            <a class="ap-nav-item {{ request()->routeIs('adminpoli.artikel.*') ? 'active' : '' }}"
            href="{{ route('adminpoli.artikel.index') }}">
                <img src="{{ asset('assets/adminPoli/artikel.png') }}" alt="artikel">
                <span>Artikel</span>
            </a>
        </nav>

        {{-- LOGOUT --}}
        <form action="{{ route('logout') }}" method="POST" class="logout-form">
        @csrf
        <button type="submit" class="logout-btn">
            <img src="{{ asset('assets/adminPoli/logout.png') }}" alt="Logout">
            Logout
        </button>
    </form>
    </aside>

    {{-- Main --}}
    <main class="ap-main">
        @yield('content')
    </main>
    <script>
    // untuk konfirmasi (tengah layar)
    window.SwalFix = Swal.mixin({
        heightAuto: false,
        scrollbarPadding: false
    });

    window.AdminPoliToast = SwalFix.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 1800,
        timerProgressBar: true,
        didOpen: (t) => {
        t.addEventListener('mouseenter', Swal.stopTimer);
        t.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });
    </script>

    @if(session('success'))
    <script>
        AdminPoliToast.fire({
            icon: 'success',
            title: "{{ session('success') }}"
        });
    </script>
    @endif

    @if(session('error'))
    <script>
        AdminPoliToast.fire({
            icon: 'error',
            title: "{{ session('error') }}"
        });
    </script>
    @endif
</div>
</body>
</html>
