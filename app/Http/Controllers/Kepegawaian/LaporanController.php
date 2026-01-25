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
            'pegawai' => 'Rekapan Pemeriksaan Pegawai',
            'pensiun' => 'Rekapan Pemeriksaan Pensiunan',
            'dokter'  => 'Rekapan Pemeriksaan Dokter',
            'obat'    => 'Rekapan Penggunaan Obat',
            'total'   => 'Rekapan Total Operasional',
        ];

        $preview = [];

        // ================= PEGAWAI & PENSIUN =================
        foreach (['pegawai','pensiun'] as $tipe) {
            $preview[$tipe] = DB::table('pemeriksaan')
                ->join('pendaftaran','pemeriksaan.id_pendaftaran','=','pendaftaran.id_pendaftaran')
                ->join('pegawai','pendaftaran.nip','=','pegawai.nip')
                ->leftJoin('keluarga','pendaftaran.id_keluarga','=','keluarga.id_keluarga')
                ->leftJoin('dokter','pendaftaran.id_dokter','=','dokter.id_dokter')
                ->leftJoin('pemeriksa','pendaftaran.id_pemeriksa','=','pemeriksa.id_pemeriksa')
                ->where(function($q) use ($tipe) {
                            if ($tipe === 'pegawai') {
                                // Ambil yang tipe pasien pegawai/keluarga, TAPI bagiannya bukan Pensiunan
                                $q->whereIn('pendaftaran.tipe_pasien', ['pegawai','keluarga'])
                                ->where('pegawai.bagian', '!=', 'Pensiunan');
                            } else {
                                // Khusus untuk tab Pensiunan
                                $q->where('pegawai.bagian', 'Pensiunan');
                            }
                        })
                ->select(
                    'pemeriksaan.id_pemeriksaan',
                    DB::raw("COALESCE(keluarga.nama_keluarga, pegawai.nama_pegawai) as nama_pasien"),
                    DB::raw("DATE(pemeriksaan.created_at) as tanggal"),
                    DB::raw("
                        COALESCE(dokter.nama, pemeriksa.nama_pemeriksa, '-') as nama_pemeriksa
                    ")
                )
                ->orderByDesc('pemeriksaan.created_at')
                ->limit(5)
                ->get();
        }

        // ================= DOKTER =================
        $preview['dokter'] = DB::table('pemeriksaan')
            ->join('pendaftaran','pemeriksaan.id_pendaftaran','=','pendaftaran.id_pendaftaran')
            ->join('dokter','pendaftaran.id_dokter','=','dokter.id_dokter')
            ->select(
                'dokter.nama as nama_dokter',
                'dokter.jenis_dokter',
                DB::raw('COUNT(*) as total_pasien')
            )
            ->groupBy('dokter.nama','dokter.jenis_dokter')
            ->limit(5)
            ->get();

        // ================= OBAT =================
        $preview['obat'] = DB::table('detail_resep')
            ->join('resep','detail_resep.id_resep','=','resep.id_resep')
            ->join('obat','detail_resep.id_obat','=','obat.id_obat')
            ->join('pemeriksaan','resep.id_pemeriksaan','=','pemeriksaan.id_pemeriksaan')
            ->select(
                'obat.nama_obat',
                DB::raw('DATE(pemeriksaan.created_at) as tanggal'),
                'detail_resep.jumlah',
                DB::raw('(detail_resep.jumlah * obat.harga) as total')
            )
            ->orderByDesc('pemeriksaan.created_at')
            ->limit(5)
            ->get();

        // ================= TOTAL =================
        $preview['total'] = $this->buildTotalOperasional();

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
            $data = $this->buildTotalOperasional($dari, $sampai);
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
                    ->where('pegawai.bagian', '!=', 'Pensiunan'); 
                } else {
                    $q->where('pegawai.bagian', 'Pensiunan'); 
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

    private function buildTotalOperasional($dari = null, $sampai = null)
    {
        // ================= TOTAL SEMUA OBAT (Pegawai + Keluarga + Alkes) =================
        // Menggabungkan semua obat menjadi satu kategori "Obat-obatan"
        $totalObatObatan = DB::table('detail_resep')
            ->join('resep', 'detail_resep.id_resep', '=', 'resep.id_resep')
            ->join('pemeriksaan', 'resep.id_pemeriksaan', '=', 'pemeriksaan.id_pemeriksaan')
            ->join('obat', 'detail_resep.id_obat', '=', 'obat.id_obat')
            ->when($dari && $sampai, fn($q) =>
                $q->whereBetween(DB::raw('DATE(pemeriksaan.created_at)'), [$dari, $sampai])
            )
            ->sum(DB::raw('detail_resep.jumlah * obat.harga'));

        // ================= DOKTER POLI =================
        $dokterPoli = DB::table('pemeriksaan')
            ->join('pendaftaran', 'pemeriksaan.id_pendaftaran', '=', 'pendaftaran.id_pendaftaran')
            ->join('dokter', 'pendaftaran.id_dokter', '=', 'dokter.id_dokter')
            ->where('dokter.jenis_dokter', 'Dokter Poliklinik')
            ->when($dari && $sampai, fn($q) =>
                $q->whereBetween(DB::raw('DATE(pemeriksaan.created_at)'), [$dari, $sampai])
            )
            ->count() * 100000;

        // ================= DOKTER PERUSAHAAN =================
        $dokterPerusahaan = 0;
        if ($dari && $sampai) {
            $bulan = $this->hitungBulanGaji($dari, $sampai)->count();
            $jumlahDokter = DB::table('dokter')
                ->where('jenis_dokter', 'Dokter Perusahaan')
                ->count();
            $dokterPerusahaan = $bulan * $jumlahDokter * 8000000;
        }

        // Hitung Total Akhir
        $totalAll = $totalObatObatan + $dokterPerusahaan + $dokterPoli;

        // Susun data koleksi untuk view
        $data = collect([
            (object)['nama' => 'Obat-obatan', 'total' => $totalObatObatan],
            (object)['nama' => 'Dokter Perusahaan', 'total' => $dokterPerusahaan],
            (object)['nama' => 'Dokter Poliklinik', 'total' => $dokterPoli],
            (object)['nama' => 'TOTAL', 'total' => $totalAll],
        ]);

        return $data;
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

        // ===========================================================
        // ===== LAPORAN PEGAWAI / PENSIUNAN =========================
        // ===========================================================
    public function downloadExcelPegawaiPensiun(Request $request, $jenis)
    {
        if (!in_array($jenis,['pegawai','pensiun'])) abort(404);

        $dari   = $request->dari;
        $sampai = $request->sampai;

        $data = $this->buildPegawaiPensiunData($jenis, $dari, $sampai);
        $totalTagihan = $data->sum('total_obat_pasien');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1',$this->getJudul($jenis));
        $sheet->mergeCells('A1:Y1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $sheet->setCellValue(
            'A2',
            'Periode: '.(
                $dari && $sampai
                    ? \Carbon\Carbon::parse($dari)->translatedFormat('d F Y').' - '.
                    \Carbon\Carbon::parse($sampai)->translatedFormat('d F Y')
                    : 'Semua Data'
            )
        );
        $sheet->mergeCells('A2:Y2');

        $row = 4;
        $headers = [
            'No','Tanggal','Nama Pegawai','Umur','Bagian',
            'Nama Pasien','Hub. Kel','TD','GDP','GD 2 Jam',
            'GDS','AU','Chol','TG','Suhu','BB','TB',
            'Diagnosa','NB','Therapy','Jml Obat','Harga Obat',
            'Total Obat','Pemeriksa','Periksa Ke'
        ];

        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col.$row,$h);
            $sheet->getStyle($col.$row)->getFont()->setBold(true);
            $col++;
        }

        $row++;
        $no = 1;

        foreach ($data as $d) {
            $sheet->fromArray([
                $no++,
                $d->tanggal,
                $d->nama_pegawai,
                $d->umur,
                $d->bagian,
                $d->nama_pasien,
                $d->hub_kel,
                $d->sistol,
                $d->gd_puasa,
                $d->gd_duajam,
                $d->gd_sewaktu,
                $d->asam_urat,
                $d->chol,
                $d->tg,
                $d->suhu,
                $d->berat,
                $d->tinggi,
                $d->diagnosa,
                $d->nb,
                $d->nama_obat,
                $d->jumlah.' '.$d->satuan,
                $d->harga,
                $d->total_obat_pasien,
                $d->pemeriksa,
                $d->periksa_ke
            ],null,"A$row");
            $row++;
        }

        $row++;
        $sheet->setCellValue("A$row",'TOTAL TAGIHAN PERIODE');
        $sheet->mergeCells("A$row:W$row");
        $sheet->setCellValue("X$row",$totalTagihan);
        $sheet->getStyle("A$row:X$row")->getFont()->setBold(true);

        foreach (range('A','Y') as $c) $sheet->getColumnDimension($c)->setAutoSize(true);

        $writer = new Xlsx($spreadsheet);
        $path = storage_path('app/Laporan_'.$jenis.'.xlsx');
        $writer->save($path);

        return response()->download($path)->deleteFileAfterSend(true);
    }


        // ===========================================================
        // ===== LAPORAN OBAT ========================================
        // ===========================================================
    public function downloadExcelObat(Request $request)
    {
        $dari   = $request->dari;
        $sampai = $request->sampai;

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
            ->orderBy('pemeriksaan.created_at');

        if ($dari && $sampai) {
            $query->whereBetween(
                DB::raw('DATE(pemeriksaan.created_at)'),
                [$dari, $sampai]
            );
        }

        $data = $query->get();
        $totalTagihan = $data->sum('total');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Obat');

        // JUDUL
        $sheet->setCellValue('A1', 'Rekapan Penggunaan Obat');
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $sheet->setCellValue(
            'A2',
            'Periode: '.(
                $dari && $sampai
                    ? \Carbon\Carbon::parse($dari)->translatedFormat('d F Y').' - '.
                    \Carbon\Carbon::parse($sampai)->translatedFormat('d F Y')
                    : 'Semua Data'
            )
        );
        $sheet->mergeCells('A2:F2');

        // HEADER
        $row = 4;
        $headers = ['No','Nama Obat','Tanggal','Jumlah','Harga','Total'];
        $col = 'A';

        foreach ($headers as $h) {
            $sheet->setCellValue($col.$row, $h);
            $sheet->getStyle($col.$row)->getFont()->setBold(true);
            $col++;
        }

        // DATA
        $row++;
        $no = 1;

        foreach ($data as $item) {
            $sheet->setCellValue("A$row", $no++);
            $sheet->setCellValue("B$row", $item->nama_obat);
            $sheet->setCellValue(
                "C$row",
                \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d F Y')
            );
            $sheet->setCellValue("D$row", $item->jumlah);
            $sheet->setCellValue("E$row", $item->harga);
            $sheet->setCellValue("F$row", $item->total);
            $row++;
        }

        // TOTAL
        $row++;
        $sheet->setCellValue("A$row", 'TOTAL TAGIHAN PERIODE');
        $sheet->mergeCells("A$row:E$row");
        $sheet->getStyle("A$row")->getFont()->setBold(true);
        $sheet->setCellValue("F$row", $totalTagihan);
        $sheet->getStyle("F$row")->getFont()->setBold(true);

        foreach (range('A','F') as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $path = storage_path('app/Laporan_Obat.xlsx');
        $writer->save($path);

        return response()->download($path)->deleteFileAfterSend(true);
    }

        // ===========================================================
        // ===== LAPORAN DOKTER ======================================
        // ===========================================================
    public function downloadExcelDokter(Request $request)
    {
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
            $rows->whereBetween(DB::raw('DATE(pemeriksaan.created_at)'), [$dari, $sampai]);
        }

        $rows = $rows->get();

        $spreadsheet = new Spreadsheet();

        /* ================= SHEET 1 — DOKTER POLIKLINIK ================= */
        $sheetPoli = $spreadsheet->getActiveSheet();
        $sheetPoli->setTitle('Dokter Poliklinik');

        $row = 1;
        $sheetPoli->setCellValue("A$row", 'DOKTER POLIKLINIK (BAYAR PER PASIEN)');
        $sheetPoli->mergeCells("A$row:D$row");
        $sheetPoli->getStyle("A$row")->getFont()->setBold(true)->setSize(14);
        $row += 2;

        $dokterPoli = $rows->where('jenis_dokter','Dokter Poliklinik')->groupBy('id_dokter');
        $grandTotal = 0;

        foreach ($dokterPoli as $items) {
            $dokter = $items->first()->nama_dokter;
            $totalPasien = $items->count();
            $totalBiaya = $totalPasien * $tarifPoliklinik;
            $grandTotal += $totalBiaya;

            $sheetPoli->setCellValue("A$row",$dokter);
            $sheetPoli->mergeCells("A$row:D$row");
            $sheetPoli->getStyle("A$row")->getFont()->setBold(true);
            $row++;

            $sheetPoli->fromArray(['No','NIP','Nama Pasien','Tanggal Periksa'],null,"A$row");
            $sheetPoli->getStyle("A$row:D$row")->getFont()->setBold(true);
            $row++;

            $no = 1;
            foreach ($items as $p) {
                $sheetPoli->setCellValue("A$row",$no++);
                $sheetPoli->setCellValueExplicit("B$row",(string)$p->nip,DataType::TYPE_STRING);
                $sheetPoli->setCellValue("C$row",$p->nama_pasien);
                $sheetPoli->setCellValue("D$row",\Carbon\Carbon::parse($p->tanggal)->translatedFormat('d F Y'));
                $row++;
            }
            $row++;
        }

        $sheetPoli->setCellValue("A$row",'TOTAL DOKTER POLIKLINIK');
        $sheetPoli->mergeCells("A$row:C$row");
        $sheetPoli->setCellValue("D$row",$grandTotal);
        $sheetPoli->getStyle("A$row:D$row")->getFont()->setBold(true);

        foreach (range('A','D') as $c) $sheetPoli->getColumnDimension($c)->setAutoSize(true);

        /* ================= SHEET 2 — DOKTER PERUSAHAAN ================= */
        $sheetPer = $spreadsheet->createSheet();
        $sheetPer->setTitle('Dokter Perusahaan');

        $row = 1;
        $sheetPer->setCellValue("A$row",'DOKTER PERUSAHAAN (GAJI BULANAN)');
        $sheetPer->mergeCells("A$row:B$row");
        $sheetPer->getStyle("A$row")->getFont()->setBold(true)->setSize(14);
        $row += 2;

        $bulanGaji = $this->hitungBulanGaji($dari, $sampai);
        $dokterPerusahaan = $rows->where('jenis_dokter','Dokter Perusahaan')->groupBy('id_dokter');

        foreach ($dokterPerusahaan as $items) {
            $namaDokter = $items->first()->nama_dokter;

            $sheetPer->setCellValue("A$row",$namaDokter);
            $sheetPer->mergeCells("A$row:B$row");
            $sheetPer->getStyle("A$row")->getFont()->setBold(true);
            $row++;

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

            $sheetPer->setCellValue("A$row",'TOTAL');
            $sheetPer->setCellValue("B$row",$totalGaji);
            $sheetPer->getStyle("A$row:B$row")->getFont()->setBold(true);
            $row += 2;
        }

        foreach (range('A','B') as $c) $sheetPer->getColumnDimension($c)->setAutoSize(true);

        $writer = new Xlsx($spreadsheet);
        $path = storage_path('app/Laporan_Dokter.xlsx');
        $writer->save($path);

        return response()->download($path)->deleteFileAfterSend(true);
    }

    public function downloadExcelTotal(Request $request)
    {
        $dari   = $request->dari;
        $sampai = $request->sampai;

        $data = $this->buildTotalOperasional($dari,$sampai);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1','REKAPAN TOTAL OPERASIONAL');
        $sheet->mergeCells('A1:B1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $sheet->setCellValue('A2','Periode: '.(
            $dari && $sampai
                ? \Carbon\Carbon::parse($dari)->translatedFormat('d F Y').' - '.
                \Carbon\Carbon::parse($sampai)->translatedFormat('d F Y')
                : 'Semua Data'
        ));
        $sheet->mergeCells('A2:B2');

        $row = 4;
        $sheet->setCellValue("A$row",'Nama Biaya');
        $sheet->setCellValue("B$row",'Total');
        $sheet->getStyle("A$row:B$row")->getFont()->setBold(true);

        $row++;
        $grandTotal = 0;

        foreach ($data as $item) {
            $sheet->setCellValue("A$row", $item->nama);
            $sheet->setCellValue("B$row", $item->total);

            $grandTotal += $item->total;
            $row++;
        }

        $sheet->setCellValue("A$row",'TOTAL');
        $sheet->setCellValue("B$row",$grandTotal);
        $sheet->getStyle("A$row:B$row")->getFont()->setBold(true);

        foreach (['A','B'] as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $path = storage_path('app/Laporan_Total_Operasional.xlsx');
        $writer->save($path);

        return response()->download($path)->deleteFileAfterSend(true);
    }

}
