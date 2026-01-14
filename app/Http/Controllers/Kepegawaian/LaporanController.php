<?php

namespace App\Http\Controllers\Kepegawaian;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    public function index()
    {
        $laporan = DB::table('pemeriksaan')
            ->join('pendaftaran', 'pemeriksaan.id_pendaftaran', '=', 'pendaftaran.id_pendaftaran')
            ->join('pasien', 'pendaftaran.id_pasien', '=', 'pasien.id_pasien')
            ->join('pegawai', 'pendaftaran.nip', '=', 'pegawai.nip')
            ->select(
                'pegawai.nip',
                'pegawai.nama_pegawai',
                'pasien.nama_pasien',
                'pemeriksaan.sistol',
                'pemeriksaan.diastol',
                'pemeriksaan.gd_puasa',
                'pemeriksaan.gd_sewaktu',
                'pemeriksaan.chol',
                'pemeriksaan.asam_urat',
                'pemeriksaan.created_at as tanggal'
            )
            ->orderBy('pemeriksaan.created_at','desc')
            ->get();

        return view('kepegawaian.laporan.index', compact('laporan'));
    }
}
