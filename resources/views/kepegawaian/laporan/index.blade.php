@extends('layouts.kepegawaian')

@section('content')
<link rel="stylesheet" href="{{ asset('css/laporan.css') }}">

<div class="laporan-page">

    <h2 class="page-title">Rekapan Laporan</h2>

    @foreach($rekapan as $key => $judul)
    <div class="laporan-card">

        <div class="laporan-header">
            <h3>{{ $judul }}</h3>
            <a href="{{ route('kepegawaian.laporan.detail', $key) }}" class="lihat-semua">
                Lihat Semua →
            </a>
        </div>

        <table class="laporan-table">
            <thead>
                <tr>
                    <th>Nama Pasien</th>
                    <th>Tanggal</th>
                    <th>Dokter</th>
                </tr>
            </thead>
            <tbody>
                @forelse($preview as $p)
                <tr>
                    <td>{{ $p->nama_pasien }}</td>
                    <td>
                        {{ \Carbon\Carbon::parse($p->tanggal)
                            ->translatedFormat('d M Y') }}
                    </td>
                    <td>{{ $p->nama_dokter ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="empty">Tidak ada data</td>
                </tr>
                @endforelse

            </tbody>
        </table>

    </div>
    @endforeach

</div>
@endsection
