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
            ->distinct(DB::raw("CONCAT(nip,'|',COALESCE(id_keluarga,'-'))"))
            ->count();

        // 3) Hasil pemeriksaan belum diinput = pendaftaran yg belum ada record pemeriksaan
        // Asumsi: pemeriksaan punya kolom id_pendaftaran
        $belumDiinput = DB::table('pendaftaran as pd')
            ->leftJoin('pemeriksaan as pm', 'pm.id_pendaftaran', '=', 'pd.id_pendaftaran')
            ->whereNull('pm.id_pendaftaran')
            ->count();

        // 4) Daftar pasien aktif hari ini (buat tabel dashboard)
        $daftarPasienAktif = DB::table('pendaftaran as p')
            ->join('pegawai as pg', 'pg.nip', '=', 'p.nip')
            ->leftJoin('keluarga as k', 'k.id_keluarga', '=', 'p.id_keluarga')
            ->leftJoin('pemeriksaan as pm', 'pm.id_pendaftaran', '=', 'p.id_pendaftaran')
            ->whereNull('pm.id_pendaftaran')
            ->orderBy('p.tanggal', 'desc')
            ->select([
                'p.id_pendaftaran',
                'p.tanggal',
                'p.nip',
                DB::raw("CASE 
                    WHEN p.tipe_pasien = 'keluarga' THEN k.nama_keluarga
                    ELSE pg.nama_pegawai
                END AS nama_pasien"),
            ])
            ->get();

        return view('adminpoli.dashboard', compact(
            'kunjunganHariIni',
            'totalPasienBulanIni',
            'belumDiinput',
            'daftarPasienAktif'
        ));
    }
}
