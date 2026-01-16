<?php

namespace App\Http\Controllers\AdminPoli;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class DiagnosaK3Controller extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('diagnosa_k3');

        // search: cari di id_nb / nama_penyakit / kategori
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function($w) use ($q){
                $w->where('id_nb', 'like', "%$q%")
                  ->orWhere('nama_penyakit', 'like', "%$q%")
                  ->orWhere('kategori_penyakit', 'like', "%$q%");
            });
        }

        // order id_nb biar 1.1, 1.2, 2.1.. rapih (numeric)
        $data = $query
            ->select('id_nb','nama_penyakit','kategori_penyakit','created_at')
            ->orderByRaw("CAST(SUBSTRING_INDEX(id_nb,'.',1) AS DECIMAL(10,2)) ASC")
            ->orderByRaw("CAST(SUBSTRING_INDEX(id_nb,'.',-1) AS DECIMAL(10,2)) ASC")
            ->get();

        $previewCount = null;
        if ($request->filled('from') && $request->filled('to')) {
            $previewCount = DB::table('diagnosa_k3')
                ->whereBetween('created_at', [
                    $request->from.' 00:00:00',
                    $request->to.' 23:59:59'
                ])->count();
        }

        return view('adminpoli.diagnosak3.index', [
            'data' => $data,
            'previewCount' => $previewCount,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_nb'            => ['required','string','max:10'],
            'nama_penyakit'    => ['required','string'],
            'kategori_penyakit'=> ['required','string','max:255'],
        ]);

        // id_nb harus unik (primary)
        $existsId = DB::table('diagnosa_k3')->where('id_nb', $request->id_nb)->exists();
        if ($existsId) {
            return redirect()->route('adminpoli.diagnosak3.index')
                ->withInput()
                ->with('error', 'Nomor (ID NB) sudah digunakan.');
        }

        // boleh duplicate nama? kalau mau dilarang per kategori, aktifkan cek ini:
        // $existsNama = DB::table('diagnosa_k3')
        //     ->whereRaw('LOWER(nama_penyakit)=?', [mb_strtolower(trim($request->nama_penyakit))])
        //     ->whereRaw('LOWER(kategori_penyakit)=?', [mb_strtolower(trim($request->kategori_penyakit))])
        //     ->exists();
        // if ($existsNama) { ... }

        DB::table('diagnosa_k3')->insert([
            'id_nb' => trim($request->id_nb),
            'nama_penyakit' => trim($request->nama_penyakit),
            'kategori_penyakit' => trim($request->kategori_penyakit),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('adminpoli.diagnosak3.index')
            ->with('success','Diagnosa K3 berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        // $id = id_nb (PK)
        $request->validate([
            'nama_penyakit'    => ['required','string'],
            'kategori_penyakit'=> ['required','string','max:255'],
        ]);

        DB::table('diagnosa_k3')->where('id_nb', $id)->update([
            'nama_penyakit' => trim($request->nama_penyakit),
            'kategori_penyakit' => trim($request->kategori_penyakit),
            'updated_at' => now(),
        ]);

        return redirect()->route('adminpoli.diagnosak3.index')
            ->with('success','Diagnosa K3 berhasil diperbarui');
    }

    public function destroy($id)
    {
        DB::table('diagnosa_k3')->where('id_nb', $id)->delete();

        return redirect()->route('adminpoli.diagnosak3.index')
            ->with('success','Diagnosa K3 berhasil dihapus');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:5120', // 5MB
        ]);

        $rows = Excel::toArray([], $request->file('file'))[0] ?? [];
        if (count($rows) <= 1) {
            return redirect()->route('adminpoli.diagnosak3.index')->with('error','File kosong / format tidak sesuai.');
        }

        $header = array_map(fn($h)=>Str::slug((string)$h,'_'), $rows[0]);
        $idxId = array_search('id_nb', $header);
        $idxNama = array_search('nama_penyakit', $header);
        $idxKat = array_search('kategori_penyakit', $header);

        if ($idxId===false || $idxNama===false || $idxKat===false) {
            return redirect()->route('adminpoli.diagnosak3.index')
                ->with('error','Header wajib: id_nb, nama_penyakit, kategori_penyakit');
        }

        $inserted=0; $skipped=0;

        foreach (array_slice($rows,1) as $r) {
            $idnb = trim((string)($r[$idxId] ?? ''));
            $nama = trim((string)($r[$idxNama] ?? ''));
            $kat  = trim((string)($r[$idxKat] ?? ''));

            if ($idnb==='' || $nama==='' || $kat==='') { $skipped++; continue; }

            $exists = DB::table('diagnosa_k3')->where('id_nb',$idnb)->exists();
            if ($exists) { $skipped++; continue; }

            DB::table('diagnosa_k3')->insert([
                'id_nb'=>$idnb,
                'nama_penyakit'=>$nama,
                'kategori_penyakit'=>$kat,
                'created_at'=>now(),
                'updated_at'=>now(),
            ]);
            $inserted++;
        }

        return redirect()->route('adminpoli.diagnosak3.index')
            ->with('success',"Import selesai. Berhasil: $inserted, Dilewati: $skipped");
    }

    public function export(Request $request)
    {
        $request->validate([
            'from'   => ['required','date'],
            'to'     => ['required','date','after_or_equal:from'],
            'format' => ['required','in:csv,excel,pdf'],
            'action' => ['required','in:preview,download'],
        ]);

        $from = $request->from.' 00:00:00';
        $to   = $request->to.' 23:59:59';

        $data = DB::table('diagnosa_k3')
            ->select('id_nb','kategori_penyakit','nama_penyakit','created_at')
            ->whereBetween('created_at', [$from,$to])
            ->orderByRaw("CAST(SUBSTRING_INDEX(id_nb,'.',1) AS DECIMAL(10,2)) ASC")
            ->orderByRaw("CAST(SUBSTRING_INDEX(id_nb,'.',-1) AS DECIMAL(10,2)) ASC")
            ->get();

        if ($request->action === 'preview') {
            return view('adminpoli.diagnosak3.preview', [
                'data'=>$data,
                'from'=>$request->from,
                'to'=>$request->to,
                'format'=>$request->format,
                'count'=>$data->count(),
            ]);
        }

        $fileBase = 'diagnosa_k3_'.$request->from.'_sd_'.$request->to;

        if ($request->format === 'csv') {
            return response()->streamDownload(function() use ($data){
                $out = fopen('php://output','w');
                fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
                fputcsv($out, ['ID NB','Kategori Penyakit','Nama Penyakit','Created At']);
                foreach ($data as $row) {
                    fputcsv($out, [$row->id_nb,$row->kategori_penyakit,$row->nama_penyakit,$row->created_at]);
                }
                fclose($out);
            }, $fileBase.'.csv', ['Content-Type'=>'text/csv; charset=UTF-8']);
        }

        if ($request->format === 'excel') {
            $html = view('adminpoli.diagnosak3.export_excel', [
                'data'=>$data, 'from'=>$request->from, 'to'=>$request->to
            ])->render();

            return response($html, 200, [
                'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="'.$fileBase.'.xls"',
            ]);
        }

        // pdf
        $pdf = Pdf::loadView('adminpoli.diagnosak3.export_pdf', [
            'data'=>$data, 'from'=>$request->from, 'to'=>$request->to
        ])->setPaper('A4','portrait');

        return $pdf->download($fileBase.'.pdf');
    }
}
