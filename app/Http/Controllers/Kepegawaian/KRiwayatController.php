<?php

namespace App\Http\Controllers\Kepegawaian;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KRiwayatController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);

        $query = DB::table('pemeriksaan')
            ->join('pendaftaran', 'pemeriksaan.id_pendaftaran', '=', 'pendaftaran.id_pendaftaran')
            ->leftJoin('keluarga', 'pendaftaran.id_keluarga', '=', 'keluarga.id_keluarga')
            ->leftJoin('pegawai', 'pendaftaran.nip', '=', 'pegawai.nip')
            ->leftJoin('dokter', 'pendaftaran.id_dokter', '=', 'dokter.id_dokter')
            ->leftJoin('pemeriksa', 'pendaftaran.id_pemeriksa', '=', 'pemeriksa.id_pemeriksa')
            ->select(
                'pemeriksaan.id_pemeriksaan',
                DB::raw("COALESCE(keluarga.nama_keluarga, pegawai.nama_pegawai) as nama_pasien"),
                'pegawai.nip',
                'pegawai.bagian',
                'pemeriksaan.created_at as tanggal',
                'dokter.nama as dokter',
                'pemeriksa.nama_pemeriksa as pemeriksa'
            )
            ->orderBy('pemeriksaan.created_at', 'desc');

        if ($perPage === 'all') {
            $riwayat = $query->get();
            $isAll = true;
        } else {
            $riwayat = $query
                ->paginate((int)$perPage)
                ->withQueryString();
            $isAll = false;
        }

        return view('kepegawaian.riwayat', compact(
            'riwayat',
            'perPage',
            'isAll'
        ));
    }
}
