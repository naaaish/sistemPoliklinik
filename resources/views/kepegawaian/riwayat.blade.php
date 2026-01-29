@extends('layouts.kepegawaian')

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
                <td>{{ $r->dokter ?? '-' }}</td>
                <td>{{ $r->pemeriksa ?? '-' }}</td>
                <td>
                    <a href="{{ route('kepegawaian.riwayat.detail', $r->id_pemeriksaan) }}" class="view-btn">
                        <img src="{{ asset('assets/adminPoli/eye.png') }}" alt="Lihat">
                    </a>
                </td>
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

    {{-- Pagination --}}
    <div class="pagination-container">
        <form method="GET" class="per-page-selector">
            <label>Tampilkan</label>

            <select name="per_page" onchange="this.form.submit()">
                @foreach ([10,25,50,100,'all'] as $size)
                    <option value="{{ $size }}"
                        {{ $perPage == $size ? 'selected' : '' }}>
                        {{ strtoupper($size) }}
                    </option>
                @endforeach
            </select>

            {{-- keep query lain --}}
            @foreach(request()->except('per_page','page') as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
        </form>

        @if(!$isAll)
            <div class="pagination-info">
                Menampilkan
                <strong>{{ $riwayat->firstItem() }}</strong>
                -
                <strong>{{ $riwayat->lastItem() }}</strong>
                dari
                <strong>{{ $riwayat->total() }}</strong> data
            </div>
        @endif

        @if(!$isAll)
            <div class="pagination-nav">
                {{ $riwayat->links() }}
            </div>
        @endif
    </div>

</div>
</div>

@endsection