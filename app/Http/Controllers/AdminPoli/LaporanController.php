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

        $q = DB::table('pendaftaran as p')
            ->leftJoin('pegawai as pg', 'pg.nip', '=', 'p.nip')
            ->leftJoin('keluarga as k', 'k.id_keluarga', '=', 'p.id_keluarga')
            ->leftJoin('pemeriksaan as pm', 'pm.id_pendaftaran', '=', 'p.id_pendaftaran')
            ->whereBetween(DB::raw('DATE(p.tanggal)'), [$from, $to]) // kalau kolommu created_at, ganti jadi DATE(p.created_at)
            ->select([
                'p.id_pendaftaran',
                DB::raw('DATE(p.tanggal) as tanggal'),
                'p.nip',
                'p.id_keluarga',
                'p.periksa_ke',

                DB::raw("COALESCE(pg.nama_pegawai, '-') as nama_pegawai"),
                DB::raw("COALESCE(pg.bagian, '-') as bagian"),

                // Nama pasien & hubungan
                DB::raw("CASE WHEN p.id_keluarga IS NULL OR p.id_keluarga = '' THEN 'YBS' ELSE COALESCE(k.nama_keluarga,'-') END as nama_pasien"),
                DB::raw("CASE WHEN p.id_keluarga IS NULL OR p.id_keluarga = '' THEN 'YBS' ELSE COALESCE(k.hubungan,'-') END as hub_kel"),

                // vital/lab (kalau kolommu beda, sesuaikan)
                'pm.sistol',
                'pm.diastol',
                'pm.gd_puasa',
                'pm.gd_2jam_pp',
                'pm.gds',
                'pm.asam_urat',
                'pm.kolesterol',
                'pm.trigliserida',
                'pm.suhu',
                'pm.bb',
                'pm.tb',

                // catatan / nb
                DB::raw("COALESCE(pm.nb, pm.catatan, '-') as nb"),

                // nama pemeriksa (kalau kamu simpan di pm)
                DB::raw("COALESCE(pm.nama_pemeriksa, pm.dokter, '-') as pemeriksa"),
            ])
            ->orderBy('tanggal', 'asc')
            ->orderBy('p.id_pendaftaran', 'asc');

        $visits = $q->get();

        // Tambahin diagnosa + obat per visit (biar format Excel bisa multi-line)
        // NOTE: sesuaikan nama tabel relasi diagnosa & detail obat kalau beda.

        foreach ($visits as $v) {
            // Diagnosa list (contoh: tabel pivot pendaftaran_diagnosa)
            $v->diagnosa_list = DB::table('pendaftaran_diagnosa as pd')
                ->leftJoin('diagnosa as d', 'd.id_diagnosa', '=', 'pd.id_diagnosa')
                ->where('pd.id_pendaftaran', $v->id_pendaftaran)
                ->pluck(DB::raw("COALESCE(d.nama_diagnosa, pd.diagnosa_text, '-')"))
                ->filter()
                ->values()
                ->all();

            // Detail obat list (contoh: tabel detail_resep)
            $v->obat_list = DB::table('detail_resep as dr')
                ->leftJoin('obat as o', 'o.id_obat', '=', 'dr.id_obat')
                ->where('dr.id_pendaftaran', $v->id_pendaftaran)
                ->select([
                    DB::raw("COALESCE(o.nama_obat, dr.nama_obat, '-') as nama_obat"),
                    DB::raw("COALESCE(dr.jumlah, 0) as jumlah"),
                    DB::raw("COALESCE(dr.satuan, '') as satuan"),
                    DB::raw("COALESCE(dr.harga_satuan, o.harga, 0) as harga_satuan"),
                    DB::raw("COALESCE(dr.subtotal, (COALESCE(dr.jumlah,0) * COALESCE(dr.harga_satuan, o.harga, 0))) as subtotal"),
                ])
                ->get()
                ->all();
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

        foreach ($visits as $v) {
            $diagnosa = $v->diagnosa_list ?? [];
            $obatList = $v->obat_list ?? [];

            $maxLines = max(count($diagnosa), count($obatList), 1);

            $td = ($v->sistol && $v->diastol) ? ($v->sistol . '/' . $v->diastol) : '-';

            $totalHarga = 0;
            foreach ($obatList as $ob) {
                $totalHarga += (int)($ob->subtotal ?? 0);
            }

            for ($i = 0; $i < $maxLines; $i++) {
                $ob = $obatList[$i] ?? null;
                $diag = $diagnosa[$i] ?? null;

                $isFirst = ($i === 0);

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
                    'GD_2JAM_PP' => $isFirst ? ($v->gd_2jam_pp ?? '-') : '',
                    'GDS' => $isFirst ? ($v->gds ?? '-') : '',
                    'AU' => $isFirst ? ($v->asam_urat ?? '-') : '',
                    'CHOL' => $isFirst ? ($v->kolesterol ?? '-') : '',
                    'TG' => $isFirst ? ($v->trigliserida ?? '-') : '',
                    'SUHU' => $isFirst ? ($v->suhu ?? '-') : '',
                    'BB' => $isFirst ? ($v->bb ?? '-') : '',
                    'TB' => $isFirst ? ($v->tb ?? '-') : '',
                    'DIAGNOSA' => $diag ?? ($isFirst ? '-' : ''),
                    'TERAPHY' => $ob ? ($ob->nama_obat ?? '-') : ($isFirst ? '-' : ''),
                    'JUMLAH_OBAT' => $ob ? trim(($ob->jumlah ?? 0) . ' ' . ($ob->satuan ?? '')) : ($isFirst ? '-' : ''),
                    'HARGA_OBAT_SATUAN' => $ob ? ($ob->harga_satuan ?? 0) : ($isFirst ? '-' : ''),
                    'TOTAL_HARGA_OBAT' => $isFirst ? ($totalHarga ?: '-') : '',
                    'PEMERIKSA' => $isFirst ? ($v->pemeriksa ?? '-') : '',
                    'NB' => $isFirst ? ($v->nb ?? '-') : '',
                    'PERIKSA_KE' => $isFirst ? ($v->periksa_ke ?? '-') : '',
                ];
            }

            $no++;
        }

        return $rows;
    }
}
