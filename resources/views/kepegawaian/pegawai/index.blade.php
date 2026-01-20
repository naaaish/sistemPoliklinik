@extends('layouts.kepegawaian')

@section('content')
<div class="pegawai-header">
    <h2 class="pegawai-title">Data Pegawai</h2>
</div>

<div class="table-box">
    {{-- Search Form --}}
    <form method="GET" action="{{ route('kepegawaian.pegawai') }}" class="pegawai-search">
        <input 
            type="text" 
            name="q" 
            value="{{ $q ?? request('q') }}" 
            placeholder="Cari nama pegawai..." 
            class="pegawai-search-input"
        >
        <button type="submit" class="pegawai-search-btn">
            <img src="{{ asset('assets/adminPoli/search.png') }}" class="pegawai-search-icon" alt="cari">
            <span>Cari</span>
        </button>
    </form>

    {{-- Table --}}
    <table>
        <thead>
            <tr>
                <th>NIP</th>
                <th>Nama Pegawai</th>
                <th>Jabatan</th>
                <th>Bidang</th>
                <th class="pegawai-cell-center">Lihat</th> 
            </tr>
        </thead>
        <tbody>
            @forelse($pegawai as $p)
            <tr>
                <td>{{ $p->nip }}</td>
                <td>{{ $p->nama_pegawai }}</td>
                <td>{{ $p->jabatan }}</td>
                <td>{{ $p->bidang }}</td>
                <td class="pegawai-cell-center">
                    <a href="{{ route('kepegawaian.pegawai.show', $p->nip) }}" class="view-btn">+</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="pegawai-empty">
                    {{ request('q') ? 'Tidak ada pegawai ditemukan dengan nama "' . request('q') . '"' : 'Belum ada data pegawai' }}
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection