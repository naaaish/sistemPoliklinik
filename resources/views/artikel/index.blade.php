@extends('layouts.pasien')

@section('title','Artikel Kesehatan')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/artikel.css') }}">
@endpush

@section('content')

{{-- HERO --}}
<section class="pasien-hero">
    <div class="hero-content">
        <h2>Artikel Kesehatan</h2>
        <p>Informasi kesehatan terpercaya untuk Anda dan keluarga</p>
    </div>
</section>

<div class="container">

    {{-- SEARCH --}}
    <div class="search-section">
        <form action="{{ route('artikel.index.public') }}" method="GET" class="search-box">
            <input
                type="text"
                name="search"
                placeholder="Cari artikel kesehatan..."
                value="{{ request('search') }}"
            >
            <button class="search-btn">Cari</button>
        </form>
    </div>

    {{-- GRID ARTIKEL --}}
    <div class="article-grid">
        @forelse ($articles as $article)
            <a href="{{ route('artikel.detail', $article->id_artikel) }}"
               class="article-card-link">

                <div class="article-card">
                    <div class="article-image">
                        <img src="{{ asset($article->cover_path) }}"
                             alt="{{ $article->judul_artikel }}">
                    </div>

                    <div class="article-content">
                        <h3>{{ $article->judul_artikel }}</h3>
                        <span class="article-date">
                            {{ \Carbon\Carbon::parse($article->tanggal)->translatedFormat('d F Y') }}
                        </span>
                    </div>
                </div>

            </a>
        @empty
            <div class="empty-state">
                <h3>Tidak ada artikel</h3>
                <p>Artikel yang kamu cari belum tersedia.</p>
            </div>
        @endforelse
    </div>

    {{-- PAGINATION --}}
    @if($articles->total() > 0)
    <div class="pagination-wrapper">
        <div class="pagination-info">
            Halaman {{ $articles->currentPage() }} dari {{ $articles->lastPage() }}
            <span class="total-articles">({{ $articles->total() }} artikel)</span>
        </div>
        
        @if($articles->hasPages())
        <div class="pagination-links">
            {{ $articles->links('pagination::bootstrap-4') }}
        </div>
        @endif
    </div>
    @endif

</div>
@endsection