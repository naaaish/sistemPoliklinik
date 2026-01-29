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

        // =================================================
        // DIAGNOSA NON-K3 (MANY TO MANY)
        // =================================================
        $diagnosa = DB::table('detail_pemeriksaan_penyakit as dpp')
            ->join('diagnosa as d', 'd.id_diagnosa', '=', 'dpp.id_diagnosa')
            ->where('dpp.id_pemeriksaan', $id_pemeriksaan)
            ->select('d.diagnosa as nama_diagnosa')
            ->get();


        // =================================================
        // DIAGNOSA K3
        // =================================================
        $diagnosa_k3 = DB::table('detail_pemeriksaan_diagnosa_k3 as dpk3')
            ->join('diagnosa_k3 as dk3', 'dk3.id_nb', '=', 'dpk3.id_nb')
            ->where('dpk3.id_pemeriksaan', $id_pemeriksaan)
            ->select('dk3.nama_penyakit')
            ->get();

        // =================================================
        // SARAN (MANY TO MANY)
        // =================================================
        $saran = DB::table('detail_pemeriksaan_saran as dps')
            ->join('saran as s', 's.id_saran', '=', 'dps.id_saran')
            ->where('dps.id_pemeriksaan', $id_pemeriksaan)
            ->select('s.saran as isi_saran')
            ->get();


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
            'namaPemeriksa',
            'diagnosa',
            'diagnosa_k3',
            'saran',
            'detailResep'
        ));
    }
}
