<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class RiwayatController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        /**
         * =====================================
         * 1. Ambil pegawai berdasarkan user login
         * =====================================
         */
        $pegawai = DB::table('pegawai')
            ->where('nip', $user->nip)
            ->first();

        if (!$pegawai) {
            return view('pasien.riwayat', [
                'pegawai'       => null,
                'keluargaAktif' => null,
                'riwayat'       => collect(),
                'daftarKeluarga'=> collect(),
                'keluargaAktifId' => null
            ]);
        }

        /**
         * =====================================
         * 2. Ambil SEMUA keluarga milik pegawai
         * (pegawai juga dianggap pasien)
         * =====================================
         */
        $daftarKeluarga = DB::table('keluarga')
            ->where('nip', $pegawai->nip)
            ->orderByRaw("
                CASE hubungan
                    WHEN 'pegawai' THEN 1
                    WHEN 'istri' THEN 2
                    WHEN 'suami' THEN 2
                    WHEN 'anak' THEN 3
                    ELSE 4
                END
            ")
            ->get();

        if ($daftarKeluarga->isEmpty()) {
            return view('pasien.riwayat', [
                'pegawai'        => $pegawai,
                'keluargaAktif'  => null,
                'riwayat'        => collect(),
                'daftarKeluarga' => collect(),
                'keluargaAktifId'=> null
            ]);
        }

        /**
         * =====================================
         * 3. Tentukan keluarga aktif (dropdown)
         * =====================================
         */
        $keluargaAktifId = $request->get(
            'id_keluarga',
            $daftarKeluarga->first()->id_keluarga
        );

        $keluargaAktif = $daftarKeluarga
            ->firstWhere('id_keluarga', $keluargaAktifId);

        if (!$keluargaAktif) {
            $keluargaAktif = $daftarKeluarga->first();
            $keluargaAktifId = $keluargaAktif->id_keluarga;
        }

        /**
         * =====================================
         * 4. Ambil RIWAYAT (dari pemeriksaan)
         * =====================================
         */
        $riwayat = DB::table('pemeriksaan')
            ->join('pendaftaran', 'pemeriksaan.id_pendaftaran', '=', 'pendaftaran.id_pendaftaran')
            ->leftJoin('dokter', 'pendaftaran.id_dokter', '=', 'dokter.id_dokter')
            ->leftJoin('pemeriksa', 'pendaftaran.id_pemeriksa', '=', 'pemeriksa.id_pemeriksa')
            ->where('pendaftaran.id_keluarga', $keluargaAktifId)
            ->select(
                'pemeriksaan.*',
                'pendaftaran.tanggal',
                'dokter.nama as dokter',
                'pemeriksa.nama_pemeriksa as pemeriksa'
            )
            ->orderBy('pemeriksaan.created_at', 'desc')
            ->get();

        return view('pasien.riwayat', compact(
            'pegawai',
            'keluargaAktif',
            'riwayat',
            'daftarKeluarga',
            'keluargaAktifId'
        ));
    }
}
