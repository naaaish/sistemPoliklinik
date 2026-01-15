<?php

namespace App\Http\Controllers\Kepegawaian;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class PegawaiController extends Controller
{
    public function index()
    {
        $pegawai = DB::table('pegawai')
            ->orderBy('nama_pegawai')
            ->get();

        return view('kepegawaian.pegawai.index', compact('pegawai'));
    }
}
