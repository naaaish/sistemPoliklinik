@extends('layouts.kepegawaian')

@section('content')

<div class="header">
    <h3>Selamat Datang, Admin Kepegawaian!</h3>
    <span>Tanggal: {{ now()->translatedFormat('l, d F Y') }}</span>
</div>

<div class="card-grid">

    <div class="card">
        <h1>{{ $totalRiwayat }}</h1>
        <p>Total Riwayat</p>
    </div>

    {{-- CARD TENGAH (yang ditonjolin) --}}
    <div class="card highlight">
        <h1>{{ $hariIni }}</h1>
        <p>Pemeriksaan Hari Ini</p>
    </div>

    <div class="card">
        <h1>{{ $totalPegawai }}</h1>
        <p>Total Pegawai</p>
    </div>

</div>

<div class="table-box">
    <h3>Daftar Riwayat Pemeriksaan Pasien</h3>

    <table class="riwayat-table">
        <thead>
            <tr>
                <th>Nama Pasien</th>
                <th>NIP</th>
                <th>Waktu Periksa</th>
                <th>Nama Pemeriksa</th>
                <th>Lihat</th>
            </tr>
        </thead>
        <tbody>
            @foreach($riwayat as $r)
            <tr>
                <td>{{ $r->nama_pasien }}</td>
                <td>{{ $r->nip }}</td>
                <td>{{ \Carbon\Carbon::parse($r->tanggal)->translatedFormat('d M Y H:i') }}</td>
                <td>{{ $r->nama_pemeriksa ?? '-' }}</td>
                <td>
                    <a href="{{ route('kepegawaian.riwayat.detail', $r->id_pemeriksaan) }}" class="view-btn">
                        <img src="{{ asset('assets/adminPoli/eye.png') }}" alt="Lihat">
                    </a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection
