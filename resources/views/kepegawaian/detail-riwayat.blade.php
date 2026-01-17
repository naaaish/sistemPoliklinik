@extends('layouts.kepegawaian')

@section('title', 'Rincian Pemeriksaan')

@section('content')

<style>
/* Detail Pemeriksaan Styles */
.detail-page {
    background: rgba(241, 248, 255, 0.58);
    min-height: calc(100vh - 64px);
    padding: 24px;
}

.detail-header {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 24px;
}

.detail-header img {
    width: 38px;
    height: 38px;
}

.detail-header h1 {
    font-size: 22px;
    font-weight: 500;
    color: #06869A;
}

/* Main Card */
.detail-main-card {
    background: white;
    border: 2px solid rgba(6, 134, 154, 0.43);
    border-radius: 20px;
    padding: 32px;
    max-width: 1100px;
}

/* Section Title */
.section-title-detail {
    font-size: 23px;
    font-weight: 600;
    color: #317E8A;
    margin-bottom: 20px;
    padding-bottom: 12px;
    border-bottom: 2.5px solid rgba(106, 196, 234, 0.4);
}

/* Data Grid */
.data-grid-detail {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px 40px;
    margin-bottom: 32px;
}

.data-row {
    display: flex;
    gap: 8px;
}

.data-label-detail {
    min-width: 160px;
    font-size: 12px;
    font-weight: 500;
    color: rgba(67, 67, 67, 0.7);
    letter-spacing: 1px;
}

.data-value-detail {
    font-size: 13px;
    font-weight: 500;
    color: black;
    letter-spacing: 0.3px;
}

/* Divider */
.divider-vertical {
    position: absolute;
    right: 50%;
    top: 0;
    bottom: 0;
    width: 1.7px;
    background: rgba(6, 134, 154, 0.5);
}

/* Health Metrics */
.health-metrics-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}

.metric-card-detail {
    background: white;
    border-radius: 6px;
    padding: 12px;
    position: relative;
    box-shadow: 0 4px 4px rgba(0, 0, 0, 0.25);
}

.metric-card-detail::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 65px;
    border-radius: 6px;
    opacity: 1;
}

/* Metric Colors */
.metric-red::before { background: #FF8686; }
.metric-pink::before { background: #FF86EC; }
.metric-yellow::before { background: #FFFB86; }
.metric-purple::before { background: #F885FE; }
.metric-orange::before { background: #FFAE86; }
.metric-blue::before { background: #86CEFF; }
.metric-green::before { background: #86FF92; }

.metric-card-detail .metric-label-detail {
    position: relative;
    font-size: 14.5px;
    font-weight: 500;
    margin-bottom: 6px;
    z-index: 1;
}

.metric-red .metric-label-detail { color: #4E0D0D; }
.metric-pink .metric-label-detail { color: #4E0D40; }
.metric-yellow .metric-label-detail { color: #878213; }
.metric-purple .metric-label-detail { color: #591387; }
.metric-orange .metric-label-detail { color: #874513; }
.metric-blue .metric-label-detail { color: #135F87; }
.metric-green .metric-label-detail { color: #138738; }

.metric-card-detail .metric-value-detail {
    position: relative;
    font-size: 18px;
    font-weight: 500;
    color: #121212;
    z-index: 1;
}

/* Info Box */
.info-box-detail {
    margin-bottom: 24px;
}

.info-box-header {
    background: #316BA1;
    padding: 12px 20px;
    border-radius: 5px 5px 0 0;
    border: 2px solid rgba(49, 107, 161, 0.4);
    border-bottom: none;
}

.info-box-header h3 {
    font-size: 17.5px;
    font-weight: 500;
    color: #F1F8FF;
}

.info-box-content {
    background: #FFFDFD;
    padding: 16px 20px;
    border-radius: 0 0 5px 5px;
    border: 2px solid rgba(49, 107, 161, 0.4);
    border-top: none;
}

.info-box-content p {
    font-size: 16.5px;
    font-weight: 600;
    color: #316BA1;
    line-height: 1.5;
}

/* Resep Table */
.resep-table-detail {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 8px;
}

.resep-table-detail thead {
    background: white;
}

.resep-table-detail th {
    padding: 14px 16px;
    font-size: 11.6px;
    font-weight: 500;
    color: #151054;
    text-align: left;
    border: 1.7px solid rgba(0, 0, 0, 0.11);
}

.resep-table-detail tbody tr {
    border-bottom: 1px solid #F3F3F3;
}

.resep-table-detail td {
    padding: 10px 16px;
    font-size: 12.5px;
    font-weight: 400;
    color: #5C6E9A;
    letter-spacing: 0.8px;
}

.resep-table-detail .total-row {
    background: rgba(180, 242, 244, 0.43);
}

.resep-table-detail .total-row td {
    font-weight: 500;
    color: #151054;
    font-size: 12.6px;
}
</style>

<div class="detail-page">
    {{-- HEADER --}}
    <div class="detail-header">
        <img src="{{ asset('assets/adminPoli/artikel.png') }}" alt="Icon">
        <h1>Rincian Pemeriksaan</h1>
    </div>

    {{-- MAIN CARD --}}
    <div class="detail-main-card">
        
        {{-- DATA PENDAFTARAN --}}
        <h2 class="section-title-detail">Data Pendaftaran Pasien</h2>
        <div class="data-grid-detail">
            <div class="data-row">
                <span class="data-label-detail">No. Registrasi :</span>
                <span class="data-value-detail">{{ $pendaftaran->id_pendaftaran }}</span>
            </div>
            <div class="data-row">
                <span class="data-label-detail">Nama Pasien :</span>
                <span class="data-value-detail">{{ $pasien->nama_pasien }}</span>
            </div>
            <div class="data-row">
                <span class="data-label-detail">Tanggal Periksa :</span>
                <span class="data-value-detail">{{ \Carbon\Carbon::parse($pendaftaran->tanggal)->translatedFormat('l, d F Y, H:i') }}</span>
            </div>
            <div class="data-row">
                <span class="data-label-detail">Hubungan Keluarga :</span>
                <span class="data-value-detail">{{ ucfirst($pasien->hub_kel) }}</span>
            </div>
            <div class="data-row">
                <span class="data-label-detail">Nama Pemeriksa :</span>
                <span class="data-value-detail">{{ $dokter->nama }}</span>
            </div>
            <div class="data-row">
                <span class="data-label-detail">Tanggal Lahir :</span>
                <span class="data-value-detail">{{ \Carbon\Carbon::parse($pasien->tgl_lahir)->translatedFormat('d F Y') }}</span>
            </div>
            <div class="data-row">
                <span class="data-label-detail">NIP :</span>
                <span class="data-value-detail">{{ $pegawai->nip ?? '-' }}</span>
            </div>
            <div class="data-row">
                <span class="data-label-detail">Umur :</span>
                <span class="data-value-detail">{{ \Carbon\Carbon::parse($pasien->tgl_lahir)->age }} tahun</span>
            </div>
            <div class="data-row">
                <span class="data-label-detail">Nama Pegawai :</span>
                <span class="data-value-detail">{{ $pegawai->nama_pegawai ?? '-' }}</span>
            </div>
            <div class="data-row">
                <span class="data-label-detail">Bidang :</span>
                <span class="data-value-detail">{{ $pegawai->bidang ?? '-' }}</span>
            </div>
        </div>

        {{-- PEMERIKSAAN KESEHATAN --}}
        <h2 class="section-title-detail">Pemeriksaan Kesehatan</h2>
        <div class="health-metrics-grid">
            <div class="metric-card-detail metric-red">
                <div class="metric-label-detail">Sistol</div>
                <div class="metric-value-detail">{{ $pemeriksaan->sistol }} mmHg</div>
            </div>
            
            <div class="metric-card-detail metric-red">
                <div class="metric-label-detail">Diastol</div>
                <div class="metric-value-detail">{{ $pemeriksaan->diastol }} mmHg</div>
            </div>
            
            <div class="metric-card-detail metric-pink">
                <div class="metric-label-detail">Nadi</div>
                <div class="metric-value-detail">{{ $pemeriksaan->nadi }} bpm</div>
            </div>
            
            <div class="metric-card-detail metric-yellow">
                <div class="metric-label-detail">Gula Darah Puasa</div>
                <div class="metric-value-detail">{{ $pemeriksaan->gd_puasa }} mg/dL</div>
            </div>
            
            <div class="metric-card-detail metric-yellow">
                <div class="metric-label-detail">Gula Darah 2 Jam PP</div>
                <div class="metric-value-detail">{{ $pemeriksaan->gd_duajam }} mg/dL</div>
            </div>
            
            <div class="metric-card-detail metric-yellow">
                <div class="metric-label-detail">Gula Darah Sewaktu</div>
                <div class="metric-value-detail">{{ $pemeriksaan->gd_sewaktu }} mg/dL</div>
            </div>
            
            <div class="metric-card-detail metric-purple">
                <div class="metric-label-detail">Asam Urat</div>
                <div class="metric-value-detail">{{ $pemeriksaan->asam_urat }} mg/dL</div>
            </div>
            
            <div class="metric-card-detail metric-orange">
                <div class="metric-label-detail">Cholesterol</div>
                <div class="metric-value-detail">{{ $pemeriksaan->chol }} mg/dL</div>
            </div>
            
            <div class="metric-card-detail metric-orange">
                <div class="metric-label-detail">Trigliserida</div>
                <div class="metric-value-detail">{{ $pemeriksaan->tg }} mg/dL</div>
            </div>
            
            <div class="metric-card-detail metric-blue">
                <div class="metric-label-detail">Suhu</div>
                <div class="metric-value-detail">{{ $pemeriksaan->suhu }} °C</div>
            </div>
            
            <div class="metric-card-detail metric-green">
                <div class="metric-label-detail">Tinggi Badan</div>
                <div class="metric-value-detail">{{ $pemeriksaan->tinggi }} cm</div>
            </div>
            
            <div class="metric-card-detail metric-green">
                <div class="metric-label-detail">Berat Badan</div>
                <div class="metric-value-detail">{{ $pemeriksaan->berat }} kg</div>
            </div>
        </div>

        {{-- DIAGNOSA DOKTER --}}
        <div class="info-box-detail">
            <div class="info-box-header">
                <h3>Diagnosa Dokter</h3>
            </div>
            <div class="info-box-content">
                <p>{{ $diagnosa->diagnosa ?? '-' }}</p>
            </div>
        </div>

        {{-- SARAN DOKTER --}}
        <div class="info-box-detail">
            <div class="info-box-header">
                <h3>Saran Dokter</h3>
            </div>
            <div class="info-box-content">
                <p>{{ $saran->saran ?? '-' }}</p>
            </div>
        </div>

        {{-- DATA RESEP OBAT --}}
        <h2 class="section-title-detail">Data Resep Obat</h2>
        <table class="resep-table-detail">
            <thead>
                <tr>
                    <th width="60">No</th>
                    <th>Nama Obat</th>
                    <th width="120">Jumlah</th>
                    <th width="140">Harga</th>
                    <th width="140">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @php $total = 0; @endphp
                @forelse($detailResep as $index => $item)
                @php 
                    $subtotal = $item->jumlah * $item->harga;
                    $total += $subtotal;
                @endphp
                <tr>
                    <td>{{ $index + 1 }}.</td>
                    <td>{{ $item->nama_obat }}</td>
                    <td>{{ $item->jumlah }} {{ $item->satuan }}</td>
                    <td>Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: #52606d;">Tidak ada resep obat</td>
                </tr>
                @endforelse
                
                @if($detailResep->isNotEmpty())
                <tr class="total-row">
                    <td colspan="4" style="text-align: right; font-weight: 500;">TOTAL</td>
                    <td style="font-weight: 600;">Rp {{ number_format($total, 0, ',', '.') }}</td>
                </tr>
                @endif
            </tbody>
        </table>

    </div>

</div>

@endsection