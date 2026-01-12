<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dokter;
use App\Models\JadwalDokter;
use App\Models\Artikel;

class HomeController extends Controller
{
    // Halaman utama (publik - bisa diakses semua orang)
    public function index()
    {
        // Ambil data jadwal dokter dengan dokter
        $jadwalDokter = JadwalDokter::with('dokter')
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->take(6)
            ->get();

        // Ambil artikel terbaru
        $articles = Artikel::orderBy('created_at', 'desc')
            ->take(4)
            ->get();

        return view('home', compact('jadwalDokter', 'articles'));
    }

    public function tentang()
    {
        return view('tentang');
    }

    public function artikelIndex()
    {
        // Ambil semua artikel (publik)
        $articles = Artikel::orderBy('created_at', 'desc')->paginate(9);
        return view('artikel.index', compact('articles'));
    }

    // Hanya untuk yang sudah login (middleware auth)
    public function riwayat()
    {
        // Ambil riwayat pemeriksaan berdasarkan user yang login
        $riwayat = []; // akan diimplementasikan sesuai kebutuhan
        return view('riwayat.index', compact('riwayat'));
    }
}