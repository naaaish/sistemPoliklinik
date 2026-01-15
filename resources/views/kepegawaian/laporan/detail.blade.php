@extends('layouts.kepegawaian')

@section('content')
<link rel="stylesheet" href="{{ asset('css/laporan.css') }}">

<div class="laporan-page">
    <h2 class="page-title">{{ $judul }}</h2>

    {{-- FILTER TANGGAL --}}
    <div class="laporan-card">
        <form method="GET" action="{{ route('kepegawaian.laporan.detail', $jenis) }}" class="filter-form">
            <div class="filter-group">
                <label>Dari:</label>
                <input type="date" name="dari" value="{{ $dari }}" class="form-control">
            </div>
            <div class="filter-group">
                <label>Sampai:</label>
                <input type="date" name="sampai" value="{{ $sampai }}" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">Tampilkan</button>
        </form>
    </div>

    {{-- TABLE --}}
    <div class="laporan-card">
        <table class="laporan-table">
            <thead>
                <tr>
                    @if($jenis === 'dokter')
                        <th>Nama Dokter</th>
                        <th>Jenis Dokter</th>
                        <th>Total Pasien</th>

                    @elseif($jenis === 'obat')
                        <th>Nama Obat</th>
                        <th>Tanggal</th>
                        <th>Jumlah</th>
                        <th>Total</th>

                    @elseif($jenis === 'total')
                        <th>Tanggal</th>
                        <th>Biaya Dokter</th>
                        <th>Biaya Obat</th>
                        <th>Total</th>

                    @else
                        <th>Nama Pasien</th>
                        <th>Tanggal</th>
                    @endif
                </tr>
            </thead>

            <tbody>
                @forelse($data as $item)
                    <tr>
                        @if($jenis === 'dokter')
                            <td>{{ $item->nama_dokter }}</td>
                            <td>{{ ucfirst($item->jenis_dokter) }}</td>
                            <td>{{ $item->total_pasien }}</td>

                        @elseif($jenis === 'obat')
                            <td>{{ $item->nama_obat }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d F Y') }}</td>
                            <td>{{ $item->jumlah }}</td>
                            <td>Rp {{ number_format($item->total, 0, ',', '.') }}</td>

                        @elseif($jenis === 'total')
                            <td>{{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d F Y') }}</td>
                            <td>Rp {{ number_format($item->biaya_dokter ?? 0, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($item->biaya_obat ?? 0, 0, ',', '.') }}</td>
                            <td>
                                <strong>
                                    Rp {{ number_format(($item->biaya_dokter ?? 0) + ($item->biaya_obat ?? 0), 0, ',', '.') }}
                                </strong>
                            </td>

                        @else
                            <td>{{ $item->nama_pasien }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d F Y') }}</td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="empty">Tidak ada data</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<style>
.filter-form {
    display: flex;
    gap: 15px;
    align-items: end;
    margin-bottom: 20px;
}
.filter-group {
    display: flex;
    flex-direction: column;
}
.filter-group label {
    margin-bottom: 5px;
    font-weight: 500;
}
.form-control {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
}
.btn-primary {
    padding: 8px 20px;
    background: #007bff;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}
.btn-primary:hover {
    background: #0056b3;
}
</style>
@endsection