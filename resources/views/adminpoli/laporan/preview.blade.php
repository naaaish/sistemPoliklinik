@extends('layouts.adminpoli')

@section('title', 'Preview Laporan')

@section('content')
<!-- <div class="lapidx-page">
  <div class="lapidx-topbar">
    <div class="lapidx-left">
      <a href="{{ route('adminpoli.dashboard') }}" class="lapidx-back-img" title="Kembali">
        <img src="{{ asset('assets/adminPoli/back-arrow.png') }}" alt="Kembali">
      </a>
      <div class="lapidx-heading">Laporan</div>
    </div>
  </div> -->
<div class="lap-page">
  <div class="lap-topbar">
    <a href="{{ route('adminpoli.laporan.index', ['tipe'=>$tipe,'from'=>$from,'to'=>$to]) }}" class="lap-back-img" title="Kembali">
      <img src="{{ asset('assets/adminPoli/back-arrow.png') }}" alt="Kembali">
    </a>
    <div class="lap-heading">Preview Laporan ({{ $nip }})</div>
  </div>

  <div class="lap-card">
    <form action="{{ route('adminpoli.laporan.index') }}" method="GET" class="lap-tools">
      <div class="lap-filter">
        <input type="hidden" name="tipe" value="{{ $tipe }}">
        <input type="date" name="from" value="{{ $from }}" required>
        <span class="lap-sep">s/d</span>
        <input type="date" name="to" value="{{ $to }}" required>
        <button type="submit" class="lap-btn">Tampilkan</button>
      </div>

      <div class="lap-filter">
        <a class="lap-btn"
           href="{{ route('adminpoli.laporan.export', ['tipe'=>$tipe,'nip'=>$nip,'from'=>$from,'to'=>$to]) }}">
          <img src="{{ asset('assets/adminPoli/download.png') }}" alt="Download">
          Download Excel
        </a>
      </div>
    </form>

    <div class="lap-info">
      <b>{{ $count ?? 0 }}</b> baris ({{ $from }} s/d {{ $to }})
    </div>

    {{-- Table --}}
    <div class="lap-table-wrap">
      <div class="lap-table">

        {{-- Header --}}
        <div class="lap-thead">
          <div class="lap-th">NO</div>
          <div class="lap-th">TANGGAL</div>
          <div class="lap-th">NAMA</div>
          <div class="lap-th">UMUR</div>
          <div class="lap-th">BAGIAN</div>
          <div class="lap-th">NAMA PASIEN</div>
          <div class="lap-th">HUB KEL</div>
          <div class="lap-th">TD</div>
          <div class="lap-th">GDP</div>
          <div class="lap-th">GD 2JAM PP</div>
          <div class="lap-th">GDS</div>
          <div class="lap-th">AU</div>
          <div class="lap-th">CHOL</div>
          <div class="lap-th">TG</div>
          <div class="lap-th">Suhu</div>
          <div class="lap-th">BB</div>
          <div class="lap-th">TB</div>
          <div class="lap-th">DIAGNOSA</div>
          <div class="lap-th">TERAPHY</div>
          <div class="lap-th">JUMLAH OBAT</div>
          <div class="lap-th">HARGA OBAT (SATUAN)</div>
          <div class="lap-th">TOTAL HARGA OBAT</div>
          <div class="lap-th">PEMERIKSA</div>
          <div class="lap-th">NB</div>
          <div class="lap-th">PERIKSA KE</div>
        </div>

        {{-- Rows --}}
        @forelse($rows as $r)
          <div class="lap-row">
            <div class="lap-td lap-center">{{ $r['NO'] }}</div>
            <div class="lap-td lap-center">{{ $r['TANGGAL'] }}</div>
            <div class="lap-td">{{ $r['NAMA'] }}</div>
            <div class="lap-td lap-center">{{ $r['UMUR'] }}</div>
            <div class="lap-td">{{ $r['BAGIAN'] }}</div>
            <div class="lap-td">{{ $r['NAMA_PASIEN'] }}</div>
            <div class="lap-td lap-center">{{ $r['HUB_KEL'] }}</div>
            <div class="lap-td lap-center">{{ $r['TD'] }}</div>
            <div class="lap-td lap-center">{{ $r['GDP'] }}</div>
            <div class="lap-td lap-center">{{ $r['GD_2JAM_PP'] }}</div>
            <div class="lap-td lap-center">{{ $r['GDS'] }}</div>
            <div class="lap-td lap-center">{{ $r['AU'] }}</div>
            <div class="lap-td lap-center">{{ $r['CHOL'] }}</div>
            <div class="lap-td lap-center">{{ $r['TG'] }}</div>
            <div class="lap-td lap-center">{{ $r['SUHU'] }}</div>
            <div class="lap-td lap-center">{{ $r['BB'] }}</div>
            <div class="lap-td lap-center">{{ $r['TB'] }}</div>
            <div class="lap-td">{{ $r['DIAGNOSA'] }}</div>
            <div class="lap-td">{{ $r['TERAPHY'] }}</div>
            <div class="lap-td lap-center">{{ $r['JUMLAH_OBAT'] }}</div>
            <div class="lap-td lap-center">
              @php
                $hs = $r['HARGA_OBAT_SATUAN'];
              @endphp
              {{ is_numeric($hs) ? number_format($hs, 0, ',', '.') : $hs }}
            </div>
            <div class="lap-td lap-center">
              @php
                $tt = $r['TOTAL_HARGA_OBAT'];
              @endphp
              {{ is_numeric($tt) ? number_format($tt, 0, ',', '.') : $tt }}
            </div>
            <div class="lap-td">{{ $r['PEMERIKSA'] }}</div>
            <div class="lap-td lap-center">{{ $r['NB'] }}</div>
            <div class="lap-td lap-center">{{ $r['PERIKSA_KE'] }}</div>
          </div>
        @empty
          <div class="lap-empty">Tidak ada data pada rentang ini.</div>
        @endforelse

      </div>
    </div>

  </div>
</div>
@endsection
