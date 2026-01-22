<?php

namespace App\Http\Controllers\AdminPoli;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        $perPage = (int) $request->input('per_page', 5);
        $allowedPerPage = [5, 10, 25, 100];
        if (!in_array($perPage, $allowedPerPage)) $perPage = 5;

        /**
         * INDEX = 1 ROW PER NIP (bukan per kunjungan)
         * Ambil NIP unik + tanggal terakhir kunjungan di range tsb.
         */
        $nipQuery = DB::table('pendaftaran as p')
            ->leftJoin('pegawai as pg', 'pg.nip', '=', 'p.nip')
            ->whereBetween('p.tanggal', [$from, $to]);

        // filter tipe (kalau struktur tipe_pasien kamu beda, ganti di sini)
        if (in_array($tipe, ['pegawai', 'pensiunan'])) {
            $nipQuery->where('p.tipe_pasien', $tipe);
        } elseif ($tipe === 'poliklinik') {
            // sementara tetap kosong/placeholder kalau memang data alkes beda tabel
            // (kalau tabel alkes sudah ada, nanti kita mapping khusus di sini)
            $nipQuery->whereRaw('1=0');
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

        // Build ringkas pasien+hubkel per NIP (di range tanggal)
        $items = [];
        $noAwal = ($nips->currentPage() - 1) * $nips->perPage();

        foreach ($nips as $idx => $row) {
            $nip = $row->nip;

            $pasienRows = DB::table('pendaftaran as p')
                ->leftJoin('keluarga as k', 'k.id_keluarga', '=', 'p.id_keluarga')
                ->whereBetween('p.tanggal', [$from, $to])
                ->where('p.nip', $nip);

            if (in_array($tipe, ['pegawai', 'pensiunan'])) {
                $pasienRows->where('p.tipe_pasien', $tipe);
            }

            // Ambil list pasien + hubkel (unik) untuk ditampilin ringkas
            $pairs = $pasienRows
                ->select([
                    DB::raw("CASE WHEN p.tipe_pasien = 'pegawai' THEN 'YBS' ELSE COALESCE(k.nama_keluarga,'-') END as nama_pasien"),
                    DB::raw("CASE WHEN p.tipe_pasien = 'pegawai' THEN 'YBS' ELSE COALESCE(k.hubungan_keluarga,'-') END as hub_kel"),
                ])
                ->get();

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

            $items[] = [
                'no' => $noAwal + $idx + 1,
                'tanggal' => $row->last_tanggal ?? '-',
                'nama' => $row->nama_pegawai ?? '-',
                'nip' => $nip ?? '-',
                'nama_pasien' => $namaTxt ?: '-',
                'hub_kel' => $hubTxt ?: '-',
                'preview_url' => route('adminpoli.laporan.preview', [
                    'nip' => $nip,
                    'tipe' => $tipe,
                    'from' => $from,
                    'to' => $to,
                ]),
            ];
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

    public function preview(Request $request, string $nip)
    {
        $tipe = $request->input('tipe', 'pegawai');
        $from = $request->input('from') ?: now()->toDateString();
        $to   = $request->input('to')   ?: now()->toDateString();

        $visits = $this->getVisits($from, $to, $tipe, $nip);
        $rows   = $this->buildReportRows($visits);

        return view('adminpoli.laporan.preview', [
            'tipe'  => $tipe,
            'from'  => $from,
            'to'    => $to,
            'nip'   => $nip,
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

        if (in_array($tipe, ['pegawai', 'pensiunan'])) {
            $q->where('p.tipe_pasien', $tipe);
        } elseif ($tipe === 'poliklinik') {
            // placeholder alkes
            $q->whereRaw('1=0');
        }

        if ($nip) $q->where('p.nip', $nip);

        $visits = $q->select([
                'p.id_pendaftaran',
                'p.tanggal',
                'p.tipe_pasien',
                'p.nip',
                'p.id_keluarga',

                'pm.id_pemeriksaan',
                'pm.id_nb',

                'pm.sistol',
                'pm.diastol',
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
                    WHEN p.tipe_pasien = 'pegawai' THEN 'YBS'
                    ELSE COALESCE(k.nama_keluarga,'-')
                END as nama_pasien"),

                DB::raw("CASE 
                    WHEN p.tipe_pasien = 'pegawai' THEN 'YBS'
                    ELSE COALESCE(k.hubungan_keluarga,'-')
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
    
    private function buildReportRows($visits): array
    {
        $rows = [];
        $no = 1;
        $counter = []; // nip => urutan tes darah

        foreach ($visits as $v) {

            $isPegawai = ($v->tipe_pasien ?? '') === 'pegawai';
            $isPensiunan = false; // kalau ada kolom pensiun, isi di sini
            $allowPeriksaKe = $isPegawai && !$isPensiunan;

            $td = ($v->sistol && $v->diastol) ? ($v->sistol . '/' . $v->diastol) : '-';

            // periksa ke: hanya pegawai + hanya jika ada tes darah
            $hasLab = $this->hasBloodTest($v);
            $periksaKe = '-';
            if ($allowPeriksaKe && $hasLab) {
                $counter[$v->nip] = ($counter[$v->nip] ?? 0) + 1;
                $periksaKe = $counter[$v->nip];
            }

            // ==== DIAGNOSA LINES (dipisah per baris) ====
            // diagnosa umum: nb kosong
            $diagnosaLines = [];

            $diagUmum = $v->diagnosa_list ?? [];
            foreach ($diagUmum as $d) {
                $diagnosaLines[] = [
                    'diagnosa' => $d,
                    'nb' => '', // diagnosa umum ga punya id_nb
                ];
            }

            // diagnosa k3: nb = id_nb
            $diagK3Items = $v->diagnosa_k3_items ?? [];
            foreach ($diagK3Items as $k3) {
                $diagnosaLines[] = [
                    'diagnosa' => $k3->nama_penyakit ?? '-',
                    'nb' => $k3->id_nb ?? '',
                ];
            }

            // kalau tidak ada diagnosa sama sekali, tetap 1 baris
            if (count($diagnosaLines) === 0) {
                $diagnosaLines[] = ['diagnosa' => '-', 'nb' => ''];
            }

            // ==== OBAT LIST ====
            $obatList = $v->obat_list ?? [];

            // total harga obat per pendaftaran (sum subtotal)
            $totalHarga = 0;
            foreach ($obatList as $ob) {
                $totalHarga += (int)($ob->subtotal ?? 0);
            }

            // jumlah baris mengikuti yang paling banyak: diagnosa atau obat
            $maxLines = max(count($diagnosaLines), count($obatList), 1);

            for ($i = 0; $i < $maxLines; $i++) {
                $isFirst = ($i === 0);

                $diag = $diagnosaLines[$i]['diagnosa'] ?? '';
                $nbLine = $diagnosaLines[$i]['nb'] ?? '';

                $ob = $obatList[$i] ?? null;

                $rows[] = [
                    'NO' => $isFirst ? $no : '',
                    'TANGGAL' => $isFirst ? ($v->tanggal ?? '-') : '',
                    'NAMA' => $isFirst ? ($v->nama_pegawai ?? '-') : '',
                    'UMUR' => $isFirst ? '-' : '',
                    'BAGIAN' => $isFirst ? ($v->bagian ?? '-') : '',
                    'NAMA_PASIEN' => $isFirst ? ($v->nama_pasien ?? '-') : '',
                    'HUB_KEL' => $isFirst ? ($v->hub_kel ?? '-') : '',
                    'TD' => $isFirst ? $td : '',

                    'GDP' => $isFirst ? ($v->gd_puasa ?? '-') : '',
                    'GD_2JAM_PP' => $isFirst ? ($v->gd_duajam ?? '-') : '',
                    'GDS' => $isFirst ? ($v->gd_sewaktu ?? '-') : '',
                    'AU' => $isFirst ? ($v->asam_urat ?? '-') : '',
                    'CHOL' => $isFirst ? ($v->chol ?? '-') : '',
                    'TG' => $isFirst ? ($v->tg ?? '-') : '',
                    'SUHU' => $isFirst ? ($v->suhu ?? '-') : '',
                    'BB' => $isFirst ? ($v->berat ?? '-') : '',
                    'TB' => $isFirst ? ($v->tinggi ?? '-') : '',

                    // diagnosa per baris
                    'DIAGNOSA' => $diag !== '' ? $diag : ($isFirst ? '-' : ''),

                    // TERAPHY = nama obat per baris
                    'TERAPHY' => $ob ? ($ob->nama_obat ?? '-') : ($isFirst ? '-' : ''),

                    'JUMLAH_OBAT' => $ob ? trim(($ob->jumlah ?? 0) . ' ' . ($ob->satuan ?? '')) : ($isFirst ? '-' : ''),
                    'HARGA_OBAT_SATUAN' => $ob ? ($ob->harga ?? 0) : ($isFirst ? '-' : ''),
                    'TOTAL_HARGA_OBAT' => $isFirst ? ($totalHarga ?: '-') : '',

                    // pemeriksa dari join dokter/pemeriksa
                    'PEMERIKSA' => $isFirst ? ($v->pemeriksa ?? '-') : '',

                    // NB: hanya pegawai, dan per baris (khusus diagnosa K3 ada id_nb)
                    'NB' => $isPegawai ? ($nbLine ?: ($isFirst ? '-' : '')) : '',

                    'PERIKSA_KE' => $isFirst ? $periksaKe : '',
                ];
            }

            $no++;
        }

        return $rows;
    }

    private function hasBloodTest($v): bool
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
}
