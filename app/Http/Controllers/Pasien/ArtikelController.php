<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ArtikelController extends Controller
{
    /**
     * Halaman daftar artikel
     */
    public function index()
    {
        $artikel = DB::table('artikel')
            ->orderBy('tanggal', 'desc')
            ->get();

        return view('pasien.artikel', compact('artikel'));
    }

    /**
     * Halaman detail artikel
     */
    public function show($id)
    {
        $artikel = DB::table('artikel')
            ->where('id_artikel', $id)
            ->first();

        // jika artikel tidak ditemukan
        if (!$artikel) {
            abort(404);
        }

        return view('pasien.artikel_detail', compact('artikel'));
    }
}
