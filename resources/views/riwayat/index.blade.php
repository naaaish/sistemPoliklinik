<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pemeriksaan - SISTEM POLIKLINIK</title>
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

        /* Navbar */
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

        .riwayat-list {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .riwayat-item {
            border-bottom: 1px solid #eee;
            padding: 1.5rem 0;
        }

        .riwayat-item:last-child {
            border-bottom: none;
        }

        .riwayat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .riwayat-date {
            color: #4a6fa5;
            font-weight: 600;
        }

        .riwayat-status {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 0.3rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
        }

        .riwayat-content h3 {
            color: #333;
            margin-bottom: 0.5rem;
        }

        .riwayat-content p {
            color: #666;
            line-height: 1.6;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #999;
        }

        .empty-state svg {
            width: 150px;
            height: 150px;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .footer {
            background: #333;
            color: white;
            text-align: center;
            padding: 1.5rem;
            margin-top: 3rem;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
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
            @endauth
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h2>Riwayat Pemeriksaan</h2>
            <p>Lihat riwayat pemeriksaan kesehatan Anda</p>
        </div>

        <div class="riwayat-list">
            @forelse($riwayat as $item)
                <div class="riwayat-item">
                    <div class="riwayat-header">
                        <span class="riwayat-date">{{ $item->tanggal }}</span>
                        <span class="riwayat-status">Selesai</span>
                    </div>
                    <div class="riwayat-content">
                        <h3>{{ $item->jenis_pemeriksaan }}</h3>
                        <p><strong>Dokter:</strong> {{ $item->dokter_nama }}</p>
                        <p><strong>Diagnosa:</strong> {{ $item->diagnosa }}</p>
                        <p><strong>Saran:</strong> {{ $item->saran }}</p>
                    </div>
                </div>
            @empty
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9 11H15M9 15H12M13 3H8.2C7.0799 3 6.51984 3 6.09202 3.21799C5.71569 3.40973 5.40973 3.71569 5.21799 4.09202C5 4.51984 5 5.0799 5 6.2V17.8C5 18.9201 5 19.4802 5.21799 19.908C5.40973 20.2843 5.71569 20.5903 6.09202 20.782C6.51984 21 7.0799 21 8.2 21H15.8C16.9201 21 17.4802 21 17.908 20.782C18.2843 20.5903 18.5903 20.2843 18.782 19.908C19 19.4802 19 18.9201 19 17.8V9M13 3L19 9M13 3V7.4C13 7.96005 13 8.24008 13.109 8.45399C13.2049 8.64215 13.3578 8.79513 13.546 8.89101C13.7599 9 14.0399 9 14.6 9H19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <h3>Belum Ada Riwayat Pemeriksaan</h3>
                    <p>Anda belum memiliki riwayat pemeriksaan kesehatan</p>
                </div>
            @endforelse
        </div>
    </div>

    <footer class="footer">
        <p>Copyright © 2026 Poliklinik PT PLN Indonesia Power UBP Mrica</p>
    </footer>
</body>
</html>
