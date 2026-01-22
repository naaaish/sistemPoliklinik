@extends('layouts.kepegawaian')

@section('content')
<link rel="stylesheet" href="{{ asset('css/laporan.css') }}">

<div class="laporan-page">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 class="page-title">{{ $judul }}</h2>
        <button onclick="downloadExcel()" class="btn btn-success">
            <i class="bi bi-download"></i> Download Excel
        </button>
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
                    <tr class="expandable-row">
                        <td>
                            {{ $dokter->nama_dokter }}
                            <button class="btn-expand" onclick="toggleDetail({{ $dokter->id_dokter }})">
                                <i class="bi bi-chevron-down" id="icon-{{ $dokter->id_dokter }}"></i>
                            </button>
                        </td>
                        <td class="text-center">{{ $dokter->total_pasien }}</td>
                        <td>
                            Rp {{ number_format($dokter->total_biaya,0,',','.') }}
                        </td>
                    </tr>

                    {{-- DETAIL PASIEN --}}
                    <tr id="detail-{{ $dokter->id_dokter }}" class="detail-row" style="display:none;">
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

                    @php $grandTotalPoli += $dokter->total_biaya; @endphp
                @empty
                    <tr>
                        <td colspan="3" class="empty">Tidak ada data dokter poliklinik</td>
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

<style>
.filter-form {
    display: flex;
    gap: 15px;
    align-items: end;
    margin-bottom: 20px;
}
.filter-group {
    display: flex;
    flex-direction: column;
}
.filter-group label {
    margin-bottom: 5px;
    font-weight: 500;
}
.form-control {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
}
.btn-primary {
    padding: 8px 20px;
    background: #007bff;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}
.btn-primary:hover {
    background: #0056b3;
}
.btn-success {
    padding: 10px 24px;
    background: #28a745;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-weight: 500;
}
.btn-success:hover {
    background: #218838;
}
.text-center {
    text-align: center;
}

/* Styling untuk detail dokter */
.dokter-summary h4 {
    margin-bottom: 15px;
    color: #333;
}
.btn-expand {
    background: none;
    border: none;
    cursor: pointer;
    padding: 2px 8px;
    margin-left: 8px;
    color: #007bff;
}
.btn-expand:hover {
    color: #0056b3;
}
.detail-container {
    padding: 15px 30px;
    background: #f8f9fa;
}
.detail-table {
    width: 100%;
    border-collapse: collapse;
}
.detail-table thead {
    background: #e9ecef;
}
.detail-table th {
    padding: 8px;
    text-align: left;
    font-size: 0.9em;
}
.detail-table td {
    padding: 8px;
    border-bottom: 1px solid #dee2e6;
}
.badge {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 0.85em;
    font-weight: 500;
}
.badge-blue {
    background: #e3f2fd;
    color: #1976d2;
}
.badge-green {
    background: #e8f5e9;
    color: #388e3c;
}
.expandable-row {
    cursor: pointer;
}
.detail-row td {
    padding: 0 !important;
}
</style>

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

function downloadExcel() {
    const wb = XLSX.utils.book_new();
    const dateFrom = document.getElementById('dateFrom').value;
    const dateTo = document.getElementById('dateTo').value;
    
    let wsData = [];
    let fileName = '';
    
    // Header
    wsData.push([judul.toUpperCase()]);
    wsData.push([`Periode: ${dateFrom ? formatTanggal(dateFrom) : 'Semua'} - ${dateTo ? formatTanggal(dateTo) : 'Semua'}`]);
    wsData.push([]);
    
    if (jenis === 'dokter') {
        // Excel khusus untuk dokter dengan detail
        wsData.push(['RINGKASAN DOKTER']);
        wsData.push([]);
        wsData.push(['No', 'Nama Dokter', 'Jenis Dokter', 'Total Pasien']);
        
        let rowNum = 1;
        let totalPerusahaan = 0;
        let totalPoliklinik = 0;
        
        dataRaw.forEach(dokter => {
            const jenisDokter = dokter.jenis_dokter === 'perusahaan' ? 'Dokter Perusahaan' : 'Dokter Poliklinik';
            wsData.push([rowNum++, dokter.nama_dokter, jenisDokter, dokter.total_pasien || 0]);
            
            if (dokter.jenis_dokter === 'perusahaan') {
                totalPerusahaan += dokter.total_pasien || 0;
            } else {
                totalPoliklinik += dokter.total_pasien || 0;
            }
        });
        
        wsData.push([]);
        wsData.push(['', '', 'TOTAL PASIEN DOKTER PERUSAHAAN', totalPerusahaan]);
        wsData.push(['', '', 'TOTAL PASIEN DOKTER POLIKLINIK', totalPoliklinik]);
        wsData.push(['', '', 'GRAND TOTAL', totalPerusahaan + totalPoliklinik]);
        
        // Detail pasien untuk dokter poliklinik
        wsData.push([]);
        wsData.push([]);
        wsData.push(['DETAIL PASIEN DOKTER POLIKLINIK']);
        wsData.push([]);
        
        dataRaw.forEach(dokter => {
            if (dokter.jenis_dokter === 'umum' && dokter.detail_pasien && dokter.detail_pasien.length > 0) {
                wsData.push([]);
                wsData.push([dokter.nama_dokter, '', '', `Total: ${dokter.detail_pasien.length} pasien`]);
                wsData.push(['No', 'Nama Pasien', 'Tanggal Pemeriksaan']);
                
                dokter.detail_pasien.forEach((pasien, idx) => {
                    wsData.push([idx + 1, pasien.nama_pasien, formatTanggal(pasien.tanggal)]);
                });
            }
        });
        
        fileName = 'Laporan_Pemeriksaan_Dokter';
        
    } else {
        // Excel untuk jenis laporan lainnya (tetap seperti sebelumnya)
        if (jenis === 'pegawai' || jenis === 'pensiun') {
            fileName = jenis === 'pegawai'
                ? 'Laporan_Pemeriksaan_Pegawai'
                : 'Laporan_Pemeriksaan_Pensiunan';

            wsData.push([
                'No',
                'Tanggal',
                'Nama Pegawai',
                'Umur',
                'Bagian',
                'Nama Pasien',
                'Hubungan',
                'TD',
                'GDP',
                'GD 2 Jam',
                'GDS',
                'AU',
                'Chol',
                'TG',
                'Suhu',
                'BB',
                'TB',
                'Diagnosa',
                'Terapi',
                'Jumlah Obat',
                'Harga Obat',
                'Total Harga Obat',
                'Pemeriksa',
                'Periksa Ke',

            ]);

            dataRaw.forEach((item, i) => {
            wsData.push([
                i + 1,
                formatTanggal(item.tanggal),
                item.nama_pegawai,
                item.umur,
                item.bagian,
                item.nama_pasien,
                item.hub_kel,
                item.sistol,
                item.gd_puasa,
                item.gd_duajam,
                item.gd_sewaktu,
                item.asam_urat,
                item.chol,
                item.tg,
                item.suhu,
                item.berat,
                item.tinggi,
                item.diagnosa,
                item.nama_obat,
                `${item.jumlah} ${item.satuan}`,
                item.harga,
                item.total_harga_obat,
                item.pemeriksa,
                item.periksa_ke
            ]);
        });

        }

        else if (jenis === 'obat') {
            fileName = 'Laporan_Penggunaan_Obat';
            wsData.push(['No', 'Nama Obat', 'Tanggal', 'Jumlah', 'Harga', 'Total']);
            let totalBiaya = 0;
            dataRaw.forEach((item, i) => {
                wsData.push([
                    i + 1,
                    item.nama_obat,
                    formatTanggal(item.tanggal),
                    item.jumlah,
                    parseFloat(item.harga || 0),
                    parseFloat(item.total || 0)
                ]);
                totalBiaya += parseFloat(item.total || 0);
            });
            wsData.push([]);
            wsData.push(['', '', '', '', 'TOTAL BIAYA', totalBiaya]);
        }
        else if (jenis === 'total') {
            fileName = 'Laporan_Total_Operasional';
            wsData.push(['No', 'Tanggal', 'Biaya Obat', 'Jumlah Dokter Perusahaan', 'Total']);
            let totalObat = 0;
            let totalDokter = 0;
            dataRaw.forEach((item, i) => {
                const biaya = parseFloat(item.biaya_obat || 0);
                wsData.push([
                    i + 1,
                    formatTanggal(item.tanggal),
                    biaya,
                    parseInt(item.biaya_dokter || 0),
                    biaya
                ]);
                totalObat += biaya;
                totalDokter += parseInt(item.biaya_dokter || 0);
            });
            wsData.push([]);
            wsData.push(['', 'GRAND TOTAL', totalObat, totalDokter, totalObat]);
        }
    }
    
    const ws = XLSX.utils.aoa_to_sheet(wsData);
    
    // Merge cells untuk header
    const maxCol = 3;
    ws['!merges'] = [
        { s: { r: 0, c: 0 }, e: { r: 0, c: maxCol } },
        { s: { r: 1, c: 0 }, e: { r: 1, c: maxCol } }
    ];
    
    // Set column widths
    ws['!cols'] = [
        { wch: 5 },
        { wch: 30 },
        { wch: 25 },
        { wch: 20 }
    ];
    
    XLSX.utils.book_append_sheet(wb, ws, judul.substring(0, 31));
    XLSX.writeFile(wb, `${fileName}_${new Date().toISOString().split('T')[0]}.xlsx`);
}

function formatTanggal(date) {
    if (!date) return '';
    const d = new Date(date);
    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
}
</script>
@endsection