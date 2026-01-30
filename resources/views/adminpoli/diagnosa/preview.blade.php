@extends('layouts.adminpoli')
@section('title', 'Preview Export Diagnosa')

@section('content')
<div class="diag-page">
  <div class="diag-topbar">
    <div class="diag-left">
      <a href="{{ route('adminpoli.diagnosa.index') }}" class="diag-back-img" title="Kembali">
        <img src="{{ asset('assets/adminPoli/back-arrow.png') }}" alt="Kembali">
      </a>
      <div class="diag-heading">Preview Export Diagnosa</div>
    </div>

    <form action="{{ route('adminpoli.diagnosa.export') }}" method="GET" style="display:flex; gap:10px; align-items:center;">
      <input type="hidden" name="from" value="{{ $from }}">
      <input type="hidden" name="to" value="{{ $to }}">
      <input type="hidden" name="format" value="{{ $format }}">
      <input type="hidden" name="action" value="download">
      <button type="submit" class="diag-btn-add"><span>Download ({{ strtoupper($format) }})</span></button>
    </form>
  </div>

  <div class="diag-card">
    <div class="diag-preview">
      <b>{{ $count }}</b> data diagnosa ditemukan ({{ $from }} s/d {{ $to }}) — format: <b>{{ strtoupper($format) }}</b>
    </div>

    <div class="diag-table diag-table--preview" style="margin-top:12px;">
      <div class="diag-table-head diag-head">
        <div>No</div>
        <div>Diagnosa</div>
        <div>Keterangan</div>
        <div>Klasifikasi</div>
        <div>Bagian Tubuh</div>
      </div>

      <div class="diag-table-body">
        @forelse($data as $row)
          <div class="diag-row diag-row">
            <div><div class="diag-cell">{{ $row->id_diagnosa }}</div></div>
            <div><div class="diag-cell">{{ $row->diagnosa }}</div></div>
            <div><div class="diag-cell">{{ $row->keterangan }}</div></div>
            <div><div class="diag-cell">{{ $row->klasifikasi_nama }}</div></div>
            <div><div class="diag-cell">{{ $row->bagian_tubuh }}</div></div>
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
