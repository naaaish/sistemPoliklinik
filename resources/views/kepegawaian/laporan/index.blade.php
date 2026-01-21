@extends('layouts.kepegawaian')

@section('content')
<link rel="stylesheet" href="{{ asset('css/laporan.css') }}">

<div class="laporan-page">
    <h2 class="page-title">Rekapan Laporan</h2>

    @foreach($rekapan as $key => $judul)
        <div class="laporan-card">

            {{-- HEADER --}}
            <div class="laporan-header">
                <h3>{{ $judul }}</h3>
                <a href="{{ route('kepegawaian.laporan.detail', $key) }}" class="lihat-semua">
                    Lihat Semua →
                </a>
            </div>

            {{-- TABLE --}}
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
                            <th>Total</th>

                        @elseif($key === 'total')
                            <th>Total Dokter</th>
                            <th>Total Obat</th>
                            <th>Grand Total</th>

                        @else
                            <th>ID Pemeriksaan</th>
                            <th>Nama Pasien</th>
                            <th>Tanggal</th>
                            <th>Nama Pemeriksa</th>
                        @endif
                    </tr>
                </thead>

                <tbody>
                    {{-- ================= TOTAL (BUKAN LOOP) ================= --}}
                    @if($key === 'total')
                        <tr>
                            <td>
                                Rp {{ number_format($preview['total']['total_dokter'] ?? 0, 0, ',', '.') }}
                            </td>
                            <td>
                                Rp {{ number_format($preview['total']['total_obat'] ?? 0, 0, ',', '.') }}
                            </td>
                            <td>
                                <strong>
                                    Rp {{ number_format($preview['total']['grand_total'] ?? 0, 0, ',', '.') }}
                                </strong>
                            </td>
                        </tr>

                    {{-- ================= YANG LAIN (LOOP) ================= --}}
                    @else
                        @forelse($preview[$key] ?? [] as $p)
                            <tr>
                                @if($key === 'dokter')
                                    <td>{{ $p->nama_dokter }}</td>
                                    <td>{{ ucfirst($p->jenis_dokter) }}</td>
                                    <td>{{ $p->total_pasien }}</td>

                                @elseif($key === 'obat')
                                    <td>{{ $p->nama_obat }}</td>
                                    <td>
                                        {{ \Carbon\Carbon::parse($p->tanggal)->translatedFormat('d F Y') }}
                                    </td>
                                    <td>{{ $p->jumlah }}</td>
                                    <td>
                                        Rp {{ number_format($p->total, 0, ',', '.') }}
                                    </td>

                                @else
                                    <td>{{ $p->id_pemeriksaan }}</td>
                                    <td>{{ $p->nama_pasien }}</td>
                                    <td>
                                        {{ \Carbon\Carbon::parse($p->tanggal)->translatedFormat('d F Y') }}
                                    </td>
                                    <td>
                                        {{ $p->nama_dokter ?? $p->nama_pemeriksa ?? '-' }}
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="empty">
                                    Tidak ada data
                                </td>
                            </tr>
                        @endforelse
                    @endif
                </tbody>
            </table>
        </div>
    @endforeach
</div>
@endsection
