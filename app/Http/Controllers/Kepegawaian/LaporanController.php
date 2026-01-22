<?php

namespace App\Http\Controllers\Kepegawaian;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    /* =========================
       INDEX (PREVIEW)
    ========================= */
    public function index()
    {
        $rekapan = [
            'pegawai'  => 'Rekapan Pemeriksaan Pegawai',
            'pensiun'  => 'Rekapan Pemeriksaan Pensiunan',
            'dokter'   => 'Rekapan Pemeriksaan Dokter',
            'obat'     => 'Rekapan Penggunaan Obat',
            'total'    => 'Rekapan Total Operasional',
        ];

        $preview = [];

        /* ================= PASIEN / PEGAWAI ================= */
        foreach (['pegawai','pensiun'] as $tipe) {
            $preview[$tipe] = DB::table('pemeriksaan')
                ->join('pendaftaran','pemeriksaan.id_pendaftaran','=','pendaftaran.id_pendaftaran')
                ->join('pegawai','pendaftaran.nip','=','pegawai.nip')
                ->leftJoin('keluarga','pendaftaran.id_keluarga','=','keluarga.id_keluarga')
                ->leftJoin('dokter','pendaftaran.id_dokter','=','dokter.id_dokter')
                ->leftJoin('pemeriksa','pendaftaran.id_pemeriksa','=','pemeriksa.id_pemeriksa')

                ->where(function ($q) use ($tipe) {
                    if ($tipe === 'pegawai') {
                        $q->whereIn('pendaftaran.tipe_pasien', ['pegawai','keluarga']);
                    } else {
                        $q->where('pegawai.status', 'pensiun');
                    }
                })

                ->select(
                    'pemeriksaan.id_pemeriksaan',
                    DB::raw("COALESCE(keluarga.nama_keluarga, pegawai.nama_pegawai) as nama_pasien"),
                    DB::raw("DATE(pemeriksaan.created_at) as tanggal"),
                    DB::raw("
                        CASE
                            WHEN dokter.id_dokter IS NOT NULL THEN dokter.nama
                            WHEN pemeriksa.id_pemeriksa IS NOT NULL THEN pemeriksa.nama_pemeriksa
                            ELSE '-'
                        END as nama_pemeriksa 
                        ")
                )
                ->orderByDesc('pemeriksaan.created_at')
                ->limit(5)
                ->get();
        }


        /* ================= DOKTER ================= */
        $preview['dokter'] = DB::table('pemeriksaan')
            ->join('pendaftaran','pemeriksaan.id_pendaftaran','=','pendaftaran.id_pendaftaran')
            ->join('dokter','pendaftaran.id_dokter','=','dokter.id_dokter')
            ->select(
                'dokter.nama as nama_dokter',
                'dokter.jenis_dokter',
                DB::raw('COUNT(pemeriksaan.id_pemeriksaan) as total_pasien')
            )
            ->groupBy('dokter.nama','dokter.jenis_dokter')
            ->limit(5)
            ->get();

        /* ================= OBAT ================= */
        $preview['obat'] = DB::table('detail_resep')
            ->join('resep','detail_resep.id_resep','=','resep.id_resep')
            ->join('obat','detail_resep.id_obat','=','obat.id_obat')
            ->join('pemeriksaan','resep.id_pemeriksaan','=','pemeriksaan.id_pemeriksaan')
            ->select(
                'obat.nama_obat',
                DB::raw('DATE(pemeriksaan.created_at) as tanggal'),
                'detail_resep.jumlah',
                'obat.harga',
                DB::raw('(detail_resep.jumlah * obat.harga) as total')
            )
            ->orderByDesc('pemeriksaan.created_at')
            ->limit(5)
            ->get();

        /* ================= TOTAL ================= */
        $preview['total'] = DB::table('pemeriksaan')
            ->join('pendaftaran','pemeriksaan.id_pendaftaran','=','pendaftaran.id_pendaftaran')
            ->join('dokter','pendaftaran.id_dokter','=','dokter.id_dokter')
            ->leftJoin('resep','pemeriksaan.id_pemeriksaan','=','resep.id_pemeriksaan')
            ->leftJoin('detail_resep','resep.id_resep','=','detail_resep.id_resep')
            ->leftJoin('obat','detail_resep.id_obat','=','obat.id_obat')
            ->select(
                DB::raw('DATE(pemeriksaan.created_at) as tanggal'),
                DB::raw('COALESCE(SUM(detail_resep.jumlah * obat.harga),0) as biaya_obat'),
                DB::raw("
                    SUM(
                        CASE 
                            WHEN dokter.jenis_dokter = 'perusahaan' THEN 1
                            ELSE 0
                        END
                    ) as biaya_dokter
                ")
            )
            ->groupBy(DB::raw('DATE(pemeriksaan.created_at)'))
            ->orderByDesc('tanggal')
            ->limit(5)
            ->get();

        return view('kepegawaian.laporan.index', compact('rekapan','preview'));
    }

    /* =========================
       DETAIL + FILTER TANGGAL
    ========================= */
    public function detail(Request $request, $jenis)
    {
        $judul = $this->getJudul($jenis);
        $dari  = $request->dari;
        $sampai = $request->sampai;

        
        /* ================= PEGAWAI / PENSIUN ================= */
        if (in_array($jenis, ['pegawai', 'pensiun'])) {

            // =====================
            // QUERY DATA INTI (TANPA DIAGNOSA & NB)
            // =====================
            $query = DB::table('pemeriksaan')
                ->join('pendaftaran', 'pemeriksaan.id_pendaftaran', '=', 'pendaftaran.id_pendaftaran')
                ->join('pegawai', 'pendaftaran.nip', '=', 'pegawai.nip')
                ->leftJoin('keluarga', 'pendaftaran.id_keluarga', '=', 'keluarga.id_keluarga')
                ->leftJoin('dokter', 'pendaftaran.id_dokter', '=', 'dokter.id_dokter')
                ->leftJoin('pemeriksa', 'pendaftaran.id_pemeriksa', '=', 'pemeriksa.id_pemeriksa')
                ->whereNotNull('pendaftaran.nip')
                ->where(function ($q) use ($jenis) {
                    if ($jenis === 'pegawai') {
                        $q->whereIn('pendaftaran.tipe_pasien', ['pegawai','keluarga'])
                        ->where('pegawai.status', '!=', 'pensiun');
                    } else { // pensiun
                        $q->where('pegawai.status', 'pensiun');
                    }
                })

                ->select(
                    'pemeriksaan.id_pemeriksaan',
                    DB::raw('DATE(pemeriksaan.created_at) as tanggal'),
                    'pegawai.nama_pegawai',
                    DB::raw('TIMESTAMPDIFF(YEAR, pegawai.tgl_lahir, CURDATE()) as umur'),
                    'pegawai.bagian',
                    DB::raw('COALESCE(keluarga.nama_keluarga, pegawai.nama_pegawai) as nama_pasien'),
                    DB::raw("COALESCE(keluarga.hubungan_keluarga, 'pegawai') as hub_kel"),
                    'pemeriksaan.sistol',
                    'pemeriksaan.gd_puasa',
                    'pemeriksaan.gd_duajam',
                    'pemeriksaan.gd_sewaktu',
                    'pemeriksaan.asam_urat',
                    'pemeriksaan.chol',
                    'pemeriksaan.tg',
                    'pemeriksaan.suhu',
                    'pemeriksaan.berat',
                    'pemeriksaan.tinggi',
                    DB::raw("
                        CASE
                            WHEN dokter.id_dokter IS NOT NULL THEN dokter.nama
                            WHEN pemeriksa.id_pemeriksa IS NOT NULL THEN pemeriksa.nama_pemeriksa
                            ELSE '-'
                        END as pemeriksa
                    ")
                )
                ->orderBy('pemeriksaan.created_at');

            if ($dari && $sampai) {
                $query->whereBetween(
                    DB::raw('DATE(pemeriksaan.created_at)'),
                    [$dari, $sampai]
                );
            }

            $dataRaw = $query->get();
            $ids = $dataRaw->pluck('id_pemeriksaan')->unique();

            // =====================
            // AMBIL DIAGNOSA, NB, SARAN
            // =====================
            $diagnosaMap = DB::table('detail_pemeriksaan_penyakit as dpp')
                ->join('diagnosa as d', 'd.id_diagnosa', '=', 'dpp.id_diagnosa')
                ->whereIn('dpp.id_pemeriksaan', $ids)
                ->get()
                ->groupBy('id_pemeriksaan');

            $nbMap = DB::table('detail_pemeriksaan_diagnosa_k3 as dpk3')
                ->join('diagnosa_k3 as dk3', 'dk3.id_nb', '=', 'dpk3.id_nb')
                ->whereIn('dpk3.id_pemeriksaan', $ids)
                ->get()
                ->groupBy('id_pemeriksaan');

            $saranMap = DB::table('detail_pemeriksaan_saran as dps')
                ->join('saran as s', 's.id_saran', '=', 'dps.id_saran')
                ->whereIn('dps.id_pemeriksaan', $ids)
                ->get()
                ->groupBy('id_pemeriksaan');

                
            // =====================
            // BENTUK DATA FINAL (FIX)
            // =====================
            $data = collect();
            $periksaCounter = [];
            $totalObatPerPemeriksaan = [];

            foreach ($dataRaw as $row) {
                $id = $row->id_pemeriksaan;

                // ===== Diagnosa & NB (INI PENGENDALI BARIS) =====
                $diagnosaList = $diagnosaMap->get($id, collect())->pluck('diagnosa')->values();
                $nbList       = $nbMap->get($id, collect())->pluck('id_nb')->values();

                $maxRow = max($diagnosaList->count(), $nbList->count(), 1);

                // ===== Obat =====
                $obatList = DB::table('resep')
                    ->join('detail_resep','resep.id_resep','=','detail_resep.id_resep')
                    ->join('obat','detail_resep.id_obat','=','obat.id_obat')
                    ->where('resep.id_pemeriksaan', $id)
                    ->select(
                        'obat.nama_obat',
                        'detail_resep.jumlah',
                        'detail_resep.satuan',
                        'obat.harga'
                    )
                    ->get();

                // total obat per pasien
                $totalObatPerPemeriksaan[$id] = $obatList->sum(function ($o) {
                    return ((int)$o->jumlah) * ((int)$o->harga);
                });

                // periksa ke (NAIK 1 PER PEMERIKSAAN)
                $periksaCounter[$id] = ($periksaCounter[$id] ?? 0) + 1;

                for ($i = 0; $i < $maxRow; $i++) {
                    $clone = clone $row;

                    // ===== DIAGNOSA & NB (SEJAJAR) =====
                    $clone->diagnosa = $diagnosaList[$i] ?? '-';
                    $clone->nb       = $nbList[$i] ?? '-';

                    // ===== OBAT (TAMPIL BARIS PERTAMA SAJA) =====
                    if ($i === 0 && isset($obatList[0])) {
                        $clone->nama_obat = $obatList[0]->nama_obat;
                        $clone->jumlah    = (int)$obatList[0]->jumlah;
                        $clone->satuan    = $obatList[0]->satuan;
                        $clone->harga     = (int)$obatList[0]->harga;
                        $clone->total_obat_pasien = $totalObatPerPemeriksaan[$id];
                    } else {
                        $clone->nama_obat = '-';
                        $clone->jumlah    = '-';
                        $clone->satuan    = '-';
                        $clone->harga     = 0;
                        $clone->total_obat_pasien = null;
                    }

                    $clone->periksa_ke = $periksaCounter[$id];

                    $data->push($clone);
                }
            }
        }
        /* ================= DOKTER ================= */

            elseif ($jenis === 'dokter') {

            $tarifPoliklinik = 100000;   // bayar per pasien
            $gajiPerusahaan  = 8000000;  // gaji bulanan tetap

            /* =========================
            DOKTER POLIKLINIK (PER PASIEN)
            ========================= */
            $queryPoli = DB::table('pemeriksaan')
                ->join('pendaftaran','pemeriksaan.id_pendaftaran','=','pendaftaran.id_pendaftaran')
                ->join('dokter','pendaftaran.id_dokter','=','dokter.id_dokter')
                ->leftJoin('pegawai','pendaftaran.nip','=','pegawai.nip')
                ->leftJoin('keluarga','pendaftaran.id_keluarga','=','keluarga.id_keluarga')
                ->whereNotNull('pendaftaran.id_dokter')
                ->whereIn('dokter.jenis_dokter',['umum','poliklinik'])
                ->select(
                    'dokter.id_dokter',
                    'dokter.nama as nama_dokter',
                    DB::raw("COALESCE(keluarga.nama_keluarga, pegawai.nama_pegawai) as nama_pasien"),
                    DB::raw('DATE(pemeriksaan.created_at) as tanggal')
                )
                ->orderBy('dokter.nama')
                ->orderBy('pemeriksaan.created_at');

            if ($dari && $sampai) {
                $queryPoli->whereBetween(
                    DB::raw('DATE(pemeriksaan.created_at)'),
                    [$dari, $sampai]
                );
            }

            $dokterPoliRaw = $queryPoli->get()->groupBy('id_dokter');

            $dokterPoli = [];

            foreach ($dokterPoliRaw as $id => $rows) {
                $dokterPoli[] = (object) [
                    'id_dokter'    => $id,
                    'nama_dokter'  => $rows->first()->nama_dokter,
                    'pasien'       => $rows,                 // list pasien
                    'total_pasien' => $rows->count(),        // jumlah pasien
                    'total_biaya'  => $rows->count() * $tarifPoliklinik
                ];
            }

            /* =========================
            DOKTER PERUSAHAAN (GAJI TETAP)
            ========================= */
            $queryPerusahaan = DB::table('pemeriksaan')
                ->join('pendaftaran','pemeriksaan.id_pendaftaran','=','pendaftaran.id_pendaftaran')
                ->join('dokter','pendaftaran.id_dokter','=','dokter.id_dokter')
                ->join('pegawai','pendaftaran.nip','=','pegawai.nip')
                ->leftJoin('keluarga','pendaftaran.id_keluarga','=','keluarga.id_keluarga')
                ->where('dokter.jenis_dokter','perusahaan')
                ->select(
                    'dokter.id_dokter',
                    'dokter.nama as nama_dokter',
                    DB::raw("COALESCE(keluarga.nama_keluarga, pegawai.nama_pegawai) as nama_pasien"),
                    DB::raw('DATE(pemeriksaan.created_at) as tanggal')
                )
                ->orderBy('dokter.nama')
                ->orderBy('pemeriksaan.created_at');

            if ($dari && $sampai) {
                $queryPerusahaan->whereBetween(
                    DB::raw('DATE(pemeriksaan.created_at)'),
                    [$dari, $sampai]
                );
            }

            $dokterPerusahaanRaw = $queryPerusahaan->get()->groupBy('id_dokter');

            $dokterPerusahaan = [];

            foreach ($dokterPerusahaanRaw as $id => $rows) {
                $dokterPerusahaan[] = (object) [
                    'id_dokter'    => $id,
                    'nama_dokter'  => $rows->first()->nama_dokter,
                    'pasien'       => $rows,          // list pasien
                    'total_pasien' => $rows->count(),
                    'gaji'         => $gajiPerusahaan
                ];
            }

            return view('kepegawaian.laporan.detail', compact(
                'judul',
                'jenis',
                'dokterPoli',
                'dokterPerusahaan',
                'dari',
                'sampai'
            ));
        }



        

        /* ================= OBAT ================= */
        elseif ($jenis === 'obat') {
            $query = DB::table('detail_resep')
                ->join('resep','detail_resep.id_resep','=','resep.id_resep')
                ->join('obat','detail_resep.id_obat','=','obat.id_obat')
                ->join('pemeriksaan','resep.id_pemeriksaan','=','pemeriksaan.id_pemeriksaan')
                ->select(
                    'obat.nama_obat',
                    DB::raw('DATE(pemeriksaan.created_at) as tanggal'),
                    'detail_resep.jumlah',
                    'obat.harga',
                    DB::raw('(detail_resep.jumlah * obat.harga) as total')
                )
                ->orderByDesc('pemeriksaan.created_at');

            if ($dari && $sampai) {
                $query->whereBetween(
                    DB::raw('DATE(pemeriksaan.created_at)'),
                    [$dari, $sampai]
                );
            }

            $data = $query->get();
        }

        /* ================= TOTAL ================= */
        elseif ($jenis === 'total') {
            $query = DB::table('pemeriksaan')
                ->join('pendaftaran','pemeriksaan.id_pendaftaran','=','pendaftaran.id_pendaftaran')
                ->join('dokter','pendaftaran.id_dokter','=','dokter.id_dokter')
                ->leftJoin('resep','pemeriksaan.id_pemeriksaan','=','resep.id_pemeriksaan')
                ->leftJoin('detail_resep','resep.id_resep','=','detail_resep.id_resep')
                ->leftJoin('obat','detail_resep.id_obat','=','obat.id_obat')
                ->select(
                    DB::raw('DATE(pemeriksaan.created_at) as tanggal'),
                    DB::raw('COALESCE(SUM(detail_resep.jumlah * obat.harga),0) as biaya_obat'),
                    DB::raw("
                        SUM(
                            CASE 
                                WHEN dokter.jenis_dokter = 'perusahaan' THEN 1
                                ELSE 0
                            END
                        ) as biaya_dokter
                    ")
                )
                ->groupBy(DB::raw('DATE(pemeriksaan.created_at)'))
                ->orderByDesc('tanggal');

            if ($dari && $sampai) {
                $query->whereBetween(
                    DB::raw('DATE(pemeriksaan.created_at)'),
                    [$dari, $sampai]
                );
            }

            $data = $query->get();
        }

        else {
            $data = collect();
        }

        return view('kepegawaian.laporan.detail', compact(
            'judul','jenis','data','dari','sampai'
        ));
    }

    private function getJudul($jenis)
    {
        return match ($jenis) {
            'pegawai'  => 'Rekapan Pemeriksaan Pegawai',
            'pensiun'  => 'Rekapan Pemeriksaan Pensiunan',
            'dokter'   => 'Rekapan Pemeriksaan Dokter',
            'obat'     => 'Rekapan Penggunaan Obat',
            'total'    => 'Rekapan Total Operasional',
            default    => 'Rekapan Laporan',
        };
    }
}
