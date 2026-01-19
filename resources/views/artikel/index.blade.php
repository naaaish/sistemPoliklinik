@extends('layouts.pasien')

@section('title', 'Artikel Kesehatan')

@push('styles')
{{-- css khusus artikel kalau ada --}}
@endpush

@section('content')

<!-- HERO -->
<div class="pasien-hero">
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <h2>Artikel Kesehatan</h2>
        <p>Informasi dan tips kesehatan untuk menjaga kebugaran pegawai</p>
    </div>
</div>

<!-- SEARCH -->
<div class="search-section">
    <form action="{{ route('artikel.index.public') }}" method="GET" class="search-box">
        <input
            type="text"
            name="search"
            placeholder="Cari artikel kesehatan..."
            value="{{ request('search') }}"
        >
        <button type="submit" class="search-btn">Cari</button>
    </form>
</div>

<!-- CONTENT -->
@forelse ($artikels as $artikel)
    <a 
        href="{{ route('artikel.detail.public', $artikel->id_artikel) }}"
        class="article-card-link"
    >
        <div class="article-card">

            <div class="article-image">
                <img
                    src="{{ asset($artikel->cover_path) }}"
                    alt="{{ $artikel->judul_artikel }}"
                    onerror="this.src='https://via.placeholder.com/400x300?text=Artikel'"
                >
            </div>

            <div class="article-content">
                <h3>{{ $artikel->judul_artikel }}</h3>

                <div class="article-date">
                    {{ \Carbon\Carbon::parse($artikel->tanggal)->translatedFormat('d F Y') }}
                </div>
            </div>

        </div>
    </a>
@empty
@endforelse


@endsection
