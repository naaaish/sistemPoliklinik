@extends('layouts.kepegawaian')

@section('content')
<link rel="stylesheet" href="{{ asset('css/laporan.css') }}">

<div class="laporan-page">
    <h2 class="page-title">Rekapan Laporan</h2>

@foreach($rekapan as $key => $judul)
<div class="laporan-card">

    <div class="laporan-header">
        <h3>{{ $judul }}</h3>
        <a href="#" class="lihat-semua">Lihat Semua →</a>
    </div>

    <table class="laporan-table">
        <thead>
            <tr>
                @if($key === 'dokter')
                    <th>Nama Dokter</th>
                    <th>Jenis Dokter</th>
                    <th>Total Pasien</th>

                @elseif($key === 'obat')
                    <th>Nama Obat</th>
                    <th>Tanggal</th>
                    <th>Jumlah</th>
                    <th>Harga</th>
                    <th>Total</th>
                    <th>Pasien</th>
                    <th>Dokter</th>

                @elseif($key === 'total')
                    <th>Total Dokter</th>
                    <th>Total Obat</th>
                    <th>Grand Total</th>

                @else
                    <th>Nama Pasien</th>
                    <th>Tanggal</th>
                @endif
            </tr>
        </thead>

        <tbody>

        {{-- TOTAL (TIDAK LOOP) --}}
        @if($key === 'total')
            <tr>
                <td>Rp {{ number_format($preview['total']['total_dokter'],0,',','.') }}</td>
                <td>Rp {{ number_format($preview['total']['total_obat'],0,',','.') }}</td>
                <td><strong>Rp {{ number_format($preview['total']['grand_total'],0,',','.') }}</strong></td>
            </tr>

        {{-- YANG LAIN LOOP --}}
        @else
            @forelse($preview[$key] ?? [] as $p)

                @if($key === 'dokter')
                <tr>
                    <td>{{ $p->nama_dokter }}</td>
                    <td>{{ ucfirst($p->jenis_dokter) }}</td>
                    <td>{{ $p->total_pasien }}</td>
                </tr>

                @elseif($key === 'obat')
                <tr>
                    <td>{{ $p->nama_obat }}</td>
                    <td>{{ \Carbon\Carbon::parse($p->tanggal)->translatedFormat('d M Y') }}</td>
                    <td>{{ $p->jumlah }}</td>
                    <td>Rp {{ number_format($p->harga,0,',','.') }}</td>
                    <td><strong>Rp {{ number_format($p->total,0,',','.') }}</strong></td>
                    <td>{{ $p->nama_pasien }}</td>
                    <td>{{ $p->nama_dokter ?? '-' }}</td>
                </tr>

                @else
                <tr>
                    <td>{{ $p->nama_pasien }}</td>
                    <td>{{ \Carbon\Carbon::parse($p->tanggal)->translatedFormat('d M Y') }}</td>
                </tr>
                @endif

            @empty
                <tr>
                    <td colspan="7" class="empty">Tidak ada data</td>
                </tr>
            @endforelse
        @endif

        </tbody>
    </table>
</div>
@endforeach
</div>
@endsection
