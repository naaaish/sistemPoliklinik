<?php
// app/Http/Controllers/Kepegawaian/LaporanController.php

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
            'pensiun'  => 'Rekapan Pemeriksaan Pensiunan',
            'dokter'   => 'Rekapan Pemeriksaan Dokter',
            'obat'     => 'Rekapan Penggunaan Obat',
            'total'    => 'Rekapan Total Operasional',
        ];

        $preview = [];

        /* ================= PASIEN ================= */
        foreach (['pegawai','pensiun'] as $tipe) {
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

        /* ================= PASIEN (PEGAWAI/PENSIUN) ================= */
        if (in_array($jenis, ['pegawai', 'pensiun'])) {
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

        /* ================= DOKTER (DENGAN DETAIL PASIEN) ================= */
        elseif ($jenis === 'dokter') {
            // Query untuk dokter dengan total pasien
            $query = DB::table('dokter')
                ->leftJoin('pendaftaran', 'dokter.id_dokter', '=', 'pendaftaran.id_dokter')
                ->leftJoin('pemeriksaan', 'pendaftaran.id_pendaftaran', '=', 'pemeriksaan.id_pendaftaran')
                ->select(
                    'dokter.id_dokter',
                    'dokter.nama as nama_dokter',
                    'dokter.jenis_dokter',
                    DB::raw('COUNT(DISTINCT pemeriksaan.id_pemeriksaan) as total_pasien')
                )
                ->groupBy('dokter.id_dokter', 'dokter.nama', 'dokter.jenis_dokter');

            if ($dari && $sampai) {
                $query->whereBetween(DB::raw('DATE(pemeriksaan.created_at)'), [$dari, $sampai]);
            }

            $data = $query->get();

            // Untuk setiap dokter poliklinik, ambil detail pasiennya
            foreach ($data as $dokter) {
                if ($dokter->jenis_dokter === 'umum') { // 'umum' = dokter poliklinik
                    $pasienQuery = DB::table('pemeriksaan')
                        ->join('pendaftaran', 'pemeriksaan.id_pendaftaran', '=', 'pendaftaran.id_pendaftaran')
                        ->join('pasien', 'pendaftaran.id_pasien', '=', 'pasien.id_pasien')
                        ->where('pendaftaran.id_dokter', $dokter->id_dokter)
                        ->select(
                            'pasien.nama_pasien',
                            DB::raw('DATE(pemeriksaan.created_at) as tanggal')
                        )
                        ->orderByDesc('pemeriksaan.created_at');

                    if ($dari && $sampai) {
                        $pasienQuery->whereBetween(DB::raw('DATE(pemeriksaan.created_at)'), [$dari, $sampai]);
                    }

                    $dokter->detail_pasien = $pasienQuery->get();
                } else {
                    $dokter->detail_pasien = collect(); // Empty collection untuk dokter perusahaan
                }
            }
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
                    DB::raw('COALESCE(SUM(detail_resep.jumlah * obat.harga), 0) as biaya_obat'),
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

        // Return JSON jika ajax request
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
        }

        return view('kepegawaian.laporan.detail', compact(
            'judul','jenis','data','dari','sampai'
        ));
    }

    /* =========================
       API: Get Data untuk Excel Download
    ========================= */
    public function getDataApi(Request $request, $jenis)
    {
        $dari  = $request->dari;
        $sampai = $request->sampai;

        /* ================= PASIEN (PEGAWAI/PENSIUN) ================= */
        if (in_array($jenis, ['pegawai', 'pensiun'])) {
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

        /* ================= DOKTER (DENGAN DETAIL PASIEN) ================= */
        elseif ($jenis === 'dokter') {
            $query = DB::table('dokter')
                ->leftJoin('pendaftaran', 'dokter.id_dokter', '=', 'pendaftaran.id_dokter')
                ->leftJoin('pemeriksaan', 'pendaftaran.id_pendaftaran', '=', 'pemeriksaan.id_pendaftaran')
                ->select(
                    'dokter.id_dokter',
                    'dokter.nama as nama_dokter',
                    'dokter.jenis_dokter',
                    DB::raw('COUNT(DISTINCT pemeriksaan.id_pemeriksaan) as total_pasien')
                )
                ->groupBy('dokter.id_dokter', 'dokter.nama', 'dokter.jenis_dokter');

            if ($dari && $sampai) {
                $query->whereBetween(DB::raw('DATE(pemeriksaan.created_at)'), [$dari, $sampai]);
            }

            $data = $query->get();

            // Untuk setiap dokter poliklinik, ambil detail pasiennya
            foreach ($data as $dokter) {
                if ($dokter->jenis_dokter === 'umum') {
                    $pasienQuery = DB::table('pemeriksaan')
                        ->join('pendaftaran', 'pemeriksaan.id_pendaftaran', '=', 'pendaftaran.id_pendaftaran')
                        ->join('pasien', 'pendaftaran.id_pasien', '=', 'pasien.id_pasien')
                        ->where('pendaftaran.id_dokter', $dokter->id_dokter)
                        ->select(
                            'pasien.nama_pasien',
                            DB::raw('DATE(pemeriksaan.created_at) as tanggal')
                        )
                        ->orderByDesc('pemeriksaan.created_at');

                    if ($dari && $sampai) {
                        $pasienQuery->whereBetween(DB::raw('DATE(pemeriksaan.created_at)'), [$dari, $sampai]);
                    }

                    $dokter->detail_pasien = $pasienQuery->get();
                } else {
                    $dokter->detail_pasien = collect();
                }
            }
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
                    DB::raw('COALESCE(SUM(detail_resep.jumlah * obat.harga), 0) as biaya_obat'),
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

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
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
}