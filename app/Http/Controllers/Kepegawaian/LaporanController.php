<?php

namespace App\Http\Controllers\Kepegawaian;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    public function index()
    {
        $rekapan = [
            'pegawai'  => 'Rekapan Pemeriksaan Pegawai',
            'keluarga' => 'Rekapan Pemeriksaan Keluarga',
            'pensiun'  => 'Rekapan Pemeriksaan Pensiunan',
            'dokter'   => 'Rekapan Biaya Dokter',
            'obat'     => 'Rekapan Penggunaan Obat',
            'total'    => 'Rekapan Total Operasional',
            'global'   => 'Rekapan Seluruh Pemeriksaan',
        ];

        /* ================= PEGawai / KELUARGA / PENSIUN ================= */
        foreach (['pegawai','keluarga','pensiun'] as $tipe) {
            $preview[$tipe] = DB::table('pemeriksaan')
                ->join('pendaftaran','pemeriksaan.id_pendaftaran','=','pendaftaran.id_pendaftaran')
                ->join('pasien','pendaftaran.id_pasien','=','pasien.id_pasien')
                ->where('pasien.tipe_pasien',$tipe)
                ->select(
                    'pasien.nama_pasien',
                    'pemeriksaan.created_at as tanggal'
                )
                ->latest('pemeriksaan.created_at')
                ->limit(5)
                ->get();
        }

        /* ================= DOKTER =================
           SESUAI ERD: TIDAK ADA GAJI
           - dokter perusahaan → dihitung per pemeriksaan
           - dokter poli → hanya ditampilkan (tanpa nominal tetap)
        ================================================= */
        $preview['dokter'] = DB::table('dokter')
            ->leftJoin('pendaftaran','dokter.id_dokter','=','pendaftaran.id_dokter')
            ->leftJoin('pemeriksaan','pendaftaran.id_pendaftaran','=','pemeriksaan.id_pendaftaran')
            ->select(
                'dokter.nama as nama_dokter',
                'dokter.jenis_dokter',
                DB::raw('COUNT(pemeriksaan.id_pemeriksaan) as total_pasien')
            )
            ->groupBy('dokter.id_dokter','dokter.nama','dokter.jenis_dokter')
            ->limit(5)
            ->get();

        /* ================= OBAT ================= */
        $preview['obat'] = DB::table('detail_resep')
            ->join('obat','detail_resep.id_obat','=','obat.id_obat')
            ->join('resep','detail_resep.id_resep','=','resep.id_resep')
            ->join('pemeriksaan','resep.id_pemeriksaan','=','pemeriksaan.id_pemeriksaan')
            ->join('pendaftaran','pemeriksaan.id_pendaftaran','=','pendaftaran.id_pendaftaran')
            ->join('pasien','pendaftaran.id_pasien','=','pasien.id_pasien')
            ->leftJoin('dokter','pendaftaran.id_dokter','=','dokter.id_dokter')
            ->select(
                'obat.nama_obat',
                'detail_resep.jumlah',
                'obat.harga',
                DB::raw('(detail_resep.jumlah * obat.harga) as total'),
                'pasien.nama_pasien',
                'dokter.nama as nama_dokter',
                'pemeriksaan.created_at as tanggal'
            )
            ->latest('pemeriksaan.created_at')
            ->limit(5)
            ->get();

        /* ================= TOTAL OPERASIONAL ================= */
        $total_obat = DB::table('detail_resep')
            ->join('obat','detail_resep.id_obat','=','obat.id_obat')
            ->select(DB::raw('SUM(detail_resep.jumlah * obat.harga) as total'))
            ->value('total');

        $preview['total'] = [
            'total_dokter' => 0, // SESUAI ERD (tidak ada field gaji)
            'total_obat'   => $total_obat ?? 0,
            'grand_total'  => $total_obat ?? 0,
        ];

        /* ================= GLOBAL ================= */
        $preview['global'] = DB::table('pemeriksaan')
            ->join('pendaftaran','pemeriksaan.id_pendaftaran','=','pendaftaran.id_pendaftaran')
            ->join('pasien','pendaftaran.id_pasien','=','pasien.id_pasien')
            ->select(
                'pasien.nama_pasien',
                'pasien.tipe_pasien',
                'pemeriksaan.created_at as tanggal'
            )
            ->latest('pemeriksaan.created_at')
            ->limit(5)
            ->get();

        return view('kepegawaian.laporan.index', compact('rekapan','preview'));
    }
}
