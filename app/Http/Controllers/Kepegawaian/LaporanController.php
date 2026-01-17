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
            // 'keluarga' => 'Rekapan Pemeriksaan Keluarga',
            'pensiun'  => 'Rekapan Pemeriksaan Pensiunan',
            'dokter'   => 'Rekapan Pemeriksaan Dokter',
            'obat'     => 'Rekapan Penggunaan Obat',
            'total'    => 'Rekapan Total Operasional',
        ];

        $preview = [];

        /* ================= PASIEN ================= */
        foreach (['pegawai','keluarga','pensiun'] as $tipe) {
            $preview[$tipe] = DB::table('pemeriksaan')
                ->join('pendaftaran','pemeriksaan.id_pendaftaran','=','pendaftaran.id_pendaftaran')
                ->join('pasien','pendaftaran.id_pasien','=','pasien.id_pasien')
                ->where('pasien.tipe_pasien',$tipe)
                ->select(
                    'pasien.nama_pasien',
                    DB::raw('DATE(pemeriksaan.created_at) as tanggal')
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
                DB::raw('COUNT(*) as total_pasien')
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

        /* ================= TOTAL OPERASIONAL ================= */
        $preview['total'] = DB::table('pemeriksaan')
            ->join('pendaftaran','pemeriksaan.id_pendaftaran','=','pendaftaran.id_pendaftaran')
            ->join('dokter','pendaftaran.id_dokter','=','dokter.id_dokter')
            ->leftJoin('resep','pemeriksaan.id_pemeriksaan','=','resep.id_pemeriksaan')
            ->leftJoin('detail_resep','resep.id_resep','=','detail_resep.id_resep')
            ->leftJoin('obat','detail_resep.id_obat','=','obat.id_obat')
            ->select(
                DB::raw('DATE(pemeriksaan.created_at) as tanggal'),
                DB::raw('SUM(detail_resep.jumlah * obat.harga) as biaya_obat'),
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

        /* ================= PASIEN (PEGAWAI/KELUARGA/PENSIUN) ================= */
        if (in_array($jenis, ['pegawai', 'keluarga', 'pensiun'])) {
            $query = DB::table('pemeriksaan')
                ->join('pendaftaran','pemeriksaan.id_pendaftaran','=','pendaftaran.id_pendaftaran')
                ->join('pasien','pendaftaran.id_pasien','=','pasien.id_pasien')
                ->where('pasien.tipe_pasien', $jenis)
                ->select(
                    'pasien.nama_pasien',
                    DB::raw('DATE(pemeriksaan.created_at) as tanggal')
                )
                ->orderByDesc('pemeriksaan.created_at');

            if ($dari && $sampai) {
                $query->whereBetween(DB::raw('DATE(pemeriksaan.created_at)'), [$dari, $sampai]);
            }

            $data = $query->get();
        }

        /* ================= DOKTER ================= */
        elseif ($jenis === 'dokter') {
            $query = DB::table('pemeriksaan')
                ->join('pendaftaran','pemeriksaan.id_pendaftaran','=','pendaftaran.id_pendaftaran')
                ->join('dokter','pendaftaran.id_dokter','=','dokter.id_dokter')
                ->select(
                    'dokter.nama as nama_dokter',
                    'dokter.jenis_dokter',
                    DB::raw('COUNT(*) as total_pasien')
                )
                ->groupBy('dokter.nama','dokter.jenis_dokter');

            if ($dari && $sampai) {
                $query->whereBetween(DB::raw('DATE(pemeriksaan.created_at)'), [$dari, $sampai]);
            }

            $data = $query->get();
        }

        /* ================= OBAT ================= */
        elseif ($jenis === 'obat') {
            $query = DB::table('detail_resep')
                ->join('resep', 'detail_resep.id_resep', '=', 'resep.id_resep')
                ->join('obat', 'detail_resep.id_obat', '=', 'obat.id_obat')
                ->join('pemeriksaan', 'resep.id_pemeriksaan', '=', 'pemeriksaan.id_pemeriksaan')
                ->select(
                    'obat.nama_obat',
                    DB::raw('DATE(pemeriksaan.created_at) as tanggal'),
                    'detail_resep.jumlah',
                    'obat.harga',
                    DB::raw('(detail_resep.jumlah * obat.harga) as total')
                )
                ->orderByDesc('pemeriksaan.created_at');

            if ($dari && $sampai) {
                $query->whereBetween(DB::raw('DATE(pemeriksaan.created_at)'), [$dari, $sampai]);
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
                    DB::raw('SUM(detail_resep.jumlah * obat.harga) as biaya_obat'),
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
                $query->whereBetween(DB::raw('DATE(pemeriksaan.created_at)'), [$dari, $sampai]);
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
            // 'keluarga' => 'Rekapan Pemeriksaan Keluarga',
            'pensiun'  => 'Rekapan Pemeriksaan Pensiunan',
            'dokter'   => 'Rekapan Pemeriksaan Dokter',
            'obat'     => 'Rekapan Penggunaan Obat',
            'total'    => 'Rekapan Total Operasional',
            default    => 'Rekapan Laporan',
        };
    }
}