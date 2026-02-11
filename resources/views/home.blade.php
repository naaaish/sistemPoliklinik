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

            @guest
                <a href="{{ route('login') }}" class="btn-primary">
                    Login
                </a>
            @endguest

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
                @forelse($dokter as $d)
                    @php
                    $hp = preg_replace('/\D+/', '', $d->no_telepon ?? '');
                    // normalisasi: kalau mulai 0 => jadi 62xxxx
                    if (strlen($hp) > 0 && $hp[0] === '0') $hp = '62'.substr($hp,1);
                    $label = $d->nama;
                    $text = rawurlencode("Halo, saya ingin konsultasi dengan {$label}");
                    @endphp

                    <a href="https://wa.me/{{ $hp }}?text={{ $text }}"
                    target="_blank"
                    class="konsultasi-btn">
                    <img src="{{ asset('assets/home/doctor.png') }}" alt="Dokter">
                    <span>{{ $label }}</span>
                    </a>
                @empty
                    <div class="konsultasi-empty">Belum ada dokter aktif.</div>
                @endforelse
                </div>
            </div>

            {{-- TENAGA KESEHATAN --}}
            <div class="konsultasi-section">
                <h4 class="section-label">Tenaga Kesehatan</h4>
                <div class="konsultasi-buttons">
                @forelse($pemeriksa as $p)
                    @php
                    $hp = preg_replace('/\D+/', '', $p->no_telepon ?? '');
                    if (strlen($hp) > 0 && $hp[0] === '0') $hp = '62'.substr($hp,1);
                    $label = $p->nama_pemeriksa;
                    $text = rawurlencode("Halo, saya ingin konsultasi dengan {$label}");
                    @endphp

                    <a href="https://wa.me/{{ $hp }}?text={{ $text }}"
                    target="_blank"
                    class="konsultasi-btn">
                    <img src="{{ asset('assets/home/chat.png') }}" alt="Pemeriksa">
                    <span>{{ $label }}</span>
                    </a>
                @empty
                    <div class="konsultasi-empty">Belum ada pemeriksa aktif.</div>
                @endforelse
                </div>
            </div>

            {{-- APOTEK --}}
            <div class="konsultasi-section">
                <h4 class="section-label">Apotek</h4>
                <div class="konsultasi-buttons">
                    <a href="https://wa.me/6283878860366?text=Halo,%20saya%20ingin%20konsultasi%20dengan%20Apoteker" 
                       target="_blank" 
                       class="konsultasi-btn">
                        <img src="{{ asset('assets/home/drugs.png') }}" alt="Apoteker">
                        <span>Apoteker (Okta)</span>
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