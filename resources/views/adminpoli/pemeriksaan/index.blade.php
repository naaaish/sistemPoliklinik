@extends('layouts.adminpoli')
@section('title', 'Pemeriksaan Pasien')

@section('content')
<div class="periksa-page">

  <div class="periksa-topbar">
    <div class="periksa-left">
      <a href="{{ route('adminpoli.dashboard') }}" class="periksa-back-img" title="Kembali">
        <img src="{{ asset('assets/adminPoli/back-arrow.png') }}" alt="Kembali">
      </a>
      <div class="periksa-heading">Pemeriksaan Pasien</div>
    </div>
  </div>

  <div class="periksa-card">

    {{-- Search --}}
    <form class="periksa-search" method="GET" action="{{ route('adminpoli.pemeriksaan.index') }}">
      <input
        type="text"
        name="q"
        value="{{ request('q') }}"
        placeholder="Masukkan nama pegawai yang dicari"
        class="periksa-search-input"
      >
      <button class="periksa-search-btn" type="submit">
        <img src="{{ asset('assets/adminPoli/search.png') }}" alt="cari" class="periksa-ic">
        <span>Cari</span>
      </button>
    </form>

    {{-- Table --}}
    <div class="periksa-table">
      <div class="periksa-table-head periksa-head">
        <div>No</div>
        <div>Nama</div>
        <div>Tanggal Periksa</div>
        <div>Dokter/Pemeriksa</div>
        <div>Aksi</div>
      </div>

      <div class="periksa-table-body">
        @forelse($pemeriksaan as $i => $row)
          <div class="periksa-row">
            <div><div class="periksa-cell periksa-center">{{ $i + 1 }}</div></div>
            <div><div class="periksa-cell">{{ $row->nama_pasien ?? '-' }}</div></div>
            <div><div class="periksa-cell periksa-center">{{ $row->tanggal_periksa ?? '-' }}</div></div>
            <div><div class="periksa-cell">{{ $row->dokter_pemeriksa ?? '-' }}</div></div>

            <div class="periksa-actions">
              <a href="{{ route('adminpoli.pemeriksaan.show', ['pendaftaranId' => $row->id_pendaftaran]) }}"
                 class="periksa-act periksa-view">
                <span>Lihat</span>
                <img src="{{ asset('assets/adminPoli/eye.png') }}" class="periksa-ic-sm" alt="">
              </a>
            </div>
          </div>
        @empty
          <div class="periksa-row periksa-row-empty">
            <div class="periksa-empty-span">
              {{ request('q') ? 'Tidak ada data ditemukan' : 'Belum ada data pemeriksaan' }}
            </div>
          </div>
        @endforelse
      </div>
    </div>

    <div class="periksa-table-foot">
            <div class="periksa-total">
                Total
                @if($pemeriksaan instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    {{ $pemeriksaan->total() }}
                @else
                    {{ $pemeriksaan->count() }}
                @endif
            </div>

            <form method="GET" action="{{ route('adminpoli.pemeriksaan.index') }}" class="periksa-lines">
                {{-- keep query biar ga reset --}}
                @if(request('q')) <input type="hidden" name="q" value="{{ request('q') }}"> @endif

                <span class="periksa-lines-label">Lines per page</span>

                <select name="per_page" class="periksa-lines-select" onchange="this.form.submit()">
                    <option value="10"  {{ request('per_page','10')=='10' ? 'selected' : '' }}>10</option>
                    <option value="25"  {{ request('per_page')=='25' ? 'selected' : '' }}>25</option>
                    <option value="50"  {{ request('per_page')=='50' ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page')=='100' ? 'selected' : '' }}>100</option>
                    <option value="all" {{ request('per_page')=='all' ? 'selected' : '' }}>All</option>
                </select>
            </form>
        </div>
  </div>

  <div class="periksa-foot">
    Copyright © 2026 Poliklinik PT PLN Indonesia Power UBP Mrica
  </div>
</div>
@endsection

@push('styles')
  <link rel="stylesheet" href="{{ asset('css/adminpoli/pemeriksaan.css') }}">
@endpush
