<?php

namespace App\Http\Controllers\Kepegawaian;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PegawaiController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('pegawai');

        // Search berdasarkan nama
        if ($request->has('q') && $request->q != '') {
            $query->where('nama_pegawai', 'LIKE', '%' . $request->q . '%');
        }

        $pegawai = $query->orderBy('nama_pegawai')->get();
        $q = $request->q;

        return view('kepegawaian.pegawai.index', compact('pegawai', 'q'));
    }

    public function show($id) {
        $pegawai = DB::table('pegawai')->where('nip', $id)->first();
        return view('kepegawaian.pegawai.detail', compact('pegawai'));
    }
}