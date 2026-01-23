<?php

namespace App\Http\Controllers\Kepegawaian;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;





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
        $dari   = $request->dari;
        $sampai = $request->sampai;

        // ambil data SAMA PERSIS kaya halaman
        $data = $this->buildPegawaiPensiunData($jenis, $dari, $sampai);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // =====================
        // HEADER
        // =====================
        $sheet->setCellValue('A1', 'Rekapan Pemeriksaan Pegawai');
        $sheet->mergeCells('A1:Z1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $sheet->setCellValue(
            'A2',
            'Periode: ' . ($dari && $sampai
                ? \Carbon\Carbon::parse($dari)->translatedFormat('d F Y').' - '.\Carbon\Carbon::parse($sampai)->translatedFormat('d F Y')
                : 'Semua Data')
        );
        $sheet->mergeCells('A2:Z2');

        // =====================
        // HEADER KOLOM (SAMA KAYA UI)
        // =====================
        $row = 4;
        $headers = [
            'No','Tanggal','Nama Pegawai','Umur','Bagian',
            'Nama Pasien','Hub. Kel','TD','GDP','GD 2 Jam',
            'GDS','AU','Chol','TG','Suhu','BB','TB',
            'Diagnosa','NB','Therapy',
            'Jml Obat','Harga Obat','Total Obat',
            'Pemeriksa','Periksa Ke'
        ];

        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col.$row, $h);
            $sheet->getStyle($col.$row)->getFont()->setBold(true);
            $col++;
        }

        // =====================
        // DATA + MERGE
        // =====================
        $row = 5;
        $no  = 1;

        $grouped = $data->groupBy('id_pemeriksaan');

        foreach ($grouped as $rows) {
            $startRow = $row;
            $rowspan  = $rows->count();

            foreach ($rows as $i => $d) {
                if ($i === 0) {
                    $sheet->setCellValue('A'.$row, $no++);
                    $sheet->setCellValue('B'.$row, $d->tanggal);
                    $sheet->setCellValue('C'.$row, $d->nama_pegawai);
                    $sheet->setCellValue('D'.$row, $d->umur);
                    $sheet->setCellValue('E'.$row, $d->bagian);
                    $sheet->setCellValue('F'.$row, $d->nama_pasien);
                    $sheet->setCellValue('G'.$row, $d->hub_kel);
                    $sheet->setCellValue('H'.$row, $d->sistol);
                    $sheet->setCellValue('I'.$row, $d->gd_puasa);
                    $sheet->setCellValue('J'.$row, $d->gd_duajam);
                    $sheet->setCellValue('K'.$row, $d->gd_sewaktu);
                    $sheet->setCellValue('L'.$row, $d->asam_urat);
                    $sheet->setCellValue('M'.$row, $d->chol);
                    $sheet->setCellValue('N'.$row, $d->tg);
                    $sheet->setCellValue('O'.$row, $d->suhu);
                    $sheet->setCellValue('P'.$row, $d->berat);
                    $sheet->setCellValue('Q'.$row, $d->tinggi);
                }

                // ❗ TIDAK DI-MERGE
                $sheet->setCellValue('R'.$row, $d->diagnosa);
                $sheet->setCellValue('S'.$row, $d->nb);
                $sheet->setCellValue('T'.$row, $d->nama_obat);
                $sheet->setCellValue('U'.$row, $d->jumlah.' '.$d->satuan);
                $sheet->setCellValue('V'.$row, $d->harga);

                if ($i === 0) {
                    $sheet->setCellValue('W'.$row, $d->total_obat_pasien);
                    $sheet->setCellValue('X'.$row, $d->pemeriksa);
                    $sheet->setCellValue('Y'.$row, $d->periksa_ke);
                }

                $row++;
            }

            // =====================
            // MERGE PER PEMERIKSAAN
            // =====================
            if ($rowspan > 1) {
                foreach (['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','W','X','Y'] as $col) {
                    $sheet->mergeCells(
                        $col.$startRow . ':' . $col.($row - 1)
                    );
                }
            }
        }


        // autosize kolom
        foreach (range('A','Y') as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }

        // =====================
        // OUTPUT
        // =====================
        $writer = new Xlsx($spreadsheet);
        $filename = 'Rekapan_Pemeriksaan_Pegawai.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }



}
