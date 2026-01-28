@extends('layouts.pasien')

@section('title', 'Tentang Kami - SISTEM POLIKLINIK')

@push('styles')

@section('content')
<div class="about-container">
    <div class="page-header">
        <h2>Tentang Kami</h2>
        <p class="text-muted">Mengenal Lebih Dekat Layanan Kesehatan Kami</p>
    </div>

    <div class="about-card-main">
        <div class="about-flex">
            <div class="about-icon-box">
                <i class="fas fa-hospital-user"></i>
            </div>
            <div class="about-text">
                <h3>Poliklinik PT PLN Indonesia Power UBP Mrica</h3>
                <p>
                    Poliklinik PT PLN Indonesia Power UBP Mrica merupakan fasilitas kesehatan yang berkomitmen menyediakan layanan medis terbaik bagi pegawai, keluarga pegawai, serta pensiunan. 
                </p>
                <p>
                    Dengan dukungan tenaga medis profesional dan fasilitas modern, kami memastikan setiap pasien mendapatkan penanganan yang cepat, tepat, dan ramah guna mendukung produktivitas dan kesejahteraan bersama.
                </p>
            </div>
        </div>

        <hr style="margin: 2.5rem 0; border: 0; border-top: 1px solid #eee;">

        <div class="info-grid">
            <div class="info-item">
                <h4><i class="fas fa-clock"></i> Jam Operasional</h4>
                <p><strong>Senin - Jumat</strong><br>10:00 - 15:00 WIB</p>
                <p class="small text-warning mt-2">*Sabtu & Minggu Libur</p>
            </div>

            <div class="info-item">
                <h4><i class="fas fa-phone-alt"></i> Kontak Kami</h4>
                <p>
                    <strong>Telepon:</strong> 0286-xxxx-xxxx<br>
                    <strong>Email:</strong> helpdesk.medis@pln.co.id
                </p>
            </div>

            <div class="info-item">
                <h4><i class="fas fa-map-marker-alt"></i> Lokasi</h4>
                <p>
                    Jl. Raya Banyumas - Banjarnegara KM 8, Mrica, Kec. Bawang, Kab. Banjarnegara, Jawa Tengah 53471.
                </p>
            </div>

            <div class="info-item">
                <h4><i class="fas fa-briefcase-medical"></i> Layanan Unggulan</h4>
                <ul>
                    <li><i class="fas fa-check-circle fa-xs"></i> Pemeriksaan Umum</li>
                    <li><i class="fas fa-check-circle fa-xs"></i> Konsultasi Kesehatan</li>
                    <li><i class="fas fa-check-circle fa-xs"></i> Pemeriksaan Berkala (K3)</li>
                    <li><i class="fas fa-check-circle fa-xs"></i> Farmasi & Resep Obat</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection