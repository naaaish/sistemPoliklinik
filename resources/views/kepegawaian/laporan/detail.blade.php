@extends('layouts.kepegawaian')

@section('content')
<link rel="stylesheet" href="{{ asset('css/laporan.css') }}">

<div class="laporan-page">

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
        <h2 class="page-title">{{ $judul }}</h2>

        @if($jenis === 'pegawai')
            <a href="{{ route('laporan.excel.pegawai-pensiun', ['jenis'=>$jenis] + request()->query()) }}"
            class="btn-download">
            Download Excel
            </a>
        @elseif($jenis === 'pensiun')
            <a href="{{ route('laporan.excel.pegawai-pensiun', ['jenis'=>$jenis] + request()->query()) }}"
            class="btn-download">
            Download Excel
            </a>
        @elseif($jenis === 'dokter')
            <a href="{{ route('laporan.excel.dokter', request()->query()) }}" class="btn-download">Download Excel</a>
        @elseif($jenis === 'obat')
            <a href="{{ route('laporan.excel.obat', request()->query()) }}" class="btn-download">Download Excel</a>
        @elseif($jenis === 'total')
            <a href="{{ route('laporan.excel.total', request()->query()) }}" class="btn-download">Download Excel</a>
        @endif
    </div>

    {{-- ========================================
    INFO PERIODE + TOMBOL UBAH
    ======================================== --}}
    @if($dari && $sampai)
    <div class="periode-info">
        <div>
            <strong> Periode:</strong> 
            {{ \Carbon\Carbon::parse($dari)->translatedFormat('d F Y') }} 
            - 
            {{ \Carbon\Carbon::parse($sampai)->translatedFormat('d F Y') }}
        </div>
        <a href="{{ route('kepegawaian.laporan') }}" class="btn-reset">
            ← Ubah Periode
        </a>
    </div>
    @endif

    {{-- ===================== DOKTER ===================== --}}
 @if($jenis === 'dokter')

    {{-- ================= DOKTER POLIKLINIK ================= --}}
    <div class="dokter-summary">
        <h4>Dokter Poliklinik (Bayar per Pasien)</h4>

        <div class="table-wrapper">
            <table class="laporan-table">

                <thead>
                    <tr>
                        <th>Nama Dokter</th>
                        <th>Total Pasien</th>
                        <th>Total Biaya</th>
                    </tr>
                </thead>
                <tbody>
                    @php $grandTotalPoli = 0; @endphp

                    @forelse($dokterPoli as $dokter)
                    <tr class="dokter-row"
                        onclick="toggleDetail('dokter-{{ $dokter->id_dokter }}')"
                        style="cursor:pointer;">
                        <td class="dokter-name">
                            <span class="toggle-label">
                                {{ $dokter->nama_dokter }}
                                <i class="bi bi-chevron-down"
                                id="icon-dokter-{{ $dokter->id_dokter }}"></i>
                            </span>
                        </td>
                        <td class="text-center">{{ $dokter->total_pasien }}</td>
                        <td>Rp {{ number_format($dokter->total_biaya,0,',','.') }}</td>
                    </tr>

                    {{-- 🔥 INI YANG KURANG --}}
                    @php $grandTotalPoli += $dokter->total_biaya; @endphp

                    {{-- DETAIL PASIEN --}}
                    <tr id="dokter-{{ $dokter->id_dokter }}" style="display:none;">
                        <td colspan="3">
                            <table class="detail-table">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>NIP</th>
                                        <th>Nama Pasien</th>
                                        <th>Tanggal Periksa</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dokter->pasien as $i => $p)
                                    <tr>
                                        <td>{{ $i+1 }}</td>
                                        <td>{{ $p->nip }}</td>
                                        <td>{{ $p->nama_pasien }}</td>
                                        <td>
                                            {{ \Carbon\Carbon::parse($p->tanggal)->translatedFormat('d F Y') }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </td>
                    </tr>

                    @empty
                    <tr>
                        <td colspan="3">Tidak ada data dokter poliklinik</td>
                    </tr>
                    @endforelse



                    @if(count($dokterPoli) > 0)
                        <tr style="background:#f0fff0;font-weight:bold;">
                            <td colspan="2">TOTAL DOKTER POLIKLINIK</td>
                            <td>Rp {{ number_format($grandTotalPoli,0,',','.') }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <br>

    {{-- ================= DOKTER PERUSAHAAN ================= --}}
    <div class="dokter-summary">
        <h4>Dokter Perusahaan (Gaji Tetap)</h4>

        <div class="table-wrapper">
            <table class="laporan-table">

                <thead>
                    <tr>
                        <th>Nama Dokter</th>
                        <th>Total Pasien</th>
                        <th>Gaji</th>
                    </tr>
                </thead>
                <tbody>
                    @php $grandTotalPerusahaan = 0; @endphp

                    @forelse($dokterPerusahaan as $dokter)
                        <tr class="expandable-row">
                            <td>
                                {{ $dokter->nama_dokter }}
                                <button class="btn-expand" onclick="toggleDetail({{ $dokter->id_dokter }}_p)">
                                    <i class="bi bi-chevron-down" id="icon-{{ $dokter->id_dokter }}_p"></i>
                                </button>
                            </td>
                            <td class="text-center">{{ $dokter->total_pasien }}</td>
                            <td>
                                Rp {{ number_format($dokter->gaji,0,',','.') }}
                            </td>
                        </tr>

                        {{-- DETAIL PASIEN --}}
                        <tr id="detail-{{ $dokter->id_dokter }}_p" class="detail-row" style="display:none;">
                            <td colspan="3" style="padding:0;">
                                <div class="detail-container">
                                    <table class="detail-table">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Nama Pasien</th>
                                                <th>Tanggal Pemeriksaan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($dokter->pasien as $i => $pasien)
                                                <tr>
                                                    <td>{{ $i+1 }}</td>
                                                    <td>{{ $pasien->nama_pasien }}</td>
                                                    <td>
                                                        {{ \Carbon\Carbon::parse($pasien->tanggal)->translatedFormat('d F Y') }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
    
                        @php $grandTotalPerusahaan += $dokter->gaji; @endphp
                    @empty
                        <tr>
                            <td colspan="3" class="empty">Tidak ada data dokter perusahaan</td>
                        </tr>
                    @endforelse

                    @if(count($dokterPerusahaan) > 0)
                        <tr style="background:#f0f8ff;font-weight:bold;">
                            <td colspan="2">TOTAL GAJI DOKTER PERUSAHAAN</td>
                            <td>Rp {{ number_format($grandTotalPerusahaan,0,',','.') }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    
    {{-- ===================== TOTAL OPERASIONAL ===================== --}}
    @elseif($jenis === 'total')

        <div class="table-wrapper">
            <table class="laporan-table">

            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Nama Pegawai</th>
                    <th>NIP</th>
                    <th>Umur</th>
                    <th>Bagian</th>
                    <th>Nama Pasien</th>
                    <th>Hub Kel</th>
                    <th>TD (S/D)</th>
                    <th>GDP</th>
                    <th>GD 2Jam</th>
                    <th>GDS</th>
                    <th>AU</th>
                    <th>CHOL</th>
                    <th>TG</th>
                    <th>Suhu</th>
                    <th>BB</th>
                    <th>TB</th>
                    <th>Diagnosa</th>
                    <th>Nama Obat</th>
                    <th>Jumlah</th>
                    <th>Harga Satuan</th>
                    <th>Subtotal</th>
                    <th>Total Obat (Pasien)</th>
                    <th>Pemeriksa</th>
                </tr>
            </thead>
            <tbody>
                @php $no = 1; @endphp
                @foreach($data as $r)
                <tr>
                    <td>{{ $r->is_first ? $no++ : '' }}</td>
                    <td>{{ $r->is_first ? \Carbon\Carbon::parse($r->tanggal)->format('d/m/Y') : '' }}</td>
                    <td>{{ $r->is_first ? $r->nama_pegawai : '' }}</td>
                    <td>{{ $r->is_first ? $r->nip : '' }}</td>
                    <td>{{ $r->is_first ? $r->umur : '' }}</td>
                    <td>{{ $r->is_first ? $r->bagian : '' }}</td>
                    <td>{{ $r->is_first ? $r->nama_pasien : '' }}</td>
                    <td>{{ $r->is_first ? $r->hub_kel : '' }}</td>
                    <td>{{ $r->is_first ? ($r->sistol . '/' . ($r->diastol ?? '-')) : '' }}</td>
                    <td>{{ $r->is_first ? $r->gd_puasa : '' }}</td>
                    <td>{{ $r->is_first ? $r->gd_duajam : '' }}</td>
                    <td>{{ $r->is_first ? $r->gd_sewaktu : '' }}</td>
                    <td>{{ $r->is_first ? $r->asam_urat : '' }}</td>
                    <td>{{ $r->is_first ? $r->chol : '' }}</td>
                    <td>{{ $r->is_first ? $r->tg : '' }}</td>
                    <td>{{ $r->is_first ? $r->suhu : '' }}</td>
                    <td>{{ $r->is_first ? $r->berat : '' }}</td>
                    <td>{{ $r->is_first ? $r->tinggi : '' }}</td>
                    
                    <td>{{ $r->diagnosa }}</td>
                    <td>{{ $r->nama_obat }}</td>
                    <td>{{ $r->jumlah }} {{ $r->satuan }}</td>
                    <td>Rp {{ number_format($r->harga_satuan, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($r->subtotal_obat, 0, ',', '.') }}</td>
                    
                    {{-- Total per Pasien --}}
                    <td style="font-weight: bold; background: #f0fff0;">
                        @if($r->total_obat_pasien !== null)
                            Rp {{ number_format($r->total_obat_pasien, 0, ',', '.') }}
                        @endif
                    </td>
                    
                    <td>{{ $r->is_first ? $r->nama_pemeriksa : '' }}</td>
                </tr>
                @endforeach
            </tbody>
            </table>
        </div>

    {{-- ===================== OBAT ===================== --}}
    @elseif($jenis === 'obat')

        <div class="table-wrapper">
            <table class="laporan-table">

                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Obat</th>
                        <th>Tanggal</th>
                        <th>Jumlah</th>
                        <th>Harga</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @php $grandTotal = 0; @endphp
                    @forelse($data as $i => $item)
                        @php $grandTotal += $item->total; @endphp
                        <tr>
                            <td>{{ $i+1 }}</td>
                            <td>{{ $item->nama_obat }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d F Y') }}</td>
                            <td>{{ $item->jumlah }}</td>
                            <td>Rp {{ number_format($item->harga,0,',','.') }}</td>
                            <td>Rp {{ number_format($item->total,0,',','.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6">Tidak ada data obat</td></tr>
                    @endforelse
                    <tr style="font-weight:bold;background:#f0fff0">
                        <td colspan="5">TOTAL OBAT</td>
                        <td>Rp {{ number_format($grandTotal,0,',','.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

    {{-- ===================== PEGAWAI & PENSIUN ===================== --}}
        @else
            {{-- Tabel Biasa untuk Jenis Laporan Lain --}}
            <div class="table-wrapper">
                <table class="laporan-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Nama Pegawai</th>
                            <th>Umur</th>
                            <th>Bagian</th>
                            <th>Nama Pasien</th>
                            <th>Hub. Kel</th>
                            <th>TD</th>
                            <th>GDP</th>
                            <th>GD 2 Jam</th>
                            <th>GDS</th>
                            <th>AU</th>
                            <th>Chol</th>
                            <th>TG</th>
                            <th>Suhu</th>
                            <th>BB</th>
                            <th>TB</th>
                            <th>Diagnosa</th>
                            <th>NB</th>
                            <th>Therapy</th>
                            <th>Jml Obat</th>
                            <th>Harga Obat</th>
                            <th>Total Obat</th>
                            <th>Pemeriksa</th>
                            <th>Periksa Ke</th> {{-- Kolom Periksa Ke diletakkan di paling kanan --}}
                        </tr>
                    </thead>

                    <tbody>
                        @php
                            $grouped = $data->groupBy('id_pemeriksaan');
                            $no = 1;
                        @endphp

                        @foreach($grouped as $rows)
                            @php
                                $rowspan = $rows->count();
                            @endphp

                            @foreach($rows as $item)
                                <tr>
                                    @if($loop->first)
                                        <td rowspan="{{ $rowspan }}" class="text-center">{{ $no++ }}</td>
                                        <td rowspan="{{ $rowspan }}">{{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d F Y') }}</td>
                                        <td rowspan="{{ $rowspan }}">{{ $item->nama_pegawai }}</td>
                                        <td rowspan="{{ $rowspan }}" class="text-center">{{ $item->umur }}</td>
                                        <td rowspan="{{ $rowspan }}">{{ $item->bagian }}</td>
                                        <td rowspan="{{ $rowspan }}">{{ $item->nama_pasien }}</td>
                                        <td rowspan="{{ $rowspan }}">{{ ucfirst($item->hub_kel) }}</td>
                                        <td rowspan="{{ $rowspan }}" class="text-center">{{ $item->sistol }}</td>
                                        <td rowspan="{{ $rowspan }}" class="text-center">{{ $item->gd_puasa }}</td>
                                        <td rowspan="{{ $rowspan }}" class="text-center">{{ $item->gd_duajam }}</td>
                                        <td rowspan="{{ $rowspan }}" class="text-center">{{ $item->gd_sewaktu }}</td>
                                        <td rowspan="{{ $rowspan }}" class="text-center">{{ $item->asam_urat }}</td>
                                        <td rowspan="{{ $rowspan }}" class="text-center">{{ $item->chol }}</td>
                                        <td rowspan="{{ $rowspan }}" class="text-center">{{ $item->tg }}</td>
                                        <td rowspan="{{ $rowspan }}" class="text-center">{{ $item->suhu }}</td>
                                        <td rowspan="{{ $rowspan }}" class="text-center">{{ $item->berat }}</td>
                                        <td rowspan="{{ $rowspan }}" class="text-center">{{ $item->tinggi }}</td>
                                    @endif

                                    {{-- Kolom yang TIDAK menggunakan rowspan (mengikuti jumlah diagnosa/obat) --}}
                                    <td>{{ $item->diagnosa }}</td>
                                    <td class="text-center">{{ $item->nb }}</td>
                                    <td>{{ $item->nama_obat }}</td>
                                    <td class="text-center">{{ $item->jumlah }} {{ $item->satuan }}</td>
                                    <td class="text-right">Rp {{ number_format($item->harga,0,',','.') }}</td>

                                    @if($loop->first)
                                        <td rowspan="{{ $rowspan }}" class="text-right fw-bold">
                                            Rp {{ number_format($item->total_obat_pasien ?? 0,0,',','.') }}
                                        </td>
                                        <td rowspan="{{ $rowspan }}">{{ $item->pemeriksa }}</td>
                                        {{-- Kolom Periksa Ke sekarang sejajar dengan header terakhir --}}
                                        <td rowspan="{{ $rowspan }}" class="text-center">{{ $item->periksa_ke }}</td>
                                    @endif
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
    @endif
</div>
@endsection



{{-- Include SheetJS for Excel Download --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<script>
const jenis = '{{ $jenis }}';
const judul = '{{ $judul }}';
@if($jenis === 'dokter')
    const dataRaw = {
        poli: @json($dokterPoli),
        perusahaan: @json($dokterPerusahaan)
    };
@else
    const dataRaw = @json($data);
@endif


// Toggle detail pasien untuk dokter poliklinik
function toggleDetail(dokterId) {
    const detailRow = document.getElementById('detail-' + dokterId);
    const icon = document.getElementById('icon-' + dokterId);
    
    if (detailRow.style.display === 'none') {
        detailRow.style.display = 'table-row';
        icon.classList.remove('bi-chevron-down');
        icon.classList.add('bi-chevron-up');
    } else {
        detailRow.style.display = 'none';
        icon.classList.remove('bi-chevron-up');
        icon.classList.add('bi-chevron-down');
    }
}
// Format tanggal ke format Indonesia
function formatTanggal(date) {
    if (!date) return '';
    const d = new Date(date);
    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
}

function toggleDetail(id) {
    const row = document.getElementById(id);
    const icon = document.getElementById('icon-' + id);

    if (!row) return;

    const isOpen = row.style.display === 'table-row';

    // toggle buka / tutup
    if (isOpen) {
        row.style.display = 'none';
        if (icon) {
            icon.classList.remove('bi-chevron-up');
            icon.classList.add('bi-chevron-down');
        }
    } else {
        row.style.display = 'table-row';
        if (icon) {
            icon.classList.remove('bi-chevron-down');
            icon.classList.add('bi-chevron-up');
        }
    }
}
</script>