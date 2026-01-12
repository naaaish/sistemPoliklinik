<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Sistem Poliklinik - Dashboard</title>
  <link href="{{ asset('css/app.css') }}" rel="stylesheet"><!-- jika pakai mix/vite -->
  <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
      <a class="navbar-brand fw-bold" href="{{ route('dashboard') }}">SISTEM POLIKLINIK</a>
      <div class="collapse navbar-collapse">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="{{ route('tentang') }}">Tentang Kami</a></li>
          <li class="nav-item"><a class="nav-link" href="{{ route('riwayat.index') }}">Riwayat Pemeriksaan</a></li>
          <li class="nav-item"><a class="nav-link" href="{{ route('artikel.index') }}">Artikel Kesehatan</a></li>
          @auth
            <li class="nav-item">
              <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn btn-sm btn-light ms-2">Logout</button>
              </form>
            </li>
          @else
            <li class="nav-item"><a class="btn btn-sm btn-light ms-2" href="{{ route('login') }}">Login</a></li>
          @endauth
        </ul>
      </div>
    </div>
  </nav>

  <!-- Hero -->
  <header class="hero py-5">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-lg-7 text-white">
          <h5 class="text-muted">Selamat Datang, {{ auth()->user()->name ?? 'User' }}!</h5>
          <h1 class="display-5 fw-bold">Pantau riwayat kesehatanmu secara berkala dengan lebih mudah</h1>
          <a href="#" class="btn btn-lg btn-primary mt-3">Konsultasi Online <i class="ms-2 bi bi-whatsapp"></i></a>
        </div>
        <div class="col-lg-5 text-end">
          <div class="hero-image rounded-circle">
            <img src="{{ asset('images/hero-circle.jpg') }}" alt="hero" class="img-fluid rounded-circle" style="width:240px;height:240px;object-fit:cover;border:6px solid #cfeefc">
          </div>
        </div>
      </div>
    </div>
  </header>

  <main class="container my-5">
    <section class="mb-5">
      <h2 class="fw-bold">Jadwal Dokter</h2>
      <div class="row mt-3">
        @foreach($doctors as $doc)
          <div class="col-md-6">
            <div class="card shadow-sm mb-3">
              <div class="row g-0">
                <div class="col-4 d-flex align-items-center justify-content-center bg-light">
                  <img src="{{ $doc['photo'] }}" class="img-fluid p-2" alt="dokter">
                </div>
                <div class="col-8">
                  <div class="card-body">
                    <h5 class="card-title mb-1">{{ $doc['name'] }}</h5>
                    <p class="text-muted small">{{ $doc['specialty'] }}</p>
                    <ul class="list-unstyled mb-0 small">
                      @foreach($doc['times'] as $t)
                        <li>{{ $t }}</li>
                      @endforeach
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </section>

    <section class="mb-5">
      <div class="d-flex justify-content-between align-items-center">
        <h2 class="fw-bold">Artikel Kesehatan</h2>
        <a href="{{ route('artikel.index') }}" class="text-decoration-none">Lihat Semua →</a>
      </div>
      <div class="row mt-3">
        @foreach($articles as $article)
        <div class="col-md-4">
          <div class="card article-card mb-3">
            <img src="{{ $article['image'] }}" class="card-img-top" alt="artikel">
            <div class="card-body">
              <h6 class="card-title fw-bold">{{ $article['title'] }}</h6>
              <p class="text-muted small">{{ $article['date'] }}</p>
            </div>
          </div>
        </div>
        @endforeach
      </div>
    </section>

    <section class="mb-5 bg-light p-4 rounded">
      <h3 class="fw-bold">Tentang Kami</h3>
      <p>Poliklinik PT PLN Indonesia Power UBP Mrica merupakan fasilitas kesehatan yang menyediakan layanan medis untuk pegawai, keluarga pegawai, dan pensiunan.</p>
      <div class="row">
        <div class="col-md-6">
          <p><strong>Jam Operasional</strong><br>Senin - Jumat 07.00 - 16.00</p>
        </div>
        <div class="col-md-6">
          <p><strong>Alamat</strong><br>Jl. Raya Banyumas – Banjarnegara No.KM 8, Mrica, Bawang, Kec. Bawang, Kab. Banjarnegara</p>
        </div>
      </div>
    </section>
  </main>

  <footer class="bg-dark text-white py-3">
    <div class="container text-center">
      <small>Copyright © 2026 Poliklinik PT PLN Indonesia Power UBP Mrica</small>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>