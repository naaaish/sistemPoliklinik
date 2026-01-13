<?php

namespace App\Http\Controllers\Kepegawaian;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class KDashboardController extends Controller
{
    public function index()
    {
        return view('kepegawaian.dashboard', [

            'totalPegawai' => DB::table('pegawai')->count(),
            'totalRiwayat' => DB::table('pemeriksaan')->count(),

            'hariIni' => DB::table('pemeriksaan')
                ->whereDate('created_at', today())
                ->count(),

            'riwayat' => DB::table('pemeriksaan')
                ->join('pendaftaran', 'pemeriksaan.id_pendaftaran', '=', 'pendaftaran.id_pendaftaran')
                ->join('pasien', 'pendaftaran.id_pasien', '=', 'pasien.id_pasien')
                ->join('dokter', 'pendaftaran.id_dokter', '=', 'dokter.id_dokter')
                ->select(
                    'pasien.nama_pasien',
                    'pemeriksaan.created_at as tanggal',
                    'dokter.nama as dokter'
                )
                ->orderBy('pemeriksaan.created_at', 'desc')
                ->limit(5)
                ->get()

        ]);
    }
}
