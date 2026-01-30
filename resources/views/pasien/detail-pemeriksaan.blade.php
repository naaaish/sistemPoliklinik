@extends('layouts.pasien') 

@section('title', 'Rincian Pemeriksaan')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pasien/detail-riwayat.css') }}">
@endpush

@section('content')
<div class="detail-container">
    
    {{-- HEADER --}}
    <div class="detail-header">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('pasien.riwayat') }}">
                <img src="{{ asset('assets/adminPoli/back-arrow.png') }}" width="38" alt="Back">
            </a>
            <h1>Rincian Pemeriksaan</h1>
        </div>
    </div>

    {{-- MAIN CARD --}}
    <div class="detail-card">
        
        {{-- DATA PENDAFTARAN --}}
        <h2 class="section-title">Data Pendaftaran Pasien</h2>
        <div class="data-grid">
            <div class="data-item">
                <span class="data-label">No. Registrasi :</span>
                <span class="data-value">#{{ $pemeriksaan->id_pendaftaran }}</span>
            </div>
            <div class="data-item">
                <span class="data-label">Nama Pasien :</span>
                <span class="data-value">{{ $pasien->nama_keluarga ?? $pasien->nama_pegawai ?? '-' }}</span>
            </div>
            <div class="data-item">
                <span class="data-label">Tanggal Periksa :</span>
                <span class="data-value">{{ \Carbon\Carbon::parse($pemeriksaan->created_at)->translatedFormat('l, d F Y, H:i') }} WIB</span>
            </div>
            <div class="data-item">
                <span class="data-label">Nama Pemeriksa :</span>
                <span class="data-value">{{ $namaPemeriksa }}</span>
            </div>
            <div class="data-item">
                <span class="data-label">Hubungan Keluarga :</span>
                <span class="data-value">{{ $pasien->hub_kel ?? 'Pegawai' }}</span>
            </div>
            <div class="data-item">
                <span class="data-label">NIP Pegawai :</span>
                <span class="data-value">{{ $pegawai->nip ?? '-' }}</span>
            </div>
        </div>

        {{-- HASIL PEMERIKSAAN LENGKAP --}}
        <h2 class="section-title">Pemeriksaan Kesehatan</h2>
        <div class="health-metrics">
            <div class="metric-card metric-red">
                <div class="metric-label">Sistol</div>
                <div class="metric-value">{{ $pemeriksaan->sistol ?? 0 }} mmHg</div>
            </div>
            <div class="metric-card metric-red">
                <div class="metric-label">Diastol</div>
                <div class="metric-value">{{ $pemeriksaan->diastol ?? 0 }} mmHg</div>
            </div>
            <div class="metric-card metric-pink">
                <div class="metric-label">Nadi</div>
                <div class="metric-value">{{ $pemeriksaan->nadi ?? 0 }} bpm</div>
            </div>
            <div class="metric-card metric-yellow">
                <div class="metric-label">Gula Darah Puasa</div>
                <div class="metric-value">{{ $pemeriksaan->gd_puasa ?? 0 }} mg/dL</div>
            </div>
            <div class="metric-card metric-yellow">
                <div class="metric-label">Gula Darah 2 Jam PP</div>
                <div class="metric-value">{{ $pemeriksaan->gd_duajam ?? 0 }} mg/dL</div>
            </div>
            <div class="metric-card metric-yellow">
                <div class="metric-label">Gula Darah Sewaktu</div>
                <div class="metric-value">{{ $pemeriksaan->gd_sewaktu ?? 0 }} mg/dL</div>
            </div>
            <div class="metric-card metric-purple">
                <div class="metric-label">Asam Urat</div>
                <div class="metric-value">{{ $pemeriksaan->asam_urat ?? 0 }} mg/dL</div>
            </div>
            <div class="metric-card metric-orange">
                <div class="metric-label">Cholesterol</div>
                <div class="metric-value">{{ $pemeriksaan->chol ?? 0 }} mg/dL</div>
            </div>
            <div class="metric-card metric-orange">
                <div class="metric-label">Trigliserida</div>
                <div class="metric-value">{{ $pemeriksaan->tg ?? 0 }} mg/dL</div>
            </div>
            <div class="metric-card metric-blue">
                <div class="metric-label">Suhu</div>
                <div class="metric-value">{{ $pemeriksaan->suhu ?? 0 }} °C</div>
            </div>
            <div class="metric-card metric-green">
                <div class="metric-label">Tinggi Badan</div>
                <div class="metric-value">{{ $pemeriksaan->tinggi ?? 0 }} cm</div>
            </div>
            <div class="metric-card metric-green">
                <div class="metric-label">Berat Badan</div>
                <div class="metric-value">{{ $pemeriksaan->berat ?? 0 }} kg</div>
            </div>
        </div>

        {{-- DIAGNOSA & TERAPI --}}
        <h2 class="section-title">Diagnosa & Terapi</h2>
        
        <div class="info-box">
            <div class="info-header"><h3>Diagnosa Dokter</h3></div>
            <div class="info-content">
                @forelse($diagnosa as $d) <p>• {{ $d->nama_diagnosa }}</p> @empty <p>-</p> @endforelse
            </div>
        </div>

        <div class="info-box">
            <div class="info-header">
                <h3>Diagnosa K3 (NB)</h3>
            </div>

            <div class="info-content">
                @forelse($diagnosa_k3 as $nb)
                    <p>• <strong>[{{ $nb }}]</strong></p>
                @empty
                    <p class="text-muted italic">Tidak ada data diagnosa K3.</p>
                @endforelse
            </div>
        </div>


        <div class="info-box">
            <div class="info-header" style="background: #64748b;"><h3>Saran & Anjuran</h3></div>
            <div class="info-content">
                @forelse($saran as $s) <p>• {{ $s->isi_saran }}</p> @empty <p>-</p> @endforelse
            </div>
        </div>

        {{-- RESEP (HANYA NAMA & JUMLAH) --}}
        <h2 class="section-title">Resep Obat</h2>
        <table style="width:100%; border-collapse: collapse; margin-top: 15px;">
            <thead style="background: #f1f5f9;">
                <tr>
                    <th style="padding:15px; text-align:left; color: #64748b; font-size: 14px;">Nama Obat</th>
                    <th style="padding:15px; text-align:center; color: #64748b; font-size: 14px;">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @forelse($detailResep as $item)
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding:15px; font-weight: 500;">{{ $item->nama_obat }}</td>
                        <td style="padding:15px; text-align:center;">{{ $item->jumlah }} {{ $item->satuan }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" style="padding:30px; text-align:center; color: #94a3b8;">Tidak ada resep obat</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection