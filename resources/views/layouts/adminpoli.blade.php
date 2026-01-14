<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', 'Admin Poliklinik')</title>

    {{-- Font Poppins --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">

    {{-- CSS khusus AdminPoli --}}
    <link rel="stylesheet" href="{{ asset('css/adminpoli.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
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
            <a class="ap-nav-item active" href="{{ route('adminpoli.dashboard') }}">
                <img src="{{ asset('assets/adminPoli/dashboard.png') }}" alt="dashboard">
                <span>Dashboard</span>
            </a>

            <a class="ap-nav-item" href="#">
                <img src="{{ asset('assets/adminPoli/obat.png') }}" alt="obat">
                <span>Obat</span>
            </a>

            <a class="ap-nav-item" href="#">
                <img src="{{ asset('assets/adminPoli/saran.png') }}" alt="saran">
                <span>Saran</span>
            </a>

            <a class="ap-nav-item" href="#">
                <img src="{{ asset('assets/adminPoli/diagnosa.png') }}" alt="diagnosa">
                <span>Diagnosa K3</span>
            </a>

            <a class="ap-nav-item" href="#">
                <img src="{{ asset('assets/adminPoli/pemeriksaan.png') }}" alt="pemeriksaan">
                <span>Pemeriksaan Pasien</span>
            </a>

            <a class="ap-nav-item" href="#">
                <img src="{{ asset('assets/adminPoli/doctor.png') }}" alt="dokter">
                <span>Dokter/Pemeriksa</span>
            </a>

            <a class="ap-nav-item" href="#">
                <img src="{{ asset('assets/adminPoli/laporan.png') }}" alt="laporan">
                <span>Laporan & Dokumen</span>
            </a>

            <a class="ap-nav-item" href="#">
                <img src="{{ asset('assets/adminPoli/artikel.png') }}" alt="artikel">
                <span>Artikel</span>
            </a>
        </nav>

        <div class="ap-logout">
            <img src="{{ asset('assets/adminPoli/masuk.png') }}" alt="logout">
            <a href="#" class="ap-logout-link">Logout</a>
        </div>
    </aside>

    {{-- Main --}}
    <main class="ap-main">
        @yield('content')
    </main>
    <script>
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 1800,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });
    </script>

    @if(session('success'))
    <script>
        Toast.fire({
            icon: 'success',
            title: "{{ session('success') }}"
        });
    </script>
    @endif

    @if(session('error'))
    <script>
        Toast.fire({
            icon: 'error',
            title: "{{ session('error') }}"
        });
    </script>
    @endif
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if(session('success'))
<script>
  Swal.fire({
    toast: true,
    position: 'top-end',
    icon: 'success',
    // title: @json(session('success')),
    showConfirmButton: false,
    timer: 1600,
    timerProgressBar: true,
  });
</script>
@endif

</body>
</html>
