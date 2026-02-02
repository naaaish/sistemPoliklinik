<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $judul }}</title>
</head>
<body>

<table border="1" cellpadding="6" cellspacing="0" width="100%">
    <tr>
        <th colspan="5" style="font-size:16px;text-align:center;">
            {{ $judul }}
        </th>
    </tr>
    <tr>
        <th colspan="5" style="text-align:center;">
            Periode:
            @if($dari && $sampai)
                {{ \Carbon\Carbon::parse($dari)->translatedFormat('d F Y') }}
                -
                {{ \Carbon\Carbon::parse($sampai)->translatedFormat('d F Y') }}
            @else
                Semua Data
            @endif
        </th>
    </tr>
</table>

<br>

<table border="1" cellpadding="6" cellspacing="0" width="100%">
    <thead>
        <tr style="background:#eee;">
            <th>No</th>

            @if($jenis === 'pegawai' || $jenis === 'pensiun')
                <th>Nama Pasien</th>
                <th>Tanggal Pemeriksaan</th>

            @elseif($jenis === 'dokter')
                <th>Nama Dokter</th>
                <th>Jenis Dokter</th>
                <th>Total Pasien</th>

            @elseif($jenis === 'obat')
                <th>Nama Obat</th>
                <th>Tanggal</th>
                <th>Jumlah</th>
                <th>Harga</th>
                <th>Total</th>
            @endif
        </tr>
    </thead>

    <tbody>
        @php $no = 1; @endphp

        @foreach($grouped as $id => $rows)
            @php
                $rowspan = $rows->count();
                $first = $rows->first();
            @endphp

            @foreach($rows as $i => $r)
                <tr>
                    {{-- ===== MERGED COLUMNS (CUMA BARIS PERTAMA) ===== --}}
                    @if($i === 0)
                        <td rowspan="{{ $rowspan }}">{{ $no++ }}</td>
                        <td rowspan="{{ $rowspan }}">{{ $r->tanggal }}</td>
                        <td rowspan="{{ $rowspan }}">{{ $r->nama_pegawai }}</td>
                        <td rowspan="{{ $rowspan }}">{{ $r->umur }}</td>
                        <td rowspan="{{ $rowspan }}">{{ $r->bagian }}</td>
                        <td rowspan="{{ $rowspan }}">{{ $r->nama_pasien }}</td>
                        <td rowspan="{{ $rowspan }}">{{ $r->hub_kel }}</td>
                        <td rowspan="{{ $rowspan }}">{{ $r->sistol }}</td>
                        <td rowspan="{{ $rowspan }}">{{ $r->diastol }}</td>
                        <td rowspan="{{ $rowspan }}">{{ $r->gd_puasa }}</td>
                        <td rowspan="{{ $rowspan }}">{{ $r->gd_duajam }}</td>
                        <td rowspan="{{ $rowspan }}">{{ $r->gd_sewaktu }}</td>
                        <td rowspan="{{ $rowspan }}">{{ $r->asam_urat }}</td>
                        <td rowspan="{{ $rowspan }}">{{ $r->chol }}</td>
                        <td rowspan="{{ $rowspan }}">{{ $r->tg }}</td>
                        <td rowspan="{{ $rowspan }}">{{ $r->suhu }}</td>
                        <td rowspan="{{ $rowspan }}">{{ $r->berat }}</td>
                        <td rowspan="{{ $rowspan }}">{{ $r->tinggi }}</td>
                    @endif

                    {{-- ===== TIDAK DI-MERGE ===== --}}
                    <td>{{ $r->diagnosa }}</td>
                    <td>{{ $r->nb }}</td>
                    <td>{{ $r->nama_obat }}</td>
                    <td>{{ $r->jumlah }}</td>
                    <td>{{ $r->harga }}</td>

                    {{-- ===== TOTAL OBAT (CUMA BARIS PERTAMA) ===== --}}
                    @if($i === 0)
                        <td rowspan="{{ $rowspan }}">{{ $r->total_obat_pasien }}</td>
                        <td rowspan="{{ $rowspan }}">{{ $r->pemeriksa }}</td>
                        <td rowspan="{{ $rowspan }}">{{ $r->periksa_ke }}</td>
                    @endif
                </tr>
            @endforeach
        @endforeach

    </tbody>
</table>

<br>

<table width="100%">
    <tr>
        <td align="right">
            Dicetak: {{ now()->translatedFormat('d F Y H:i') }} WIB
        </td>
    </tr>
</table>

</body>
</html>
