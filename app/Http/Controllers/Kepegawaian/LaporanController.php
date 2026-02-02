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

    private function buildPreviewPemeriksaan($jenis, $dari, $sampai)
    {
        $q = DB::table('pemeriksaan')
            ->join('pendaftaran','pemeriksaan.id_pendaftaran','=','pendaftaran.id_pendaftaran')
            ->join('pegawai','pendaftaran.nip','=','pegawai.nip')
            ->leftJoin('keluarga','pendaftaran.id_keluarga','=','keluarga.id_keluarga')
            ->leftJoin('dokter','pendaftaran.id_dokter','=','dokter.id_dokter')
            ->leftJoin('pemeriksa','pendaftaran.id_pemeriksa','=','pemeriksa.id_pemeriksa');

        // FILTER JENIS
        if ($jenis === 'pegawai') {
            $q->whereIn('pendaftaran.tipe_pasien',['pegawai','keluarga'])
            ->whereNotIn('pegawai.bagian',['Pensiunan','OJT']);
        }

        if ($jenis === 'pensiun') {
            $q->where('pegawai.bagian','Pensiunan');
        }

        if ($jenis === 'total') {
            // TANPA FILTER
        }

        if ($dari && $sampai) {
            $q->whereBetween(DB::raw('DATE(pemeriksaan.created_at)'), [$dari,$sampai]);
        }

        return $q->select(
            'pemeriksaan.id_pemeriksaan',
            DB::raw("COALESCE(keluarga.nama_keluarga, pegawai.nama_pegawai) as nama_pasien"),
            DB::raw("DATE(pemeriksaan.created_at) as tanggal"),
            DB::raw("COALESCE(dokter.nama, pemeriksa.nama_pemeriksa, '-') as nama_pemeriksa")
        )
        ->orderByDesc('pemeriksaan.created_at')
        ->limit(5)
        ->get();
    }

    public function index(Request $request)
    {

        $dari   = $request->dari;
        $sampai = $request->sampai;
        $rekapan = [
            'pegawai' => 'Rekap Pemeriksaan Pegawai',
            'pensiun' => 'Rekap Pemeriksaan Pensiunan',
            'dokter'  => 'Rekap Pemeriksaan Dokter',
            'obat'    => 'Rekap Penggunaan Obat',
            'total'   => 'Rekap Keseluruhan Operasional',
        ];

        $preview = [];



        // ================= DOKTER =================
    $preview['dokter'] = DB::table('pemeriksaan')
        ->join('pendaftaran','pemeriksaan.id_pendaftaran','=','pendaftaran.id_pendaftaran')
        ->join('dokter','pendaftaran.id_dokter','=','dokter.id_dokter')
        ->select(
            'dokter.nama as nama_dokter',
            'dokter.jenis_dokter',
            DB::raw('COUNT(*) as total_pasien')
        )
        ->when($dari && $sampai, fn($q) =>
            $q->whereBetween(
                DB::raw('DATE(pemeriksaan.created_at)'),
                [$dari, $sampai]
            )
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
                ->when($dari && $sampai, fn($q) =>
                    $q->whereBetween(
                        DB::raw('DATE(pemeriksaan.created_at)'),
                        [$dari, $sampai]
                    )
                )
            ->orderByDesc('pemeriksaan.created_at')
            ->limit(5)
            ->get();

        // ================= PEGAWAI DAN KELUARGA PEGAWAI =================
        $preview['pegawai'] = $this->buildPreviewPemeriksaan('pegawai', $dari, $sampai);

        // ================= PENSIUNAN DAN KELUARGA PENSIUNAN =================        
        $preview['pensiun'] = $this->buildPreviewPemeriksaan('pensiun', $dari, $sampai);

        // ================= SEMUA =================
        $preview['total']   = $this->buildPreviewPemeriksaan('total', $dari, $sampai);

        return view('kepegawaian.laporan.index', compact(
            'rekapan',
            'preview',
            'dari',
            'sampai'
));

    }

    /* =========================
       DETAIL + FILTER TANGGAL
    ========================= */
    public function detail(Request $request, $jenis)
    {
        $judul = $this->getJudul($jenis);
        $dari  = $request->dari;
        $sampai = $request->sampai;

        
        /* ================= PEGAWAI / PENSIUN / KESELURUHAN OPERASIONAL ================= */
        if (in_array($jenis, ['pegawai', 'pensiun', 'total'])) {
            $data = $this->buildPemeriksaan($jenis, $dari, $sampai);
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

                    'pendaftaran.nip',

                    DB::raw("COALESCE(keluarga.nama_keluarga, pegawai.nama_pegawai) as nama_pasien"),
                    DB::raw("DATE(pemeriksaan.created_at) as tanggal")
                );


            $rows = $query->get();

            // =======================
            // DOKTER POLIKLINIK
            // =======================
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
                    'pendaftaran.nip',
                    DB::raw("COALESCE(keluarga.nama_keluarga, pegawai.nama_pegawai) as nama_pasien"),
                    DB::raw("DATE(pemeriksaan.created_at) as tanggal")
                );

            //  FILTER TANGGAL
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
                ->map(function ($items) use ($gajiPerusahaan, $dari, $sampai) {
                    
                    // Hitung bulan gaji berdasarkan periode
                    $bulanGaji = $this->hitungBulanGaji($dari, $sampai);
                    $totalGaji = $bulanGaji->count() * $gajiPerusahaan;
                    
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
                        'bulanGaji'    => $bulanGaji,
                        'gaji'         => $totalGaji
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
                ->when($dari && $sampai, fn($q) =>
                    $q->whereBetween(
                        DB::raw('DATE(pemeriksaan.created_at)'),
                        [$dari, $sampai]
                    )
                )
                ->when($dari && $sampai, fn($q) =>
                    $q->whereBetween(
                        DB::raw('DATE(pemeriksaan.created_at)'),
                        [$dari, $sampai]
                    )
                )
                ->orderByDesc('pemeriksaan.created_at');

            $data = $query->get();
        }

        /* ================= TOTAL ================= */

        elseif ($jenis === 'total') {
            $data = $this->buildPemeriksaan('total', $dari, $sampai);
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
        // Jika tidak ada dari/sampai, return 1 bulan saja (bulan sekarang)
        if (!$dari || !$sampai) {
            return collect([now()->translatedFormat('F Y')]);
        }

        $start = \Carbon\Carbon::parse($dari);
        $end   = \Carbon\Carbon::parse($sampai);

        $bulan = collect();

        // Cari bulan pertama dari tanggal mulai
        $currentMonth = $start->copy()->startOfMonth();

        // Loop sampai melewati tanggal akhir
        while ($currentMonth->copy()->endOfMonth() <= $end->copy()->endOfMonth()) {
            
            // Tanggal gajian di bulan ini adalah tgl 25
            $tglGajian = $currentMonth->copy()->day(25);

            // Cek apakah tanggal gajian ada dalam rentang periode
            if ($tglGajian >= $start && $tglGajian <= $end) {
                $bulan->push($currentMonth->translatedFormat('F Y'));
            }

            // Pindah ke bulan berikutnya
            $currentMonth->addMonth();
        }

        // Jika tidak ada bulan gaji (misalnya periode terlalu pendek), return array kosong
        return $bulan;
    }


    /* =========================
       LOGIC UTAMA (DIPAKAI ULANG)
    ========================= */
    
    private function buildPemeriksaan($jenis, $dari, $sampai)
    {
        $query = DB::table('pemeriksaan')
            ->join('pendaftaran', 'pemeriksaan.id_pendaftaran', '=', 'pendaftaran.id_pendaftaran')
            ->join('pegawai', 'pendaftaran.nip', '=', 'pegawai.nip')
            ->leftJoin('keluarga', 'pendaftaran.id_keluarga', '=', 'keluarga.id_keluarga')
            ->leftJoin('dokter', 'pendaftaran.id_dokter', '=', 'dokter.id_dokter')
            ->leftJoin('pemeriksa', 'pendaftaran.id_pemeriksa', '=', 'pemeriksa.id_pemeriksa');

        $query->when($jenis !== 'total', function ($q) use ($jenis) {

            // ================= PEGAWAI =================
            if ($jenis === 'pegawai') {
                $q->whereIn('pendaftaran.tipe_pasien', ['pegawai', 'keluarga'])
                ->whereNotIn('pegawai.bagian', ['Pensiunan', 'OJT']);
            }

            // ================= PENSIUNAN =================
            if ($jenis === 'pensiun') {
                $q->where('pegawai.bagian', 'Pensiunan');
            }
        });




        $query->select(
            'pemeriksaan.id_pemeriksaan',
            DB::raw('DATE(pemeriksaan.created_at) as tanggal'),
            'pemeriksaan.created_at as full_created_at',

            'pegawai.nama_pegawai',
            'pegawai.nip',
            'pegawai.bagian',

            DB::raw('COALESCE(keluarga.nama_keluarga, pegawai.nama_pegawai) as nama_pasien'),
            DB::raw("COALESCE(keluarga.hubungan_keluarga, 'pegawai') as hub_kel"),
            DB::raw('TIMESTAMPDIFF(YEAR, COALESCE(keluarga.tgl_lahir, pegawai.tgl_lahir), CURDATE()) as umur'),

            'pemeriksaan.sistol',
            'pemeriksaan.diastol',
            'pemeriksaan.gd_puasa',
            'pemeriksaan.gd_duajam',
            'pemeriksaan.gd_sewaktu',
            'pemeriksaan.asam_urat',
            'pemeriksaan.chol',
            'pemeriksaan.tg',
            'pemeriksaan.suhu',
            'pemeriksaan.berat',
            'pemeriksaan.tinggi',

            DB::raw("COALESCE(dokter.nama, pemeriksa.nama_pemeriksa, '-') as pemeriksa")
        );


        if ($dari && $sampai) {
            $query->whereBetween(
                DB::raw('DATE(pemeriksaan.created_at)'),
                [$dari, $sampai]
            );
        }


        $raw = $query->orderBy('pemeriksaan.created_at', 'asc')->get();
        $ids = $raw->pluck('id_pemeriksaan');

        // Ambil Map Diagnosa, NB, dan Obat (Kode kamu yang sudah ada tetap sama)
        $diagnosaMap = DB::table('detail_pemeriksaan_penyakit as dpp')
            ->join('diagnosa as d','d.id_diagnosa','=','dpp.id_diagnosa')
            ->select(
                'dpp.id_pemeriksaan',
                'd.diagnosa',
                'dpp.id_nb'
            )
            ->whereIn('dpp.id_pemeriksaan',$ids)
            ->get()
            ->groupBy('id_pemeriksaan');


        $obatMap = DB::table('resep')
            ->join('detail_resep','resep.id_resep','=','detail_resep.id_resep')
            ->join('obat','detail_resep.id_obat','=','obat.id_obat')
            ->select(
                'resep.id_pemeriksaan',
                'obat.nama_obat',
                'detail_resep.jumlah',
                'detail_resep.satuan',
                'obat.harga'
            )
            ->whereIn('resep.id_pemeriksaan',$ids)
            ->get()
            ->groupBy('id_pemeriksaan');


        $final = collect();

        foreach ($raw as $r) {
            $id = $r->id_pemeriksaan;

            $periksaKe = DB::table('pemeriksaan')
                ->join('pendaftaran', 'pemeriksaan.id_pendaftaran', '=', 'pendaftaran.id_pendaftaran')
                ->where('pendaftaran.nip', $r->nip) 
                ->where('pemeriksaan.created_at', '<=', $r->full_created_at) 
                ->where(function($q) {
                    // Syarat: Salah satu kolom lab/kesehatan ini terisi > 0
                    $q->where('pemeriksaan.gd_puasa', '>', 0)
                    ->orWhere('pemeriksaan.gd_duajam', '>', 0)
                    ->orWhere('pemeriksaan.gd_sewaktu', '>', 0)
                    ->orWhere('pemeriksaan.asam_urat', '>', 0)
                    ->orWhere('pemeriksaan.chol', '>', 0)
                    ->orWhere('pemeriksaan.tg', '>', 0);
                })
                ->count();

            // Fallback jika pemeriksaan saat ini hanya tensi (tidak masuk hitungan di atas), 
            // kita tetap beri angka 1 atau biarkan mengikuti counter riwayat terakhir
            $displayPeriksaKe = ($periksaKe > 0) ? $periksaKe : 1;

            // Data pendukung baris (Diagnosa & Obat)
            $diag = $diagnosaMap[$id] ?? collect([(object)['diagnosa'=>'-']]);
            $obat = $obatMap[$id] ?? collect([(object)['nama_obat'=>'-','jumlah'=>'-','satuan'=>'','harga'=>0]]);

            $max = max($diag->count(), $obat->count());
            $totalObat = $obat->sum(fn($o)=>((int)$o->jumlah*(int)$o->harga));

            for ($i=0; $i<$max; $i++) {
                $row = clone $r;

                $row->diagnosa = $diag[$i]->diagnosa ?? '-';
                $row->nb       = $diag[$i]->id_nb ?? '-';

                $o = $obat[$i] ?? null;

                $row->nama_obat    = $o->nama_obat ?? '-';
                $row->jumlah = (int) ($o->jumlah ?? 0);
                $row->satuan       = $o ? $o->satuan : '-';
                $row->harga_satuan = (int) ($o->harga ?? 0);

                // subtotal per baris (AMAN)
                $row->subtotal_obat = $row->jumlah * $row->harga_satuan;


                // total per pasien (row pertama saja)
                $row->total_obat_pasien = ($i === 0) ? $totalObat : null;

                // penanda row pertama (dipakai blade git)
                $row->is_first = ($i === 0);

                // nama pemeriksa
                $row->nama_pemeriksa = $r->nama_pemeriksa ?? '-';

                // periksa ke
                $row->periksa_ke = $displayPeriksaKe;


                $final->push($row);
            }
        }

        return $final;
    }

    
    private function getJudul($jenis)
    {
        return match ($jenis) {
            'pegawai'  => 'Rekap Pemeriksaan Pegawai',
            'pensiun'  => 'Rekap Pemeriksaan Pensiunan',
            'dokter'   => 'Rekap Pemeriksaan Dokter',
            'obat'     => 'Rekap Penggunaan Obat',
            'total'    => 'Rekap Keseluruhan Operasional',
            default    => 'Rekap Laporan',
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

        $data = $this->buildPemeriksaan($jenis, $dari, $sampai);
        $totalTagihan = $data->sum('total_obat_pasien');

        $grouped = $data->groupBy('id_pemeriksaan');

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
            'Diagnosa','NB','Therapy','Jml Obat','Harga Obat', 'Subtotal Obat',
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

        foreach ($grouped as $rows) {

            $first = $rows->first();
            $totalObat = $rows->sum('total_obat_pasien');

            foreach ($rows as $i => $r) {

                $sheet->fromArray([
                    $i === 0 ? $no++ : '',
                    $i === 0 ? $first->tanggal : '',
                    $i === 0 ? $first->nama_pegawai : '',
                    $i === 0 ? $first->umur : '',
                    $i === 0 ? $first->bagian : '',
                    $i === 0 ? $first->nama_pasien : '',
                    $i === 0 ? $first->hub_kel : '',
                    $i === 0 ? $first->sistol : '',
                    $i === 0 ? $first->gd_puasa : '',
                    $i === 0 ? $first->gd_duajam : '',
                    $i === 0 ? $first->gd_sewaktu : '',
                    $i === 0 ? $first->asam_urat : '',
                    $i === 0 ? $first->chol : '',
                    $i === 0 ? $first->tg : '',
                    $i === 0 ? $first->suhu : '',
                    $i === 0 ? $first->berat : '',
                    $i === 0 ? $first->tinggi : '',
                    $r->diagnosa,
                    $r->nb,
                    $r->nama_obat,
                    $r->jumlah.' '.$r->satuan,
                    $r->harga_satuan ?? $r->harga ?? 0,
                    $r->subtotal_obat ?? '-',
                    $i === 0 ? $first->total_obat_pasien : '',
                    $i === 0 ? $first->pemeriksa : '',
                    $i === 0 ? $first->periksa_ke : '',
                ], null, "A$row");

                $row++;
            }
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
        $dari   = $request->query('dari');
        $sampai = $request->query('sampai');

        $data = $this->buildPemeriksaan('total', $dari, $sampai);
        $totalTagihan = $data->sum('total_obat_pasien');
        
        $grouped = $data->groupBy('id_pemeriksaan');

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header Judul
        $sheet->setCellValue('A1', 'LAPORAN OPERASIONAL KESELURUHAN (DETAIL)');
        $sheet->mergeCells('A1:Y1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ]);

        // Baris Periode
        $periodeText = ($dari && $sampai) 
            ? \Carbon\Carbon::parse($dari)->translatedFormat('d F Y') . ' - ' . \Carbon\Carbon::parse($sampai)->translatedFormat('d F Y')
            : 'Semua Data';
        $sheet->setCellValue('A2', 'Periode: ' . $periodeText);
        $sheet->mergeCells('A2:Y2');

        // Header Tabel - SAMA PERSIS dengan Pegawai/Pensiun
        $row = 4;
        $headers = [
            'No', 'Tanggal', 'Nama Pegawai', 'Bagian',
            'Nama Pasien', 'Umur', 'Hub. Kel', 'TD', 'GDP', 'GD 2 Jam',
            'GDS', 'AU', 'Chol', 'TG', 'Suhu', 'BB', 'TB',
            'Diagnosa', 'NB', 'Therapy', 'Jml Obat', 'Harga Obat', 'Subtotal Obat',
            'Total Obat', 'Pemeriksa', 'Periksa Ke'
        ];

        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . $row, $h);
            $col++;
        }
        
        // Style Header Biru
        $sheet->getStyle('A4:Y4')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
            'alignment' => ['horizontal' => 'center'],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
        ]);

        $row++;
        $no = 1;

        foreach ($grouped as $rows) {
            $first = $rows->first();
            $totalObat = $rows->sum('total_obat_pasien');

            foreach ($rows as $i => $r) {
                $sheet->fromArray([
                    $i === 0 ? $no++ : '',
                    $i === 0 ? $first->tanggal : '',
                    $i === 0 ? $first->nama_pegawai : '',
                    $i === 0 ? $first->umur : '',
                    $i === 0 ? $first->bagian : '',
                    $i === 0 ? $first->nama_pasien : '',
                    $i === 0 ? $first->hub_kel : '',
                    $i === 0 ? $first->sistol : '',
                    $i === 0 ? $first->gd_puasa : '',
                    $i === 0 ? $first->gd_duajam : '',
                    $i === 0 ? $first->gd_sewaktu : '',
                    $i === 0 ? $first->asam_urat : '',
                    $i === 0 ? $first->chol : '',
                    $i === 0 ? $first->tg : '',
                    $i === 0 ? $first->suhu : '',
                    $i === 0 ? $first->berat : '',
                    $i === 0 ? $first->tinggi : '',
                    $r->diagnosa,
                    $r->nb,
                    $r->nama_obat,
                    $r->jumlah.' '.$r->satuan,
                    $r->harga_satuan ?? $r->harga ?? 0,
                    $r->subtotal_obat ?? '-',
                    $i === 0 ? $first->total_obat_pasien : '',
                    $i === 0 ? $first->pemeriksa : '',
                    $i === 0 ? $first->periksa_ke : '',
                ], null, "A$row");

                // Border untuk setiap baris data
                $sheet->getStyle("A$row:Y$row")->getBorders()->getAllBorders()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                $row++;
            }
        }

        // Total Tagihan
        $row++;
        $sheet->setCellValue("A$row", 'TOTAL TAGIHAN PERIODE');
        $sheet->mergeCells("A$row:W$row");
        $sheet->setCellValue("X$row", $totalTagihan);
        $sheet->getStyle("A$row:X$row")->getFont()->setBold(true);
        
        // Format Rupiah untuk Total
        $sheet->getStyle("X$row")->getNumberFormat()->setFormatCode('#,##0');

        // Auto Size Kolom
        foreach (range('A', 'Y') as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }

        // Download
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = 'Laporan_Detail_Operasional_Keseluruhan_' . date('Ymd_His') . '.xlsx';
        
        return response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function applyTanggal($query, $request)
    {
        if ($request->filled('dari')) {
            $query->whereDate('pemeriksaan.tanggal_periksa', '>=', $request->dari);
        }

        if ($request->filled('sampai')) {
            $query->whereDate('pemeriksaan.tanggal_periksa', '<=', $request->sampai);
        }

        return $query;
    }

}