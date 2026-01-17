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
         * 1. Cari pegawai berdasarkan user (NIP)
         * =====================================
         */
        $pegawai = DB::table('pegawai')
            ->where('nip', $user->nip)
            ->first();

        if (!$pegawai) {
            return view('pasien.riwayat', [
                'pasien'        => null,
                'pegawai'       => null,
                'riwayat'       => collect(),
                'daftarPasien'  => collect(),
                'pasienAktifId' => null
            ]);
        }

        /**
         * =====================================
         * 2. Ambil SEMUA pasien milik pegawai
         * =====================================
         * Berdasarkan ERD: pegawai.nip → pasien.nip (jika pegawai punya pasien)
         * ATAU cek lewat tabel pegawai yang punya id_pasien
         */
        
        // Cara 1: Jika pasien punya kolom nip (relasi langsung)
        $daftarPasien = DB::table('pasien')
            ->where('nip', $pegawai->nip)
            ->orderByRaw("
                CASE hub_kel
                    WHEN 'ybs' THEN 1
                    WHEN 'istri pegawai' THEN 2
                    WHEN 'suami pegawai' THEN 2
                    WHEN 'anak pegawai' THEN 3
                    ELSE 4
                END
            ")
            ->get();

        // Jika tidak ada pasien
        if ($daftarPasien->isEmpty()) {
            return view('pasien.riwayat', [
                'pasien'        => null,
                'pegawai'       => $pegawai,
                'riwayat'       => collect(),
                'daftarPasien'  => collect(),
                'pasienAktifId' => null
            ]);
        }

        /**
         * =====================================
         * 3. Tentukan pasien aktif
         * =====================================
         */
        $pasienAktifId = $request->get('pasien_id', $daftarPasien->first()->id_pasien);
        $pasien = $daftarPasien->firstWhere('id_pasien', $pasienAktifId);

        if (!$pasien) {
            $pasien = $daftarPasien->first();
            $pasienAktifId = $pasien->id_pasien;
        }

        /**
         * =====================================
         * 4. Ambil riwayat pemeriksaan pasien
         * =====================================
         */
        $riwayat = DB::table('pemeriksaan')
            ->join('pendaftaran', 'pemeriksaan.id_pendaftaran', '=', 'pendaftaran.id_pendaftaran')
            ->join('dokter', 'pendaftaran.id_dokter', '=', 'dokter.id_dokter')
            ->where('pendaftaran.id_pasien', $pasien->id_pasien)
            ->orderBy('pemeriksaan.created_at', 'desc')
            ->select(
                'pemeriksaan.id_pemeriksaan',
                'pemeriksaan.created_at',
                'pendaftaran.keluhan',
                'dokter.nama as nama_dokter',
                'dokter.jenis_dokter'
            )
            ->get();

        return view('pasien.riwayat', compact(
            'pasien',
            'pegawai',
            'riwayat',
            'daftarPasien',
            'pasienAktifId'
        ));
    }
}