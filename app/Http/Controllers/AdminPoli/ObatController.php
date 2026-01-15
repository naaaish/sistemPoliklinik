<?php

namespace App\Http\Controllers\AdminPoli;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ObatController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('obat');

        if ($request->q) {
            $query->where('nama_obat', 'like', '%' . $request->q . '%');
        }

        $obat = $query->orderBy('nama_obat')->get();

        return view('adminpoli.obat.index', compact('obat'));
    }

    public function create()
    {
        return view('adminpoli.obat.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_obat' => 'required',
            'harga'     => 'required|numeric',
            'exp_date'  => 'required|date',
        ]);

        DB::table('obat')->insert([
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
        $obat = DB::table('obat')->where('id', $id)->first();

        return view('adminpoli.obat.edit', compact('obat'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_obat' => 'required',
            'harga'     => 'required|numeric',
            'exp_date'  => 'required|date',
        ]);

        DB::table('obat')->where('id', $id)->update([
            'nama_obat' => $request->nama_obat,
            'harga'     => $request->harga,
            'exp_date'  => $request->exp_date,
            'updated_at'=> now(),
        ]);

        return redirect()->route('adminpoli.obat.index')
            ->with('success', 'Obat berhasil diperbarui');
    }

    public function destroy($id)
    {
        DB::table('obat')->where('id', $id)->delete();

        return redirect()->route('adminpoli.obat.index')
            ->with('success', 'Obat berhasil dihapus');
    }
}
