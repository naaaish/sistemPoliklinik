@extends('layouts.pasien')

@section('title', $artikel->judul_artikel ?? 'Detail Artikel')

@section('content')

<div class="artikel-detail">

    {{-- HERO --}}
    <div class="artikel-hero">
        <a href="{{ route('pasien.artikel') }}" class="artikel-kembali">
            ← Kembali
        </a>

        <h1>{{ $artikel->judul_artikel }}</h1>

        <div class="artikel-meta">
            <img src="/icons/calendar.svg" alt="">
            {{ \Carbon\Carbon::parse($artikel->tanggal)->translatedFormat('d F Y') }}
        </div>
    </div>

    {{-- CONTENT --}}
    <div class="artikel-container">

        {{-- IMAGE --}}
        <div class="artikel-cover">
            <img src="{{ asset($artikel->cover_path) }}" alt="Cover Artikel">
        </div>

        {{-- ISI --}}
        <div class="artikel-body">
            {!! nl2br(e($artikel->isi_artikel)) !!}
        </div>

    </div>

    {{-- FOOTER --}}
    <div class="artikel-footer">
        Copyright © 2026 Poliklinik PT PLN Indonesia Power UBP Mrica
    </div>

</div>

@endsection
