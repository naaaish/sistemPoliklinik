<?php

namespace App\Http\Controllers\AdminPoli;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ObatController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('obat');

        if ($request->filled('q')) {
            $query->where('nama_obat', 'like', '%' . $request->q . '%');
        }

        $obat = $query->select('id_obat', 'nama_obat', 'harga', 'exp_date')
            ->orderBy('nama_obat')
            ->get();

        return view('adminpoli.obat.index', compact('obat'));
    }

    public function create()
    {
        // Kalau kamu pakai modal, method ini boleh diabaikan.
        return view('adminpoli.obat.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_obat' => 'required|string|max:255',
            'harga'     => 'required|numeric',
            'exp_date'  => 'required|date',
        ]);

        // Ambil id_obat terakhir berdasarkan urutan terbesar (OBT-xxx)
        $lastId = DB::table('obat')
            ->where('id_obat', 'like', 'OBT-%')
            ->orderByRaw("CAST(SUBSTRING(id_obat, 5) AS UNSIGNED) DESC")
            ->value('id_obat');

        $nextNumber = 1;
        if ($lastId) {
            $nextNumber = (int) substr($lastId, 4) + 1; // "OBT-" = 4 char
        }

        $newId = 'OBT-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        DB::table('obat')->insert([
            'id_obat'   => $newId,
            'nama_obat' => $request->nama_obat,
            'harga'     => $request->harga,
            'exp_date'  => $request->exp_date,
            'created_at'=> now(),
            'updated_at'=> now(),
        ]);

        return redirect()->route('adminpoli.obat.index')
            ->with('success', 'Obat berhasil ditambahkan');
    }

    public function edit($id)
    {
        $obat = DB::table('obat')->where('id_obat', $id)->first();

        if (!$obat) {
            return redirect()->route('adminpoli.obat.index')
                ->with('error', 'Data obat tidak ditemukan');
        }

        return view('adminpoli.obat.edit', compact('obat'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_obat' => 'required|string|max:255',
            'harga'     => 'required|numeric',
            'exp_date'  => 'required|date',
        ]);

        DB::table('obat')
            ->where('id_obat', $id)
            ->update([
                'nama_obat'  => $request->nama_obat,
                'harga'      => $request->harga,
                'exp_date'   => $request->exp_date,
                'updated_at' => now(),
            ]);

        return redirect()->route('adminpoli.obat.index')
            ->with('success', 'Obat berhasil diperbarui');
    }

    public function destroy($id)
    {
        DB::table('obat')->where('id_obat', $id)->delete();

        return redirect()->route('adminpoli.obat.index')
            ->with('success', 'Obat berhasil dihapus');
    }

    // Placeholder (biar route ada & UI upload/download gak error)
    public function import(Request $request)
    {
        return redirect()->route('adminpoli.obat.index')
            ->with('error', 'Fitur import belum diaktifkan.');
    }

    public function export(Request $request)
    {
        return redirect()->route('adminpoli.obat.index')
            ->with('error', 'Fitur export belum diaktifkan.');
    }
}
