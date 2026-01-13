@extends('pasien.layout')

@section('title','Dashboard Pasien')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/pasien.css') }}">
@endpush

@section('content')

{{-- HERO --}}
<section class="hero">
    <div class="hero-left">
        <h4>Selamat Datang, {{ Auth::user()->nama ?? 'User' }}!</h4>
        <h1>Pantau riwayat kesehatanmu<br>dengan lebih mudah</h1>

        <a href="#" class="btn-konsultasi">
            <i class="fas fa-phone"></i> Konsultasi Online
        </a>
    </div>

    <div class="hero-right">
        <img src="{{ asset('images/doctor-hero.png') }}">
    </div>
</section>


<div class="page-container">

    {{-- JADWAL DOKTER --}}
    <section class="section">
        <h2>Jadwal Dokter</h2>

        <div class="doctor-grid">
            @forelse($jadwalDokter as $jadwal)
            <div class="doctor-card">
                <img src="{{ asset('images/doctors/'.$jadwal->dokter->foto) }}" class="doctor-img">

                <div class="doctor-info">
                    <h4>{{ $jadwal->dokter->nama }}</h4>
                    <span>{{ $jadwal->dokter->spesialisasi }}</span>
                </div>

                <div class="doctor-time">
                    <p>{{ $jadwal->hari }}</p>
                    <p>{{ substr($jadwal->jam_mulai,0,5) }} - {{ substr($jadwal->jam_selesai,0,5) }}</p>
                </div>
            </div>
            @empty
            <div class="doctor-card">
                <img src="{{ asset('images/doctor.png') }}" class="doctor-img">
                <div class="doctor-info">
                    <h4>Dr. Nama Dokter</h4>
                    <span>Spesialis Anak</span>
                </div>
                <div class="doctor-time">
                    <p>Senin</p>
                    <p>07.00 - 10.00</p>
                </div>
            </div>
            @endforelse
        </div>
    </section>


    {{-- ARTIKEL --}}
    <section class="section">
        <div class="section-header">
            <h2>Artikel Kesehatan</h2>
            <a href="{{ route('artikel.index') }}">Lihat Semua →</a>
        </div>

        <div class="article-grid">
            @forelse($articles as $article)
            <div class="article-card">
                <img src="{{ asset('images/articles/'.$article->gambar) }}">
                <h4>{{ $article->judul }}</h4>
                <small>{{ $article->created_at->format('d F Y') }}</small>
            </div>
            @empty
                @for($i=0;$i<4;$i++)
                <div class="article-card">
                    <img src="{{ asset('images/article.png') }}">
                    <h4>Apa Bedanya Superflu dengan Flu Biasa?</h4>
                    <small>15 Desember 2025</small>
                </div>
                @endfor
            @endforelse
        </div>
    </section>


    {{-- TENTANG KAMI --}}
    <section class="about">
        <h2>Tentang Kami</h2>
        <p>
            Poliklinik PT PLN Indonesia Power UBP Mrica merupakan fasilitas kesehatan yang
            menyediakan layanan medis untuk pegawai, keluarga pegawai, dan pensiunan.
        </p>

        <div class="about-grid">
            <div>📅 Senin – Jumat</div>
            <div>🕘 07.00 – 16.00</div>
            <div>📞 0286-xxxx-xxxx</div>
            <div>📍 Banyumas – Banjarnegara KM 8</div>
        </div>
    </section>

</div>

@endsection
