<?php

namespace App\Http\Controllers\AdminPoli;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class DiagnosaController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('diagnosa')
            ->where('is_active', 1);

        // search
        if ($request->filled('q')) {
            $q = mb_strtolower($request->q);
            $query->whereRaw('LOWER(diagnosa) LIKE ?', ["%{$q}%"]);
        }

        $diagnosa = $query
            ->select('id_diagnosa', 'diagnosa', 'created_at')
            ->orderBy('diagnosa')
            ->get();

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

        return view('adminpoli.diagnosa.index', compact('diagnosa', 'previewCount'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'diagnosa' => ['required',
            Rule::unique('diagnosa', 'diagnosa')->where(fn ($q) => $q->where('is_active', 1)),
            ],
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

        $last = DB::table('diagnosa')
            ->select('id_diagnosa')
            ->orderByRaw("CAST(SUBSTRING(id_diagnosa, 5) AS UNSIGNED) DESC")
            ->value('id_diagnosa');

        $nextNum = $last ? ((int)substr($last, 4) + 1) : 1;
        $newId = 'DG-' . str_pad((string)$nextNum, 3, '0', STR_PAD_LEFT);

        DB::table('diagnosa')->insert([
            'id_diagnosa' => $newId,
            'diagnosa'    => $text,
            'is_active'   => 1,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return redirect()->route('adminpoli.diagnosa.index')
            ->with('success', 'Diagnosa berhasil ditambahkan');
    }

    public function edit($id)
    {
        $row = DB::table('diagnosa')->where('id_diagnosa', $id)->first();
        if (!$row) return redirect()->route('adminpoli.diagnosa.index')->with('error', 'Data tidak ditemukan.');

        return view('adminpoli.diagnosa.edit', compact('row'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'diagnosa' => ['required', 'string'],
            Rule::unique('diagnosa', 'diagnosa')
                ->where(fn ($q) => $q->where('is_active', 1))
                ->ignore($id, 'id_diagnosa')
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
                'diagnosa'   => $text,
                'updated_at' => now(),
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

        $rows = Excel::toArray([], $request->file('file'))[0] ?? [];
        if (count($rows) <= 1) {
            return redirect()->route('adminpoli.diagnosa.index')->with('error', 'File kosong / format tidak sesuai.');
        }

        $header = array_map(fn($h) => Str::slug((string)$h, '_'), $rows[0]);
        $idxDiagnosa = array_search('diagnosa', $header);

        if ($idxDiagnosa === false) {
            return redirect()->route('adminpoli.diagnosa.index')
                ->with('error', 'Header harus mengandung: diagnosa');
        }

        $inserted = 0;
        $skipped = 0;

        foreach (array_slice($rows, 1) as $r) {
            $text = trim((string)($r[$idxDiagnosa] ?? ''));
            if ($text === '') { $skipped++; continue; }

            $exists = DB::table('diagnosa')
                ->whereRaw('LOWER(diagnosa) = ?', [mb_strtolower($text)])
                ->where('is_active', 1)
                ->exists();

            if ($exists) { $skipped++; continue; }

            $last = DB::table('diagnosa')
                ->orderByRaw("CAST(SUBSTRING(id_diagnosa, 5) AS UNSIGNED) DESC")
                ->value('id_diagnosa');

            $nextNum = $last ? ((int)substr($last, 4) + 1) : 1;
            $newId = 'DGS-' . str_pad((string)$nextNum, 3, '0', STR_PAD_LEFT);

            DB::table('diagnosa')->insert([
                'id_diagnosa' => $newId,
                'diagnosa'    => $text,
                'is_active'   => 1,
                'created_at'  => now(),
                'updated_at'  => now(),
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
            ->select('id_diagnosa', 'diagnosa', 'created_at')
            ->whereBetween('created_at', [$from, $to])
            ->where('is_active', 1)
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
                fputcsv($out, ['ID Diagnosa', 'Diagnosa', 'Created At']);

                foreach ($data as $row) {
                    fputcsv($out, [$row->id_diagnosa, $row->diagnosa, $row->created_at]);
                }
                fclose($out);
            }, $fileBase . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
        }

        if ($request->format === 'excel') {
            $html = view('adminpoli.diagnosa.export_excel', [
                'data' => $data,
                'from' => $request->from,
                'to'   => $request->to,
            ])->render();

            return response($html, 200, [
                'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="'.$fileBase.'.xls"',
            ]);
        }

        // pdf
        if (!class_exists(Pdf::class)) {
            return redirect()->route('adminpoli.diagnosa.index')
                ->with('error', 'Export PDF belum aktif (Dompdf belum terpasang).');
        }

        $pdf = Pdf::loadView('adminpoli.diagnosa.export_pdf', [
            'data' => $data,
            'from' => $request->from,
            'to'   => $request->to,
        ])->setPaper('A4', 'portrait');

        return $pdf->download($fileBase . '.pdf');
    }
}
