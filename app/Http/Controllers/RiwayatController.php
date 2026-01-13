<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RiwayatController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Cari pasien berdasarkan NIP yang dipakai login
        $idPasien = DB::table('pasien')
            ->where('nip', $user->username)
            ->value('id_pasien');

        $riwayat = DB::table('pemeriksaan')
            ->join('pendaftaran', 'pemeriksaan.id_pendaftaran', '=', 'pendaftaran.id_pendaftaran')
            ->where('pendaftaran.id_pasien', $idPasien)
            ->orderBy('pemeriksaan.created_at', 'desc')
            ->select('pemeriksaan.*', 'pendaftaran.tanggal', 'pendaftaran.keluhan')
            ->get();

        return view('pasien.riwayat', compact('riwayat'));
    }


    public function dashboard()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        return $this->redirectByRole(Auth::user()->role);
    }

    private function redirectByRole($role)
    {
        $role = strtolower($role);

        if ($role === 'pasien') {
            return redirect()->route('pasien.riwayat');
        }

        if ($role === 'adminpoli') {
            return view('poliklinik.dashboard');
        }

        if ($role === 'adminkepegawaian') {
            return view('kepegawaian.dashboard');
        }

        return redirect()->route('login');
    }
}

