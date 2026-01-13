<?php

namespace App\Http\Controllers\Kepegawaian;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class KRiwayatController extends Controller
{
    public function index()
    {
        $riwayat = DB::table('pemeriksaan')
            ->join('pendaftaran', 'pemeriksaan.id_pendaftaran', '=', 'pendaftaran.id_pendaftaran')
            ->join('pasien', 'pendaftaran.id_pasien', '=', 'pasien.id_pasien')
            ->join('dokter', 'pendaftaran.id_dokter', '=', 'dokter.id_dokter')
            ->join('pemeriksa', 'pendaftaran.id_pemeriksa', '=', 'pemeriksa.id_pemeriksa')
            ->select(
                'pasien.nama_pasien',
                'pemeriksaan.created_at as tanggal',
                'dokter.nama as dokter',
                'pemeriksa.nama_pemeriksa'
            )
            ->orderBy('pemeriksaan.created_at', 'desc')
            ->get();


        return view('kepegawaian.riwayat', compact('riwayat'));
    }
}
