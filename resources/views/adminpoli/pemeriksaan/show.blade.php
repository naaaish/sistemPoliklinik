@extends('layouts.adminpoli')
@section('title', 'Detail Pemeriksaan Pasien')

@section('content')
<div class="periksa-detail-page">

  <div class="periksa-detail-topbar">
    <div class="periksa-detail-left">
      <a href="{{ route('adminpoli.pemeriksaan.index') }}" class="periksa-detail-back" title="Kembali">
        <img src="{{ asset('assets/adminPoli/back-arrow.png') }}" alt="Kembali">
      </a>
      <div class="periksa-detail-heading">Pemeriksaan Pasien</div>
    </div>
  </div>

  <div class="periksa-detail-card">

    <div class="periksa-detail-title">Data Hasil Pemeriksaan Pasien</div>

    {{-- DATA PASIEN (ambil dari $pendaftaran + relasi/hasil yang ada) --}}
    <div class="periksa-detail-grid">
      <div class="periksa-detail-item">
        <div class="periksa-detail-label">No. Registrasi</div>
        <div class="periksa-detail-value">{{ $pendaftaran->id_pendaftaran ?? '-' }}</div>
      </div>

      <div class="periksa-detail-item">
        <div class="periksa-detail-label">Tanggal Periksa</div>
        <div class="periksa-detail-value">
          {{ $hasil?->created_at ? \Carbon\Carbon::parse($hasil->created_at)->format('d F Y, H:i') : '-' }}
        </div>
      </div>

      <div class="periksa-detail-item">
        <div class="periksa-detail-label">Nama Pasien</div>
        <div class="periksa-detail-value">
          {{ $pendaftaran->pasien->nama_pasien ?? '-' }}
        </div>
      </div>

      <div class="periksa-detail-item">
        <div class="periksa-detail-label">Jenis Kelamin</div>
        <div class="periksa-detail-value">
          {{ $pendaftaran->pasien->jenis_kelamin ?? '-' }}
        </div>
      </div>

      <div class="periksa-detail-item">
        <div class="periksa-detail-label">Bidang / Unit</div>
        <div class="periksa-detail-value">
          {{ $pendaftaran->pasien->bidang ?? '-' }}
        </div>
      </div>

      <div class="periksa-detail-item">
        <div class="periksa-detail-label">Tanggal Lahir</div>
        <div class="periksa-detail-value">
          {{ $pendaftaran->pasien->tanggal_lahir ?? '-' }}
        </div>
      </div>

      <div class="periksa-detail-item">
        <div class="periksa-detail-label">Dokter / Pemeriksa</div>
        <div class="periksa-detail-value">
          {{ $pendaftaran->dokter->nama ?? ($pendaftaran->pemeriksa->nama_pemeriksa ?? '-') }}
        </div>
      </div>

      <div class="periksa-detail-item">
        <div class="periksa-detail-label">Status</div>
        <div class="periksa-detail-value">
          {{ $pendaftaran->status ?? '-' }}
        </div>
      </div>
    </div>

    {{-- PEMERIKSAAN KESEHATAN --}}
    <div class="periksa-detail-subtitle">Pemeriksaan Kesehatan</div>

    <div class="periksa-chip-wrap">
      <div class="periksa-chip"><div class="periksa-chip-label">Sistol</div><div class="periksa-chip-val">{{ $hasil->sistol ?? '-' }}</div></div>
      <div class="periksa-chip"><div class="periksa-chip-label">Diastol</div><div class="periksa-chip-val">{{ $hasil->diastol ?? '-' }}</div></div>
      <div class="periksa-chip"><div class="periksa-chip-label">Nadi</div><div class="periksa-chip-val">{{ $hasil->nadi ?? '-' }}</div></div>

      <div class="periksa-chip"><div class="periksa-chip-label">GD Puasa</div><div class="periksa-chip-val">{{ $hasil->gd_puasa ?? ($hasil->gula_puasa ?? '-') }}</div></div>
      <div class="periksa-chip"><div class="periksa-chip-label">GD 2 Jam PP</div><div class="periksa-chip-val">{{ $hasil->gd_duajam ?? ($hasil->gula_2jam_pp ?? '-') }}</div></div>
      <div class="periksa-chip"><div class="periksa-chip-label">GD Sewaktu</div><div class="periksa-chip-val">{{ $hasil->gd_sewaktu ?? ($hasil->gula_sewaktu ?? '-') }}</div></div>

      <div class="periksa-chip"><div class="periksa-chip-label">Asam Urat</div><div class="periksa-chip-val">{{ $hasil->asam_urat ?? '-' }}</div></div>
      <div class="periksa-chip"><div class="periksa-chip-label">Cholesterol</div><div class="periksa-chip-val">{{ $hasil->chol ?? ($hasil->cholesterol ?? '-') }}</div></div>
      <div class="periksa-chip"><div class="periksa-chip-label">Trigliseride</div><div class="periksa-chip-val">{{ $hasil->tg ?? ($hasil->trigliseride ?? '-') }}</div></div>

      <div class="periksa-chip"><div class="periksa-chip-label">Suhu</div><div class="periksa-chip-val">{{ $hasil->suhu ?? '-' }}</div></div>
      <div class="periksa-chip"><div class="periksa-chip-label">Berat</div><div class="periksa-chip-val">{{ $hasil->berat ?? ($hasil->berat_badan ?? '-') }}</div></div>
      <div class="periksa-chip"><div class="periksa-chip-label">Tinggi</div><div class="periksa-chip-val">{{ $hasil->tinggi ?? ($hasil->tinggi_badan ?? '-') }}</div></div>
    </div>

    {{-- Diagnosa & Saran (sementara placeholder kalau belum ada kolomnya) --}}
    <div class="periksa-box-title">Diagnosa Dokter</div>
    <div class="periksa-box">
      {{ $hasil->diagnosa_dokter ?? '-' }}
    </div>

    <div class="periksa-box-title">Saran Dokter</div>
    <div class="periksa-box">
      {{ $hasil->saran_dokter ?? '-' }}
    </div>

    {{-- RESEP --}}
    <div class="periksa-detail-subtitle">Data Resep Obat</div>

    <div class="periksa-resep-table">
      <div class="periksa-resep-head">
        <div>Nama</div>
        <div>Jumlah</div>
        <div>Satuan</div>
        <div>Harga Satuan</div>
        <div>Subtotal</div>
      </div>

      @php
        $total = 0;
      @endphp

      <div class="periksa-resep-body">
        @forelse($detailResep as $r)
          @php $total += (int)($r->subtotal ?? 0); @endphp
          <div class="periksa-resep-row">
            <div class="periksa-resep-cell">{{ $r->obat->nama_obat ?? $r->id_obat ?? '-' }}</div>
            <div class="periksa-resep-cell periksa-center">{{ $r->jumlah ?? '-' }}</div>
            <div class="periksa-resep-cell periksa-center">{{ $r->satuan ?? '-' }}</div>
            <div class="periksa-resep-cell periksa-right">Rp{{ number_format((int)($r->harga_satuan ?? 0),0,',','.') }}</div>
            <div class="periksa-resep-cell periksa-right">Rp{{ number_format((int)($r->subtotal ?? 0),0,',','.') }}</div>
          </div>
        @empty
          <div class="periksa-resep-empty">Belum ada resep obat</div>
        @endforelse
      </div>

      <div class="periksa-total">
        <div class="periksa-total-label">Total</div>
        <div class="periksa-total-val">Rp{{ number_format($total,0,',','.') }}</div>
      </div>
    </div>

    <div class="periksa-detail-actions">
      <a href="{{ route('adminpoli.pemeriksaan.edit', $pendaftaran->id_pendaftaran) }}" class="periksa-detail-btn">
        Edit
      </a>
    </div>

  </div>

  <div class="periksa-detail-foot">
    Copyright © 2026 Poliklinik PT PLN Indonesia Power UBP Mrica
  </div>

</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/adminpoli/pemeriksaan-detail.css') }}">
@endpush
