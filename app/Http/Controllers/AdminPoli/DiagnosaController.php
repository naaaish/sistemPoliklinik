<?php

namespace App\Http\Controllers\AdminPoli;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\DiagnosaExport;

class DiagnosaController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('diagnosa')
            ->where('is_active', 1)
            ->orderBy('created_at', 'desc');

        // search
        if ($request->filled('q')) {
            $q = mb_strtolower($request->q);
            $query->whereRaw('LOWER(diagnosa) LIKE ?', ["%{$q}%"]);
        }

        $perPage = $request->get('per_page', 10);
        $allowed = ['10', '25', '50', '100', 'all'];
        if (!in_array((string) $perPage, $allowed)) $perPage = 10;

        $base = $query
            ->select(
                'id_diagnosa',
                'diagnosa',
                'keterangan',
                'klasifikasi_nama',
                'bagian_tubuh',
                'created_at'
            )
            ->orderBy('diagnosa', 'asc');

        $diagnosa = ($perPage === 'all')
            ? $base->get()
            : $base->paginate((int) $perPage)->appends($request->query());

        // preview count by created_at range (untuk download)
        $previewCount = null;
        if ($request->filled('from') && $request->filled('to')) {
            $previewCount = DB::table('diagnosa')
                ->where('is_active', 1)
                ->whereBetween('created_at', [
                    $request->from . ' 00:00:00',
                    $request->to   . ' 23:59:59',
                ])->count();
        }

        return view('adminpoli.diagnosa.index', compact('diagnosa', 'previewCount', 'perPage'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'diagnosa' => 'required|string',
            'keterangan' => 'nullable|string',
            'klasifikasi_nama' => 'nullable|string',
            'bagian_tubuh' => 'nullable|string',
        ]);

        $text = trim($request->diagnosa);
        
        $exists = DB::table('diagnosa')
            ->whereRaw('LOWER(diagnosa) = ?', [mb_strtolower($text)])
            ->where('is_active', 1)
            ->exists();

        if ($exists) {
            return redirect()->route('adminpoli.diagnosa.index')
                ->withInput()
                ->with('error', 'Diagnosa sudah ada.');
        }

        $last = (int) DB::table('diagnosa')->max('id_diagnosa');
        $newId = $last + 1;

        DB::table('diagnosa')->insert([
            'id_diagnosa' => $newId,
            'diagnosa' => $text,
            'keterangan' => $request->keterangan,
            'klasifikasi_nama' => $request->klasifikasi_nama,
            'bagian_tubuh' => $request->bagian_tubuh,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        return redirect()->route('adminpoli.diagnosa.index')
            ->with('success', 'Diagnosa berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'diagnosa' => 'required|string',
            'keterangan' => 'nullable|string',
            'klasifikasi_nama' => 'nullable|string',
            'bagian_tubuh' => 'nullable|string',
        ]);

        $text = trim($request->diagnosa);

        $exists = DB::table('diagnosa')
            ->whereRaw('LOWER(diagnosa) = ?', [mb_strtolower($text)])
            ->where('is_active', 1)
            ->where('id_diagnosa', '!=', $id)
            ->exists();

        if ($exists) {
            return redirect()->route('adminpoli.diagnosa.index')
                ->withInput()
                ->with('error', 'Diagnosa sudah ada.');
        }

        DB::table('diagnosa')
            ->where('id_diagnosa', $id)
            ->update([
                'diagnosa'         => $text,
                'keterangan'       => $request->keterangan,
                'klasifikasi_nama' => $request->klasifikasi_nama,
                'bagian_tubuh'     => $request->bagian_tubuh,
                'updated_at'       => now(),
            ]);

        return redirect()->route('adminpoli.diagnosa.index')
            ->with('success', 'Diagnosa berhasil diperbarui');
    }

    public function destroy($id)
    {
        DB::table('diagnosa')
            ->where('id_diagnosa', $id)
            ->update([
                'is_active' => 0,
                'updated_at' => now()
            ]);

        return redirect()->route('adminpoli.diagnosa.index')
            ->with('success', 'Diagnosa berhasil dihapus');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:2048',
        ]);

        $file = $request->file('file');
        $ext  = strtolower($file->getClientOriginalExtension());

        // ===== 1) Ambil rows dari file (XLSX/XLS pakai Excel, CSV pakai str_getcsv) =====
        if (in_array($ext, ['xlsx', 'xls'])) {
            $sheets = Excel::toArray([], $file);     // sheet[0] = array of rows
            $rows = $sheets[0] ?? [];
        } else {
            $rows = array_map('str_getcsv', file($file->getRealPath()));
        }

        if (count($rows) <= 1) {
            return redirect()->route('adminpoli.diagnosa.index')
                ->with('error', 'File kosong / format tidak sesuai.');
        }

        // ===== 2) Header normalize =====
        $rawHeader = $rows[0];
        $header = array_map(function ($h) {
            $h = (string)$h;
            $h = trim($h);
            $h = str_replace(["\u{00A0}"], ' ', $h);  // non-breaking space
            $h = preg_replace('/\s+/', ' ', $h);      // rapihin spasi
            $h = strtolower($h);
            $h = str_replace('.', '', $h);            // NO. -> no
            return $h;
        }, $rawHeader);

        // ===== 3) Mapping header EXCEL -> kolom DB =====
        $map = [
            'diagnosa nama'    => 'diagnosa',
            'diagnosa'         => 'diagnosa',
            'keterangan'       => 'keterangan',
            'klasifikasi nama' => 'klasifikasi_nama',
            'klasifikasi_nama' => 'klasifikasi_nama',
            'bagian tubuh'     => 'bagian_tubuh',
            'bagian_tubuh'     => 'bagian_tubuh',
            'no'               => null,
        ];

        $colIndex = [];
        foreach ($header as $i => $col) {
            if (array_key_exists($col, $map) && $map[$col]) {
                $colIndex[$map[$col]] = $i;
            }
        }

        if (!isset($colIndex['diagnosa'])) {
            return redirect()->route('adminpoli.diagnosa.index')
                ->with('error', 'Header harus mengandung kolom "DIAGNOSA NAMA".');
        }

        // ===== 4) Insert data =====
        $inserted = 0;
        $skipped  = 0;

        foreach (array_slice($rows, 1) as $r) {
            // Kadang row excel kebaca pendek, amankan dengan null-coalesce
            $diagnosa = trim((string)($r[$colIndex['diagnosa']] ?? ''));

            if ($diagnosa === '') { $skipped++; continue; }

            // skip duplikat diagnosa aktif
            $exists = DB::table('diagnosa')
                ->whereRaw('LOWER(diagnosa) = ?', [mb_strtolower($diagnosa)])
                ->where('is_active', 1)
                ->exists();

            if ($exists) { $skipped++; continue; }

            $newId = ((int) DB::table('diagnosa')->max('id_diagnosa')) + 1;

            DB::table('diagnosa')->insert([
                'id_diagnosa'      => $newId,
                'diagnosa'         => $diagnosa,
                'keterangan'       => isset($colIndex['keterangan'])
                    ? trim((string)($r[$colIndex['keterangan']] ?? ''))
                    : null,
                'klasifikasi_nama' => isset($colIndex['klasifikasi_nama'])
                    ? trim((string)($r[$colIndex['klasifikasi_nama']] ?? ''))
                    : null,
                'bagian_tubuh'     => isset($colIndex['bagian_tubuh'])
                    ? trim((string)($r[$colIndex['bagian_tubuh']] ?? ''))
                    : null,
                'is_active'        => 1,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            $inserted++;
        }

        return redirect()->route('adminpoli.diagnosa.index')
            ->with('success', "Import selesai. Berhasil: $inserted, Dilewati: $skipped");
    }

    public function export(Request $request)
    {
        $request->validate([
            'from'   => ['required', 'date'],
            'to'     => ['required', 'date', 'after_or_equal:from'],
            'format' => ['required', 'in:csv,excel,pdf'],
            'action' => ['required', 'in:preview,download'],
        ]);

        $from = $request->from . ' 00:00:00';
        $to   = $request->to   . ' 23:59:59';

        $data = DB::table('diagnosa')
            ->select(
                'id_diagnosa',
                'diagnosa',
                'keterangan',
                'klasifikasi_nama',
                'bagian_tubuh'
            )
            ->where('is_active', 1)
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('diagnosa')
            ->get();

        // preview
        if ($request->action === 'preview') {
            return view('adminpoli.diagnosa.preview', [
                'data'   => $data,
                'from'   => $request->from,
                'to'     => $request->to,
                'format' => $request->format,
                'count'  => $data->count(),
            ]);
        }

        $fileBase = 'data-diagnosa_' . $request->from . '_sd_' . $request->to;

        if ($request->format === 'csv') {
            return response()->streamDownload(function () use ($data) {
                $out = fopen('php://output', 'w');
                fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
                fputcsv($out, ['ID Diagnosa', 'Diagnosa', 'Keterangan', 'Klasifikasi Nama', 'Bagian Tubuh']);

                foreach ($data as $row) {
                    fputcsv($out, [
                        $row->id_diagnosa,
                        $row->diagnosa,
                        $row->keterangan,
                        $row->klasifikasi_nama,
                        $row->bagian_tubuh
                    ]);
                }

                fclose($out);
            }, $fileBase . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
        }

        if ($request->format === 'excel') {
            $filename = $fileBase . '.xlsx';

            return Excel::download(
                new DiagnosaExport($from, $to),
                $filename
            );
        }

        if ($request->format === 'pdf') {
            if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
                return redirect()->route('adminpoli.diagnosa.index')
                    ->with('error', 'Export PDF belum aktif (Dompdf belum terpasang).');
            }

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('adminpoli.diagnosa.export_pdf', [
                'data' => $data,
                'from' => $request->from,
                'to'   => $request->to,
            ])->setPaper('A4', 'portrait');

            return $pdf->download($fileBase . '.pdf');
        }
    }
}
