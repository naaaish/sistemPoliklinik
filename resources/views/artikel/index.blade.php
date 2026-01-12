<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artikel Kesehatan - SISTEM POLIKLINIK</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }

        .navbar {
            background: linear-gradient(135deg, #4a6fa5 0%, #5b7db1 100%);
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .navbar h1 {
            font-size: 1.5rem;
            font-weight: 600;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: opacity 0.3s;
        }

        .nav-links a:hover {
            opacity: 0.8;
        }

        .logout-btn {
            background: white;
            color: #4a6fa5;
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: transform 0.2s;
        }

        .logout-btn:hover {
            transform: translateY(-2px);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 3rem 2rem;
        }

        .page-header {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .page-header h2 {
            color: #333;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .page-header p {
            color: #666;
        }

        .article-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
        }

        .article-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }

        .article-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }

        .article-image {
            width: 100%;
            height: 200px;
            background: #e8f0f8;
            overflow: hidden;
        }

        .article-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .article-content {
            padding: 1.5rem;
        }

        .article-content h3 {
            color: #333;
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
            line-height: 1.4;
        }

        .article-content p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 1rem;
        }

        .article-date {
            color: #888;
            font-size: 0.9rem;
        }

        .footer {
            background: #333;
            color: white;
            text-align: center;
            padding: 1.5rem;
            margin-top: 3rem;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 2rem;
        }

        .pagination a,
        .pagination span {
            padding: 0.5rem 1rem;
            background: white;
            border-radius: 5px;
            text-decoration: none;
            color: #4a6fa5;
        }

        .pagination .active {
            background: #4a6fa5;
            color: white;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <h1>SISTEM POLIKLINIK</h1>
        <div class="nav-links">
            <a href="{{ route('home') }}">Beranda</a>
            <a href="{{ route('tentang') }}">Tentang Kami</a>
            <a href="{{ route('riwayat.index') }}">Riwayat Pemeriksaan</a>
            <a href="{{ route('artikel.index') }}">Artikel Kesehatan</a>
            @auth
                <button class="logout-btn" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    Logout
                </button>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            @else
                <!-- <button class="logout-btn" onclick="window.location.href='{{ route('login') }}'">
                    Login
                </button> -->
            @endauth
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h2>Artikel Kesehatan</h2>
            <p>Baca artikel kesehatan terbaru dan tips hidup sehat</p>
        </div>

        <div class="article-cards">
            @forelse($articles as $article)
                <div class="article-card">
                    <div class="article-image">
                        <img src="{{ asset('images/articles/' . ($article->gambar ?? 'default.jpg')) }}" 
                             alt="{{ $article->judul }}"
                             onerror="this.src='https://via.placeholder.com/300x200/5b7db1/ffffff?text=Artikel'">
                    </div>
                    <div class="article-content">
                        <h3>{{ $article->judul }}</h3>
                        <p>{{ Str::limit($article->konten ?? 'Artikel kesehatan menarik', 100) }}</p>
                        <p class="article-date">{{ $article->created_at->format('d F Y') }}</p>
                    </div>
                </div>
            @empty
                @for($i = 0; $i < 6; $i++)
                    <div class="article-card">
                        <div class="article-image">
                            <img src="https://via.placeholder.com/300x200/5b7db1/ffffff?text=Artikel+Kesehatan" alt="Artikel">
                        </div>
                        <div class="article-content">
                            <h3>Apa Bedanya Superflu dengan Flu Biasa?</h3>
                            <p>Pelajari perbedaan antara superflu dan flu biasa, serta cara pencegahan yang tepat.</p>
                            <p class="article-date">15 Desember 2025</p>
                        </div>
                    </div>
                @endfor
            @endforelse
        </div>

        @if(isset($articles) && method_exists($articles, 'links'))
            <div class="pagination">
                {{ $articles->links() }}
            </div>
        @endif
    </div>

    <footer class="footer">
        <p>Copyright © 2026 Poliklinik PT PLN Indonesia Power UBP Mrica</p>
    </footer>
</body>
</html>
