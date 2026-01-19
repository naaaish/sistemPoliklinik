<?php

namespace App\Http\Controllers;

use App\Models\JadwalDokter;
use App\Models\Artikel;
use Illuminate\Http\Request;

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

    public function artikelIndex(Request $request)
    {
        $search = $request->search;

        $artikels = Artikel::when($search, function ($query, $search) {
            $query->where('judul_artikel', 'like', '%' . $search . '%');
        })
        ->orderBy('tanggal', 'desc')
        ->get();

        return view('artikel.index', compact('artikels'));
    }

    public function artikelDetail($id_artikel)
    {
        $artikel = Artikel::where('id_artikel', $id_artikel)->firstOrFail();

        return view('artikel.detail', compact('artikel'));
    }


}