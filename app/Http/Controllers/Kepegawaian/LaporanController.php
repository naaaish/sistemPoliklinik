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
            'keluarga' => 'Rekapan Pemeriksaan Keluarga',
            'pensiun'  => 'Rekapan Pemeriksaan Pensiunan',
            'dokter'   => 'Rekapan Pemeriksaan Dokter',
            'obat'     => 'Rekapan Penggunaan Obat',
            'total'    => 'Rekapan Total Operasional',
        ];

        $preview = [];

        /* ===== PASIEN (pegawai / keluarga / pensiun) ===== */
        foreach (['pegawai','keluarga','pensiun'] as $tipe) {
            $preview[$tipe] = DB::table('pemeriksaan')
                ->join('pendaftaran','pemeriksaan.id_pendaftaran','=','pendaftaran.id_pendaftaran')
                ->join('pasien','pendaftaran.id_pasien','=','pasien.id_pasien')
                ->where('pasien.tipe_pasien',$tipe)
                ->select(
                    'pasien.nama_pasien',
                    DB::raw('DATE(pemeriksaan.created_at) as tanggal')
                )
                ->orderBy('pemeriksaan.created_at','desc')
                ->limit(5)
                ->get();
        }

        /* ===== DOKTER ===== */
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

        /* ===== OBAT ===== */
        $preview['obat'] = DB::table('detail_resep')
            ->join('obat','detail_resep.id_obat','=','obat.id_obat')
            ->join('resep','detail_resep.id_resep','=','resep.id_resep')
            ->join('pemeriksaan','resep.id_pemeriksaan','=','pemeriksaan.id_pemeriksaan')
            ->select(
                'obat.nama_obat',
                DB::raw('DATE(pemeriksaan.created_at) as tanggal'),
                'detail_resep.jumlah',
                'obat.harga',
                DB::raw('(detail_resep.jumlah * obat.harga) as total')
            )
            ->orderBy('pemeriksaan.created_at','desc')
            ->limit(5)
            ->get();

        /* ===== TOTAL OPERASIONAL (1 BARIS) ===== */
    $totalObat = DB::table('detail_resep')
        ->join('obat','detail_resep.id_obat','=','obat.id_obat')
        ->join('resep','detail_resep.id_resep','=','resep.id_resep')
        ->join('pemeriksaan','resep.id_pemeriksaan','=','pemeriksaan.id_pemeriksaan')
        ->sum(DB::raw('detail_resep.jumlah * obat.harga'));

    /**
     * sementara total dokter = 0
     * (nanti bisa dihitung dari:
     * - dokter poli (gaji tetap)
     * - dokter perusahaan (per pasien))
     */
    $totalDokter = 0;

    $preview['total'] = [
        'total_dokter' => $totalDokter,
        'total_obat'   => $totalObat,
        'grand_total'  => $totalDokter + $totalObat,
    ];

        return view('kepegawaian.laporan.index', compact('rekapan','preview'));
    }

    /* =========================
       DETAIL + FILTER TANGGAL
    ========================= */
    public function detail(Request $request, $jenis)
    {
        $judul  = $this->getJudul($jenis);
        $dari   = $request->dari;
        $sampai = $request->sampai;

        /* ===== DETAIL OBAT ===== */
        if ($jenis === 'obat') {
            $query = DB::table('detail_resep')
                ->join('obat','detail_resep.id_obat','=','obat.id_obat')
                ->join('resep','detail_resep.id_resep','=','resep.id_resep')
                ->join('pemeriksaan','resep.id_pemeriksaan','=','pemeriksaan.id_pemeriksaan')
                ->select(
                    'obat.nama_obat',
                    DB::raw('DATE(pemeriksaan.created_at) as tanggal'),
                    'detail_resep.jumlah',
                    DB::raw('(detail_resep.jumlah * obat.harga) as total')
                );

            if ($dari && $sampai) {
                $query->whereBetween(
                    DB::raw('DATE(pemeriksaan.created_at)'),
                    [$dari, $sampai]
                );
            }

            $data = $query->orderBy('tanggal','desc')->get();
        }

        /* ===== DETAIL TOTAL ===== */
        elseif ($jenis === 'total') {
            $query = DB::table('detail_resep')
                ->join('obat','detail_resep.id_obat','=','obat.id_obat')
                ->join('resep','detail_resep.id_resep','=','resep.id_resep')
                ->join('pemeriksaan','resep.id_pemeriksaan','=','pemeriksaan.id_pemeriksaan')
                ->select(
                    DB::raw('DATE(pemeriksaan.created_at) as tanggal'),
                    DB::raw('SUM(detail_resep.jumlah * obat.harga) as total_obat')
                )
                ->groupBy(DB::raw('DATE(pemeriksaan.created_at)'));

            if ($dari && $sampai) {
                $query->whereBetween(
                    DB::raw('DATE(pemeriksaan.created_at)'),
                    [$dari, $sampai]
                );
            }

            $data = $query->orderBy('tanggal','desc')->get();
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
            'keluarga' => 'Rekapan Pemeriksaan Keluarga',
            'pensiun'  => 'Rekapan Pemeriksaan Pensiunan',
            'dokter'   => 'Rekapan Pemeriksaan Dokter',
            'obat'     => 'Rekapan Penggunaan Obat',
            'total'    => 'Rekapan Total Operasional',
            default    => 'Rekapan Laporan',
        };
    }
}
