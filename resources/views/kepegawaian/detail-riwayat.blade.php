@extends('layouts.kepegawaian')

@section('title','Rincian Riwayat Pemeriksaan')

@section('content')


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
                <span class="data-value">
                    {{ $pasien->nama_keluarga ?? $pasien->nama_pegawai ?? '-' }}
                </span>
            </div>
            <div class="data-item">
                <span class="data-label">Tanggal Periksa :</span>
                <span class="data-value">{{ \Carbon\Carbon::parse($pendaftaran->tanggal)->translatedFormat('l, d F Y, H:i') }}</span>
            </div>
            <div class="data-item">
                <span class="data-label">Nama Pemeriksa :</span>
                <span class="data-value">{{ $namaPemeriksa }}</span>
            </div>
            <div class="data-item">
                <span class="data-label">Hubungan Keluarga :</span>
                <span class="data-value">
                    {{ $pasien->hub_kel ?? 'Pegawai' }}
                </span>

            </div>
            <div class="data-item">
                <span class="data-label">NIP :</span>
                <span class="data-value">{{ $pegawai->nip ?? '-' }}</span>
            </div>
            <div class="data-item">
                <span class="data-label">Tanggal Lahir :</span>
                <span class="data-value">
                    {{ $pasien->tgl_lahir 
                        ? \Carbon\Carbon::parse($pasien->tgl_lahir)->translatedFormat('d F Y') 
                        : '-' 
                    }}
                </span>
            </div>
            <div class="data-item">
                <span class="data-label">Nama Pegawai :</span>
                <span class="data-value">{{ $pegawai->nama_pegawai ?? '—' }}</span>
            </div>
            <div class="data-item">
                <span class="data-label">Umur :</span>
                <span class="data-value">{{ \Carbon\Carbon::parse($pasien->tgl_lahir)->age }} tahun</span>
            </div>
            <div class="data-item">
                <span class="data-label">Bagian :</span>
                <span class="data-value">{{ $pegawai->bagian ?? '-' }}</span>
            </div>
        </div>

        {{-- PEMERIKSAAN KESEHATAN --}}
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

        {{-- DIAGNOSA DOKTER --}}
        <div class="info-box">
            <div class="info-header">
                <h3>Diagnosa Dokter</h3>
            </div>
            <div class="info-content">
                @if($diagnosa->count())
                    @foreach($diagnosa as $d)
                        <p>• {{ $d->nama_diagnosa }}</p>
                    @endforeach
                @else
                    <p>-</p>
                @endif
            </div>
        </div>

        {{-- SARAN DOKTER --}}
        <div class="info-box">
            <div class="info-header">
                <h3>Saran Dokter</h3>
            </div>
            <div class="info-content">
                @if($saran->count())
                    @foreach($saran as $s)
                        <p>• {{ $s->isi_saran }}</p>
                    @endforeach
                @else
                    <p>-</p>
                @endif
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
                    <th width="150">Harga</th>
                    <th width="150">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @php $total = 0; @endphp
                @forelse($detailResep as $index => $item)
                    @php 
                        $harga = (float) ($item->harga ?? 0);
                        $jumlah = (int) ($item->jumlah ?? 0);
                        $subtotal = $jumlah * $harga;
                        $total += $subtotal;
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}.</td>
                        <td>{{ $item->nama_obat }}</td>
                        <td>{{ $jumlah }} {{ $item->satuan ?? 'Pcs' }}</td>
                        <td>Rp {{ number_format($harga, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: #6c757d; padding: 20px;">Tidak ada resep obat</td>
                    </tr>
                @endforelse
                
                {{-- Pastikan TR Total berada DI DALAM TBODY --}}
                @if($detailResep->isNotEmpty())
                <tr class="total-row">
                    <td colspan="4" style="text-align: right; font-weight: 600;">TOTAL</td>
                    <td style="font-weight: 700;">Rp {{ number_format($total, 0, ',', '.') }}</td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
@endsection