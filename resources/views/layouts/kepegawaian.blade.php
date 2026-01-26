<!DOCTYPE html>

@php
    $mode = $mode ?? null;
@endphp

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
    <link rel="stylesheet" href="{{ asset('css/kepegawaian/pegawai.css') }}?v={{ filemtime(public_path('css/kepegawaian/pegawai.css')) }}">
    
    {{-- Sweet Alert --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>


<script>
setTimeout(function() {
        const alerts = document.querySelectorAll('.custom-alert');
        alerts.forEach(function(alert) {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        });
    }, 4000); // Hilang dalam 4 detik
</script>

    {{-- Sweet Alert --}}
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

.notification-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .toast-alert {
        background: white;
        padding: 16px 24px;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        display: flex;
        align-items: center;
        gap: 15px;
        min-width: 300px;
        animation: slideInRight 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        border: 1px solid #f0f0f0;
    }

    .toast-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    /* Versi Berhasil (Hijau) */
    .toast-success .toast-icon {
        background-color: #ECFDF5;
        border: 2px solid #D1FAE5;
    }
    
    .toast-success svg {
        color: #10B981;
    }

    .toast-content {
        color: #1E293B;
        font-weight: 500;
        font-size: 14px;
        line-height: 1.4;
    }

    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }

    .fade-out {
        opacity: 0;
        transform: translateX(20px);
        transition: all 0.5s ease;
    }

.admin-toast {
            background: #ffffff !important;
            border-radius: 12px !important;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08) !important;
            padding: 10px 15px !important;
        }
        .swal2-html-container {
            font-family: 'Poppins', sans-serif !important;
            font-weight: 500 !important;
            color: #1e293b !important;
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
                <img src="{{ asset('assets/adminPoli/doctor.png') }}" alt="dokter">
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
{{-- <div class="notification-container">
    @if(session('success'))
        <div class="toast-alert toast-success">
            <div class="toast-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </div>
            <div class="toast-content">
                {{ session('success') }}
            </div>
        </div>
    @endif --}}



</div>
<div class="main">
    <div class="content-wrapper">
        @yield('content')
    </div>

    <div class="page-footer">
        Copyright © 2026 Poliklinik PT PLN Indonesia Power UBP Mrica
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 4000, // Hilang otomatis dalam 4 detik
        timerProgressBar: true,
        background: '#ffffff',
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });

    @if(session('success'))
        Toast.fire({ icon: 'success', title: "{{ session('success') }}" });
    @endif

    @if(session('error'))
        Toast.fire({ icon: 'error', title: 'Gagal!', text: "{{ session('error') }}" });
    @endif
});
</script>

</body>
</html>