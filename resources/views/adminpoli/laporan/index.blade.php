@extends('layouts.adminpoli')

@section('title', 'Laporan')

@section('content')
<div class="lap-page">

  <div class="lap-topbar">
    <div class="lap-left">
      <a href="{{ route('adminpoli.dashboard') }}" class="lap-back-img" title="Kembali">
        <img src="{{ asset('assets/adminPoli/back-arrow.png') }}" alt="Kembali">
      </a>
      <div class="lap-heading">Laporan Klinik</div>
    </div>
  </div>

  <div class="lap-card">

    <div class="lap-tools-row" style="justify-content:space-between; gap:12px; flex-wrap:wrap;">
      <form action="{{ route('adminpoli.laporan.index') }}" method="GET" class="lap-download" style="margin:0;">
        <input type="date" name="from" value="{{ $from }}" class="lap-date" required>
        <span class="lap-sep">s/d</span>
        <input type="date" name="to" value="{{ $to }}" class="lap-date" required>

        <button type="submit" class="lap-btn-soft">
          <span>Tampilkan</span>
        </button>

        <a
          href="{{ route('adminpoli.laporan.export', ['from'=>$from,'to'=>$to]) }}"
          class="lap-btn-soft"
          style="text-decoration:none; display:inline-flex; align-items:center; gap:8px;"
        >
          <img src="{{ asset('assets/adminPoli/download.png') }}" alt="download" class="lap-ic">
          <span>Download Excel</span>
        </a>
      </form>
    </div>

    <div class="lap-preview" style="margin-top:10px;">
      <span>
        <b>{{ $count }}</b> baris laporan ({{ $from }} s/d {{ $to }})
      </span>
    </div>

    <div style="overflow:auto; margin-top:12px;">
      <div class="lap-table" style="min-width:1400px;">
        <div class="lap-table-head">
          <div>NO</div>
          <div>TANGGAL</div>
          <div>NAMA</div>
          <div>UMUR</div>
          <div>BAGIAN</div>
          <div>NAMA PASIEN</div>
          <div>HUB KEL</div>
          <div>TD</div>
          <div>GDP</div>
          <div>GD 2JAM PP</div>
          <div>GDS</div>
          <div>AU</div>
          <div>CHOL</div>
          <div>TG</div>
          <div>Suhu</div>
          <div>BB</div>
          <div>TB</div>
          <div>DIAGNOSA</div>
          <div>TERAPHY</div>
          <div>JUMLAH OBAT</div>
          <div>HARGA OBAT (SATUAN)</div>
          <div>TOTAL HARGA OBAT</div>
          <div>PEMERIKSA</div>
          <div>NB</div>
          <div>PERIKSA KE :</div>
        </div>

        <div class="lap-table-body">
          @forelse($rows as $r)
            <div class="lap-row">
              <div><div class="lap-cell lap-center">{{ $r['NO'] }}</div></div>
              <div><div class="lap-cell lap-center">{{ $r['TANGGAL'] }}</div></div>
              <div><div class="lap-cell">{{ $r['NAMA'] }}</div></div>
              <div><div class="lap-cell lap-center">{{ $r['UMUR'] }}</div></div>
              <div><div class="lap-cell">{{ $r['BAGIAN'] }}</div></div>
              <div><div class="lap-cell">{{ $r['NAMA_PASIEN'] }}</div></div>
              <div><div class="lap-cell lap-center">{{ $r['HUB_KEL'] }}</div></div>
              <div><div class="lap-cell lap-center">{{ $r['TD'] }}</div></div>
              <div><div class="lap-cell lap-center">{{ $r['GDP'] }}</div></div>
              <div><div class="lap-cell lap-center">{{ $r['GD_2JAM_PP'] }}</div></div>
              <div><div class="lap-cell lap-center">{{ $r['GDS'] }}</div></div>
              <div><div class="lap-cell lap-center">{{ $r['AU'] }}</div></div>
              <div><div class="lap-cell lap-center">{{ $r['CHOL'] }}</div></div>
              <div><div class="lap-cell lap-center">{{ $r['TG'] }}</div></div>
              <div><div class="lap-cell lap-center">{{ $r['SUHU'] }}</div></div>
              <div><div class="lap-cell lap-center">{{ $r['BB'] }}</div></div>
              <div><div class="lap-cell lap-center">{{ $r['TB'] }}</div></div>
              <div><div class="lap-cell">{{ $r['DIAGNOSA'] }}</div></div>
              <div><div class="lap-cell">{{ $r['TERAPHY'] }}</div></div>
              <div><div class="lap-cell lap-center">{{ $r['JUMLAH_OBAT'] }}</div></div>
              <div><div class="lap-cell lap-center">
                {{ is_numeric($r['HARGA_OBAT_SATUAN']) ? number_format($r['HARGA_OBAT_SATUAN'],0,',','.') : $r['HARGA_OBAT_SATUAN'] }}
              </div></div>
              <div><div class="lap-cell lap-center">
                {{ is_numeric($r['TOTAL_HARGA_OBAT']) ? number_format($r['TOTAL_HARGA_OBAT'],0,',','.') : $r['TOTAL_HARGA_OBAT'] }}
              </div></div>
              <div><div class="lap-cell">{{ $r['PEMERIKSA'] }}</div></div>
              <div><div class="lap-cell">{{ $r['NB'] }}</div></div>
              <div><div class="lap-cell lap-center">{{ $r['PERIKSA_KE'] }}</div></div>
            </div>
          @empty
            <div style="padding:14px; text-align:center; color:#7B8DA8; font-weight:700;">
              Tidak ada data pada rentang ini.
            </div>
          @endforelse
        </div>
      </div>
    </div>

  </div>
</div>
@endsection
