<?php

namespace App\Http\Controllers\Kepegawaian;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DetailRiwayatController extends Controller
{
    public function show($id_pemeriksaan)
    {
        /**
         * =========================
         * 1. Pemeriksaan
         * =========================
         */
        $pemeriksaan = DB::table('pemeriksaan')
            ->where('id_pemeriksaan', $id_pemeriksaan)
            ->first();

        if (!$pemeriksaan) {
            abort(404, 'Data pemeriksaan tidak ditemukan');
        }

        /**
         * =========================
         * 2. Pendaftaran
         * =========================
         */
        $pendaftaran = DB::table('pendaftaran')
            ->where('id_pendaftaran', $pemeriksaan->id_pendaftaran)
            ->first();

        /**
         * =========================
         * 3. Tentukan PASIEN
         * =========================
         * - Jika id_keluarga ada → keluarga
         * - Jika tidak → pegawai langsung
         */
        $pasien = null;
        $pegawai = null;

        if (!empty($pendaftaran->id_keluarga)) {
            // pasien = keluarga
            $pasien = DB::table('keluarga')
                ->where('id_keluarga', $pendaftaran->id_keluarga)
                ->first();

            // induk pegawai
            if ($pasien) {
                $pegawai = DB::table('pegawai')
                    ->where('nip', $pasien->nip)
                    ->first();
            }
        } else {
            // pasien = pegawai langsung
            $pegawai = DB::table('pegawai')
                ->where('nip', $pendaftaran->nip)
                ->first();

            $pasien = $pegawai; // supaya view tetap bisa pakai $pasien
        }

        /**
         * =========================
         * 4. Pemeriksa (dokter / pemeriksa)
         * =========================
         */
        $namaPemeriksa = '-';

        if (!empty($pendaftaran->id_dokter)) {
            $dokter = DB::table('dokter')
                ->where('id_dokter', $pendaftaran->id_dokter)
                ->first();

            if ($dokter) {
                $namaPemeriksa = $dokter->nama;
            }
        } elseif (!empty($pendaftaran->id_pemeriksa)) {
            $pemeriksa = DB::table('pemeriksa')
                ->where('id_pemeriksa', $pendaftaran->id_pemeriksa)
                ->first();

            if ($pemeriksa) {
                $namaPemeriksa = $pemeriksa->nama_pemeriksa;
            }
        }

        /**
         * =========================
         * 5. Diagnosa
         * =========================
         */
        $diagnosa = null;
        if (!empty($pemeriksaan->id_diagnosa)) {
            $diagnosa = DB::table('diagnosa')
                ->where('id_diagnosa', $pemeriksaan->id_diagnosa)
                ->first();
        }

        /**
         * =========================
         * 6. Saran
         * =========================
         */
        $saran = null;
        if (!empty($pemeriksaan->id_saran)) {
            $saran = DB::table('saran')
                ->where('id_saran', $pemeriksaan->id_saran)
                ->first();
        }

        /**
         * =========================
         * 7. Resep + Detail Resep
         * =========================
         */
        $resep = DB::table('resep')
            ->where('id_pemeriksaan', $id_pemeriksaan)
            ->first();

        $detailResep = collect();

        if ($resep) {
            $detailResep = DB::table('detail_resep')
                ->join('obat', 'detail_resep.id_obat', '=', 'obat.id_obat')
                ->where('detail_resep.id_resep', $resep->id_resep)
                ->select(
                    'obat.nama_obat',
                    'obat.harga',
                    'detail_resep.jumlah',
                    'detail_resep.satuan'
                )
                ->get();
        }

        return view('kepegawaian.detail-riwayat', compact(
            'pemeriksaan',
            'pendaftaran',
            'pasien',
            'pegawai',
            'namaPemeriksa',
            'diagnosa',
            'saran',
            'detailResep'
        ));
    }
}
