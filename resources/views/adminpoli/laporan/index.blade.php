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

      <div class="lapidx-filter">
        <span class="lapidx-muted">Tampilkan</span>
        <select name="per_page" class="lapidx-select" onchange="this.form.submit()">
          @foreach([5,10,25,100] as $n)
            <option value="{{ $n }}" {{ (int)$perPage===$n ? 'selected' : '' }}>{{ $n }}</option>
          @endforeach
        </select>
        <span class="lapidx-muted">NIP</span>
      </div>

    </form>

    <div class="lapidx-info">
      Menampilkan <b>{{ $nips->count() }}</b> dari <b>{{ $nips->total() }}</b> data
      ({{ $from }} s/d {{ $to }})
    </div>

    <div class="lapidx-table">
      <div class="lapidx-thead">
        <div class="lapidx-th">NO</div>
        <div class="lapidx-th">TANGGAL</div>
        <div class="lapidx-th">NAMA</div>
        <div class="lapidx-th">NIP</div>
        <div class="lapidx-th">NAMA PASIEN</div>
        <div class="lapidx-th">HUB KEL</div>
        <div class="lapidx-th">AKSI</div>
      </div>

      <div class="lapidx-tbody">
        @forelse($items as $it)
          <div class="lapidx-row">
            <div class="lapidx-td lapidx-center">{{ $it['no'] }}</div>
            <div class="lapidx-td lapidx-center">{{ $it['tanggal'] }}</div>
            <div class="lapidx-td">{{ $it['nama'] }}</div>
            <div class="lapidx-td lapidx-center">{{ $it['nip'] }}</div>
            <div class="lapidx-td lapidx-pre">{{ $it['nama_pasien'] }}</div>
            <div class="lapidx-td lapidx-pre lapidx-center">{{ $it['hub_kel'] }}</div>
            <div class="lapidx-td lapidx-center">
              <a class="lapidx-btn-soft" href="{{ $it['preview_url'] }}">
                <img class="lapidx-ic-sm" src="{{ asset('assets/adminPoli/eye.png') }}" alt="">
                Preview
              </a>
            </div>
          </div>
        @empty
          <div class="lapidx-empty">Tidak ada data pada filter ini.</div>
        @endforelse
      </div>
    </div>

    <div class="lapidx-paginate">
      {{ $nips->links() }}
    </div>

  </div>

  <div class="lapidx-foot">
    Copyright © 2026 Poliklinik PT PLN Indonesia Power UBP Mrica
  </div>
</div>
@endsection
