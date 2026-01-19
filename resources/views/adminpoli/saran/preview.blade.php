@extends('layouts.adminpoli')

@section('title', 'Preview Export Saran')

@section('content')
<div class="saran-page">
    <div class="saran-topbar">
        <div class="saran-left">
            <a href="{{ route('adminpoli.saran.index', ['from'=>$from,'to'=>$to]) }}" class="saran-back-img" title="Kembali">
                <img src="{{ asset('assets/adminPoli/back-arrow.png') }}" alt="Kembali">
            </a>
            <div class="saran-heading">Preview Export Saran</div>
        </div>

        <a
            class="saran-btn-add"
            href="{{ route('adminpoli.saran.export', ['from'=>$from,'to'=>$to,'format'=>$format,'action'=>'download']) }}"
        >
            <span>Download</span>
        </a>
    </div>

    <div class="saran-card">
        <div class="saran-preview">
            Rentang: <b>{{ $from }}</b> s/d <b>{{ $to }}</b> • Total: <b>{{ $count }}</b> data • Format: <b>{{ strtoupper($format) }}</b>
        </div>

        <div style="margin-top:12px;">
            <div class="saran-table-head" style="grid-template-columns: 1fr 2fr 1fr 1fr;">
                <div>ID Saran</div>
                <div>Saran</div>
                <div>ID Diagnosa</div>
                <div>Diagnosa</div>
            </div>

            <div class="saran-table-body">
                @forelse($data as $row)
                    <div class="saran-row" style="grid-template-columns: 1fr 2fr 1fr 1fr;">
                        <div class="saran-cell saran-center">{{ $row->id_saran }}</div>
                        <div class="saran-cell">{{ $row->saran }}</div>
                        <div class="saran-cell saran-center">{{ $row->id_diagnosa }}</div>
                        <div class="saran-cell">{{ $row->diagnosa_text }}</div>
                    </div>
                @empty
                    <div class="saran-row saran-row-empty">
                        <div class="saran-empty-span">Tidak ada data pada rentang ini.</div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="saran-foot">
        Copyright © 2026 Poliklinik PT PLN Indonesia Power UBP Mrica
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('css/adminpoli/saran.css') }}">
@endpush