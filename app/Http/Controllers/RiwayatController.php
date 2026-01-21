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

        // === PASIEN PEGAWAI (YBS) ===
        $pasienPegawai = (object) [
            'id_keluarga'        => 'pegawai', // ID KHUSUS
            'hubungan_keluarga'  => 'pegawai',
            'urutan_anak'        => null,
            'nama_keluarga'      => $pegawai->nama_pegawai,
            'tgl_lahir'          => $pegawai->tgl_lahir,
        ];


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
        $Keluarga = DB::table('keluarga')
            ->where('nip', $pegawai->nip)
            ->orderByRaw("
                CASE hubungan_keluarga
                    
                    WHEN 'pasangan' THEN 2
                    WHEN 'anak' THEN 3
                    ELSE 4
                END
            ")
            ->orderBy('urutan_anak')
            ->get();

            $daftarKeluarga = collect([$pasienPegawai])->merge($Keluarga);


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
        $keluargaAktifId = $request->get('id_keluarga', 'pegawai');

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
            ->leftJoin('keluarga', 'pendaftaran.id_keluarga', '=', 'keluarga.id_keluarga')
            ->leftJoin('pegawai', 'pendaftaran.nip', '=', 'pegawai.nip')
            ->leftJoin('dokter', 'pendaftaran.id_dokter', '=', 'dokter.id_dokter')
            ->leftJoin('pemeriksa', 'pendaftaran.id_pemeriksa', '=', 'pemeriksa.id_pemeriksa')
            ->where(function ($q) use ($keluargaAktifId, $pegawai) {
                if ($keluargaAktifId === 'pegawai') {
                    $q->whereNull('pendaftaran.id_keluarga')
                    ->where('pendaftaran.nip', $pegawai->nip);
                } else {
                    $q->where('pendaftaran.id_keluarga', $keluargaAktifId);
                }
            })
            ->select(
                'pemeriksaan.id_pemeriksaan',
                'pemeriksaan.created_at',
                DB::raw("COALESCE(keluarga.nama_keluarga, pegawai.nama_pegawai) as nama_pasien"),
                DB::raw("
                    CASE
                        WHEN pendaftaran.id_dokter IS NOT NULL THEN dokter.nama
                        WHEN pendaftaran.id_pemeriksa IS NOT NULL THEN pemeriksa.nama_pemeriksa
                        ELSE '-'
                    END as nama_pemeriksa
                "),
                'pendaftaran.keluhan'
                
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
