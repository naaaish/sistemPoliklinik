@extends('layouts.adminpoli')

@section('title', 'Preview Export Diagnosa K3')

@section('content')
<div class="k3-page">
  <div class="k3-topbar">
    <div class="k3-left">
      <a href="{{ route('adminpoli.diagnosak3.index') }}" class="k3-back-img" title="Kembali">
        <img src="{{ asset('assets/adminPoli/back-arrow.png') }}" alt="Kembali">
      </a>
      <div class="k3-heading">Preview Export Diagnosa K3</div>
    </div>

    <form action="{{ route('adminpoli.diagnosak3.export') }}" method="GET" style="display:flex; gap:10px; align-items:center;">
      <input type="hidden" name="format" value="{{ $format }}">
      <input type="hidden" name="action" value="download">

      <button type="submit" class="k3-btn-add">
        <span>Download ({{ strtoupper($format) }})</span>
      </button>
    </form>
  </div>

  <div class="k3-card">
    <div class="k3-preview">
      <b>{{ $countKategori }}</b> kategori • <b>{{ $countPenyakit }}</b> penyakit — format: <b>{{ strtoupper($format) }}</b>
    </div>

    <div class="k3-table" style="margin-top:12px;">
      <div class="k3-preview-table">
        <div class="k3-preview-head">
            <div class="k3-col-id">ID NB</div>
            <div class="k3-col-kat">Kategori</div>
            <div class="k3-col-nama">Nama Penyakit</div>
        </div>

        <div class="k3-preview-body">
            @forelse($rows as $r)
            @if($r->tipe === 'kategori')
                <div class="k3-preview-row k3-preview-cat">
                <div class="k3-col-id"><b>{{ $r->id_nb }}</b></div>
                <div class="k3-col-kat"><b>{{ $r->kategori_penyakit }}</b></div>
                <div class="k3-col-nama k3-muted">—</div>
                </div>
            @else
                <div class="k3-preview-row">
                <div class="k3-col-id">{{ $r->id_nb }}</div>
                <div class="k3-col-kat">{{ $r->kategori_penyakit }}</div>
                <div class="k3-col-nama">{{ $r->nama_penyakit }}</div>
                </div>
            @endif
            @empty
            <div class="k3-preview-empty">Tidak ada data.</div>
            @endforelse
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
