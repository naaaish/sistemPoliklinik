@extends('pasien.layout')

@section('title','SISTEM POLIKLINIK - Dashboard')

@push('styles')

@endpush

@section('content')
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SISTEM POLIKLINIK - Dashboard</title>
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

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, #5b7db1 0%, #7699c9 100%);
            color: white;
            padding: 4rem 2rem;
            position: relative;
            overflow: hidden;
        }

        .hero-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            z-index: 2;
        }

        .hero-text {
            flex: 1;
        }

        .hero-text h2 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .hero-text p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .konsultasi-btn {
            background: white;
            color: #4a6fa5;
            border: none;
            padding: 1rem 2rem;
            border-radius: 30px;
            cursor: pointer;
            font-weight: 600;
            font-size: 1rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: transform 0.2s;
        }

        .konsultasi-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .hero-image {
            flex: 1;
            text-align: right;
        }

        .hero-image img {
            max-width: 400px;
            border-radius: 50%;
            border: 5px solid rgba(255,255,255,0.3);
        }

        /* Decorative dots */
        .hero::before {
            content: '';
            position: absolute;
            right: 100px;
            bottom: 50px;
            width: 200px;
            height: 200px;
            background: url("data:image/svg+xml,%3Csvg width='20' height='20' xmlns='http://www.w3.org/2000/svg'%3E%3Ccircle cx='2' cy='2' r='2' fill='rgba(255,255,255,0.2)'/%3E%3C/svg%3E") repeat;
            z-index: 1;
        }

        /* Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 3rem 2rem;
        }

        /* Section Title */
        .section-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .section-title h2 {
            font-size: 2rem;
            color: #333;
        }

        .section-title a {
            color: #4a6fa5;
            text-decoration: none;
            font-weight: 500;
        }

        /* Jadwal Dokter */
        .doctor-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .doctor-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            gap: 1rem;
            transition: transform 0.3s;
        }

        .doctor-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }

        .doctor-photo {
            width: 80px;
            height: 80px;
            border-radius: 10px;
            background: #e8f0f8;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            overflow: hidden;
        }

        .doctor-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .doctor-info h3 {
            color: #333;
            font-size: 1rem;
            margin-bottom: 0.3rem;
        }

        .doctor-info .specialty {
            color: #888;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .schedule-item {
            background: #f8f9fa;
            padding: 0.3rem 0.5rem;
            border-radius: 5px;
            font-size: 0.85rem;
            color: #555;
            margin-bottom: 0.3rem;
        }

        /* Artikel Kesehatan */
        .article-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
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
            height: 180px;
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
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
            line-height: 1.4;
        }

        .article-date {
            color: #888;
            font-size: 0.9rem;
        }

        /* Tentang Kami */
        .about-section {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .about-content {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .about-icon {
            font-size: 3rem;
            color: #4a6fa5;
        }

        .about-text p {
            color: #555;
            line-height: 1.8;
            margin-bottom: 1rem;
        }

        .about-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #555;
        }

        /* Footer */
        .footer {
            background: #333;
            color: white;
            text-align: center;
            padding: 1.5rem;
            margin-top: 3rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-content {
                flex-direction: column;
                text-align: center;
            }

            .hero-text h2 {
                font-size: 1.8rem;
            }

            .hero-image {
                text-align: center;
                margin-top: 2rem;
            }

            .nav-links {
                gap: 1rem;
                font-size: 0.9rem;
            }

            .about-content {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <div class="hero-text">
                <h2>Selamat Datang, User!</h2>
                <p>Pantau riwayat kesehatanmu secara berkala dengan lebih mudah</p>
                <button class="konsultasi-btn">
                    <span>📱</span> Konsultasi Online
                </button>
            </div>
            <div class="hero-image">
                <img src="{{ asset('images/doctor-hero.jpg') }}" alt="Doctor" 
                     onerror="this.src='https://via.placeholder.com/400x400/5b7db1/ffffff?text=Doctor'">
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container">
        <!-- Jadwal Dokter -->
        <div class="section-title">
            <h2>Jadwal Dokter</h2>
        </div>
        <div class="doctor-cards">
            @forelse($jadwalDokter as $jadwal)
                <div class="doctor-card">
                    <div class="doctor-photo">
                        <img src="{{ asset('images/doctors/' . ($jadwal->dokter->foto ?? 'default.jpg')) }}" 
                             alt="{{ $jadwal->dokter->nama ?? 'Dokter' }}"
                             onerror="this.src='https://via.placeholder.com/80x80/e8f0f8/4a6fa5?text=Dr'">
                    </div>
                    <div class="doctor-info">
                        <h3>{{ $jadwal->dokter->nama ?? 'Dr. Nama Dokter' }}</h3>
                        <p class="specialty">{{ $jadwal->dokter->spesialisasi ?? 'Spesialis Anak' }}</p>
                        <div class="schedule-item">
                            {{ $jadwal->hari }} {{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }}
                        </div>
                    </div>
                </div>
            @empty
                <div class="doctor-card">
                    <div class="doctor-photo">
                        <img src="https://via.placeholder.com/80x80/e8f0f8/4a6fa5?text=Dr" alt="Dokter">
                    </div>
                    <div class="doctor-info">
                        <h3>Dr. Nama Dokter</h3>
                        <p class="specialty">Spesialis Anak</p>
                        <div class="schedule-item">Senin 07.00 - 10.00</div>
                        <div class="schedule-item">Rabu 07.00 - 10.00</div>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Artikel Kesehatan -->
        <div class="section-title">
            <h2>Artikel Kesehatan</h2>
            <a href="{{ route('artikel.index') }}">Lihat Semua →</a>
        </div>
        <div class="article-cards">
            @forelse($articles as $article)
                <div class="article-card">
                    <div class="article-image">
                        <img src="{{ asset('images/articles/' . ($article->gambar ?? 'default.jpg')) }}" 
                             alt="{{ $article->judul }}"
                             onerror="this.src='https://via.placeholder.com/250x180/5b7db1/ffffff?text=Artikel'">
                    </div>
                    <div class="article-content">
                        <h3>{{ $article->judul }}</h3>
                        <p class="article-date">{{ $article->created_at->format('d F Y') }}</p>
                    </div>
                </div>
            @empty
                @for($i = 0; $i < 4; $i++)
                    <div class="article-card">
                        <div class="article-image">
                            <img src="https://via.placeholder.com/250x180/5b7db1/ffffff?text=Artikel+Kesehatan" alt="Artikel">
                        </div>
                        <div class="article-content">
                            <h3>Apa Bedanya Superflu dengan Flu Biasa?</h3>
                            <p class="article-date">15 Desember 2025</p>
                        </div>
                    </div>
                @endfor
            @endforelse
        </div>

        <!-- Tentang Kami -->
        <div class="section-title">
            <h2>Tentang Kami</h2>
        </div>
        <div class="about-section">
            <div class="about-content">
                <div class="about-icon">🏥</div>
                <div class="about-text">
                    <p>
                        Poliklinik PT PLN Indonesia Power UBP Mrica merupakan fasilitas kesehatan yang menyediakan 
                        layanan medis untuk pegawai, keluarga pegawai, dan pensiunan.
                    </p>
                    <p><strong>Jam Operasional:</strong></p>
                    <div class="about-details">
                        <div class="detail-item">
                            📅 <strong>Senin - Jumat</strong>
                        </div>
                        <div class="detail-item">
                            🕐 <strong>07:00 - 15:00</strong>
                        </div>
                        <div class="detail-item">
                            📞 <strong>0286-xxxx-xxxx</strong>
                        </div>
                        <div class="detail-item">
                            📧 <strong>xxxxx@xxxx.com</strong>
                        </div>
                    </div>
                    <p style="margin-top: 1rem;">
                        <strong>Alamat:</strong> Jl. Raya Banyumas - Banjarnegara No KM 8, Mrica, Bawang, Kec. Bawang, 
                        Kab. Banjarnegara, Jawa Tengah 53471, Indonesia
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <p>Copyright © 2026 Poliklinik PT PLN Indonesia Power UBP Mrica</p>
    </footer>
</body>
@endsection