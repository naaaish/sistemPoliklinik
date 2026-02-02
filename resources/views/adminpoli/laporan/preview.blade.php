@extends('layouts.adminpoli')

@section('title', 'Preview Laporan')

@section('content')
<div class="lap-page">
  <div class="lap-topbar">
    <div class="lap-left">
      <a href="{{ route('adminpoli.laporan.index', ['tipe'=>$tipe,'from'=>$from,'to'=>$to]) }}" class="lap-back-img" title="Kembali">
        <img src="{{ asset('assets/adminPoli/back-arrow.png') }}" alt="Kembali">
      </a>
      <div class="lap-heading">Preview Laporan</div>
    </div>
  </div>

  <div class="lap-card lap-preview-card">
    <form action="{{ route('adminpoli.laporan.preview') }}" method="GET" class="lap-tools">
      <div class="lap-tools">
        <div class="lap-info">
          <b></b>
        </div>

        <a class="lap-btn"
           href="{{ route('adminpoli.laporan.export', request()->query()) }}">
          Download Excel
        </a>
      </div>
    </form>

    {{-- Table --}}
    <div class="lap-table-wrap lap-preview-wrap">
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
          <div class="lap-th">S</div>
          <div class="lap-th">D</div>
          <div class="lap-th">N</div>
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
          <div class="lap-th">SUBTOTAL HARGA OBAT</div>
          <div class="lap-th">TOTAL HARGA OBAT</div>
          <div class="lap-th">SARAN</div>
          <div class="lap-th">PEMERIKSA</div>
          <div class="lap-th">KODE DIAGNOSA K3</div>
          <div class="lap-th">PERIKSA KE</div>
          <div class="lap-th">KODE DIAGNOSA</div>
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
            <div class="lap-td lap-center">{{ $r['S'] }}</div>
            <div class="lap-td lap-center">{{ $r['D'] }}</div>
            <div class="lap-td lap-center">{{ $r['N'] }}</div>
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
              @php $st = $r['SUBTOTAL_HARGA_OBAT'] ?? '-'; @endphp
              {{ is_numeric($st) ? number_format($st, 0, ',', '.') : $st }}
            </div>
            <div class="lap-td lap-center">
              @php
                $tt = $r['TOTAL_HARGA_OBAT'];
              @endphp
              {{ is_numeric($tt) ? number_format($tt, 0, ',', '.') : $tt }}
            </div>
            <div class="lap-td lap-center">{{ $r['SARAN'] }}</div>
            <div class="lap-td lap-center">{{ $r['PEMERIKSA'] }}</div>
            <div class="lap-td lap-center">{{ $r['KODE_DIAGNOSA_K3'] ?? '-' }}</div>
            <div class="lap-td lap-center">{{ $r['PERIKSA_KE'] ?? '-' }}</div>
            <div class="lap-td lap-center">{{ $r['KODE_DIAGNOSA'] ?? '-' }}</div>
          </div>
        @empty
          <div class="lap-empty">Tidak ada data pada rentang ini.</div>
        @endforelse
      </div>
    </div>

  </div>
</div>
@endsection
