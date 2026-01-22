<?php

namespace App\Http\Controllers\Kepegawaian;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PegawaiExport;
use App\Exports\PensiunanExport;
use App\Exports\DokterExport;




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

                    
                $maxRow = max(
                    $diagnosaList->count(),
                    $nbList->count(),
                    $obatList->count(),
                    1
                );

                // total obat per pasien
                $totalObatPerPemeriksaan[$id] = $obatList->sum(function ($o) {
                    return ((int)$o->jumlah) * ((int)$o->harga);
                });

                // periksa ke (NAIK 1 PER PEMERIKSAAN)
                $periksaCounter[$id] = ($periksaCounter[$id] ?? 0) + 1;

                for ($i = 0; $i < $maxRow; $i++) {
                    $clone = clone $row;

                    // Diagnosa & NB
                    $clone->diagnosa = $diagnosaList[$i] ?? '-';
                    $clone->nb       = $nbList[$i] ?? '-';

                    // Obat
                    if (isset($obatList[$i])) {
                        $clone->nama_obat = $obatList[$i]->nama_obat;
                        $clone->jumlah    = (int)$obatList[$i]->jumlah;
                        $clone->satuan    = $obatList[$i]->satuan;
                        $clone->harga     = (int)$obatList[$i]->harga;
                    } else {
                        $clone->nama_obat = '-';
                        $clone->jumlah    = '-';
                        $clone->satuan    = '-';
                        $clone->harga     = 0;
                    }

                    // Total obat per pasien (1x saja)
                    $clone->total_obat_pasien = ($i === 0)
                        ? $totalObatPerPemeriksaan[$id]
                        : null;

                    $clone->periksa_ke = $periksaCounter[$id];

                    $data->push($clone);
                }

            }
        }
        /* ================= DOKTER ================= */

        elseif ($jenis === 'dokter') {

            $tarifPoliklinik = 100000;
            $gajiPerusahaan  = 8000000;

            $query = DB::table('pemeriksaan')
                ->join('pendaftaran', 'pemeriksaan.id_pendaftaran', '=', 'pendaftaran.id_pendaftaran')
                ->join('dokter', 'pendaftaran.id_dokter', '=', 'dokter.id_dokter')
                ->leftJoin('pegawai', 'pendaftaran.nip', '=', 'pegawai.nip')
                ->leftJoin('keluarga', 'pendaftaran.id_keluarga', '=', 'keluarga.id_keluarga')
                ->whereNotNull('pendaftaran.id_dokter')
                ->select(
                    'dokter.id_dokter',
                    'dokter.nama as nama_dokter',
                    'dokter.jenis_dokter',

                    // 🔑 INI PENTING
                    'pendaftaran.nip',

                    DB::raw("COALESCE(keluarga.nama_keluarga, pegawai.nama_pegawai) as nama_pasien"),
                    DB::raw("DATE(pemeriksaan.created_at) as tanggal")
                );

            if ($dari && $sampai) {
                $query->whereBetween(
                    DB::raw('DATE(pemeriksaan.created_at)'),
                    [$dari, $sampai]
                );
            }

            $rows = $query->get();

            // =======================
            // DOKTER POLIKLINIK
            // =======================
            $dokterPoli = $rows
                ->where('jenis_dokter', 'Dokter Poliklinik')
                ->groupBy('id_dokter')
                ->map(function ($items) use ($tarifPoliklinik) {
                    return (object) [
                        'id_dokter'    => $items->first()->id_dokter,
                        'nama_dokter'  => $items->first()->nama_dokter,
                        'pasien'       => $items->map(function ($p) {
                            return (object) [
                                'nip'          => $p->nip,
                                'nama_pasien'  => $p->nama_pasien,
                                'tanggal'      => $p->tanggal,
                            ];
                        }),
                        'total_pasien' => $items->count(),
                        'total_biaya'  => $items->count() * $tarifPoliklinik
                    ];
                })
                ->values();

            // =======================
            // DOKTER PERUSAHAAN
            // =======================
            $dokterPerusahaan = $rows
                ->where('jenis_dokter', 'Dokter Perusahaan')
                ->groupBy('id_dokter')
                ->map(function ($items) use ($gajiPerusahaan) {
                    return (object) [
                        'id_dokter'    => $items->first()->id_dokter,
                        'nama_dokter'  => $items->first()->nama_dokter,
                        'pasien'       => $items->map(function ($p) {
                            return (object) [
                                'nip'          => $p->nip,
                                'nama_pasien'  => $p->nama_pasien,
                                'tanggal'      => $p->tanggal,
                            ];
                        }),
                        'total_pasien' => $items->count(),
                        'gaji'         => $gajiPerusahaan
                    ];
                })
                ->values();

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


    /* =========================
       LOGIC UTAMA (DIPAKAI ULANG)
    ========================= */
    private function getLaporanData($jenis, $dari, $sampai)
    {
        /* ================= PEGAWAI / PENSIUN ================= */
        if (in_array($jenis, ['pegawai','pensiun'])) {

            $query = DB::table('pemeriksaan')
                ->join('pendaftaran','pemeriksaan.id_pendaftaran','=','pendaftaran.id_pendaftaran')
                ->join('pegawai','pendaftaran.nip','=','pegawai.nip')
                ->leftJoin('keluarga','pendaftaran.id_keluarga','=','keluarga.id_keluarga')
                ->leftJoin('dokter','pendaftaran.id_dokter','=','dokter.id_dokter')
                ->leftJoin('pemeriksa','pendaftaran.id_pemeriksa','=','pemeriksa.id_pemeriksa')
                ->where(function ($q) use ($jenis) {
                    if ($jenis === 'pegawai') {
                        $q->where('pegawai.status','!=','pensiun');
                    } else {
                        $q->where('pegawai.status','pensiun');
                    }
                })
                ->select(
                    DB::raw("COALESCE(keluarga.nama_keluarga, pegawai.nama_pegawai) as nama_pasien"),
                    DB::raw("DATE(pemeriksaan.created_at) as tanggal")
                );

            if ($dari && $sampai) {
                $query->whereBetween(DB::raw('DATE(pemeriksaan.created_at)'), [$dari,$sampai]);
            }

            return $query->get();
        }

        /* ================= DOKTER ================= */
        if ($jenis === 'dokter') {

            $tarifPoli = 100000;
            $gaji      = 8000000;

            $rows = DB::table('pemeriksaan')
                ->join('pendaftaran','pemeriksaan.id_pendaftaran','=','pendaftaran.id_pendaftaran')
                ->join('dokter','pendaftaran.id_dokter','=','dokter.id_dokter')
                ->leftJoin('pegawai','pendaftaran.nip','=','pegawai.nip')
                ->leftJoin('keluarga','pendaftaran.id_keluarga','=','keluarga.id_keluarga')
                ->select(
                    'dokter.id_dokter',
                    'dokter.nama as nama_dokter',
                    'dokter.jenis_dokter',
                    'pendaftaran.nip',
                    DB::raw("COALESCE(keluarga.nama_keluarga, pegawai.nama_pegawai) as nama_pasien"),
                    DB::raw("DATE(pemeriksaan.created_at) as tanggal")
                )
                ->when($dari && $sampai, fn($q) =>
                    $q->whereBetween(DB::raw('DATE(pemeriksaan.created_at)'), [$dari,$sampai])
                )
                ->get();

            $dokterPoli = $rows->where('jenis_dokter','Dokter Poliklinik')
                ->groupBy('id_dokter')
                ->map(fn($r)=> (object)[
                    'nama_dokter'=>$r->first()->nama_dokter,
                    'jenis_dokter'=>'umum',
                    'total_pasien'=>$r->count(),
                    'detail_pasien'=>$r
                ]);

            $dokterPerusahaan = $rows->where('jenis_dokter','Dokter Perusahaan')
                ->groupBy('id_dokter')
                ->map(fn($r)=> (object)[
                    'nama_dokter'=>$r->first()->nama_dokter,
                    'jenis_dokter'=>'perusahaan',
                    'total_pasien'=>$r->count(),
                ]);

            return [
                'data' => $dokterPoli->merge($dokterPerusahaan)
            ];
        }

        return collect();
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

    /* =========================
       DOWNLOAD EXCEL (XLS VIA BLADE)
    ========================= */
    public function downloadExcel(Request $request, $jenis)
    {
        $dari   = $request->dari;
        $sampai = $request->sampai;

        $data = $this->detail($request, $jenis)->getData()['data'] ?? collect();

        $filename = 'laporan_'.$jenis.'_'.date('Y-m-d').'.csv';

        $headers = [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
        ];

        $callback = function () use ($data, $jenis) {
            $file = fopen('php://output', 'w');

            // HEADER
            if (in_array($jenis, ['pegawai','pensiun'])) {
                fputcsv($file, ['No','Nama Pasien','Tanggal']);
                foreach ($data as $i => $row) {
                    fputcsv($file, [
                        $i+1,
                        $row->nama_pasien,
                        $row->tanggal
                    ]);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }


}
