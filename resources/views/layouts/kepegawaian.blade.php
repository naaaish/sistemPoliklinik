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
    <link rel="stylesheet" href="{{ asset('css/kepegawaian/modal.css') }}?v={{ filemtime(public_path('css/kepegawaian/modal.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/kepegawaian/pagination.css') }}?v={{ filemtime(public_path('css/kepegawaian/pagination.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/kepegawaian/kelolauser.css') }}?v={{ filemtime(public_path('css/kepegawaian/kelolauser.css')) }}">


    {{-- Sweet Alert --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    window.AdminPoliToast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        iconColor: '#2ecc71',
        customClass: {
        popup: 'admin-toast'
        },
        didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer)
        toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });
    </script>


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
        <h2>HETORICA</h2>
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

            {{-- ✅ FIX --}}
            <a href="{{ route('kepegawaian.dokter_pemeriksa.index') }}"
            class="{{ request()->routeIs('kepegawaian.dokter_pemeriksa*') ? 'active' : '' }}">
                <img src="{{ asset('assets/adminPoli/doctor.png') }}" alt="dokter">
                <span>Dokter & Pemeriksa</span>
            </a>

            {{-- ✅ FIX --}}
            <a href="{{ route('kepegawaian.kelolaUser.index') }}"
            class="{{ request()->routeIs('kepegawaian.kelolaUser*') ? 'active' : '' }}">
                <img src="{{ asset('assets/adminPoli/akun.png') }}" alt="user">
                <span>Kelola User</span>
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

        <div class="dp-foot">
        Copyright © 2026 Poliklinik PT PLN Indonesia Power UBP Mrica
        </div>
    </div>

    {{-- ================= SCRIPTS STACK  ================= --}}
    @stack('scripts')
</body>
</html>