<?php

namespace App\Http\Controllers\AdminPoli;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        /**
         * TODO: sesuaikan nama tabel & kolom sesuai DB kamu
         * Asumsi dari ERD:
         * - pendaftaran: id_pendaftaran, tanggal, id_pasien
         * - pemeriksaan: id_pemeriksaan, id_pendaftaran (atau relasi ke pendaftaran)
         * - pasien: id_pasien, nama_pasien, nip (atau relasi ke pegawai)
         * - pegawai: nip
         */

        // 1) Kunjungan hari ini = jumlah pendaftaran tanggal hari ini
        $kunjunganHariIni = DB::table('pendaftaran')
            ->whereDate('tanggal', $today)
            ->count();

        // 2) Total pasien bulan ini = distinct pasien yang berkunjung bulan ini
        $totalPasienBulanIni = DB::table('pendaftaran')
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->distinct('id_pasien')
            ->count('id_pasien');

        // 3) Hasil pemeriksaan belum diinput = pendaftaran yg belum ada record pemeriksaan
        // Asumsi: pemeriksaan punya kolom id_pendaftaran
        $belumDiinput = DB::table('pendaftaran as pd')
            ->leftJoin('pemeriksaan as pm', 'pm.id_pendaftaran', '=', 'pd.id_pendaftaran')
            ->whereNull('pm.id_pendaftaran')
            ->count();

        // 4) Daftar pasien aktif hari ini (buat tabel dashboard)
        $daftarPasienAktif = DB::table('pendaftaran as p')
            ->join('pasien as ps', 'ps.id_pasien', '=', 'p.id_pasien')
            ->select(
                'p.id_pendaftaran',
                'p.tanggal',
                'ps.nama_pasien',
                'ps.nip'
            )
            ->whereDate('p.tanggal', $today)
            ->orderBy('p.created_at', 'desc')
            ->get();

        return view('adminpoli.dashboard', compact(
            'kunjunganHariIni',
            'totalPasienBulanIni',
            'belumDiinput',
            'daftarPasienAktif'
        ));
    }
}
