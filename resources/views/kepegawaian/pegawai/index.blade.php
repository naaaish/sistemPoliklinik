
@extends('layouts.kepegawaian')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Data Pegawai</h4>

    <a href="{{ route('pegawai.create') }}" class="btn btn-tambah">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2.5">
            <line x1="12" y1="5" x2="12" y2="19"></line>
            <line x1="5" y1="12" x2="19" y2="12"></line>
        </svg>
        Tambah Pegawai
    </a>
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
                <td>{{ $p->bagian}}</td>
                <td class="pegawai-cell-center">
                    <a href="{{ route('kepegawaian.pegawai.show', $p->nip) }}"
                    class="view-btn"
                    title="Lihat Detail">
                        <img src="{{ asset('assets/adminPoli/eye.png') }}" alt="Lihat">
                    </a>
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