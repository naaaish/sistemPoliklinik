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
    private function nextKategoriId(): string
    {
        $max = DB::table('diagnosa_k3')
            ->where('tipe', 'kategori')
            ->selectRaw("MAX(CAST(id_nb AS UNSIGNED)) as m")
            ->value('m');

        $next = ((int)$max) + 1;
        return (string)$next; // "1", "2", dst
    }

    private function nextPenyakitId(string $parentId): string
    {
        $max = DB::table('diagnosa_k3')
            ->where('tipe', 'penyakit')
            ->where('parent_id', $parentId)
            ->selectRaw("MAX(CAST(SUBSTRING_INDEX(id_nb,'.',-1) AS UNSIGNED)) as m")
            ->value('m');

        $next = ((int)$max) + 1;
        return $parentId . '.' . $next; // "1.1", "1.2", dst
    }

    public function index(Request $request)
    {
        $q = trim((string)$request->q);

        // Ambil kategori
        $cats = DB::table('diagnosa_k3')
            ->where('tipe', 'kategori')
            ->when($q, function($query) use ($q){
                // search bisa kena kategori juga
                $query->where(function($w) use ($q){
                    $w->where('id_nb','like',"%$q%")
                      ->orWhere('nama_penyakit','like',"%$q%")
                      ->orWhere('kategori_penyakit','like',"%$q%");
                });
            })
            ->orderByRaw("CAST(id_nb AS UNSIGNED) ASC")
            ->get();

        // Ambil semua penyakit (filter search: kalau q ada, filter juga)
        $children = DB::table('diagnosa_k3')
            ->where('tipe', 'penyakit')
            ->when($q, function($query) use ($q){
                $query->where(function($w) use ($q){
                    $w->where('id_nb','like',"%$q%")
                      ->orWhere('nama_penyakit','like',"%$q%")
                      ->orWhere('kategori_penyakit','like',"%$q%");
                });
            })
            ->orderByRaw("CAST(SUBSTRING_INDEX(id_nb,'.',1) AS UNSIGNED) ASC")
            ->orderByRaw("CAST(SUBSTRING_INDEX(id_nb,'.',-1) AS UNSIGNED) ASC")
            ->get()
            ->groupBy('parent_id');

        // Kalau search: kategori yang tidak match tapi punya anak match, tetap tampilkan.
        if ($q) {
            $catIdsFromChild = collect($children)->keys()->filter()->values();
            $extraCats = DB::table('diagnosa_k3')
                ->where('tipe','kategori')
                ->whereIn('id_nb', $catIdsFromChild)
                ->orderByRaw("CAST(id_nb AS UNSIGNED) ASC")
                ->get();

            $cats = $cats->concat($extraCats)->unique('id_nb')->values()
                ->sortBy(fn($c)=>(int)$c->id_nb)
                ->values();
        }

        return view('adminpoli.diagnosak3.index', [
            'categories' => $cats,
            'children'   => $children,
        ]);
    }

    // ================= KATEGORI =================

    public function storeKategori(Request $request)
    {
        $request->validate([
            'nama_kategori' => ['required','string','max:255'],
        ]);

        $nama = trim($request->nama_kategori);

        $dup = DB::table('diagnosa_k3')
            ->where('tipe','kategori')
            ->whereRaw('LOWER(nama_penyakit)=?', [mb_strtolower($nama)])
            ->exists();

        if ($dup) return back()->with('error','Kategori sudah ada (duplikat).');

        $id = $this->nextKategoriId();

        DB::table('diagnosa_k3')->insert([
            'id_nb'           => $id,
            'tipe'            => 'kategori',
            'parent_id'       => null,
            'nama_penyakit'   => $nama, // dipakai sebagai nama kategori
            'kategori_penyakit'=> $nama, // biar konsisten
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        return back()->with('success','Kategori berhasil ditambahkan.');
    }

    public function updateKategori(Request $request, $id_nb)
    {
        $request->validate([
            'nama_kategori' => ['required','string','max:255'],
        ]);

        $id_nb = trim($id_nb);
        $nama  = trim($request->nama_kategori);

        $cat = DB::table('diagnosa_k3')->where('id_nb',$id_nb)->where('tipe','kategori')->first();
        if (!$cat) return back()->with('error','Kategori tidak ditemukan.');

        $dup = DB::table('diagnosa_k3')
            ->where('tipe','kategori')
            ->whereRaw('LOWER(nama_penyakit)=?', [mb_strtolower($nama)])
            ->where('id_nb','!=',$id_nb)
            ->exists();

        if ($dup) return back()->with('error','Nama kategori sudah ada (duplikat).');

        DB::transaction(function() use ($id_nb, $nama){
            // update row kategori
            DB::table('diagnosa_k3')->where('id_nb',$id_nb)->update([
                'nama_penyakit' => $nama,
                'kategori_penyakit' => $nama,
                'updated_at' => now(),
            ]);

            // update semua anak: kategori_penyakit ikut berubah
            DB::table('diagnosa_k3')
                ->where('tipe','penyakit')
                ->where('parent_id',$id_nb)
                ->update([
                    'kategori_penyakit' => $nama,
                    'updated_at' => now(),
                ]);
        });

        return back()->with('success','Kategori berhasil diubah.');
    }

    public function destroyKategori($id_nb)
    {
        $id_nb = trim($id_nb);

        DB::transaction(function() use ($id_nb){
            DB::table('diagnosa_k3')->where('tipe','penyakit')->where('parent_id',$id_nb)->delete();
            DB::table('diagnosa_k3')->where('tipe','kategori')->where('id_nb',$id_nb)->delete();
            
            $this->renumberAllCategories();
        });

        return back()->with('success','Kategori dan seluruh isinya berhasil dihapus.');
    }

    // ================= PENYAKIT =================

    public function storePenyakit(Request $request)
    {
        $request->validate([
            'parent_id'     => ['required','string','max:10'],
            'nama_penyakit' => ['required','string'],
        ]);

        $parent = trim($request->parent_id);
        $nama   = trim($request->nama_penyakit);

        $cat = DB::table('diagnosa_k3')->where('id_nb',$parent)->where('tipe','kategori')->first();
        if (!$cat) return back()->with('error','Kategori tidak valid.');

        $dup = DB::table('diagnosa_k3')
            ->where('tipe','penyakit')
            ->where('parent_id',$parent)
            ->whereRaw('LOWER(nama_penyakit)=?', [mb_strtolower($nama)])
            ->exists();

        if ($dup) return back()->with('error','Nama penyakit sudah ada di kategori ini (duplikat).');

        $id = $this->nextPenyakitId($parent);

        DB::table('diagnosa_k3')->insert([
            'id_nb'            => $id,
            'tipe'             => 'penyakit',
            'parent_id'        => $parent,
            'nama_penyakit'    => $nama,
            'kategori_penyakit'=> $cat->nama_penyakit, // nama kategori terbaru
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        return back()->with('success','Penyakit berhasil ditambahkan.');
    }

    public function updatePenyakit(Request $request, $id_nb)
    {
        $request->validate([
            'parent_id'     => ['required','string','max:10'],
            'nama_penyakit' => ['required','string'],
        ]);

        $id_nb  = trim($id_nb);
        $parent = trim($request->parent_id);
        $nama   = trim($request->nama_penyakit);

        $row = DB::table('diagnosa_k3')->where('id_nb',$id_nb)->where('tipe','penyakit')->first();
        if (!$row) return back()->with('error','Penyakit tidak ditemukan.');

        $cat = DB::table('diagnosa_k3')->where('id_nb',$parent)->where('tipe','kategori')->first();
        if (!$cat) return back()->with('error','Kategori tidak valid.');

        // cek duplikat di kategori target
        $dup = DB::table('diagnosa_k3')
            ->where('tipe','penyakit')
            ->where('parent_id',$parent)
            ->whereRaw('LOWER(nama_penyakit)=?', [mb_strtolower($nama)])
            ->where('id_nb','!=',$id_nb)
            ->exists();

        if ($dup) return back()->with('error','Nama penyakit sudah ada di kategori target (duplikat).');

        DB::transaction(function() use ($row, $id_nb, $parent, $nama, $cat){
            if ($row->parent_id !== $parent) {
                // pindah kategori => id_nb harus jadi paling bawah kategori target
                $newId = $this->nextPenyakitId($parent);

                DB::table('diagnosa_k3')->where('id_nb',$id_nb)->update([
                    'id_nb' => $newId,
                    'parent_id' => $parent,
                    'nama_penyakit' => $nama,
                    'kategori_penyakit' => $cat->nama_penyakit,
                    'updated_at' => now(),
                ]);

                // optional: rapikan nomor di kategori lama (isi gap)
                $this->renumberChildren($row->parent_id);
            } else {
                DB::table('diagnosa_k3')->where('id_nb',$id_nb)->update([
                    'nama_penyakit' => $nama,
                    'kategori_penyakit' => $cat->nama_penyakit,
                    'updated_at' => now(),
                ]);
            }
        });

        return back()->with('success','Penyakit berhasil diperbarui.');
    }

    public function destroyPenyakit($id_nb)
    {
        $id_nb = trim($id_nb);
        $row = DB::table('diagnosa_k3')->where('id_nb',$id_nb)->where('tipe','penyakit')->first();
        if (!$row) return back()->with('error','Data tidak ditemukan.');

        DB::transaction(function() use ($row, $id_nb){
            DB::table('diagnosa_k3')->where('id_nb',$id_nb)->delete();
            $this->renumberChildren($row->parent_id);
        });

        return back()->with('success','Penyakit berhasil dihapus.');
    }

    private function renumberChildren(string $parentId): void
    {
        $kids = DB::table('diagnosa_k3')
            ->where('tipe','penyakit')
            ->where('parent_id',$parentId)
            ->orderByRaw("CAST(SUBSTRING_INDEX(id_nb,'.',-1) AS UNSIGNED) ASC")
            ->get();

        // pakai temporary untuk menghindari collision
        foreach ($kids as $i => $k) {
            $tmp = 't' . str_pad((string)($i+1), 9, '0', STR_PAD_LEFT); // 10 char
            DB::table('diagnosa_k3')->where('id_nb',$k->id_nb)->update(['id_nb'=>$tmp]);
        }
        foreach ($kids as $i => $k) {
            $tmp = 't' . str_pad((string)($i+1), 9, '0', STR_PAD_LEFT);
            $new = $parentId . '.' . ($i+1);
            DB::table('diagnosa_k3')->where('id_nb',$tmp)->update(['id_nb'=>$new, 'updated_at'=>now()]);
        }
    }

    private function renumberAllCategories(): void
    {
        $cats = DB::table('diagnosa_k3')
            ->where('tipe','kategori')
            ->orderByRaw("CAST(id_nb AS UNSIGNED) ASC")
            ->get();

        // 1) TEMP rename kategori + parent_id anak (hindari collision)
        foreach ($cats as $i => $c) {
            $tmp = 'c' . str_pad((string)($i+1), 9, '0', STR_PAD_LEFT); // c000000001
            DB::table('diagnosa_k3')
                ->where('tipe','kategori')
                ->where('id_nb', $c->id_nb)
                ->update(['id_nb' => $tmp, 'updated_at' => now()]);

            DB::table('diagnosa_k3')
                ->where('tipe','penyakit')
                ->where('parent_id', $c->id_nb)
                ->update(['parent_id' => $tmp, 'updated_at' => now()]);
        }

        // 2) FINAL rename kategori => 1..n
        foreach ($cats as $i => $c) {
            $tmp   = 'c' . str_pad((string)($i+1), 9, '0', STR_PAD_LEFT);
            $final = (string)($i+1);

            DB::table('diagnosa_k3')
                ->where('tipe','kategori')
                ->where('id_nb', $tmp)
                ->update(['id_nb' => $final, 'updated_at' => now()]);

            DB::table('diagnosa_k3')
                ->where('tipe','penyakit')
                ->where('parent_id', $tmp)
                ->update(['parent_id' => $final, 'updated_at' => now()]);
        }

        // 3) setelah kategori rapih, rapihin penyakit per kategori
        $finalCats = DB::table('diagnosa_k3')
            ->where('tipe','kategori')
            ->orderByRaw("CAST(id_nb AS UNSIGNED) ASC")
            ->pluck('id_nb')
            ->toArray();

        foreach ($finalCats as $catId) {
            $this->renumberChildren((string)$catId);
        }
    }

    // ================= import/export kamu boleh tetap, tapi pastikan filter tipe penyakit =================
    // (biar exportnya untuk laporan "riwayat pemeriksaan" enak)
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:5120',
        ]);

        $rows = Excel::toArray([], $request->file('file'))[0] ?? [];
        if (count($rows) <= 1) {
            return back()->with('error','File kosong / format tidak sesuai.');
        }

        $header = array_map(fn($h)=>Str::slug((string)$h,'_'), $rows[0]);
        $idxNama = array_search('nama_penyakit', $header);
        $idxKat  = array_search('kategori_penyakit', $header);

        if ($idxNama===false || $idxKat===false) {
            return back()->with('error','Header wajib: nama_penyakit, kategori_penyakit');
        }

        $inserted=0; $skipped=0;

        DB::transaction(function() use ($rows, $idxNama, $idxKat, &$inserted, &$skipped){
            foreach (array_slice($rows,1) as $r) {
                $nama = trim((string)($r[$idxNama] ?? ''));
                $kat  = trim((string)($r[$idxKat] ?? ''));

                if ($nama==='' || $kat==='') { $skipped++; continue; }

                // cari / buat kategori
                $cat = DB::table('diagnosa_k3')
                    ->where('tipe','kategori')
                    ->whereRaw('LOWER(nama_penyakit)=?', [mb_strtolower($kat)])
                    ->first();

                if (!$cat) {
                    $newCatId = $this->nextKategoriId();
                    DB::table('diagnosa_k3')->insert([
                        'id_nb'=>$newCatId,
                        'tipe'=>'kategori',
                        'parent_id'=>null,
                        'nama_penyakit'=>$kat,
                        'kategori_penyakit'=>$kat,
                        'created_at'=>now(),
                        'updated_at'=>now(),
                    ]);
                    $cat = (object)['id_nb'=>$newCatId, 'nama_penyakit'=>$kat];
                }

                // duplikat penyakit di kategori
                $dup = DB::table('diagnosa_k3')
                    ->where('tipe','penyakit')
                    ->where('parent_id',$cat->id_nb)
                    ->whereRaw('LOWER(nama_penyakit)=?', [mb_strtolower($nama)])
                    ->exists();
                if ($dup) { $skipped++; continue; }

                $newId = $this->nextPenyakitId($cat->id_nb);

                DB::table('diagnosa_k3')->insert([
                    'id_nb'=>$newId,
                    'tipe'=>'penyakit',
                    'parent_id'=>$cat->id_nb,
                    'nama_penyakit'=>$nama,
                    'kategori_penyakit'=>$cat->nama_penyakit,
                    'created_at'=>now(),
                    'updated_at'=>now(),
                ]);

                $inserted++;
            }
        });

        return back()->with('success',"Import selesai. Berhasil: $inserted, Dilewati: $skipped");
    }

    public function export(Request $request)
    {
        $request->validate([
            'format' => ['required','in:csv,excel,pdf'],
            'action' => ['required','in:preview,download'],
        ]);

        // Ambil kategori urut 1..n
        $cats = DB::table('diagnosa_k3')
            ->where('tipe','kategori')
            ->orderByRaw("CAST(id_nb AS UNSIGNED) ASC")
            ->get();

        // Ambil penyakit urut per kategori
        $kids = DB::table('diagnosa_k3')
            ->where('tipe','penyakit')
            ->orderByRaw("CAST(SUBSTRING_INDEX(id_nb,'.',1) AS UNSIGNED) ASC")
            ->orderByRaw("CAST(SUBSTRING_INDEX(id_nb,'.',-1) AS UNSIGNED) ASC")
            ->get()
            ->groupBy('parent_id');

        // Flatten untuk export tabel (kategori jadi row header)
        $rows = [];
        foreach ($cats as $c) {
            $rows[] = (object)[
                'tipe' => 'kategori',
                'id_nb' => $c->id_nb,
                'kategori_penyakit' => $c->nama_penyakit,
                'nama_penyakit' => '',
            ];

            foreach (($kids[$c->id_nb] ?? collect()) as $p) {
                $rows[] = (object)[
                    'tipe' => 'penyakit',
                    'id_nb' => $p->id_nb,
                    'kategori_penyakit' => $p->kategori_penyakit,
                    'nama_penyakit' => $p->nama_penyakit,
                ];
            }
        }

        $countPenyakit = DB::table('diagnosa_k3')->where('tipe','penyakit')->count();
        $countKategori = DB::table('diagnosa_k3')->where('tipe','kategori')->count();

        if ($request->action === 'preview') {
            return view('adminpoli.diagnosak3.preview', [
                'rows' => $rows,
                'countKategori' => $countKategori,
                'countPenyakit' => $countPenyakit,
                'format' => $request->format,
            ]);
        }

        $fileBase = 'diagnosa_k3_' . date('Y-m-d_His');

        if ($request->format === 'csv') {
            return response()->streamDownload(function() use ($rows){
                $out = fopen('php://output','w');
                fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
                fputcsv($out, ['Tipe','ID NB','Kategori','Nama Penyakit']);
                foreach ($rows as $r) {
                    fputcsv($out, [$r->tipe, $r->id_nb, $r->kategori_penyakit, $r->nama_penyakit]);
                }
                fclose($out);
            }, $fileBase.'.csv', ['Content-Type'=>'text/csv; charset=UTF-8']);
        }

        if ($request->format === 'excel') {
            $html = view('adminpoli.diagnosak3.export_excel', [
                'rows' => $rows
            ])->render();

            return response($html, 200, [
                'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="'.$fileBase.'.xls"',
            ]);
        }

        $pdf = Pdf::loadView('adminpoli.diagnosak3.export_pdf', [
            'rows' => $rows
        ])->setPaper('A4','portrait');

        return $pdf->download($fileBase.'.pdf');
    }

}
