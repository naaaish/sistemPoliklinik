@extends('pasien.layout')

@section('title','TENTANG KAMI - SISTEM POLIKLINIK')

@push('styles')

@endpush

@section('content')
    <title>Tentang Kami - SISTEM POLIKLINIK</title>
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

        .about-section {
            background: white;
            border-radius: 15px;
            padding: 2.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .about-content {
            display: flex;
            gap: 2rem;
            align-items: start;
            margin-bottom: 2rem;
        }

        .about-icon {
            font-size: 4rem;
            flex-shrink: 0;
        }

        .about-text h3 {
            color: #4a6fa5;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .about-text p {
            color: #555;
            line-height: 1.8;
            margin-bottom: 1rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .info-card {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            border-left: 4px solid #4a6fa5;
        }

        .info-card h4 {
            color: #333;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-card p {
            color: #666;
            line-height: 1.6;
        }

        .location-map {
            background: white;
            border-radius: 15px;
            padding: 2.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .location-map h3 {
            color: #333;
            margin-bottom: 1.5rem;
        }

        .map-placeholder {
            width: 100%;
            height: 400px;
            background: #e8f0f8;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #4a6fa5;
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
 
    <div class="container">
        <div class="page-header">
            <h2>Tentang Kami</h2>
            <p>Mengenal Poliklinik PT PLN Indonesia Power UBP Mrica</p>
        </div>

        <div class="about-section">
            <div class="about-content">
                <div class="about-icon">🏥</div>
                <div class="about-text">
                    <h3>Poliklinik PT PLN Indonesia Power UBP Mrica</h3>
                    <p>
                        Poliklinik PT PLN Indonesia Power UBP Mrica merupakan fasilitas kesehatan yang 
                        menyediakan layanan medis untuk pegawai, keluarga pegawai, dan pensiunan. Kami 
                        berkomitmen untuk memberikan pelayanan kesehatan yang berkualitas dengan tenaga 
                        medis yang profesional dan berpengalaman.
                    </p>
                    <p>
                        Dengan fasilitas yang lengkap dan modern, kami siap melayani berbagai kebutuhan 
                        kesehatan Anda mulai dari pemeriksaan umum, konsultasi kesehatan, hingga 
                        pemeriksaan kesehatan berkala (K3).
                    </p>
                </div>
            </div>

            <div class="info-grid">
                <div class="info-card">
                    <h4>📅 Jam Operasional</h4>
                    <p><strong>Senin - Jumat</strong><br>07:00 - 15:00 WIB</p>
                </div>

                <div class="info-card">
                    <h4>📞 Kontak</h4>
                    <p>
                        <strong>Telepon:</strong> 0286-xxxx-xxxx<br>
                        <strong>Email:</strong> xxxxx@xxxx.com
                    </p>
                </div>

                <div class="info-card">
                    <h4>📍 Alamat</h4>
                    <p>
                        Jl. Raya Banyumas - Banjarnegara No KM 8, Mrica, Bawang, 
                        Kec. Bawang, Kab. Banjarnegara, Jawa Tengah 53471, Indonesia
                    </p>
                </div>

                <div class="info-card">
                    <h4>⚕️ Layanan</h4>
                    <p>
                        • Pemeriksaan Umum<br>
                        • Konsultasi Kesehatan<br>
                        • Pemeriksaan K3<br>
                        • Layanan Obat & Resep
                    </p>
                </div>
            </div>
        </div>

        <div class="location-map">
            <h3>Lokasi Kami</h3>
            <div class="map-placeholder">
                <p>🗺️ Google Maps akan ditampilkan di sini</p>
            </div>
        </div>
    </div>

    <footer class="footer">
        <p>Copyright © 2026 Poliklinik PT PLN Indonesia Power UBP Mrica</p>
    </footer>
</body>
@endsection