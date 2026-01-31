@extends('layouts.pasien')
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') Dashboard</title>
    <link rel="stylesheet" href="{{ asset('css/dashboard-pasien.css') }}">

    @stack('styles') 
</head>

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

            <button class="btn-outline" onclick="openKonsultasiModal()">
                Konsultasi Online
            </button>
        </div>
    </div>

    <div class="hero-image">
        <img src="{{ asset('images/bg5.png') }}" alt="Healthcare">
    </div>
</section>

{{-- ================= MODAL KONSULTASI ================= --}}
<div id="konsultasiModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Pilih Layanan Konsultasi</h3>
            <span class="close-modal" onclick="closeKonsultasiModal()">&times;</span>
        </div>
        
        <div class="modal-body">
            {{-- TENAGA MEDIS --}}
            <div class="konsultasi-section">
                <h4 class="section-label">Tenaga Medis</h4>
                <div class="konsultasi-buttons">
                    <a href="https://wa.me/6281234567890?text=Halo,%20saya%20ingin%20konsultasi%20dengan%20Dokter%20Poliklinik" 
                       target="_blank" 
                       class="konsultasi-btn">
                        <img src="{{ asset('assets/home/doctor.png') }}" alt="Dokter">
                        <span>Dokter Poliklinik</span>
                    </a>
                    <a href="https://wa.me/6281234567891?text=Halo,%20saya%20ingin%20konsultasi%20dengan%20Dokter%20Perusahaan" 
                       target="_blank" 
                       class="konsultasi-btn">
                        <img src="{{ asset('assets/home/doctor.png') }}" alt="Dokter">
                        <span>Dokter Perusahaan</span>
                    </a>
                </div>
            </div>

            {{-- TENAGA KESEHATAN --}}
            <div class="konsultasi-section">
                <h4 class="section-label">Tenaga Kesehatan</h4>
                <div class="konsultasi-buttons">
                    <a href="https://wa.me/6281234567892?text=Halo,%20saya%20ingin%20konsultasi%20dengan%20Perawat" 
                       target="_blank" 
                       class="konsultasi-btn">
                        <img src="{{ asset('assets/home/chat.png') }}" alt="Perawat">
                        <span>Perawat</span>
                    </a>
                </div>
            </div>

            {{-- APOTIK --}}
            <div class="konsultasi-section">
                <h4 class="section-label">Apotik</h4>
                <div class="konsultasi-buttons">
                    <a href="https://wa.me/6281234567893?text=Halo,%20saya%20ingin%20konsultasi%20dengan%20Apoteker" 
                       target="_blank" 
                       class="konsultasi-btn">
                        <img src="{{ asset('assets/home/drugs.png') }}" alt="Apoteker">
                        <span>Apoteker</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function openKonsultasiModal() {
    document.getElementById('konsultasiModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeKonsultasiModal() {
    document.getElementById('konsultasiModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Close modal ketika klik di luar modal
window.onclick = function(event) {
    const modal = document.getElementById('konsultasiModal');
    if (event.target === modal) {
        closeKonsultasiModal();
    }
}
</script>

{{-- ================= LAYANAN ================= --}}
<section id="layanan" class="services">
    <div class="services-grid">

        <div class="service-card">
            <div class="service-icon">
                <img src="{{ asset('assets/home/doctor.png') }}" alt="Pemeriksaan Umum">
            </div>
            <h4>Pemeriksaan Umum</h4>
            <p>Deteksi dini penyakit</p>
        </div>

        <div class="service-card">
            <div class="service-icon">
                <img src="{{ asset('assets/home/drugs.png') }}" alt="Resep Dokter">
            </div>
            <h4>Resep Dokter</h4>
            <p>Resep langsung dari dokter</p>
        </div>

        <div class="service-card">
            <div class="service-icon">
                <img src="{{ asset('assets/home/report.png') }}" alt="Riwayat Medis">
            </div>
            <h4>Riwayat Medis</h4>
            <p>Akses riwayat pemeriksaan kapan saja</p>
        </div>

        <div class="service-card">
            <div class="service-icon">
                <img src="{{ asset('assets/home/chat.png') }}" alt="Konsultasi Online">
            </div>
            <h4>Konsultasi Online</h4>
            <p>Konsultasi jarak jauh melalui WhatsApp</p>
        </div>

    </div>
</section>

{{-- ================= JADWAL DOKTER ================= --}}
<section class="section">
    <div class="container">
        <h2 class="section-title">Jadwal Dokter</h2>

        <div class="doctor-grid">

        @foreach($jadwalDokter as $idDokter => $jadwals)
        @php
            $dokter = $jadwals->first()->dokter;
        @endphp

        <div class="doctor-card-modern">

            <div class="doctor-photo">
                <div class="initials">
                    {{ collect(explode(' ', $dokter->nama))->map(fn($n) => strtoupper($n[0]))->take(2)->implode('') }}
                </div>
            </div>

            <div class="doctor-body">
                <h4>{{ $dokter->nama }}</h4>
                <span>{{ $dokter->jenis_dokter }}</span>

                <div class="doctor-schedule-list">
                    @foreach($jadwals as $jadwal)
                        <div class="doctor-schedule">
                            <p>{{ $jadwal->hari }}</p>
                            <strong>
                                {{ substr($jadwal->jam_mulai,0,5) }} –
                                {{ substr($jadwal->jam_selesai,0,5) }}
                            </strong>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
        @endforeach

        </div>


    </div>
</section>

{{-- ================= ARTIKEL KESEHATAN ================= --}}
<section class="section alt">
    <div class="container">

        <div class="section-header-between">
            <h2 class="section-title">Artikel Kesehatan</h2>
            <a href="{{ route('artikel.index.public') }}" class="lihat-semua">
                Lihat Semua →
            </a>
        </div>

        <div class="artikel-home-grid">
            @foreach($articles as $article)
                <a href="{{ route('artikel.detail.public', $article->id_artikel) }}" class="artikel-home-card">
                    <div class="artikel-home-image">
                        <img src="{{ asset($article->cover_path) }}">
                    </div>
                    <div class="artikel-home-content">
                        <h4>{{ $article->judul_artikel }}</h4>
                        <span>{{ \Carbon\Carbon::parse($article->tanggal)->translatedFormat('d F Y') }}</span>
                    </div>
                </a>
            @endforeach
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
            <div>🕘 10.00 - 15.00</div>
            <div>📞 0286-xxxx-xxxx</div>
            <div>📍 Banyumas - Banjarnegara KM 8</div>
        </div>
    </div>
</section>

@endsection