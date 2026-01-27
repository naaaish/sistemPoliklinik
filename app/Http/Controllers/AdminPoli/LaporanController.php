<?php

namespace App\Http\Controllers\AdminPoli;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanMultiTipeExport;
use App\Exports\LaporanSingleTipeExport;
use Carbon\Carbon;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $tipe = $request->input('tipe', 'pegawai'); // pegawai | pensiunan | poliklinik
        $from = $request->input('from');
        $to   = $request->input('to');

        if (!$from || !$to) {
            $from = $from ?: now()->toDateString();
            $to   = $to   ?: now()->toDateString();
        }

        $perPage = $request->get('per_page', 10);
        $allowed = ['10','25','50','100','all'];
        if (!in_array((string)$perPage, $allowed)) $perPage = 10;

        // Ambil daftar NIP yang ada di pendaftaran pada range tanggal
        $nipQuery = DB::table('pendaftaran as p')
            ->leftJoin('pegawai as pg', 'pg.nip', '=', 'p.nip')
            ->whereBetween('p.tanggal', [$from, $to]);

        $poliklinikCond = "(pg.nip = '001' OR LOWER(COALESCE(pg.nama_pegawai,'')) = 'poliklinik')";

        if ($tipe === 'poliklinik') {
            $nipQuery->whereRaw($poliklinikCond);
        } elseif ($tipe === 'pensiunan') {
            $nipQuery->whereRaw("LOWER(COALESCE(pg.bagian,'')) = 'pensiunan'")
                    ->whereRaw("NOT $poliklinikCond");
        } else {
            $nipQuery->whereRaw("LOWER(COALESCE(pg.bagian,'')) NOT IN ('pensiunan')")
                    ->whereRaw("NOT $poliklinikCond");
        }

        $nips = $nipQuery
            ->select([
                'p.nip',
                DB::raw("MAX(p.tanggal) as last_tanggal"),
                DB::raw("COALESCE(pg.nama_pegawai,'-') as nama_pegawai"),
            ])
            ->groupBy('p.nip', 'pg.nama_pegawai')
            ->orderByDesc('last_tanggal')
            ->paginate($perPage)
            ->appends($request->query());

        if ($perPage === 'all') {
            $nips = $nipQuery->get();
            $totalNips = $nips->count();
            $currentPage = 1;
            $perPageInt = $totalNips > 0 ? $totalNips : 1;
            $offsetNo = 0;
        } else {
            $nips = $nipQuery->paginate((int)$perPage)->appends($request->query());
            $totalNips = $nips->total();
            $currentPage = $nips->currentPage();
            $perPageInt = $nips->perPage();
            $offsetNo = ($currentPage - 1) * $perPageInt;
        }
        // Build ringkas pasien+hubkel per NIP (di range tanggal)
        $itemsArr = [];
        
        foreach ($nips as $idx => $row) {
            $nip = $row->nip;

            $pasienRows = DB::table('pendaftaran as p')
                ->leftJoin('pegawai as pg', 'pg.nip', '=', 'p.nip')
                ->leftJoin('keluarga as k', 'k.id_keluarga', '=', 'p.id_keluarga')
                ->whereBetween('p.tanggal', [$from, $to])
                ->where('p.nip', $nip);

            $poliklinikCond = "(pg.nip = '001' OR LOWER(COALESCE(pg.nama_pegawai,'')) = 'poliklinik')";

            if ($tipe === 'poliklinik') {
                $pasienRows->whereRaw($poliklinikCond);
            } elseif ($tipe === 'pensiunan') {
                $pasienRows->whereRaw("LOWER(COALESCE(pg.bagian,'')) = 'pensiunan'")
                        ->whereRaw("NOT $poliklinikCond");
            } else {
                $pasienRows->whereRaw("LOWER(COALESCE(pg.bagian,'')) <> 'pensiunan'")
                        ->whereRaw("NOT $poliklinikCond");
            }

            $pairs = $pasienRows->select([
                DB::raw("CASE
                    WHEN $poliklinikCond THEN '-'
                    WHEN p.id_keluarga IS NULL THEN 'YBS'
                    ELSE COALESCE(k.nama_keluarga,'-')
                END as nama_pasien"),
                DB::raw("CASE
                    WHEN $poliklinikCond THEN '-'
                    WHEN p.id_keluarga IS NULL THEN 'YBS'
                    WHEN k.hubungan_keluarga = 'pasangan' THEN 'Pasangan'
                    WHEN k.hubungan_keluarga = 'anak' THEN 'Anak'
                    ELSE '-'
                END as hub_kel"),
            ])->get();

            $namaList = [];
            $hubList  = [];
            foreach ($pairs as $p) {
                $namaList[] = $p->nama_pasien ?? '-';
                $hubList[]  = $p->hub_kel ?? '-';
            }
            $namaList = array_values(array_unique($namaList));
            $hubList  = array_values(array_unique($hubList));

            // Biar ga kepanjangan: tampilkan max 3, sisanya jadi "+n"
            $namaShown = array_slice($namaList, 0, 3);
            $hubShown  = array_slice($hubList, 0, 3);

            $namaTxt = implode("\n", $namaShown);
            $hubTxt  = implode("\n", $hubShown);

            if (count($namaList) > 3) $namaTxt .= "\n(+".(count($namaList)-3).")";
            if (count($hubList) > 3)  $hubTxt  .= "\n(+".(count($hubList)-3).")";

            $itemsArr[] = [
                'no' => $offsetNo + $idx + 1,
                'tanggal' => $row->last_tanggal ?? '-',
                'nama' => $row->nama_pegawai ?? '-',
                'nip' => $nip ?? '-',
                'nama_pasien' => $namaTxt ?: '-',
                'hub_kel' => $hubTxt ?: '-',
            ];
        }
        if ($perPage === 'all') {
            $items = collect($itemsArr);
        } else {
            $items = new \Illuminate\Pagination\LengthAwarePaginator(
                $itemsArr,
                $totalNips,
                $perPageInt,
                $currentPage,
                [
                    'path'  => $request->url(),
                    'query' => $request->query(),
                ]
            );
        }

        return view('adminpoli.laporan.index', [
            'tipe' => $tipe,
            'from' => $from,
            'to' => $to,
            'perPage' => $perPage,
            'nips' => $nips,
            'items' => $items,
        ]);
    }

    public function preview(Request $request)
    {
        $tipe = $request->input('tipe', 'pegawai');
        $from = $request->input('from') ?: now()->toDateString();
        $to   = $request->input('to')   ?: now()->toDateString();

        $visits = $this->getVisits($from, $to, $tipe);
        $rows   = $this->buildReportRows($visits, $tipe);

        return view('adminpoli.laporan.preview', [
            'tipe'  => $tipe,
            'from'  => $from,
            'to'    => $to,
            'rows'  => $rows,
            'count' => count($rows),
        ]);
    }

    private function getVisits(string $from, string $to, string $tipe = 'pegawai', ?string $nip = null)
    {
        $q = DB::table('pendaftaran as p')
            ->leftJoin('pegawai as pg', 'pg.nip', '=', 'p.nip')
            ->leftJoin('keluarga as k', 'k.id_keluarga', '=', 'p.id_keluarga')
            ->leftJoin('pemeriksaan as pm', 'pm.id_pendaftaran', '=', 'p.id_pendaftaran')
            ->leftJoin('dokter as d', 'd.id_dokter', '=', 'p.id_dokter')
            ->leftJoin('pemeriksa as pr', 'pr.id_pemeriksa', '=', 'p.id_pemeriksa')
            ->whereBetween('p.tanggal', [$from, $to]);

        $poliklinikCond = "(pg.nip = '001' OR LOWER(COALESCE(pg.nama_pegawai,'')) = 'poliklinik')";

        if ($tipe === 'poliklinik') {
            $q->whereRaw($poliklinikCond);
        } elseif ($tipe === 'pensiunan') {
            $q->whereRaw("LOWER(COALESCE(pg.bagian,'')) = 'pensiunan'")
            ->whereRaw("NOT $poliklinikCond");
        } else {
            $q->whereRaw("LOWER(COALESCE(pg.bagian,'')) <> 'pensiunan'")
            ->whereRaw("NOT $poliklinikCond");
        }

        if ($nip) $q->where('p.nip', $nip);

        $visits = $q->select([
                'p.id_pendaftaran',
                'p.tanggal',
                'p.tipe_pasien',
                'p.nip',
                'p.id_keluarga',
                'pg.tgl_lahir as pegawai_tgl_lahir',
                'k.tgl_lahir as keluarga_tgl_lahir',

                'pm.id_pemeriksaan',

                'pm.sistol',
                'pm.diastol',
                'pm.nadi',
                'pm.gd_puasa',
                'pm.gd_duajam',
                'pm.gd_sewaktu',
                'pm.asam_urat',
                'pm.chol',
                'pm.tg',
                'pm.suhu',
                'pm.berat',
                'pm.tinggi',

                DB::raw("COALESCE(pg.nama_pegawai,'-') as nama_pegawai"),
                DB::raw("COALESCE(pg.bagian,'-') as bagian"),
                
                DB::raw("CASE
                    WHEN $poliklinikCond THEN '-'
                    WHEN p.id_keluarga IS NULL THEN 'YBS'
                    ELSE COALESCE(k.nama_keluarga,'-')
                END as nama_pasien"),

                DB::raw("CASE
                    WHEN $poliklinikCond THEN '-'
                    WHEN p.id_keluarga IS NULL THEN 'YBS'
                    WHEN k.hubungan_keluarga = 'pasangan' THEN 'Pasangan'
                    WHEN k.hubungan_keluarga = 'anak' THEN 'Anak'
                    ELSE '-'
                END as hub_kel"),

                DB::raw("COALESCE(d.nama, pr.nama_pemeriksa, '-') as pemeriksa"),
            ])
            ->orderBy('p.tanggal')
            ->orderBy('p.id_pendaftaran')
            ->get();

        foreach ($visits as $v) {
            $v->diagnosa_list = DB::table('detail_pemeriksaan_penyakit as dp')
                ->join('diagnosa as d', 'd.id_diagnosa', '=', 'dp.id_diagnosa')
                ->where('dp.id_pemeriksaan', $v->id_pemeriksaan)
                ->pluck('d.diagnosa')
                ->toArray();

            $v->diagnosa_k3_items = DB::table('detail_pemeriksaan_diagnosa_k3 as dk')
                ->join('diagnosa_k3 as k3', 'k3.id_nb', '=', 'dk.id_nb')
                ->where('dk.id_pemeriksaan', $v->id_pemeriksaan)
                ->select(['k3.id_nb', 'k3.nama_penyakit'])
                ->get()
                ->toArray();

            $v->saran_list = DB::table('detail_pemeriksaan_saran as ds')
                ->join('saran as s', 's.id_saran', '=', 'ds.id_saran')
                ->where('ds.id_pemeriksaan', $v->id_pemeriksaan)
                ->pluck('s.saran')
                ->toArray();

            $v->obat_list = DB::table('resep as r')
                ->join('detail_resep as dr', 'dr.id_resep', '=', 'r.id_resep')
                ->join('obat as o', 'o.id_obat', '=', 'dr.id_obat')
                ->where('r.id_pemeriksaan', $v->id_pemeriksaan)
                ->select(['o.nama_obat', 'o.harga', 'dr.jumlah', 'dr.satuan', 'dr.subtotal'])
                ->get()
                ->toArray();
        }

        return $visits;
    }
    
    private function buildReportRows($visits, string $tipe): array
    {
        $rows = [];
        $no = 1;
        $counter = [];

        foreach ($visits as $v) {

            $isPegawai = ($v->tipe_pasien ?? '') === 'pegawai';
            $isPensiunan = false; // kalau ada kolom pensiun, isi di sini
            $allowPeriksaKe = $isPegawai && !$isPensiunan;

            $s = ($v->sistol !== null && $v->sistol !== '') ? $v->sistol : '-';
            $d = ($v->diastol !== null && $v->diastol !== '') ? $v->diastol : '-';
            $n = ($v->nadi !== null && $v->nadi !== '') ? $v->nadi : '-';

            // periksa ke: hanya pegawai + hanya jika ada tes darah
            $hasLab = $this->hasBloodTest($v);
            $periksaKe = '-';
            if ($allowPeriksaKe && $hasLab) {
                $counter[$v->nip] = ($counter[$v->nip] ?? 0) + 1;
                $periksaKe = $counter[$v->nip];
            }

            $umur = null;
            if ($tipe === 'poliklinik') {
                $umur = null;
            }
            elseif (!empty($v->id_keluarga)) {
                // pasien keluarga
                $umur = $this->hitungUmur($v->keluarga_tgl_lahir);
            }
            else {
                // pasien pegawai
                $umur = $this->hitungUmur($v->pegawai_tgl_lahir);
            }

            $diagUmum = $v->diagnosa_list ?? [];
            $nbItems = $v->diagnosa_k3_items ?? [];
            $obatList = $v->obat_list ?? [];

            if (count($diagUmum) === 0) $diagUmum = ['-'];
            if (count($nbItems) === 0) $nbItems = []; 
            
            $totalHarga = 0;
            foreach ($obatList as $ob) {
                $totalHarga += (int)($ob->subtotal ?? 0);
            }
            
            $maxLines = max(count($diagUmum), count($nbItems), count($obatList), 1);

            for ($i = 0; $i < $maxLines; $i++) {
                $isFirst = ($i === 0);

                // diagnosa hanya dari tabel diagnosa
                $diag = $diagUmum[$i] ?? ($isFirst ? '-' : '');
                
                // NB hanya dari diagnosa_k3 (zip by index)
                $nbLine = isset($nbItems[$i]) ? ($nbItems[$i]->id_nb ?? '-') : '-';

                $ob = $obatList[$i] ?? null;

                $saranText = '-';
                if (!empty($v->saran_list) && count($v->saran_list) > 0) {
                    $items = [];
                    foreach ($v->saran_list as $sr) {
                        $items[] = $sr->nama_saran ?? (string)$sr;
                    }
                    $saranText = implode("\n", $items);
                }

                $rows[] = [
                    'NO' => $isFirst ? $no : '',
                    'TANGGAL' => $isFirst ? ($v->tanggal ?? '-') : '',
                    'NAMA' => $isFirst ? ($v->nama_pegawai ?? '-') : '',
                    'UMUR' => $isFirst ? $umur : '',
                    'BAGIAN' => $isFirst ? ($v->bagian ?? '-') : '',
                    'NAMA_PASIEN' => $isFirst ? ($v->nama_pasien ?? '-') : '',
                    'HUB_KEL' => $isFirst ? ($v->hub_kel ?? '-') : '',
                    'S' => $isFirst ? ($v->sistol ?? '-') : '',
                    'D' => $isFirst ? ($v->diastol ?? '-') : '',
                    'N' => $isFirst ? ($v->nadi ?? '-') : '',

                    'GDP' => $isFirst ? ($v->gd_puasa ?? '-') : '',
                    'GD_2JAM_PP' => $isFirst ? ($v->gd_duajam ?? '-') : '',
                    'GDS' => $isFirst ? ($v->gd_sewaktu ?? '-') : '',
                    'AU' => $isFirst ? ($v->asam_urat ?? '-') : '',
                    'CHOL' => $isFirst ? ($v->chol ?? '-') : '',
                    'TG' => $isFirst ? ($v->tg ?? '-') : '',
                    'SUHU' => $isFirst ? ($v->suhu ?? '-') : '',
                    'BB' => $isFirst ? ($v->berat ?? '-') : '',
                    'TB' => $isFirst ? ($v->tinggi ?? '-') : '',

                    'DIAGNOSA' => $diag !== '' ? $diag : ($isFirst ? '-' : ''),

                    'TERAPHY' => $ob ? ($ob->nama_obat ?? '-') : ($isFirst ? '-' : ''),
                    'JUMLAH_OBAT' => $ob ? trim(($ob->jumlah ?? 0) . ' ' . ($ob->satuan ?? '')) : ($isFirst ? '-' : ''),
                    'HARGA_OBAT_SATUAN' => $ob ? ($ob->harga ?? 0) : ($isFirst ? '-' : ''),
                    'TOTAL_HARGA_OBAT' => $isFirst ? ($totalHarga ?: '-') : '',
                    'SARAN' => $isFirst ? $saranText : '',
                    'PEMERIKSA' => $isFirst ? ($v->pemeriksa ?? '-') : '',

                    'NB' => $isPegawai ? ($nbLine ?: '-') : '',
                    'PERIKSA_KE' => $isPegawai ? ($isFirst ? $periksaKe : '') : '',
                ];
            }

            $no++;
        }

        return $rows;
    }

    public function hasBloodTest($v): bool
    {
        $fields = [
            $v->gd_puasa ?? null,
            $v->gd_duajam ?? null,
            $v->gd_sewaktu ?? null,
            $v->asam_urat ?? null,
            $v->chol ?? null,
            $v->tg ?? null,
        ];

        foreach ($fields as $x) {
            if ($x !== null && $x !== '' && is_numeric($x) && (float)$x > 0) return true;
        }
        return false;
    }

    public function exportExcel(Request $request)
    {
        // === untuk PREVIEW: 1 sheet sesuai tipe yg dipilih ===
        $tipe = $request->input('tipe', 'pegawai');
        $from = $request->input('from') ?: now()->toDateString();
        $to   = $request->input('to')   ?: now()->toDateString();

        // ambil semua kunjungan sesuai tipe + rentang tanggal
        $visits = $this->getVisits($from, $to, $tipe, null);   // nip = null (semua)
        $rows   = $this->buildReportRows($visits, $tipe);

        $filename = "Laporan_{$tipe}_{$from}_sd_{$to}.xlsx";
        return Excel::download(new LaporanSingleTipeExport($tipe, $from, $to, $rows), $filename);
    }

    public function exportExcelAll(Request $request)
    {
        // === untuk INDEX: 1 file, 3 sheet: pegawai/pensiunan/poliklinik ===
        $from = $request->input('from') ?: now()->toDateString();
        $to   = $request->input('to')   ?: now()->toDateString();

        $types = ['pegawai', 'pensiunan', 'poliklinik'];
        $data = [];

        foreach ($types as $tipe) {
            $visits = $this->getVisits($from, $to, $tipe, null);
            $rows = $this->buildReportRows($visits, $tipe);

            $data[] = [
                'sheet' => ucfirst($tipe),
                'tipe'  => $tipe,
                'from'  => $from,
                'to'    => $to,
                'rows'  => $rows,
            ];
        }

        $filename = "Laporan_ALL_{$from}_sd_{$to}.xlsx";
        return Excel::download(new LaporanMultiTipeExport($data), $filename);
    }

    private function hitungUmur($tglLahir)
    {
        if (!$tglLahir) return null;
        return Carbon::parse($tglLahir)->age;
    }

}
