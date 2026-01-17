<?php

namespace App\Http\Controllers;

use App\Models\JadwalDokter;
use App\Models\Artikel;

class HomeController extends Controller
{
    public function index()
    {
        $jadwalDokter = JadwalDokter::with('dokter')
            ->orderByRaw("
                FIELD(hari,'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu')
            ")
            ->orderBy('jam_mulai')
            ->get();

        $articles = Artikel::latest()->take(4)->get();

        return view('home', compact('jadwalDokter', 'articles'));
    }

    public function tentang()
    {
        return view('tentang');
    }

    public function artikelIndex()
    {
        $articles = Artikel::latest()->paginate(9);
        return view('artikel.index', compact('articles'));
    }
}
