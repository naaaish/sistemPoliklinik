<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $judul }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 3px solid #333;
        }
        .header h1 {
            font-size: 18px;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .header .periode {
            font-size: 11px;
            color: #666;
            margin-top: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background-color: #f0f0f0;
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
            font-weight: bold;
        }
        td {
            padding: 8px 10px;
            border: 1px solid #ddd;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .total-row {
            background-color: #e8e8e8 !important;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            font-size: 10px;
            color: #666;
            text-align: right;
        }
        .empty {
            text-align: center;
            padding: 30px;
            color: #999;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $judul }}</h1>
        <div class="periode">
            Periode: 
            @if($dari && $sampai)
                {{ \Carbon\Carbon::parse($dari)->translatedFormat('d F Y') }} - 
                {{ \Carbon\Carbon::parse($sampai)->translatedFormat('d F Y') }}
            @else
                Semua Data
            @endif
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 40px;">No</th>
                @if($jenis === 'dokter')
                    <th style="width: 40%;">Nama Dokter</th>
                    <th style="width: 30%;">Jenis Dokter</th>
                    <th class="text-center" style="width: 30%;">Total Pasien</th>

                @elseif($jenis === 'obat')
                    <th>Nama Obat</th>
                    <th>Tanggal</th>
                    <th class="text-center">Jumlah</th>
                    <th class="text-right">Harga</th>
                    <th class="text-right">Total</th>

                @elseif($jenis === 'total')
                    <th>Tanggal</th>
                    <th class="text-right">Biaya Obat</th>
                    <th class="text-center">Jumlah Dokter Perusahaan</th>
                    <th class="text-right">Total</th>

                @else
                    <th>Nama Pasien</th>
                    <th>Tanggal Pemeriksaan</th>
                @endif
            </tr>
        </thead>

        <tbody>
            @forelse($data as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    
                    @if($jenis === 'dokter')
                        <td>{{ $item->nama_dokter }}</td>
                        <td>
                            @if($item->jenis_dokter === 'perusahaan')
                                Dokter Perusahaan
                            @else
                                Dokter Poliklinik
                            @endif
                        </td>
                        <td class="text-center">{{ $item->total_pasien }}</td>

                    @elseif($jenis === 'obat')
                        <td>{{ $item->nama_obat }}</td>
                        <td>{{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d F Y') }}</td>
                        <td class="text-center">{{ $item->jumlah }}</td>
                        <td class="text-right">Rp {{ number_format($item->harga, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format($item->total, 0, ',', '.') }}</td>

                    @elseif($jenis === 'total')
                        <td>{{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d F Y') }}</td>
                        <td class="text-right">Rp {{ number_format($item->biaya_obat ?? 0, 0, ',', '.') }}</td>
                        <td class="text-center">{{ $item->biaya_dokter ?? 0 }}</td>
                        <td class="text-right">Rp {{ number_format($item->biaya_obat ?? 0, 0, ',', '.') }}</td>

                    @else
                        <td>{{ $item->nama_pasien }}</td>
                        <td>{{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d F Y') }}</td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="empty">Tidak ada data untuk periode yang dipilih</td>
                </tr>
            @endforelse

            {{-- Total Rows --}}
            @if($data->count() > 0)
                @if($jenis === 'dokter')
                    @php
                        $totalPerusahaan = $data->where('jenis_dokter', 'perusahaan')->sum('total_pasien');
                        $totalPoliklinik = $data->where('jenis_dokter', 'umum')->sum('total_pasien');
                    @endphp
                    <tr style="background: #e3f2fd;">
                        <td colspan="3" class="text-right" style="font-weight: bold;">TOTAL PASIEN DOKTER PERUSAHAAN</td>
                        <td class="text-center" style="font-weight: bold;">{{ $totalPerusahaan }}</td>
                    </tr>
                    <tr style="background: #e8f5e9;">
                        <td colspan="3" class="text-right" style="font-weight: bold;">TOTAL PASIEN DOKTER POLIKLINIK</td>
                        <td class="text-center" style="font-weight: bold;">{{ $totalPoliklinik }}</td>
                    </tr>
                    <tr class="total-row">
                        <td colspan="3" class="text-right">GRAND TOTAL</td>
                        <td class="text-center">{{ $totalPerusahaan + $totalPoliklinik }}</td>
                    </tr>
                @endif

                @if($jenis === 'obat')
                    <tr class="total-row">
                        <td colspan="4" class="text-right">TOTAL BIAYA</td>
                        <td class="text-right">Rp {{ number_format($data->sum('total'), 0, ',', '.') }}</td>
                    </tr>
                @endif

                @if($jenis === 'total')
                    <tr class="total-row">
                        <td class="text-right">GRAND TOTAL</td>
                        <td class="text-right">Rp {{ number_format($data->sum('biaya_obat'), 0, ',', '.') }}</td>
                        <td class="text-center">{{ $data->sum('biaya_dokter') }}</td>
                        <td class="text-right">Rp {{ number_format($data->sum('biaya_obat'), 0, ',', '.') }}</td>
                    </tr>
                @endif
            @endif
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada: {{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i') }} WIB
    </div>
    
    {{-- Detail Pasien untuk Dokter Poliklinik (Halaman Tambahan) --}}
    @if($jenis === 'dokter')
        @foreach($data->where('jenis_dokter', 'umum') as $dokter)
            @if(isset($dokter->detail_pasien) && $dokter->detail_pasien->count() > 0)
                <div style="page-break-before: always;"></div>
                
                <div class="header">
                    <h1>DETAIL PASIEN - {{ strtoupper($dokter->nama_dokter) }}</h1>
                    <div class="periode">
                        Dokter Poliklinik | Total: {{ $dokter->detail_pasien->count() }} pasien
                    </div>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 40px;">No</th>
                            <th>Nama Pasien</th>
                            <th>Tanggal Pemeriksaan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($dokter->detail_pasien as $index => $pasien)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>{{ $pasien->nama_pasien }}</td>
                                <td>{{ \Carbon\Carbon::parse($pasien->tanggal)->translatedFormat('d F Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        @endforeach
    @endif
</body>
</html>