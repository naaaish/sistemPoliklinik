@extends('layouts.adminpoli')

@section('title', 'Preview Export Saran')

@section('content')
<div class="obat-page">
    <div class="obat-topbar">
        <div class="obat-left">
            <a href="{{ route('adminpoli.saran.index', ['from'=>$from,'to'=>$to]) }}" class="obat-back-img" title="Kembali">
                <img src="{{ asset('assets/adminPoli/back-arrow.png') }}" alt="Kembali">
            </a>
            <div class="obat-heading">Preview Export Saran</div>
        </div>

        <a
            class="obat-btn-add"
            href="{{ route('adminpoli.saran.export', ['from'=>$from,'to'=>$to,'format'=>$format,'action'=>'download']) }}"
        >
            <span>Download</span>
        </a>
    </div>

    <div class="obat-card">
        <div class="obat-preview">
            Rentang: <b>{{ $from }}</b> s/d <b>{{ $to }}</b> • Total: <b>{{ $count }}</b> data • Format: <b>{{ strtoupper($format) }}</b>
        </div>

        <div style="margin-top:12px;">
            <div class="obat-table-head" style="grid-template-columns: 1fr 2fr 1fr 1fr;">
                <div>ID Saran</div>
                <div>Saran</div>
                <div>ID Diagnosa</div>
                <div>Diagnosa</div>
            </div>

            <div class="obat-table-body">
                @forelse($data as $row)
                    <div class="obat-row" style="grid-template-columns: 1fr 2fr 1fr 1fr;">
                        <div class="obat-cell obat-center">{{ $row->id_saran }}</div>
                        <div class="obat-cell">{{ $row->saran }}</div>
                        <div class="obat-cell obat-center">{{ $row->id_diagnosa }}</div>
                        <div class="obat-cell">{{ $row->diagnosa_text }}</div>
                    </div>
                @empty
                    <div class="obat-row obat-row-empty">
                        <div class="obat-empty-span">Tidak ada data pada rentang ini.</div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="obat-foot">
        Copyright © 2026 Poliklinik PT PLN Indonesia Power UBP Mrica
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/adminpoli/obat.css') }}">
@endpush