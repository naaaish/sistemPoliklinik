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
            // 🔑 WAJIB TAMBAH JOIN INI BIAR DIAGNOSA MUNCUL
            ->leftJoin('detail_pemeriksaan_penyakit', 'pemeriksaan.id_pemeriksaan', '=', 'detail_pemeriksaan_penyakit.id_pemeriksaan')
            ->leftJoin('diagnosa', 'detail_pemeriksaan_penyakit.id_diagnosa', '=', 'diagnosa.id_diagnosa')
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
                'pendaftaran.keluhan',
                DB::raw("COALESCE(keluarga.nama_keluarga, pegawai.nama_pegawai) as nama_pasien"),
                DB::raw("CASE 
                    WHEN pendaftaran.id_dokter IS NOT NULL THEN dokter.nama 
                    WHEN pendaftaran.id_pemeriksa IS NOT NULL THEN pemeriksa.nama_pemeriksa 
                    ELSE '-' 
                END as nama_pemeriksa"),
                // 🔑 GABUNGKAN DIAGNOSA
                DB::raw("GROUP_CONCAT(DISTINCT diagnosa.diagnosa SEPARATOR ', ') as daftar_diagnosa")
            )
            ->groupBy(
                'pemeriksaan.id_pemeriksaan', 
                'pemeriksaan.created_at', 
                'pendaftaran.keluhan', 
                'keluarga.nama_keluarga', 
                'pegawai.nama_pegawai', 
                'pendaftaran.id_dokter', 
                'dokter.nama', 
                'pendaftaran.id_pemeriksa', 
                'pemeriksa.nama_pemeriksa'
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
    /**
     * ==================================================
     * DETAIL RIWAYAT PEMERIKSAAN
     * (FETCH DIAGNOSA & SARAN DARI TABEL DETAIL)
     * ==================================================
     */
    public function detail($id_pemeriksaan)
    {
        // 1. DATA PEMERIKSAAN & PENDAFTARAN (Tambahkan select)
        $pemeriksaan = DB::table('pemeriksaan')
            ->join('pendaftaran', 'pemeriksaan.id_pendaftaran', '=', 'pendaftaran.id_pendaftaran')
            ->where('pemeriksaan.id_pemeriksaan', $id_pemeriksaan)
            ->select(
                'pemeriksaan.*', 
                'pendaftaran.nip', 
                'pendaftaran.id_keluarga', 
                'pendaftaran.id_dokter', 
                'pendaftaran.id_pemeriksa'
            )
            ->first();

        if (!$pemeriksaan) abort(404);

        // 2. TENTUKAN PASIEN & PEGAWAI INDUK
        $pegawai = DB::table('pegawai')->where('nip', $pemeriksaan->nip)->first();
        
        if (!empty($pemeriksaan->id_keluarga)) {
            $pasien = DB::table('keluarga')->where('id_keluarga', $pemeriksaan->id_keluarga)->first();
        } else {
            $pasien = $pegawai;
        }

        // 3. AMBIL NAMA PEMERIKSA
        $namaPemeriksa = '-';
        if (!empty($pemeriksaan->id_dokter)) {
            $namaPemeriksa = DB::table('dokter')->where('id_dokter', $pemeriksaan->id_dokter)->value('nama');
        } elseif (!empty($pemeriksaan->id_pemeriksa)) {
            // Pastikan nama kolom di tabel pemeriksa adalah 'nama_pemeriksa'
            $namaPemeriksa = DB::table('pemeriksa')->where('id_pemeriksa', $pemeriksaan->id_pemeriksa)->value('nama_pemeriksa');
        }

        // 4. DATA DIAGNOSA & SARAN (Query Anda sudah cukup bagus)
        $diagnosa = DB::table('detail_pemeriksaan_penyakit as dpp')
            ->join('diagnosa as d', 'd.id_diagnosa', '=', 'dpp.id_diagnosa')
            ->where('dpp.id_pemeriksaan', $id_pemeriksaan)
            ->select('d.diagnosa as nama_diagnosa')->get();

        $diagnosa_k3 = DB::table('detail_pemeriksaan_diagnosa_k3 as dpk3')
            ->join('diagnosa_k3 as dk3', 'dk3.id_nb', '=', 'dpk3.id_nb')
            ->where('dpk3.id_pemeriksaan', $id_pemeriksaan)
            ->select('dk3.id_nb', 'dk3.nama_penyakit')->get();

        $saran = DB::table('detail_pemeriksaan_saran as dps')
            ->join('saran as s', 's.id_saran', '=', 'dps.id_saran')
            ->where('dps.id_pemeriksaan', $id_pemeriksaan)
            ->select('s.saran as isi_saran')->get();

        // 5. RESEP
        $resep = DB::table('resep')->where('id_pemeriksaan', $id_pemeriksaan)->first();
        $detailResep = $resep ? DB::table('detail_resep')
            ->join('obat', 'detail_resep.id_obat', '=', 'obat.id_obat')
            ->where('detail_resep.id_resep', $resep->id_resep)
            ->select('obat.nama_obat', 'detail_resep.jumlah', 'detail_resep.satuan')
            ->get() : collect();

        return view('pasien.detail-pemeriksaan', compact(
            'pemeriksaan', 'pasien', 'pegawai', 'namaPemeriksa', 
            'diagnosa', 'diagnosa_k3', 'saran', 'detailResep'
        ));
    }
}
