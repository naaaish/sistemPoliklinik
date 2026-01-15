<?php

namespace App\Http\Controllers\Kepegawaian;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;


class LaporanController extends Controller
{
    public function index()
    {
        $rekapan = [
            'pegawai'  => 'Rekapan Pemeriksaan Pegawai',
            'keluarga' => 'Rekapan Pemeriksaan Keluarga',
            'pensiun'  => 'Rekapan Pemeriksaan Pensiunan',
            'dokter'   => 'Rekapan Biaya Dokter',
            'obat'     => 'Rekapan Biaya Obat',
            'alat'     => 'Rekapan Alat Kesehatan',
            'total'    => 'Rekapan Total Operasional',
        ];

        $preview = DB::table('pemeriksaan')
            ->join('pendaftaran', 'pemeriksaan.id_pendaftaran', '=', 'pendaftaran.id_pendaftaran')
            ->join('pasien', 'pendaftaran.id_pasien', '=', 'pasien.id_pasien')
            ->leftJoin('dokter', 'pendaftaran.id_dokter', '=', 'dokter.id_dokter')
            ->select(
                'pasien.nama_pasien',
                'dokter.nama',
                'pemeriksaan.created_at as tanggal'
            )
            ->orderBy('pemeriksaan.created_at', 'desc')
            ->limit(5)
            ->get();

        return view('kepegawaian.laporan.index', compact('rekapan', 'preview'));
    }

    // ===============================
    // DETAIL LAPORAN + FILTER TANGGAL
    // ===============================
    public function detail(Request $request, $jenis)
    {
        $judul = $this->getJudul($jenis);

        $query = DB::table('pemeriksaan')
            ->join('pendaftaran', 'pemeriksaan.id_pendaftaran', '=', 'pendaftaran.id_pendaftaran')
            ->join('pasien', 'pendaftaran.id_pasien', '=', 'pasien.id_pasien')
            ->leftJoin('dokter', 'pendaftaran.id_dokter', '=', 'dokter.id_dokter')
            ->select(
                'pasien.nama_pasien',
                'dokter.nama',
                'pendaftaran.keluhan',
                'pemeriksaan.created_at as tanggal'
            );

        // filter jenis pasien (ERD: pasien.tipe_pasien)
        if (in_array($jenis, ['pegawai', 'keluarga', 'pensiun'])) {
            $query->where('pasien.tipe_pasien', $jenis);
        }

        // filter rentang tanggal
        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('pemeriksaan.created_at', [
                $request->from . ' 00:00:00',
                $request->to . ' 23:59:59'
            ]);
        }

        $data = $query
            ->orderBy('pemeriksaan.created_at', 'desc')
            ->get();

        return view('kepegawaian.laporan.detail', compact(
            'judul',
            'jenis',
            'data'
        ));
    }

    // ===============================
    // DOWNLOAD PDF
    // ===============================
    public function downloadPdf(Request $request, $jenis)
    {
        $judul = $this->getJudul($jenis);

        $data = DB::table('pemeriksaan')
            ->join('pendaftaran', 'pemeriksaan.id_pendaftaran', '=', 'pendaftaran.id_pendaftaran')
            ->join('pasien', 'pendaftaran.id_pasien', '=', 'pasien.id_pasien')
            ->leftJoin('dokter', 'pendaftaran.id_dokter', '=', 'dokter.id_dokter')
            ->select(
                'pasien.nama_pasien',
                'dokter.nama',
                'pendaftaran.keluhan',
                'pemeriksaan.created_at as tanggal'
            )
            ->when(in_array($jenis, ['pegawai','keluarga','pensiun']), function ($q) use ($jenis) {
                $q->where('pasien.tipe_pasien', $jenis);
            })
            ->orderBy('pemeriksaan.created_at', 'desc')
            ->get();

        $pdf = Pdf::loadView('kepegawaian.laporan.pdf', compact('judul', 'data'));

        return $pdf->download("laporan-$jenis.pdf");
    }

    private function getJudul($jenis)
    {
        return match ($jenis) {
            'pegawai'  => 'Rekapan Pemeriksaan Pegawai',
            'keluarga' => 'Rekapan Pemeriksaan Keluarga',
            'pensiun'  => 'Rekapan Pemeriksaan Pensiunan',
            'dokter'   => 'Rekapan Biaya Dokter',
            'obat'     => 'Rekapan Biaya Obat',
            'alat'     => 'Rekapan Alat Kesehatan',
            'total'    => 'Rekapan Total Operasional',
            default    => 'Rekapan Laporan',
        };
    }
}
