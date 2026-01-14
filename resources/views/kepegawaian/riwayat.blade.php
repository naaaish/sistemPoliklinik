@extends('kepegawaian.layout')

@section('title','Riwayat Pemeriksaan')

@section('content')

<h1 class="page-title">Riwayat Pemeriksaan</h1>

<div class="riwayat-card">
   <div class="table-box">
    <table>
        <thead>
            <tr>
                <th>Nama Pasien</th>
                <th>Waktu Periksa</th>
                <th>Dokter</th>
                <th>Pemeriksa</th>
                <th>Lihat</th>
            </tr>
        </thead>
        <tbody>
            @forelse($riwayat as $r)
            <tr>
                <td>{{ $r->nama_pasien }}</td>
                <td>{{ \Carbon\Carbon::parse($r->tanggal)->translatedFormat('l, d M Y, H:i') }}</td>
                <td>{{ $r->dokter }}</td>
                <td>{{ $r->pemeriksa }}</td>
                <td><a class="view-btn">+</a></td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align:center; padding:20px;">
                    Tidak ada data pemeriksaan
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
</div>

<div class="page-footer">
    Copyright © 2026 Poliklinik PT PLN Indonesia Power UBP Mrica
</div>

@endsection

