@extends('layouts.kepegawaian')

@section('content')
<link rel="stylesheet" href="{{ asset('css/laporan.css') }}">

<div class="laporan-page">
    <h2 class="page-title">Rekapan Laporan</h2>

    {{-- ========================================
         FILTER TANGGAL - SATU-SATUNYA TEMPAT!
         Sekali pilih langsung filter semua
    ======================================== --}}
    <div class="filter-card">
        <form method="GET" action="{{ route('kepegawaian.laporan') }}" class="filter-form">

            <div>
                <label>Dari</label>
                <input type="date" name="dari" value="{{ request('dari') }}">
            </div>

            <div>
                <label>Sampai</label>
                <input type="date" name="sampai" value="{{ request('sampai') }}">
            </div>

            <button type="submit" class="btn-primary">
                Tampilkan
            </button>

        </form>
    </div>

    {{-- INFO PERIODE YANG DIPILIH --}}
    @if(request('dari') && request('sampai'))
    <div class="periode-info">
        <strong>Periode :</strong> 
        {{ \Carbon\Carbon::parse(request('dari'))->translatedFormat('d F Y') }} 
        - 
        {{ \Carbon\Carbon::parse(request('sampai'))->translatedFormat('d F Y') }}
    </div>
    @endif

    @foreach($rekapan as $key => $judul)
        <div class="laporan-card">

            {{-- HEADER --}}
            <div class="laporan-header">
                <h3>{{ $judul }}</h3>
                
                    <a class="btn-lihat-semua"
                    href="{{ route('kepegawaian.laporan.detail', ['jenis' => $key] + request()->only(['dari','sampai'])) }}">
                        Lihat Semua 
                    </a>


            </div>

            {{-- TABLE --}}
            <div class="table-wrapper">
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
                            @elseif ($key === 'total')
                                <th>ID Pemeriksaan</th>
                                <th>Nama Pasien</th>
                                <th>Tanggal</th>
                                <th>Nama Pemeriksa</th>
                                <th>Total Obat Pasien</th>
                            @else
                            {{-- Pegawai / Pensiunan / Keseluruhan --}}
                                <th>ID Pemeriksaan</th>
                                <th>Nama Pasien</th>
                                <th>Tanggal</th>
                                <th>Nama Pemeriksa</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>


                    {{-- ================= DOKTER ================= --}}
                    @if($key === 'dokter')
                        @forelse($preview['dokter'] as $p)
                            <tr>
                                <td>{{ $p->nama_dokter }}</td>
                                <td>{{ $p->jenis_dokter }}</td>
                                <td>{{ $p->total_pasien }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3">Tidak ada data</td></tr>
                        @endforelse

                    {{-- ================= OBAT ================= --}}
                    @elseif($key === 'obat')
                        @forelse($preview['obat'] as $p)
                            <tr>
                                <td>{{ $p->nama_obat }}</td>
                                <td>{{ $p->tanggal }}</td>
                                <td>{{ $p->jumlah }}</td>
                                <td>Rp {{ number_format($p->total,0,',','.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4">Tidak ada data</td></tr>
                        @endforelse

                    {{-- ================= PEGAWAI / PENSIUN  / KESELURUHAN ================= --}}
                    @else
                        @forelse($preview[$key] as $p)
                            <tr>
                                <td>{{ $p->id_pemeriksaan }}</td>
                                <td>{{ $p->nama_pasien }}</td>
                                <td>{{ $p->tanggal }}</td>
                                <td>{{ $p->nama_pemeriksa }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4">Tidak ada data</td></tr>
                        @endforelse
                    @endif

                    </tbody>
                </table>
            </div>
        </div>
    @endforeach
</div>
@endsection