@extends('layouts.kepegawaian')

@section('content')
<div class="laporan-page">

    <h2 class="page-title">{{ $judul }}</h2>

    {{-- FILTER TANGGAL --}}
    <form method="GET">
        <input type="date" name="dari" value="{{ $dari }}">
        s/d
        <input type="date" name="sampai" value="{{ $sampai }}">
        <button>Tampilkan</button>
    </form>

    <table>
    <thead>
    @if($jenis === 'obat')
    <tr>
        <th>Nama Obat</th>
        <th>Tanggal</th>
        <th>Jumlah</th>
        <th>Total</th>
    </tr>
    @elseif($jenis === 'total')
    <tr>
        <th>Tanggal</th>
        <th>Total Obat</th>
    </tr>
    @endif
    </thead>

    <tbody>
    @forelse($data as $d)
    @if($jenis === 'obat')
    <tr>
        <td>{{ $d->nama_obat }}</td>
        <td>{{ \Carbon\Carbon::parse($d->tanggal)->translatedFormat('d F Y') }}</td>
        <td>{{ $d->jumlah }}</td>
        <td>Rp {{ number_format($d->total,0,',','.') }}</td>
    </tr>
    @elseif($jenis === 'total')
    <tr>
        <td>{{ \Carbon\Carbon::parse($d->tanggal)->translatedFormat('d F Y') }}</td>
        <td>Rp {{ number_format($d->total_obat,0,',','.') }}</td>
    </tr>
    @endif
    @empty
    <tr>
        <td colspan="4">Tidak ada data</td>
    </tr>
    @endforelse
    </tbody>
    </table>


</div>
@endsection
