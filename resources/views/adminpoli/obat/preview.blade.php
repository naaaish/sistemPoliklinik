@extends('layouts.adminpoli')

@section('title', 'Preview Export Obat')

@section('content')
<div class="obat-page">

    <div class="obat-topbar">
        <div class="obat-left">
            <a href="{{ route('adminpoli.dashboard') }}" class="obat-back-img" title="Kembali">
                <img src="{{ asset('assets/adminPoli/back-arrow.png') }}" alt="Kembali">
            </a>
        <div class="obat-heading">Preview Export Obat</div>
    </div>
    <form action="{{ route('adminpoli.obat.export') }}" method="GET" style="display:flex; gap:10px; align-items:center;">
      <input type="hidden" name="from" value="{{ $from }}">
      <input type="hidden" name="to" value="{{ $to }}">
      <input type="hidden" name="format" value="{{ $format }}">
      <input type="hidden" name="action" value="download">

      <button type="submit" class="obat-btn-add">
        <span>Download ({{ strtoupper($format) }})</span>
      </button>
    </form>
  </div>

  <div class="obat-card">
    <div class="obat-preview">
      <b>{{ $count }}</b> data obat ditemukan ({{ $from }} s/d {{ $to }}) — format: <b>{{ strtoupper($format) }}</b>
    </div>

    <div class="obat-table" style="margin-top:12px;">
      <div class="obat-table-head">
        <div>ID Obat</div>
        <div>Nama</div>
        <div>Harga</div>
        <div>Exp Date</div>
      </div>

      <div class="obat-table-body">
        @forelse($data as $row)
          <div class="obat-row">
            <div><div class="obat-cell obat-center">{{ $row->id_obat }}</div></div>
            <div><div class="obat-cell">{{ $row->nama_obat }}</div></div>
            <div><div class="obat-cell obat-center">{{ 'Rp'.number_format($row->harga,0,',','.') }}</div></div>
            <div><div class="obat-cell obat-center">{{ $row->exp_date }}</div></div>
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
@endsection
