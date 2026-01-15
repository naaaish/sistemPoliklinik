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
            ->leftJoin('dokter', 'pendaftaran.id_dokter', '=', 'dokter.id_dokter')
            ->leftJoin('pemeriksa', 'pendaftaran.id_pemeriksa', '=', 'pemeriksa.id_pemeriksa')
            ->select(
                'pasien.nama_pasien',
                'pasien.nip',
                'pemeriksaan.created_at as tanggal',
                'dokter.nama as dokter',
                'pemeriksa.nama_pemeriksa as pemeriksa'
            )
            ->orderBy('pemeriksaan.created_at', 'desc')
            ->paginate(10);


        return view('kepegawaian.riwayat', compact('riwayat'));
    }
}
