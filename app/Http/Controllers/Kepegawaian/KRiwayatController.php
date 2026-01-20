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

            // ⛔ sebelumnya: join pasien
            // ✅ sekarang: join keluarga
            ->leftjoin('keluarga', 'pendaftaran.id_keluarga', '=', 'keluarga.id_keluarga')

            // tetap
            ->leftJoin('pegawai', 'pendaftaran.nip', '=', 'pegawai.nip')
            ->leftJoin('dokter', 'pendaftaran.id_dokter', '=', 'dokter.id_dokter')
            ->leftJoin('pemeriksa', 'pendaftaran.id_pemeriksa', '=', 'pemeriksa.id_pemeriksa')

            ->select(
                'pemeriksaan.id_pemeriksaan',

                DB::raw("COALESCE(keluarga.nama_keluarga, pegawai.nama_pegawai) as nama_pasien"),

                // nip induk
                'pegawai.nip',

                'pemeriksaan.created_at as tanggal',
                'dokter.nama as dokter',
                'pemeriksa.nama_pemeriksa as pemeriksa'
            )
            ->orderBy('pemeriksaan.created_at', 'desc')
            ->paginate(10);

        return view('kepegawaian.riwayat', compact('riwayat'));
    }
}
