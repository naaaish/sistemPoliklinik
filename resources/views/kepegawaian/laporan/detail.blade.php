@extends('layouts.kepegawaian')

@section('content')
<link rel="stylesheet" href="{{ asset('css/laporan.css') }}">

<div class="laporan-page">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 class="page-title">{{ $judul }}</h2>
            <a href="{{ route('laporan.excel', ['jenis' => $jenis, 'dari' => request('dari'), 'sampai' => request('sampai')]) }}"
            class="btn-download">
                Download Excel
            </a>



    </div>

    {{-- FILTER TANGGAL --}}
    <div class="laporan-card">
        <form method="GET" action="{{ route('kepegawaian.laporan.detail', $jenis) }}" class="filter-form" id="filterForm">
            <div class="filter-group">
                <label>Dari:</label>
                <input type="date" name="dari" value="{{ $dari }}" class="form-control" id="dateFrom">
            </div>
            <div class="filter-group">
                <label>Sampai:</label>
                <input type="date" name="sampai" value="{{ $sampai }}" class="form-control" id="dateTo">
            </div>
            <button type="submit" class="btn btn-primary">Tampilkan</button>
        </form>
    </div>

    {{-- TABLE --}}
    @if($jenis === 'dokter')

    {{-- ================= DOKTER POLIKLINIK ================= --}}
    <div class="dokter-summary">
        <h4>Dokter Poliklinik (Bayar per Pasien)</h4>

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

    <br>

    {{-- ================= DOKTER PERUSAHAAN ================= --}}
    <div class="dokter-summary">
        <h4>Dokter Perusahaan (Gaji Tetap)</h4>

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
            
        @else
            {{-- Tabel Biasa untuk Jenis Laporan Lain --}}
            <table class="laporan-table" id="dataTable">
                <thead>
                    <tr>
                        @if($jenis === 'obat')
                            <th>Nama Obat</th>
                            <th>Tanggal</th>
                            <th>Jumlah</th>
                            <th>Harga</th>
                            <th>Total</th>

                        @elseif($jenis === 'total')
                            <th>Tanggal</th>
                            <th>Biaya Obat</th>
                            <th>Jumlah Dokter Perusahaan</th>
                            <th>Total</th>

                        @else
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
                                <th>Periksa Ke</th>

                            </tr>
                        @endif
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
                                <td rowspan="{{ $rowspan }}">{{ $no++ }}</td>
                                <td rowspan="{{ $rowspan }}">{{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d F Y') }}</td>
                                <td rowspan="{{ $rowspan }}">{{ $item->nama_pegawai }}</td>
                                <td rowspan="{{ $rowspan }}">{{ $item->umur }}</td>
                                <td rowspan="{{ $rowspan }}">{{ $item->bagian }}</td>
                                <td rowspan="{{ $rowspan }}">{{ $item->nama_pasien }}</td>
                                <td rowspan="{{ $rowspan }}">{{ ucfirst($item->hub_kel) }}</td>

                                <td rowspan="{{ $rowspan }}">{{ $item->sistol }}</td>
                                <td rowspan="{{ $rowspan }}">{{ $item->gd_puasa }}</td>
                                <td rowspan="{{ $rowspan }}">{{ $item->gd_duajam }}</td>
                                <td rowspan="{{ $rowspan }}">{{ $item->gd_sewaktu }}</td>
                                <td rowspan="{{ $rowspan }}">{{ $item->asam_urat }}</td>
                                <td rowspan="{{ $rowspan }}">{{ $item->chol }}</td>
                                <td rowspan="{{ $rowspan }}">{{ $item->tg }}</td>
                                <td rowspan="{{ $rowspan }}">{{ $item->suhu }}</td>
                                <td rowspan="{{ $rowspan }}">{{ $item->berat }}</td>
                                <td rowspan="{{ $rowspan }}">{{ $item->tinggi }}</td>
                            @endif

                        {{-- DIAGNOSA --}}
                        <td>{{ $item->diagnosa }}</td>

                        {{-- NB --}}
                        <td>{{ $item->nb }}</td>

                        {{-- THERAPY / OBAT --}}
                        <td>{{ $item->nama_obat }}</td>

                        <td class="text-center">
                            {{ $item->jumlah }} {{ $item->satuan }}
                        </td>

                        <td class="text-right">
                            Rp {{ number_format($item->harga,0,',','.') }}
                        </td>

                        @if($loop->first)
                        <td rowspan="{{ $rowspan }}" class="text-right fw-bold">
                            Rp {{ number_format($item->total_obat_pasien,0,',','.') }}
                        </td>
                        <td rowspan="{{ $rowspan }}">{{ $item->pemeriksa }}</td>
                        <td rowspan="{{ $rowspan }}">{{ $item->periksa_ke }}</td>
                        @endif

                        </tr>
                    @endforeach
                @endforeach
                </tbody>

            </table>
        @endif
    </div>
</div>



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

    // Tutup semua dulu (biar rapi)
    document.querySelectorAll('.detail-row').forEach(r => r.style.display = 'none');
    document.querySelectorAll('.bi-chevron-down').forEach(i => i.style.transform = 'rotate(0deg)');

    if (!isOpen) {
        row.style.display = 'table-row';
        if (icon) icon.style.transform = 'rotate(180deg)';
    }
}
</script>
@endsection