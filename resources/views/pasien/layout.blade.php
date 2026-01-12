<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Sistem Poliklinik')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @stack('styles')

    <style>
        body{
            margin:0;
            font-family:'Segoe UI', Tahoma, sans-serif;
            background:#f5f5f5;
        }

        /* NAVBAR PASIEN */
        .navbar-pasien{
            background: linear-gradient(90deg,#4a6fa5,#5b7db1);
            padding:18px 50px;
            display:flex;
            justify-content:space-between;
            align-items:center;
            color:white;
            box-shadow:0 2px 10px rgba(0,0,0,.15);
        }

        .brand{
            display:flex;
            align-items:center;
            gap:14px;
        }

        .brand img{
            width:34px;
            height:auto;
        }

        .brand span{
            font-size:22px;
            font-weight:700;
            letter-spacing:.5px;
        }

        .nav-menu{
            display:flex;
            align-items:center;
            gap:32px;
        }

        .nav-menu a{
            color:white;
            text-decoration:none;
            font-weight:500;
            font-size:16px;
            opacity:.9;
        }

        .nav-menu a:hover{
            opacity:1;
        }

        .logout-btn{
            background:white;
            color:#4a6fa5;
            border:none;
            padding:8px 18px;
            border-radius:8px;
            cursor:pointer;
            font-weight:600;
        }
    </style>
</head>
<body>

<nav class="navbar-pasien">
    <a href="{{ route('home') }}" class="brand" style="text-decoration:none; color:white;">
        <img src="{{ asset('images/logo-poliklinik.png') }}">
        <span>SISTEM POLIKLINIK</span>
    </a>


    <div class="nav-menu">
        <a href="{{ route('tentang') }}">Tentang Kami</a>
        <a href="{{ route('pasien.riwayat') }}">Riwayat Pemeriksaan</a>
        <a href="{{ route('artikel.index') }}">Artikel Kesehatan</a>

        @auth
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button class="logout-btn">Logout</button>
        </form>
        @endauth
    </div>
</nav>

@yield('content')

</body>
</html>
