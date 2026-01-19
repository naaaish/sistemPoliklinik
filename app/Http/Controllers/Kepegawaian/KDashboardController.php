<?php

namespace App\Http\Controllers\Kepegawaian;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class KDashboardController extends Controller
{
    public function index()
    {
        $totalPegawai = DB::table('pegawai')->count();
        $totalRiwayat = DB::table('pemeriksaan')->count();

        $hariIni = DB::table('pemeriksaan')
            ->whereDate('created_at', now()->toDateString())
            ->count();

        $riwayat = DB::table('pemeriksaan')
            ->join('pendaftaran', 'pemeriksaan.id_pendaftaran', '=', 'pendaftaran.id_pendaftaran')
            ->join('pasien', 'pendaftaran.id_pasien', '=', 'pasien.id_pasien')
            ->leftJoin('dokter', 'pendaftaran.id_dokter', '=', 'dokter.id_dokter')
            ->leftJoin('pemeriksa', 'pendaftaran.id_pemeriksa', '=', 'pemeriksa.id_pemeriksa')
            ->select(
                'pemeriksaan.id_pemeriksaan', 
                'pasien.nama_pasien',
                'pasien.nip',
                'pemeriksaan.created_at as tanggal',
                DB::raw('COALESCE(dokter.nama, pemeriksa.nama_pemeriksa) as nama_pemeriksa')
            )
            ->orderBy('pemeriksaan.created_at', 'desc')
            ->limit(5)
            ->get();


        return view('kepegawaian.dashboard', compact(
            'totalPegawai',
            'totalRiwayat',
            'hariIni',
            'riwayat'
        ));
    }
}