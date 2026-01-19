<?php

namespace App\Http\Controllers;

use App\Models\JadwalDokter;
use App\Models\Artikel;

class HomeController extends Controller
{
    public function index(){
        $jadwalDokter = \App\Models\JadwalDokter::with('dokter')
            ->whereHas('dokter', fn ($q) => $q->where('status', 'Aktif'))
            ->orderByRaw("
                FIELD(hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu')
            ")
            ->get()
            ->groupBy('id_dokter');

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
