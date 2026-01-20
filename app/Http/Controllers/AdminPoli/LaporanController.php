<?php

namespace App\Http\Controllers\AdminPoli;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->input('from');
        $to   = $request->input('to');

        // Default: hari ini kalau user belum pilih
        if (!$from || !$to) {
            $from = $from ?: now()->toDateString();
            $to   = $to   ?: now()->toDateString();
        }

        $visits = $this->getVisits($from, $to);
        $rows   = $this->buildReportRows($visits);

        return view('adminpoli.laporan.index', [
            'from'  => $from,
            'to'    => $to,
            'rows'  => $rows,
            'count' => count($rows),
        ]);
    }

    public function exportExcel(Request $request)
    {
        $from = $request->input('from');
        $to   = $request->input('to');

        if (!$from || !$to) {
            abort(422, 'Rentang tanggal wajib diisi.');
        }

        $visits = $this->getVisits($from, $to);
        $rows   = $this->buildReportRows($visits);

        $fileName = "LAPORAN_KLINIK_{$from}_sd_{$to}.xls";

        return response()
            ->view('adminpoli.laporan.export_excel', [
                'from' => $from,
                'to'   => $to,
                'rows' => $rows,
            ])
            ->header('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="'.$fileName.'"');
    }

    /**
     * Ambil data kunjungan dalam rentang tanggal.
     * NOTE: BAGIAN INI yang paling mungkin kamu perlu sesuaikan nama tabel & kolomnya.
     */
    private function getVisits(string $from, string $to)
    {
        // ========= MAPPING ASUMSI TABEL =========
        // pendaftaran: id_pendaftaran, tanggal (atau created_at), nip, id_keluarga, periksa_ke
        // pegawai: nip, nama_pegawai, bagian
        // keluarga: id_keluarga, nip, nama_keluarga, hubungan (Istri/Anak/Suami)
        // pemeriksaan: id_pendaftaran, sistol, diastol, suhu, bb, tb, gd_puasa, gd_2jam_pp, gds, asam_urat, kolesterol, trigliserida, catatan/nb
        // dokter/pemeriksa: bebas, nanti ditarik dari kolom nama pemeriksa kalau ada
        //
        // Kalau nama tabelmu beda: ganti di sini aja.

        $visits = DB::table('pendaftaran as p')
            ->leftJoin('pegawai as pg', 'pg.nip', '=', 'p.nip')
            ->leftJoin('keluarga as k', 'k.id_keluarga', '=', 'p.id_keluarga')
            ->leftJoin('pemeriksaan as pm', 'pm.id_pendaftaran', '=', 'p.id_pendaftaran')
            ->leftJoin('dokter as d', 'd.id_dokter', '=', 'p.id_dokter')
            ->leftJoin('pemeriksa as pr', 'pr.id_pemeriksa', '=', 'p.id_pemeriksa')
            ->whereBetween('p.tanggal', [$from, $to])
            ->select([
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
                    WHEN p.tipe_pasien = 'pegawai' 
                    THEN 'YBS' 
                    ELSE COALESCE(k.nama_keluarga,'-') 
                END as nama_pasien"),

                DB::raw("CASE 
                    WHEN p.tipe_pasien = 'pegawai' 
                    THEN 'YBS' 
                    ELSE COALESCE(k.hubungan_keluarga,'-') 
                END as hub_kel"),

                DB::raw("COALESCE(d.nama, pr.nama_pemeriksa, '-') as pemeriksa"),
            ])
            ->orderBy('p.tanggal')
            ->orderBy('p.id_pendaftaran')
            ->get();

        // Tambahin diagnosa + obat per visit (biar format Excel bisa multi-line)
        // NOTE: sesuaikan nama tabel relasi diagnosa & detail obat kalau beda.

        foreach ($visits as $v) {
            // diagnosa umum
            $v->diagnosa_list = DB::table('detail_pemeriksaan_penyakit as dp')
                ->join('diagnosa as d', 'd.id_diagnosa', '=', 'dp.id_diagnosa')
                ->where('dp.id_pemeriksaan', $v->id_pemeriksaan)
                ->pluck('d.diagnosa')
                ->toArray();

            // diagnosa K3
            $v->diagnosa_k3_items = DB::table('detail_pemeriksaan_diagnosa_k3 as dk')
                ->join('diagnosa_k3 as k3', 'k3.id_nb', '=', 'dk.id_nb')
                ->where('dk.id_pemeriksaan', $v->id_pemeriksaan)
                ->select(['k3.id_nb', 'k3.nama_penyakit'])
                ->get()
                ->toArray();

            // saran / teraphy
            $v->saran_list = DB::table('detail_pemeriksaan_saran as ds')
                ->join('saran as s', 's.id_saran', '=', 'ds.id_saran')
                ->where('ds.id_pemeriksaan', $v->id_pemeriksaan)
                ->pluck('s.saran')
                ->toArray();

            // obat
            $v->obat_list = DB::table('resep as r')
                ->join('detail_resep as dr', 'dr.id_resep', '=', 'r.id_resep')
                ->join('obat as o', 'o.id_obat', '=', 'dr.id_obat')
                ->where('r.id_pemeriksaan', $v->id_pemeriksaan)
                ->select([
                    'o.nama_obat',
                    'o.harga',
                    'dr.jumlah',
                    'dr.satuan',
                    'dr.subtotal',
                ])
                ->get()
                ->toArray();
        }
        return $visits;
    }

    /**
     * Bentuk baris seperti Excel "LAPORAN KLINIK":
     * - Kolom tetap tampil di baris pertama kunjungan
     * - Baris lanjutannya cuma isi diagnosa/teraphy/obat (kolom lain kosong)
     * - Total harga obat hanya muncul di baris pertama kunjungan
     */
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
