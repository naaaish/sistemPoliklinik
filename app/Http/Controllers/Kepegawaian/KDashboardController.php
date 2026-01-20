<?php

namespace App\Http\Controllers\Kepegawaian;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class KDashboardController extends Controller
{
    public function index()
    {
        // total pegawai
        $totalPegawai = DB::table('pegawai')->count();
        // total riwayat pemeriksaan
        $totalRiwayat = DB::table('pemeriksaan')->count();

        // pemeriksaan hari ini
        $hariIni = DB::table('pemeriksaan')
            ->whereDate('created_at', now()->toDateString())
            ->count();

            
        $riwayat = DB::table('pemeriksaan')
            ->join('pendaftaran', 'pemeriksaan.id_pendaftaran', '=', 'pendaftaran.id_pendaftaran')
            ->leftJoin('keluarga', 'pendaftaran.id_keluarga', '=', 'keluarga.id_keluarga')
            ->leftJoin('pegawai', 'pendaftaran.nip', '=', 'pegawai.nip')
            ->leftJoin('dokter', 'pendaftaran.id_dokter', '=', 'dokter.id_dokter')
            ->leftJoin('pemeriksa', 'pendaftaran.id_pemeriksa', '=', 'pemeriksa.id_pemeriksa')
            ->select(
                'pemeriksaan.id_pemeriksaan', 
                DB::raw("COALESCE(keluarga.nama_keluarga, pegawai.nama_pegawai) as nama_pasien"),
                'pegawai.nip',
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