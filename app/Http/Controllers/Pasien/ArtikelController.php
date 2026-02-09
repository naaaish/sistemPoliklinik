<?php

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\Artikel;
use Illuminate\Http\Request;

class ArtikelController extends Controller
{
    /**
     * Halaman daftar artikel
     */
    public function indexPublic(Request $request)
    {
        $query = Artikel::query();

        // SEARCH
        if ($request->filled('search')) {
            $query->where('judul_artikel', 'like', '%' . $request->search . '%');
        }

        // PAGINATION - 8 artikel per halaman
        $articles = $query
            ->orderBy('tanggal', 'desc')
            ->paginate(12)
            ->appends($request->except('page'));

        return view('artikel.index', compact('articles'));
    }

    /**
     * Halaman detail artikel
     */
    public function show($id)
    {
        $artikel = DB::table('artikel')
            ->where('id_artikel', $id)
            ->first();

        // jika artikel tidak ditemukan
        if (!$artikel) {
            abort(404);
        }

        return view('pasien.artikel_detail', compact('artikel'));
    }
}