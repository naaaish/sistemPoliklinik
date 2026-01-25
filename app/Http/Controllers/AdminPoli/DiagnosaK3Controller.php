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
            ->where('is_active', true)
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
            ->where('is_active', true)
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
        $nama = trim($request->input('nama_kategori'));

        if ($nama === '') {
            return back()->with('error', 'Nama kategori wajib diisi.');
        }

        // 1. kalau sudah ada & aktif → tolak
        $existsActive = DB::table('diagnosa_k3')
            ->where('tipe','kategori')
            ->where('is_active',1)
            ->whereRaw('LOWER(nama_penyakit)=?', [mb_strtolower($nama)])
            ->exists();

        if ($existsActive) {
            return back()->with('error', 'Kategori sudah ada.');
        }

        // 2. kalau ada tapi nonaktif → restore
        $inactive = DB::table('diagnosa_k3')
            ->where('tipe','kategori')
            ->where('is_active',0)
            ->whereRaw('LOWER(nama_penyakit)=?', [mb_strtolower($nama)])
            ->first();

        DB::transaction(function() use ($inactive, $nama) {

            if ($inactive) {
                // aktifkan kategori
                DB::table('diagnosa_k3')
                    ->where('tipe','kategori')
                    ->where('id_nb',$inactive->id_nb)
                    ->update([
                        'is_active'=>1,
                        'updated_at'=>now()
                    ]);

                // aktifkan lagi semua penyakitnya
                DB::table('diagnosa_k3')
                    ->where('tipe','penyakit')
                    ->where('parent_id',$inactive->id_nb)
                    ->update([
                        'is_active'=>1,
                        'updated_at'=>now()
                    ]);
            } else {
                // insert baru
                $next = (int) DB::table('diagnosa_k3')
                    ->where('tipe','kategori')
                    ->max(DB::raw('CAST(id_nb AS UNSIGNED)')) + 1;

                DB::table('diagnosa_k3')->insert([
                    'id_nb' => (string)$next,
                    'tipe' => 'kategori',
                    'parent_id' => null,
                    'nama_penyakit' => $nama,
                    'kategori_penyakit' => $nama,
                    'is_active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        return back()->with('success', $inactive
            ? 'Kategori diaktifkan kembali.'
            : 'Kategori berhasil ditambahkan.'
        );
    }

    public function updateKategori(Request $request, $id_nb)
    {
        $request->validate([
            'nama_kategori' => ['required','string','max:255'],
        ]);

        $id_nb = trim($id_nb);
        $nama  = trim($request->nama_kategori);

        $cat = DB::table('diagnosa_k3')
            ->where('tipe','kategori')
            ->where('id_nb',$id_nb)
            ->where('is_active',1)
            ->first();

        if (!$cat) {
            return back()->with('error','Kategori tidak ditemukan.');
        }

        // cek duplikat (aktif saja)
        $dup = DB::table('diagnosa_k3')
            ->where('tipe','kategori')
            ->where('is_active',1)
            ->whereRaw('LOWER(nama_penyakit)=?', [mb_strtolower($nama)])
            ->where('id_nb','!=',$id_nb)
            ->exists();

        if ($dup) {
            return back()->with('error','Nama kategori sudah ada.');
        }

        DB::transaction(function() use ($id_nb, $nama){
            // update kategori
            DB::table('diagnosa_k3')
                ->where('tipe','kategori')
                ->where('id_nb',$id_nb)
                ->update([
                    'nama_penyakit' => $nama,
                    'kategori_penyakit' => $nama,
                    'updated_at' => now(),
                ]);

            // update nama kategori di semua penyakit anak
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
        DB::transaction(function () use ($id_nb) {
            DB::table('diagnosa_k3')
                ->where('tipe','kategori')
                ->where('id_nb',$id_nb)
                ->update([
                    'is_active' => 0,
                    'updated_at' => now()
                ]);

            DB::table('diagnosa_k3')
                ->where('tipe','penyakit')
                ->where('parent_id',$id_nb)
                ->update([
                    'is_active' => 0,
                    'updated_at' => now()
                ]);
        });

        return back()->with('success','Kategori dinonaktifkan.');
    }

    public function storePenyakit(Request $request)
    {
        $parent = trim($request->input('parent_id'));
        $nama   = trim($request->input('nama_penyakit'));

        if ($parent === '' || $nama === '') {
            return back()->with('error','Kategori dan nama penyakit wajib diisi.');
        }

        $cat = DB::table('diagnosa_k3')
            ->where('tipe','kategori')
            ->where('id_nb',$parent)
            ->where('is_active',1)
            ->first();

        if (!$cat) {
            return back()->with('error','Kategori tidak aktif / tidak ditemukan.');
        }

        // aktif & duplikat
        $existsActive = DB::table('diagnosa_k3')
            ->where('tipe','penyakit')
            ->where('parent_id',$parent)
            ->where('is_active',1)
            ->whereRaw('LOWER(nama_penyakit)=?', [mb_strtolower($nama)])
            ->exists();

        if ($existsActive) {
            return back()->with('error','Penyakit sudah ada di kategori ini.');
        }

        // restore?
        $inactive = DB::table('diagnosa_k3')
            ->where('tipe','penyakit')
            ->where('parent_id',$parent)
            ->where('is_active',0)
            ->whereRaw('LOWER(nama_penyakit)=?', [mb_strtolower($nama)])
            ->first();

        DB::transaction(function() use ($inactive, $parent, $nama, $cat){
            if ($inactive) {
                DB::table('diagnosa_k3')
                    ->where('id_nb',$inactive->id_nb)
                    ->update([
                        'is_active'=>1,
                        'updated_at'=>now()
                    ]);
            } else {
                $id = $this->nextPenyakitId($parent);

                DB::table('diagnosa_k3')->insert([
                    'id_nb' => $id,
                    'tipe' => 'penyakit',
                    'parent_id' => $parent,
                    'nama_penyakit' => $nama,
                    'kategori_penyakit' => $cat->nama_penyakit,
                    'is_active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        return back()->with('success', $inactive
            ? 'Penyakit diaktifkan kembali.'
            : 'Penyakit berhasil ditambahkan.'
        );
    }

    public function updatePenyakit(Request $request, $id_nb)
    {
        $request->validate([
            'parent_id'     => ['required','string'],
            'nama_penyakit' => ['required','string'],
        ]);

        $id_nb  = trim($id_nb);
        $parent = trim($request->parent_id);
        $nama   = trim($request->nama_penyakit);

        $row = DB::table('diagnosa_k3')
            ->where('tipe','penyakit')
            ->where('id_nb',$id_nb)
            ->where('is_active',1)
            ->first();

        if (!$row) {
            return back()->with('error','Penyakit tidak ditemukan.');
        }

        $cat = DB::table('diagnosa_k3')
            ->where('tipe','kategori')
            ->where('id_nb',$parent)
            ->where('is_active',1)
            ->first();

        if (!$cat) {
            return back()->with('error','Kategori tidak valid.');
        }

        // duplikat aktif di kategori target
        $dup = DB::table('diagnosa_k3')
            ->where('tipe','penyakit')
            ->where('is_active',1)
            ->where('parent_id',$parent)
            ->whereRaw('LOWER(nama_penyakit)=?', [mb_strtolower($nama)])
            ->where('id_nb','!=',$id_nb)
            ->exists();

        if ($dup) {
            return back()->with('error','Nama penyakit sudah ada di kategori tersebut.');
        }

        DB::transaction(function() use ($row, $id_nb, $parent, $nama, $cat){

            if ($row->parent_id !== $parent) {
                // pindah kategori
                $newId = $this->nextPenyakitId($parent);

                DB::table('diagnosa_k3')
                    ->where('id_nb',$id_nb)
                    ->update([
                        'id_nb' => $newId,
                        'parent_id' => $parent,
                        'nama_penyakit' => $nama,
                        'kategori_penyakit' => $cat->nama_penyakit,
                        'updated_at' => now(),
                    ]);
            } else {
                DB::table('diagnosa_k3')
                    ->where('id_nb',$id_nb)
                    ->update([
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
        DB::table('diagnosa_k3')
            ->where('tipe','penyakit')
            ->where('id_nb',$id_nb)
            ->update([
                'is_active' => 0,
                'updated_at' => now()
            ]);

        return back()->with('success','Penyakit dinonaktifkan.');
    }

    public function import(Request $request)
    {
        $request->validate(
            [
                'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
            ],
            [
                'file.required' => 'File wajib dipilih.',
                'file.mimes'    => 'Format file tidak valid. Gunakan Excel (.xlsx / .xls).',
                'file.max'      => 'Ukuran file maksimal 5 MB.',
            ]
        );


        $rows = Excel::toArray([], $request->file('file'))[0] ?? [];
        if (count($rows) <= 1) {
            return back()->with('error','File kosong / format tidak sesuai.');
        }

        // header normalize
        $headerRaw = $rows[0] ?? [];
        $header = array_map(function($h){
            $h = (string)$h;
            $h = preg_replace('/^\xEF\xBB\xBF/', '', $h); // buang BOM UTF-8
            return Str::slug($h, '_');
        }, $headerRaw);

        // DETECT format
        $idxNama = array_search('nama_penyakit', $header);
        $idxKat  = array_search('kategori_penyakit', $header);

        $idxNomor = array_search('nomor', $header);
        $idxJenis = array_search('jenis_penyakit', $header);
        if ($idxJenis === false) {
            $idxJenis = array_search('jenis_penyakit', array_map(fn($h)=>Str::slug((string)$h,'_'), $headerRaw));
        }
        // (kalau di excel headernya "Jenis Penyakit", slug -> jenis_penyakit)

        $isFormatA = ($idxNama !== false && $idxKat !== false);
        $isFormatB = ($idxNomor !== false && $idxJenis !== false);

        $idxTipe = array_search('tipe', $header);
        $idxIdNb = array_search('id_nb', $header);
        $idxKategori = array_search('kategori', $header);
        $idxNamaP = array_search('nama_penyakit', $header);

        $isFormatC = ($idxTipe !== false && $idxIdNb !== false && $idxKategori !== false && $idxNamaP !== false);

        if (!$isFormatA && !$isFormatB && !$isFormatC) {
            return back()->with('error','Header tidak dikenali. Pakai (nama_penyakit,kategori_penyakit) atau (Nomor,Jenis Penyakit) atau (Tipe,ID NB,Kategori,Nama Penyakit).');
        }

        $inserted = 0; $skipped = 0; $restored = 0;

        DB::transaction(function() use (
            $rows,
            $isFormatA, $idxNama, $idxKat,
            $isFormatB, $idxNomor, $idxJenis,
            $isFormatC, $idxTipe, $idxIdNb, $idxKategori, $idxNamaP,
            &$inserted, &$skipped, &$restored
        ){
            // helper: cari kategori by nama (case-insensitive), restore kalau nonaktif
            $getOrCreateCategory = function(string $namaKat) use (&$restored, &$inserted){
                $namaKat = trim($namaKat);
                if ($namaKat === '') return null;

                $cat = DB::table('diagnosa_k3')
                    ->where('tipe','kategori')
                    ->whereRaw('LOWER(nama_penyakit)=?', [mb_strtolower($namaKat)])
                    ->first();

                if ($cat) {
                    if ((int)$cat->is_active === 0) {
                        DB::table('diagnosa_k3')->where('tipe','kategori')->where('id_nb',$cat->id_nb)
                            ->update(['is_active'=>1,'updated_at'=>now()]);
                        DB::table('diagnosa_k3')->where('tipe','penyakit')->where('parent_id',$cat->id_nb)
                            ->update(['is_active'=>1,'updated_at'=>now()]);
                        $restored++;
                    }
                    return $cat;
                }

                $max = DB::table('diagnosa_k3')->where('tipe','kategori')
                    ->selectRaw("MAX(CAST(id_nb AS UNSIGNED)) as m")->value('m');
                $next = ((int)$max) + 1;

                DB::table('diagnosa_k3')->insert([
                    'id_nb' => (string)$next,
                    'tipe' => 'kategori',
                    'parent_id' => null,
                    'nama_penyakit' => $namaKat,
                    'kategori_penyakit' => $namaKat,
                    'is_active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $inserted++;

                return (object)['id_nb'=>(string)$next,'nama_penyakit'=>$namaKat,'is_active'=>1];
            };

            // helper: add/restore penyakit dalam kategori id_nb
            $addOrRestoreDisease = function(string $parentId, string $namaP) use (&$restored, &$inserted, &$skipped){
                $namaP = trim($namaP);
                if ($namaP === '' || preg_match('/^lainnya sebutkan/i', $namaP)) { $skipped++; return; }

                $existsActive = DB::table('diagnosa_k3')
                    ->where('tipe','penyakit')
                    ->where('parent_id',$parentId)
                    ->where('is_active',1)
                    ->whereRaw('LOWER(nama_penyakit)=?', [mb_strtolower($namaP)])
                    ->exists();
                if ($existsActive) { $skipped++; return; }

                $inactive = DB::table('diagnosa_k3')
                    ->where('tipe','penyakit')
                    ->where('parent_id',$parentId)
                    ->where('is_active',0)
                    ->whereRaw('LOWER(nama_penyakit)=?', [mb_strtolower($namaP)])
                    ->first();
                if ($inactive) {
                    DB::table('diagnosa_k3')->where('tipe','penyakit')->where('id_nb',$inactive->id_nb)
                        ->update(['is_active'=>1,'updated_at'=>now()]);
                    $restored++;
                    return;
                }

                $max = DB::table('diagnosa_k3')
                    ->where('tipe','penyakit')
                    ->where('parent_id',$parentId)
                    ->selectRaw("MAX(CAST(SUBSTRING_INDEX(id_nb,'.',-1) AS UNSIGNED)) as m")
                    ->value('m');

                $next = ((int)$max) + 1;
                $newId = $parentId . '.' . $next;

                $catName = DB::table('diagnosa_k3')->where('tipe','kategori')->where('id_nb',$parentId)->value('nama_penyakit');

                DB::table('diagnosa_k3')->insert([
                    'id_nb' => $newId,
                    'tipe' => 'penyakit',
                    'parent_id' => $parentId,
                    'nama_penyakit' => $namaP,
                    'kategori_penyakit' => $catName ?: '',
                    'is_active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $inserted++;
            };

            // -------- PARSE (rapih pake elseif) --------
            if ($isFormatA) {
                foreach (array_slice($rows, 1) as $r) {
                    $nama = trim((string)($r[$idxNama] ?? ''));
                    $kat  = trim((string)($r[$idxKat] ?? ''));
                    if ($nama==='' || $kat==='') { $skipped++; continue; }

                    $cat = $getOrCreateCategory($kat);
                    if (!$cat) { $skipped++; continue; }

                    $addOrRestoreDisease($cat->id_nb, $nama);
                }
                return;
            }

            if ($isFormatB) {
                $currentCatId = null;

                foreach (array_slice($rows, 1) as $r) {
                    $nomor = trim((string)($r[$idxNomor] ?? ''));
                    $jenis = trim((string)($r[$idxJenis] ?? ''));

                    if ($nomor === '' && $jenis === '') { $skipped++; continue; }
                    if ($jenis !== '' && preg_match('/^lainnya sebutkan/i', $jenis)) { $skipped++; continue; }

                    if (preg_match('/^\d+$/', $nomor)) {
                        if ($jenis === '') { $skipped++; continue; }
                        $cat = $getOrCreateCategory($jenis);
                        $currentCatId = $cat?->id_nb;
                        continue;
                    }

                    if (preg_match('/^(\d+)\.(\d+)$/', $nomor, $m)) {
                        $catIdFromNomor = $m[1];

                        $catRow = DB::table('diagnosa_k3')
                            ->where('tipe','kategori')
                            ->where('id_nb',$catIdFromNomor)
                            ->first();

                        $parentId = $catRow?->id_nb ?: $currentCatId;
                        if (!$parentId) { $skipped++; continue; }

                        $addOrRestoreDisease((string)$parentId, $jenis);
                        continue;
                    }

                    $skipped++;
                }
                return;
            }

            if ($isFormatC) {
                foreach (array_slice($rows, 1) as $r) {
                    $tipe = trim((string)($r[$idxTipe] ?? ''));
                    $kat  = trim((string)($r[$idxKategori] ?? ''));
                    $nama = trim((string)($r[$idxNamaP] ?? ''));

                    if ($tipe === '' || $kat === '') { $skipped++; continue; }

                    if (mb_strtolower($tipe) === 'kategori') {
                        $cat = $getOrCreateCategory($kat);
                        if (!$cat) { $skipped++; }
                        continue;
                    }

                    if (mb_strtolower($tipe) === 'penyakit') {
                        if ($nama === '' || preg_match('/^lainnya sebutkan/i', $nama)) { $skipped++; continue; }
                        $cat = $getOrCreateCategory($kat);
                        if (!$cat) { $skipped++; continue; }

                        $addOrRestoreDisease($cat->id_nb, $nama);
                        continue;
                    }

                    $skipped++;
                }
                return;
            }
        });
        return back()->with('success',"Import selesai. Ditambah: $inserted, Diaktifkan lagi: $restored, Dilewati: $skipped");
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
            return Excel::download(
                new \App\Exports\DiagnosaK3Export($rows),
                $fileBase . '.xlsx'
            );
        }

        $pdf = Pdf::loadView('adminpoli.diagnosak3.export_pdf', [
            'rows' => $rows
        ])->setPaper('A4','portrait');

        return $pdf->download($fileBase.'.pdf');
    }

}
