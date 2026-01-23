<?php

namespace App\Http\Controllers\Kepegawaian;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataType;




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
        if (in_array($jenis,['pegawai','pensiun'])) {
            $data = $this->buildPegawaiPensiunData($jenis, $dari, $sampai);

            return view('kepegawaian.laporan.detail', compact(
                'judul','jenis','data','dari','sampai'
            ));
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


    private function hitungBulanGaji($dari, $sampai)
{
    $start = \Carbon\Carbon::parse($dari)->startOfMonth();
    $end   = \Carbon\Carbon::parse($sampai)->startOfMonth();

    $bulan = collect();

    while ($start <= $end) {
        $tglGajian = $start->copy()->day(25);

        if ($tglGajian >= $dari && $tglGajian <= $sampai) {
            $bulan->push($start->translatedFormat('F Y'));
        }

        $start->addMonth();
    }

    return $bulan;
}


    /* =========================
       LOGIC UTAMA (DIPAKAI ULANG)
    ========================= */
    private function buildPegawaiPensiunData($jenis, $dari, $sampai)
    {
        // === QUERY INTI (SAMA PERSIS SEPERTI DETAIL) ===
        $query = DB::table('pemeriksaan')
            ->join('pendaftaran', 'pemeriksaan.id_pendaftaran', '=', 'pendaftaran.id_pendaftaran')
            ->join('pegawai', 'pendaftaran.nip', '=', 'pegawai.nip')
            ->leftJoin('keluarga', 'pendaftaran.id_keluarga', '=', 'keluarga.id_keluarga')
            ->leftJoin('dokter', 'pendaftaran.id_dokter', '=', 'dokter.id_dokter')
            ->leftJoin('pemeriksa', 'pendaftaran.id_pemeriksa', '=', 'pemeriksa.id_pemeriksa')
            ->where(function ($q) use ($jenis) {
                if ($jenis === 'pegawai') {
                    $q->whereIn('pendaftaran.tipe_pasien', ['pegawai','keluarga'])
                    ->where('pegawai.status','!=','pensiun');
                } else {
                    $q->where('pegawai.status','pensiun');
                }
            })
            ->select(
                'pemeriksaan.id_pemeriksaan',
                DB::raw('DATE(pemeriksaan.created_at) as tanggal'),
                'pegawai.nama_pegawai',
                DB::raw('TIMESTAMPDIFF(YEAR, pegawai.tgl_lahir, CURDATE()) as umur'),
                'pegawai.bagian',
                DB::raw('COALESCE(keluarga.nama_keluarga, pegawai.nama_pegawai) as nama_pasien'),
                DB::raw("COALESCE(keluarga.hubungan_keluarga,'pegawai') as hub_kel"),
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
            );

        if ($dari && $sampai) {
            $query->whereBetween(
                DB::raw('DATE(pemeriksaan.created_at)'),
                [$dari, $sampai]
            );
        }

        $raw = $query->get();
        $ids = $raw->pluck('id_pemeriksaan');

        // === DIAGNOSA ===
        $diagnosaMap = DB::table('detail_pemeriksaan_penyakit as dpp')
            ->join('diagnosa as d','d.id_diagnosa','=','dpp.id_diagnosa')
            ->whereIn('dpp.id_pemeriksaan',$ids)
            ->get()
            ->groupBy('id_pemeriksaan');

        // === NB ===
        $nbMap = DB::table('detail_pemeriksaan_diagnosa_k3 as dpk3')
            ->join('diagnosa_k3 as dk3','dk3.id_nb','=','dpk3.id_nb')
            ->whereIn('dpk3.id_pemeriksaan',$ids)
            ->get()
            ->groupBy('id_pemeriksaan');

        // === OBAT ===
        $obatMap = DB::table('resep')
            ->join('detail_resep','resep.id_resep','=','detail_resep.id_resep')
            ->join('obat','detail_resep.id_obat','=','obat.id_obat')
            ->whereIn('resep.id_pemeriksaan',$ids)
            ->get()
            ->groupBy('id_pemeriksaan');

        // === FINAL DATA (SAMA KAYA PREVIEW) ===
        $final = collect();
        $counter = [];

        foreach ($raw as $r) {
            $id = $r->id_pemeriksaan;

            $diag = $diagnosaMap[$id] ?? collect([ (object)['diagnosa'=>'-'] ]);
            $nb   = $nbMap[$id] ?? collect([ (object)['id_nb'=>'-'] ]);
            $obat = $obatMap[$id] ?? collect([ (object)[
                'nama_obat'=>'-','jumlah'=>'-','satuan'=>'','harga'=>0
            ]]);

            $max = max($diag->count(), $nb->count(), $obat->count());

            $counter[$id] = ($counter[$id] ?? 0) + 1;
            $totalObat = $obat->sum(fn($o)=>((int)$o->jumlah*(int)$o->harga));

            for ($i=0;$i<$max;$i++) {
                $row = clone $r;
                $row->diagnosa = $diag[$i]->diagnosa ?? '-';
                $row->nb = $nb[$i]->id_nb ?? '-';

                $row->nama_obat = $obat[$i]->nama_obat ?? '-';
                $row->jumlah = $obat[$i]->jumlah ?? '-';
                $row->satuan = $obat[$i]->satuan ?? '';
                $row->harga = $obat[$i]->harga ?? 0;

                $row->total_obat_pasien = $i === 0 ? $totalObat : null;
                $row->periksa_ke = $counter[$id];

                $final->push($row);
            }
        }

        return $final;
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
        if ($jenis !== 'dokter') {
            abort(404);
        }

        $dari   = $request->dari;
        $sampai = $request->sampai;

        $tarifPoliklinik = 100000;
        $gajiPerusahaan  = 8000000;

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
            );

        if ($dari && $sampai) {
            $rows->whereBetween(
                DB::raw('DATE(pemeriksaan.created_at)'),
                [$dari, $sampai]
            );
        }

        $rows = $rows->get();

        // =============================
        // GROUP DATA
        // =============================
        $dokterPoli = $rows->where('jenis_dokter','Dokter Poliklinik')
            ->groupBy('id_dokter');

        $dokterPerusahaan = $rows->where('jenis_dokter','Dokter Perusahaan')
            ->groupBy('id_dokter');

        // =============================
        // EXCEL
        // =============================
        $spreadsheet = new Spreadsheet();

        /* =====================================================
        SHEET 1 — DOKTER POLIKLINIK
        ===================================================== */
        $sheetPoli = $spreadsheet->getActiveSheet();
        $sheetPoli->setTitle('Dokter Poliklinik');

        $row = 1;
        $sheetPoli->setCellValue("A$row",'DOKTER POLIKLINIK (BAYAR PER PASIEN)');
        $sheetPoli->mergeCells("A$row:D$row");
        $sheetPoli->getStyle("A$row")->getFont()->setBold(true)->setSize(14);
        $row += 2;

        $grandTotal = 0;
        $startRow = $row;


        foreach ($dokterPoli as $items) {
            $dokter = $items->first()->nama_dokter;
            $totalPasien = $items->count();
            $totalBiaya  = $totalPasien * $tarifPoliklinik;
            $grandTotal += $totalBiaya;

            // Nama dokter
            $sheetPoli->setCellValue("A$row",$dokter);
            $sheetPoli->mergeCells("A$row:D$row");
            $sheetPoli->getStyle("A$row")->getFont()->setBold(true);
            $row++;

            // Header tabel pasien
            $sheetPoli->fromArray(
                ['No','NIP','Nama Pasien','Tanggal Periksa'],
                null,
                "A$row"
            );
            $sheetPoli->getStyle("A$row:D$row")->getFont()->setBold(true);
            $row++;

            $no = 1;
            foreach ($items as $p) {
                $sheetPoli->setCellValue("A$row",$no++);
                $sheetPoli->setCellValueExplicit(
                    "B$row",
                    (string) $p->nip,
                    DataType::TYPE_STRING
                );

                $sheetPoli->setCellValue("C$row",$p->nama_pasien);
                $sheetPoli->setCellValue("D$row",\Carbon\Carbon::parse($p->tanggal)->translatedFormat('d F Y'));
                $row++;
            }

            $row++;
        }

        if ($row - 1 > $startRow) {
            $sheetPoli->mergeCells("A$startRow:A".($row-1));
        }

        // TOTAL POLI
        $sheetPoli->setCellValue("A$row",'TOTAL DOKTER POLIKLINIK');
        $sheetPoli->mergeCells("A$row:C$row");
        $sheetPoli->setCellValue("D$row",$grandTotal);
        $sheetPoli->getStyle("A$row:D$row")->getFont()->setBold(true);

        foreach (range('A','D') as $c) {
            $sheetPoli->getColumnDimension($c)->setAutoSize(true);
        }

        /* =====================================================
        SHEET 2 — DOKTER PERUSAHAAN (GAJI BULANAN)
        ===================================================== */

        $dokterPerusahaanList = $rows
            ->where('jenis_dokter','Dokter Perusahaan')
            ->groupBy('id_dokter');

        $sheetPer = $spreadsheet->createSheet();
        $sheetPer->setTitle('Dokter Perusahaan');

        $row = 1;
        $sheetPer->setCellValue("A$row",'DOKTER PERUSAHAAN (GAJI BULANAN)');
        $sheetPer->mergeCells("A$row:B$row");
        $sheetPer->getStyle("A$row")->getFont()->setBold(true)->setSize(14);
        $row += 2;

        $bulanGaji = $this->hitungBulanGaji($dari, $sampai);

        foreach ($dokterPerusahaanList as $items) {

            $namaDokter = $items->first()->nama_dokter;

            // ===================
            // NAMA DOKTER
            // ===================
            $sheetPer->setCellValue("A$row",$namaDokter);
            $sheetPer->mergeCells("A$row:B$row");
            $sheetPer->getStyle("A$row")->getFont()->setBold(true);
            $row++;

            // Header
            $sheetPer->setCellValue("A$row",'Periode');
            $sheetPer->setCellValue("B$row",'Gaji');
            $sheetPer->getStyle("A$row:B$row")->getFont()->setBold(true);
            $row++;

            $totalGaji = 0;

            foreach ($bulanGaji as $bulan) {
                $sheetPer->setCellValue("A$row",'Bulan '.$bulan);
                $sheetPer->setCellValue("B$row",$gajiPerusahaan);
                $totalGaji += $gajiPerusahaan;
                $row++;
            }

            // TOTAL PER DOKTER
            $sheetPer->setCellValue("A$row",'TOTAL');
            $sheetPer->setCellValue("B$row",$totalGaji);
            $sheetPer->getStyle("A$row:B$row")->getFont()->setBold(true);
            $row += 2; // jarak antar dokter
        }

        // Autosize
        foreach (range('A','B') as $c) {
            $sheetPer->getColumnDimension($c)->setAutoSize(true);
        }

        // =============================
        // DOWNLOAD
        // =============================
        $writer = new Xlsx($spreadsheet);
        $filename = 'Rekapan_Pemeriksaan_Dokter.xlsx';
        $path = storage_path('app/'.$filename);
        $writer->save($path);

        return response()->download($path)->deleteFileAfterSend(true);
    }


}
