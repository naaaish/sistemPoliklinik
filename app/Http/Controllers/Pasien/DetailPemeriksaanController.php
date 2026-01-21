<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DetailPemeriksaanController extends Controller
{
    public function show($id_pemeriksaan)
    {
        // ================= PEMERIKSAAN =================
        $pemeriksaan = DB::table('pemeriksaan')
            ->where('id_pemeriksaan', $id_pemeriksaan)
            ->first();

        if (!$pemeriksaan) {
            abort(404, 'Data pemeriksaan tidak ditemukan');
        }

        // ================= PENDAFTARAN =================
        $pendaftaran = DB::table('pendaftaran')
            ->where('id_pendaftaran', $pemeriksaan->id_pendaftaran)
            ->first();

        // ================= PASIEN (PEGAWAI / KELUARGA) =================
        $pasien = null;
        $pegawai = null;

        // ambil pegawai (selalu ada)
        if ($pendaftaran->nip) {
            $pegawai = DB::table('pegawai')
                ->where('nip', $pendaftaran->nip)
                ->first();
        }

        // kalau keluarga
        if ($pendaftaran->id_keluarga) {
            $pasien = DB::table('keluarga')
                ->where('id_keluarga', $pendaftaran->id_keluarga)
                ->first();
        } else {
            // kalau pegawai (YBS)
            $pasien = $pegawai;
        }

        // ================= DOKTER / PEMERIKSA =================
        $dokter = null;
        $pemeriksa = null;

        if ($pendaftaran->id_dokter) {
            $dokter = DB::table('dokter')
                ->where('id_dokter', $pendaftaran->id_dokter)
                ->first();
        }

        if ($pendaftaran->id_pemeriksa) {
            $pemeriksa = DB::table('pemeriksa')
                ->where('id_pemeriksa', $pendaftaran->id_pemeriksa)
                ->first();
        }

        // ================= DIAGNOSA =================
        $diagnosa = null;
        if (!empty($pemeriksaan->id_diagnosa)) {
            $diagnosa = DB::table('diagnosa')
                ->where('id_diagnosa', $pemeriksaan->id_diagnosa)
                ->first();
        }

        // ================= SARAN =================
        $saran = null;
        if (!empty($pemeriksaan->id_saran)) {
            $saran = DB::table('saran')
                ->where('id_saran', $pemeriksaan->id_saran)
                ->first();
        }

        // ================= RESEP =================
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

        // ================= RETURN =================
        return view('pasien.detail-pemeriksaan', compact(
            'pemeriksaan',
            'pendaftaran',
            'pasien',
            'pegawai',
            'dokter',
            'pemeriksa',
            'diagnosa',
            'saran',
            'detailResep'
        ));
    }
}
