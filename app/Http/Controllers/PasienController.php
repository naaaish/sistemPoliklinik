<?php

namespace App\Http\Controllers;

use App\Models\Artikel;
use App\Models\Riwayat;
use Illuminate\Support\Facades\Auth;

class PasienController extends Controller
{
    public function dashboard()
    {
        $articles = Artikel::latest()->take(4)->get();

        $riwayat = Riwayat::join('pendaftaran', 'pemeriksaan.id_pendaftaran', '=', 'pendaftaran.id_pendaftaran')
            ->join('pasien', 'pendaftaran.id_pasien', '=', 'pasien.id_pasien')
            ->where('pasien.id_user', Auth::id())   // ⬅️ INI YANG BENAR
            ->orderBy('pemeriksaan.created_at', 'desc')
            ->take(3)
            ->get();

        return view('pasien.dashboard', compact('articles','riwayat'));
    }
}
