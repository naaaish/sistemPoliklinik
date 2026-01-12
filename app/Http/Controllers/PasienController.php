<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Artikel;
use App\Models\Riwayat;

class PasienController extends Controller
{
    /**
     * Dashboard pasien
     */
    public function dashboard()
    {
        // Ambil 4 artikel terbaru
        $articles = Artikel::latest()->take(4)->get();

        // Ambil 3 riwayat terakhir pasien
        $riwayat = Riwayat::where('user_id', auth()->id())
                    ->latest()
                    ->take(3)
                    ->get();

        return view('pasien.dashboard', compact('articles','riwayat'));
    }
}
