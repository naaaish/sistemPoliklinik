@extends('layouts.pasien')

@section('title', 'Rincian Pemeriksaan')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/detail-pemeriksaan.css') }}">
@endpush

@section('content')

<div class="detail-container">
    
    {{-- HEADER --}}
    <div class="detail-header">
        <svg xmlns="http://www.w3.org/2000/svg" width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
            <polyline points="14 2 14 8 20 8"></polyline>
            <line x1="16" y1="13" x2="8" y2="13"></line>
            <line x1="16" y1="17" x2="8" y2="17"></line>
            <polyline points="10 9 9 9 8 9"></polyline>
        </svg>
        <h1>Rincian Pemeriksaan</h1>
    </div>

    {{-- MAIN CARD --}}
    <div class="detail-card">
        
        {{-- DATA PENDAFTARAN --}}
        <h2 class="section-title">Data Pendaftaran Pasien</h2>
        <div class="data-grid">
            <div class="data-item">
                <span class="data-label">No. Registrasi :</span>
                <span class="data-value">{{ $pendaftaran->id_pendaftaran }}</span>
            </div>
            <div class="data-item">
                <span class="data-label">Nama Pasien :</span>
                <span class="data-value">{{ $pasien->nama_pasien }}</span>
            </div>
            <div class="data-item">
                <span class="data-label">Tanggal Periksa :</span>
                <span class="data-value">{{ \Carbon\Carbon::parse($pendaftaran->tanggal)->translatedFormat('l, d F Y, H:i') }}</span>
            </div>
            <div class="data-item">
                <span class="data-label">Nama Pemeriksa :</span>
                <span class="data-value">{{ $dokter->nama }}</span>
            </div>
            <div class="data-item">
                <span class="data-label">Hubungan Keluarga :</span>
                <span class="data-value">{{ ucfirst($pasien->hub_kel) }}</span>
            </div>
            <div class="data-item">
                <span class="data-label">NIP :</span>
                <span class="data-value">{{ $pegawai->nip ?? '-' }}</span>
            </div>
            <div class="data-item">
                <span class="data-label">Tanggal Lahir :</span>
                <span class="data-value">{{ \Carbon\Carbon::parse($pasien->tgl_lahir)->translatedFormat('d F Y') }}</span>
            </div>
            <div class="data-item">
                <span class="data-label">Nama Pegawai :</span>
                <span class="data-value">{{ $pegawai->nama_pegawai ?? '-' }}</span>
            </div>
            <div class="data-item">
                <span class="data-label">Umur :</span>
                <span class="data-value">{{ \Carbon\Carbon::parse($pasien->tgl_lahir)->age }} tahun</span>
            </div>
            <div class="data-item">
                <span class="data-label">Bidang :</span>
                <span class="data-value">{{ $pegawai->bidang ?? '-' }}</span>
            </div>
        </div>

        {{-- PEMERIKSAAN KESEHATAN --}}
        <h2 class="section-title">Pemeriksaan Kesehatan</h2>
        <div class="health-metrics">
            <div class="metric-card metric-red">
                <div class="metric-label">Sistol</div>
                <div class="metric-value">{{ $pemeriksaan->sistol }} mmHg</div>
            </div>
            
            <div class="metric-card metric-red">
                <div class="metric-label">Diastol</div>
                <div class="metric-value">{{ $pemeriksaan->diastol }} mmHg</div>
            </div>
            
            <div class="metric-card metric-pink">
                <div class="metric-label">Nadi</div>
                <div class="metric-value">{{ $pemeriksaan->nadi }} bpm</div>
            </div>
            
            <div class="metric-card metric-yellow">
                <div class="metric-label">Gula Darah Puasa</div>
                <div class="metric-value">{{ $pemeriksaan->gd_puasa }} mg/dL</div>
            </div>
            
            <div class="metric-card metric-yellow">
                <div class="metric-label">Gula Darah 2 Jam PP</div>
                <div class="metric-value">{{ $pemeriksaan->gd_duajam }} mg/dL</div>
            </div>
            
            <div class="metric-card metric-yellow">
                <div class="metric-label">Gula Darah Sewaktu</div>
                <div class="metric-value">{{ $pemeriksaan->gd_sewaktu }} mg/dL</div>
            </div>
            
            <div class="metric-card metric-purple">
                <div class="metric-label">Asam Urat</div>
                <div class="metric-value">{{ $pemeriksaan->asam_urat }} mg/dL</div>
            </div>
            
            <div class="metric-card metric-orange">
                <div class="metric-label">Cholesterol</div>
                <div class="metric-value">{{ $pemeriksaan->chol }} mg/dL</div>
            </div>
            
            <div class="metric-card metric-orange">
                <div class="metric-label">Trigliserida</div>
                <div class="metric-value">{{ $pemeriksaan->tg }} mg/dL</div>
            </div>
            
            <div class="metric-card metric-blue">
                <div class="metric-label">Suhu</div>
                <div class="metric-value">{{ $pemeriksaan->suhu }} °C</div>
            </div>
            
            <div class="metric-card metric-green">
                <div class="metric-label">Tinggi Badan</div>
                <div class="metric-value">{{ $pemeriksaan->tinggi }} cm</div>
            </div>
            
            <div class="metric-card metric-green">
                <div class="metric-label">Berat Badan</div>
                <div class="metric-value">{{ $pemeriksaan->berat }} kg</div>
            </div>
        </div>

        {{-- DIAGNOSA DOKTER --}}
        <div class="info-box">
            <div class="info-header">
                <h3>Diagnosa Dokter</h3>
            </div>
            <div class="info-content">
                <p>{{ $diagnosa->diagnosa ?? '-' }}</p>
            </div>
        </div>

        {{-- SARAN DOKTER --}}
        <div class="info-box">
            <div class="info-header">
                <h3>Saran Dokter</h3>
            </div>
            <div class="info-content">
                <p>{{ $saran->saran ?? '-' }}</p>
            </div>
        </div>

        {{-- DATA RESEP OBAT --}}
        <h2 class="section-title">Data Resep Obat</h2>
        <table class="resep-table">
            <thead>
                <tr>
                    <th width="60">No</th>
                    <th>Nama Obat</th>
                    <th width="120">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @forelse($detailResep as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}.</td>
                    <td>{{ $item->nama_obat }}</td>
                    <td>{{ $item->jumlah }} {{ $item->satuan }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" style="text-align: center; color: #52606d;">Tidak ada resep obat</td>
                </tr>
                @endforelse
            </tbody>
        </table>

    </div>

</div>

@endsection