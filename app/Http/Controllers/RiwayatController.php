<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\Pemeriksaan;

class RiwayatController extends Controller
{
    public function index()
    {
        // Kalau belum login, redirect ke login
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        $riwayat = Pemeriksaan::where('user_id', $user->id)
            ->orderBy('tanggal', 'desc')
            ->get();

        return view('pasien.riwayat', compact('riwayat'));
    }
}
