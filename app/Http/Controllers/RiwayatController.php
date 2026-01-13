<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RiwayatController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        // Ambil data pasien dari user login
        $pasien = DB::table('pasien')
            ->join('pendaftaran','pasien.id_pasien','=','pendaftaran.id_pasien')
            ->where('pendaftaran.id_pasien', $userId)
            ->select('pasien.*')
            ->first();

        // Kalau pasien belum terdaftar
        if (!$pasien) {
            return view('pasien.riwayat', [
                'pasien' => null,
                'riwayat' => collect()
            ]);
        }

        // Ambil riwayat pemeriksaan pasien
        $riwayat = DB::table('pemeriksaan')
            ->join('pendaftaran','pemeriksaan.id_pendaftaran','=','pendaftaran.id_pendaftaran')
            ->join('dokter','pendaftaran.id_dokter','=','dokter.id_dokter')
            ->where('pendaftaran.id_pasien', $pasien->id_pasien)
            ->orderBy('pemeriksaan.created_at','desc')
            ->select(
                'pemeriksaan.*',
                'pendaftaran.keluhan',
                'dokter.nama as dokter'
            )
            ->get();

        return view('pasien.riwayat', compact('pasien','riwayat'));
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

