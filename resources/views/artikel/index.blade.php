@extends('layouts.pasien')

@section('title','Artikel Kesehatan')

@push('styles')
<style>

/* ===== HERO ===== */
.pasien-hero{
    background:
        url("/images/bg2.png") top center no-repeat,
        linear-gradient(135deg, rgba(49,107,161,.95), rgba(63,127,191,.95));
    background-size:100% auto;
    padding:90px 80px 150px;
    color:white;
}

/* .hero-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(
        to right,
        rgba(255,255,255,0.9),
        rgba(255,255,255,0.65),
        rgba(255,255,255,0.2)
    );
} */

.hero-content {
    position: relative;
    z-index: 2;
}

.hero-content h2 {
    font-size: 40px;
    font-weight: 700;
    color: #f4f6f8;
}

.hero-content p {
    margin-top: 5px;
    font-size: 18px;
    color: #f9fafb;
    max-width: 520px;
}

/* ===== CONTAINER ===== */
.container {
    max-width: 1200px;
    margin: -90px auto 60px;
    padding: 0 40px;
}

/* ===== ARTICLE GRID ===== */
.article-cards {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 28px;
}

/* ===== CARD ===== */
.article-card {
    background: white;
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    transition: 0.3s ease;
}

.article-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 32px rgba(0,0,0,0.12);
}

.article-image {
    height: 190px;
    background: #eaf2f8;
}

.article-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.article-content {
    padding: 22px;
}

.article-content h3 {
    font-size: 18px;
    color: #1f2d3d;
    margin-bottom: 10px;
    line-height: 1.4;
}

.article-content p {
    font-size: 14px;
    color: #5b6b82;
    line-height: 1.7;
    margin-bottom: 12px;
}

.article-date {
    font-size: 13px;
    color: #8fa0b5;
}

/* ===== EMPTY STATE ===== */
.empty-state {
    grid-column: 1 / -1;
    text-align: center;
    padding: 100px 20px;
    color: #8fa0b5;
}

.empty-state h3 {
    font-size: 20px;
    margin-bottom: 6px;
}

</style>
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

<!-- CONTENT -->
<div class="container">
    <div class="article-cards">

        @forelse($articles as $article)
            <div class="article-card">
                <div class="article-image">
                    <img src="{{ asset('images/articles/' . ($article->gambar ?? 'default.jpg')) }}"
                         onerror="this.src='https://via.placeholder.com/400x300/4fa3d1/ffffff?text=Artikel'">
                </div>
                <div class="article-content">
                    <h3>{{ $article->judul }}</h3>
                    <p>{{ Str::limit($article->konten, 100) }}</p>
                    <div class="article-date">
                        {{ $article->created_at->format('d F Y') }}
                    </div>
                </div>
            </div>
        @empty
            <div class="empty-state">
                <h3>Belum ada artikel</h3>
                <p>Artikel kesehatan akan muncul di sini</p>
            </div>
        @endforelse

    </div>
</div>

@endsection
