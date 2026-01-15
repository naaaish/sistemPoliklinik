@extends('layouts.kepegawaian')

@section('content')
<link rel="stylesheet" href="{{ asset('css/laporan.css') }}">

<div class="laporan-detail">

    <div class="detail-header">
        <h2>Rekapan {{ ucfirst(str_replace('_',' ',$jenis)) }}</h2>

        <a href="{{ route('kepegawaian.laporan.download',$jenis) }}" class="btn-download">
            Download PDF
        </a>
    </div>

    <form method="GET" class="filter-box">
        <input type="date" name="dari" value="{{ $dari }}">
        <input type="date" name="sampai" value="{{ $sampai }}">
        <button type="submit">Terapkan</button>
    </form>@extends('kepegawaian.layout')

@section('content')
<link rel="stylesheet" href="{{ asset('css/laporan.css') }}">

<div class="laporan-page">

    <div class="laporan-detail-header">
        <h2>{{ $judul }}</h2>

        <a href="{{ route('kepegawaian.laporan.download', $jenis) }}"
           class="btn-download">
            Download PDF
        </a>
    </div>

    <form method="GET" class="filter-form">
        <input type="date" name="from" value="{{ request('from') }}">
        <span>-</span>
        <input type="date" name="to" value="{{ request('to') }}">
        <button type="submit">Terapkan</button>
    </form>

    <table class="laporan-table">
        <thead>
            <tr>
                <th>Nama Pasien</th>
                <th>Dokter</th>
                <th>Tanggal</th>
                <th>Keluhan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $d)
            <tr>
                <td>{{ $d->nama_pasien }}</td>
                <td>{{ $d->nama_dokter ?? '-' }}</td>
                <td>{{ \Carbon\Carbon::parse($d->tanggal)->translatedFormat('d M Y H:i') }}</td>
                <td>{{ $d->keluhan }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="empty">Tidak ada data</td>
            </tr>
            @endforelse
        </tbody>
    </table>

</div>
@endsection


    <table class="laporan-table">
        <thead>
            <tr>
                <th>Nama Pasien</th>
                <th>Tanggal</th>
                <th>Dokter</th>
                <th>Keluhan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $d)
            <tr>
                <td>{{ $d->nama_pasien }}</td>
                <td>{{ \Carbon\Carbon::parse($d->tanggal)->translatedFormat('d M Y') }}</td>
                <td>{{ $d->nama_dokter ?? '-' }}</td>
                <td>{{ $d->keluhan }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

</div>
@endsection
