<?php

namespace App\Http\Controllers\Kepegawaian;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DetailRiwayatController extends Controller
{
    public function show($id_pemeriksaan)
    {
        // Fetch pemeriksaan data
        $pemeriksaan = DB::table('pemeriksaan')
            ->where('id_pemeriksaan', $id_pemeriksaan)
            ->first();

        if (!$pemeriksaan) {
            abort(404, 'Data pemeriksaan tidak ditemukan');
        }

        // Fetch pendaftaran data
        $pendaftaran = DB::table('pendaftaran')
            ->where('id_pendaftaran', $pemeriksaan->id_pendaftaran)
            ->first();

        // Fetch pasien data
        $pasien = DB::table('pasien')
            ->where('id_pasien', $pendaftaran->id_pasien)
            ->first();

        // Fetch dokter data
        $dokter = DB::table('dokter')
            ->where('id_dokter', $pendaftaran->id_dokter)
            ->first();

        // Fetch pegawai data
        $pegawai = DB::table('pegawai')
            ->where('nip', $pasien->nip)
            ->first();

        // Fetch diagnosa
        $diagnosa = null;
        if ($pemeriksaan->id_diagnosa) {
            $diagnosa = DB::table('diagnosa')
                ->where('id_diagnosa', $pemeriksaan->id_diagnosa)
                ->first();
        }

        // Fetch saran
        $saran = null;
        if ($pemeriksaan->id_saran) {
            $saran = DB::table('saran')
                ->where('id_saran', $pemeriksaan->id_saran)
                ->first();
        }

        // Fetch resep data
        $resep = DB::table('resep')
            ->where('id_pemeriksaan', $id_pemeriksaan)
            ->first();

        // Fetch detail resep with harga dari tabel obat
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
            'dokter',
            'pegawai',
            'diagnosa',
            'saran',
            'detailResep'
        ));
    }
}