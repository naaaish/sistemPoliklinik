@extends('layouts.adminpoli')

@section('title', 'Laporan')

@section('content')
<div class="lapidx-page">
  <div class="lapidx-topbar">
    <div class="lapidx-left">
      <a href="{{ route('adminpoli.dashboard') }}" class="lapidx-back-img" title="Kembali">
        <img src="{{ asset('assets/adminPoli/back-arrow.png') }}" alt="Kembali">
      </a>
      <div class="lapidx-heading">Laporan</div>
    </div>

    <div class="lapidx-right">
      <a class="lapidx-btn-soft"
        href="{{ route('adminpoli.laporan.exportAll', request()->query()) }}">
        <img class="lapidx-ic-sm" src="{{ asset('assets/adminPoli/download.png') }}" alt="">
        Download Semua
      </a>
    </div>
  </div>

  <div class="lapidx-card">

    <form action="{{ route('adminpoli.laporan.index') }}" method="GET" class="lapidx-tools">

      <div class="lapidx-filter">
        <select name="tipe" class="lapidx-select">
          <option value="pegawai" {{ $tipe==='pegawai' ? 'selected' : '' }}>Pegawai</option>
          <option value="pensiunan" {{ $tipe==='pensiunan' ? 'selected' : '' }}>Pensiunan</option>
          <option value="poliklinik" {{ $tipe==='poliklinik' ? 'selected' : '' }}>Poliklinik</option>
        </select>

        <input type="date" name="from" value="{{ $from }}" class="lapidx-date" required>
        <span class="lapidx-sep">s/d</span>
        <input type="date" name="to" value="{{ $to }}" class="lapidx-date" required>

        <button type="submit" class="lapidx-btn-search">
          <img class="lapidx-ic" src="{{ asset('assets/adminPoli/search.png') }}" alt="">
          Tampilkan
        </button>
      </div>
      <div class="lapidx-actions-top">
        <a class="lapidx-btn-soft"
            href="{{ route('adminpoli.laporan.preview', ['tipe'=>$tipe,'from'=>$from,'to'=>$to]) }}">
          <img class="lapidx-ic-sm" src="{{ asset('assets/adminPoli/eye.png') }}" alt="">
          Preview
        </a>
      </div>
    </form>

    <div class="lapidx-table">
      <div class="lapidx-thead">
        <div class="lapidx-th">NO</div>
        <div class="lapidx-th">TANGGAL</div>
        <div class="lapidx-th">NAMA</div>
        <div class="lapidx-th">NIP</div>
      </div>

      <div class="lapidx-tbody">
        @forelse($items as $it)
          <div class="lapidx-row">
            <div class="lapidx-td lapidx-center">{{ $it['no'] }}</div>
            <div class="lapidx-td lapidx-center">{{ $it['tanggal'] }}</div>
            <div class="lapidx-td">{{ $it['nama'] }}</div>
            <div class="lapidx-td lapidx-center">{{ $it['nip'] }}</div>
          </div>
        @empty
          <div class="lapidx-empty">Tidak ada data pada filter ini.</div>
        @endforelse
      </div>
    </div>

    <div class="lapidx-bottom-full">
      {{-- kiri: total --}}
      <div class="lapidx-total">
        Total <strong>{{ $nips->total() }}</strong>
      </div>

      {{-- tengah: lines per page --}}
      <form method="GET"
            action="{{ route('adminpoli.laporan.index') }}"
            class="lapidx-lines">

        {{-- keep filter --}}
        <input type="hidden" name="tipe" value="{{ $tipe }}">
        <input type="hidden" name="from" value="{{ $from }}">
        <input type="hidden" name="to" value="{{ $to }}">

        <span>Lines per page</span>
        <select name="per_page" onchange="this.form.submit()">
          @foreach([5,10,15,25,50] as $n)
            <option value="{{ $n }}" {{ (int)$perPage === $n ? 'selected' : '' }}>
              {{ $n }}
            </option>
          @endforeach
        </select>
      </form>

      {{-- kanan: pagination --}}
      <div class="lapidx-paginate">
        {{ $nips->links() }}
      </div>
    </div>
  </div>

  <div class="lapidx-foot">
    Copyright © 2026 Poliklinik PT PLN Indonesia Power UBP Mrica
  </div>
</div>
@endsection
