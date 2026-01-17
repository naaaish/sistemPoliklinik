@extends('layouts.pasien')

@section('title','Dashboard Pasien')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/dashboard-pasien.css') }}">
@endpush

@section('content')

{{-- ================= HERO ================= --}}
<section class="hero">
    <div class="hero-content">
        <span class="hero-badge">
            Selamat Datang, {{ Auth::user()->nama_user ?? 'User' }}!
        </span>

        <h1>
            Pantau riwayat kesehatanmu<br>
            <span>dengan lebih mudah</span>
        </h1>

        <p>
            Akses layanan kesehatan profesional untuk Anda dan keluarga di
            Poliklinik PT PLN Indonesia Power UBP Mrica
        </p>

        <div class="hero-actions">
            <a href="{{ route('login') }}" class="btn-primary">
                Login
            </a>

            <a href="#layanan" class="btn-outline">
                Konsultasi Online
            </a>
        </div>
    </div>

    <div class="hero-image">
        <img src="{{ asset('images/bg5.png') }}" alt="Healthcare">
    </div>
</section>

{{-- ================= LAYANAN ================= --}}
<section id="layanan" class="services">
    <div class="services-grid">

        <div class="service-card">
            <div class="icon">🩺</div>
            <h3>Pemeriksaan Umum</h3>
            <p>Deteksi dini penyakit</p>
        </div>

        <div class="service-card">
            <div class="icon">💊</div>
            <h3>Resep Dokter</h3>
            <p>Resep langsung dari dokter</p>
        </div>

        <div class="service-card">
            <div class="icon">📄</div>
            <h3>Riwayat Medis</h3>
            <p>Akses data kapan saja</p>
        </div>

        <div class="service-card">
            <div class="icon">💬</div>
            <h3>Konsultasi Online</h3>
            <p>Konsultasi jarak jauh</p>
        </div>

    </div>
</section>

{{-- ================= JADWAL DOKTER ================= --}}
<section class="section">
    <div class="container">
        <h2 class="section-title">Jadwal Dokter</h2>

        <div class="doctor-grid">

            @forelse($jadwalDokter as $jadwal)
                <div class="doctor-card-modern">

                    {{-- FOTO --}}
                    <div class="doctor-photo">
                        <img
                            src="https://ui-avatars.com/api/?name={{ urlencode($jadwal->dokter->nama ?? 'Dokter') }}&background=EAF4FF&color=1E3A8A&size=256"
                            alt="{{ $jadwal->dokter->nama }}"
                        >
                    </div>

                    {{-- INFO --}}
                    <div class="doctor-body">
                        <h4>{{ $jadwal->dokter->nama ?? '-' }}</h4>
                        <span>{{ $jadwal->dokter->jenis_dokter ?? '-' }}</span>

                        <div class="doctor-schedule">
                            <p>{{ $jadwal->hari }}</p>
                            <strong>
                                {{ substr($jadwal->jam_mulai,0,5) }} –
                                {{ substr($jadwal->jam_selesai,0,5) }}
                            </strong>
                        </div>
                    </div>

                </div>
            @empty
                <p class="empty">Belum ada jadwal dokter</p>
            @endforelse

        </div>
    </div>
</section>



{{-- ================= TENTANG ================= --}}
<section class="section">
    <div class="container about">
        <h2>Tentang Kami</h2>
        <p>
            Poliklinik PT PLN Indonesia Power UBP Mrica menyediakan layanan kesehatan
            untuk pegawai, keluarga, dan pensiunan.
        </p>

        <div class="about-grid">
            <div>📅 Senin - Jumat</div>
            <div>🕘 07.00 - 16.00</div>
            <div>📞 0286-xxxx-xxxx</div>
            <div>📍 Banyumas - Banjarnegara KM 8</div>
        </div>
    </div>
</section>

@endsection
