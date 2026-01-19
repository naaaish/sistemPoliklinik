<?php

namespace App\Http\Controllers\AdminPoli;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class SaranController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('saran')
            ->join('diagnosa', 'diagnosa.id_diagnosa', '=', 'saran.id_diagnosa')
            ->where('saran.is_active', '1');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($w) use ($q) {
                $w->where('saran.saran', 'like', '%' . $q . '%')
                  ->orWhere('diagnosa.diagnosa', 'like', '%' . $q . '%')
                  ->orWhere('saran.id_saran', 'like', '%' . $q . '%');
            });
        }

        $saran = $query
            ->select(
                'saran.id_saran',
                'saran.saran as saran_text',
                'saran.id_diagnosa',
                'diagnosa.diagnosa as diagnosa_text',
                'saran.is_active',
                'saran.created_at'
            )
            ->orderBy('saran.created_at', 'desc')
            ->get();

        // ===== PREVIEW COUNT (DOWNLOAD) =====
        $previewCount = null;
        if ($request->filled('from') && $request->filled('to')) {
            $previewCount = DB::table('saran')
                ->whereBetween('created_at', [
                    $request->from . ' 00:00:00',
                    $request->to   . ' 23:59:59',
                ])
                ->count();
        }

        $diagnosaList = DB::table('diagnosa')
            ->select('id_diagnosa', 'diagnosa')
            ->orderBy('diagnosa')
            ->get();

        return view('adminpoli.saran.index', compact('saran', 'previewCount', 'diagnosaList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'saran'      => 'required|string',
            'id_diagnosa'=> 'required|string|max:20',
        ]);

        $saranText = trim($request->saran);

        // cek duplikat saran aktif (biar konsisten seperti obat)
        $existsAktif = DB::table('saran')
            ->whereRaw('LOWER(saran) = ?', [mb_strtolower($saranText)])
            ->where('id_diagnosa', $request->id_diagnosa)
            ->where('is_active', '1')
            ->exists();

        if ($existsAktif) {
            return redirect()->route('adminpoli.saran.index')
                ->withInput()
                ->with('error', 'Saran untuk diagnosa ini sudah ada. Gunakan teks lain.');
        }

        // Ambil id_saran terakhir berdasarkan urutan terbesar (SAR-xxx)
        $lastId = DB::table('saran')
            ->where('id_saran', 'like', 'SAR-%')
            ->orderByRaw("CAST(SUBSTRING(id_saran, 5) AS UNSIGNED) DESC")
            ->value('id_saran');

        $nextNumber = 1;
        if ($lastId) {
            $nextNumber = (int) substr($lastId, 4) + 1; // "SAR-" = 4 char
        }

        $newId = 'SAR-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        DB::table('saran')->insert([
            'id_saran'   => $newId,
            'saran'      => $saranText,
            'id_diagnosa'=> $request->id_diagnosa,
            'is_active'  => '1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('adminpoli.saran.index')
            ->with('success', 'Saran berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'saran'      => 'required|string',
            'id_diagnosa'=> 'required|string|max:20',
        ]);

        $saranText = trim($request->saran);

        $existsAktif = DB::table('saran')
            ->whereRaw('LOWER(saran) = ?', [mb_strtolower($saranText)])
            ->where('id_diagnosa', $request->id_diagnosa)
            ->where('is_active', '1')
            ->where('id_saran', '!=', $id)
            ->exists();

        if ($existsAktif) {
            return redirect()->route('adminpoli.saran.index')
                ->withInput()
                ->with('error', 'Saran untuk diagnosa ini sudah ada.');
        }

        DB::table('saran')
            ->where('id_saran', $id)
            ->update([
                'saran'       => $saranText,
                'id_diagnosa' => $request->id_diagnosa,
                'updated_at'  => now(),
            ]);

        return redirect()->route('adminpoli.saran.index')
            ->with('success', 'Saran berhasil diperbarui');
    }

    public function destroy($id)
    {
        DB::table('saran')
            ->where('id_saran', $id)
            ->update([
                'is_active'  => '0',
                'updated_at' => now(),
            ]);

        return redirect()->route('adminpoli.saran.index')
            ->with('success', 'Saran berhasil dihapus');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:5120', // max 5MB
        ]);

        // baca file jadi array (1 sheet) -> ngikut pola obat
        $rows = Excel::toArray([], $request->file('file'))[0] ?? [];

        if (count($rows) <= 1) {
            return redirect()->route('adminpoli.saran.index')
                ->with('error', 'File kosong / format tidak sesuai.');
        }

        // anggap baris pertama header
        $header = array_map(fn($h) => Str::slug((string)$h, '_'), $rows[0]);

        // header yang diterima:
        // saran | id_diagnosa
        $idxSaran = array_search('saran', $header);
        $idxDiag  = array_search('id_diagnosa', $header);

        if ($idxSaran === false || $idxDiag === false) {
            return redirect()->route('adminpoli.saran.index')
                ->with('error', 'Header harus mengandung: saran, id_diagnosa');
        }

        $inserted = 0;
        $skipped  = 0;

        foreach (array_slice($rows, 1) as $r) {
            $saranText = trim((string)($r[$idxSaran] ?? ''));
            $idDiag    = trim((string)($r[$idxDiag] ?? ''));

            if ($saranText === '' || $idDiag === '') {
                $skipped++;
                continue;
            }

            // pastikan diagnosa ada
            $diagExists = DB::table('diagnosa')->where('id_diagnosa', $idDiag)->exists();
            if (!$diagExists) {
                $skipped++;
                continue;
            }

            // duplikat aktif (saran+diagnosa)
            $existsAktif = DB::table('saran')
                ->whereRaw('LOWER(saran) = ?', [mb_strtolower($saranText)])
                ->where('id_diagnosa', $idDiag)
                ->where('is_active', '1')
                ->exists();

            if ($existsAktif) {
                $skipped++;
                continue;
            }

            // generate id_saran SAR-XXX
            $last = DB::table('saran')
                ->select('id_saran')
                ->orderByRaw("CAST(SUBSTRING(id_saran, 5) AS UNSIGNED) DESC")
                ->value('id_saran');

            $nextNum = $last ? ((int)substr($last, 4) + 1) : 1;
            $newId   = 'SAR-' . str_pad((string)$nextNum, 3, '0', STR_PAD_LEFT);

            DB::table('saran')->insert([
                'id_saran'    => $newId,
                'saran'       => $saranText,
                'id_diagnosa' => $idDiag,
                'is_active'   => '1',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            $inserted++;
        }

        return redirect()->route('adminpoli.saran.index')
            ->with('success', "Import selesai. Berhasil: $inserted, Dilewati: $skipped");
    }

    public function export(Request $request)
    {
        $request->validate([
            'from'   => ['required', 'date'],
            'to'     => ['required', 'date', 'after_or_equal:from'],
            'format' => ['required', 'in:csv,excel,pdf'],
            'action' => ['required', 'in:preview,download'],
        ], [
            'from.required'   => 'Tanggal awal wajib diisi.',
            'to.required'     => 'Tanggal akhir wajib diisi.',
            'format.required' => 'Format wajib dipilih.',
        ]);

        $from = $request->from . ' 00:00:00';
        $to   = $request->to   . ' 23:59:59';

        $data = DB::table('saran')
            ->join('diagnosa', 'diagnosa.id_diagnosa', '=', 'saran.id_diagnosa')
            ->select(
                'saran.id_saran',
                'saran.saran',
                'saran.id_diagnosa',
                'diagnosa.diagnosa as diagnosa_text',
                'saran.created_at',
                'saran.is_active'
            )
            ->whereBetween('saran.created_at', [$from, $to])
            ->orderBy('saran.created_at', 'desc')
            ->get();

        // ====== PREVIEW ======
        if ($request->action === 'preview') {
            return view('adminpoli.saran.preview', [
                'data'   => $data,
                'from'   => $request->from,
                'to'     => $request->to,
                'format' => $request->format,
                'count'  => $data->count(),
            ]);
        }

        // ====== DOWNLOAD ======
        $fileBase = 'data-saran_' . $request->from . '_sd_' . $request->to;

        if ($request->format === 'csv') {
            $filename = $fileBase . '.csv';

            return response()->streamDownload(function () use ($data) {
                $out = fopen('php://output', 'w');
                fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8

                fputcsv($out, ['ID Saran', 'Saran', 'ID Diagnosa', 'Diagnosa']);

                foreach ($data as $row) {
                    fputcsv($out, [$row->id_saran, $row->saran, $row->id_diagnosa, $row->diagnosa_text]);
                }

                fclose($out);
            }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
        }

        if ($request->format === 'excel') {
            $filename = $fileBase . '.xls';

            $html = view('adminpoli.saran.export_excel', [
                'data' => $data,
                'from' => $request->from,
                'to'   => $request->to,
            ])->render();

            return response($html, 200, [
                'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ]);
        }

        if ($request->format === 'pdf') {
            if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
                return redirect()->route('adminpoli.saran.index')
                    ->with('error', 'Export PDF belum aktif (Dompdf belum terpasang).');
            }

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('adminpoli.saran.export_pdf', [
                'data' => $data,
                'from' => $request->from,
                'to'   => $request->to,
            ])->setPaper('A4', 'portrait');

            return $pdf->download($fileBase . '.pdf');
        }

        return redirect()->route('adminpoli.saran.index')->with('error', 'Format tidak dikenali.');
    }

    public function show($id)
    {
        return redirect()->route('adminpoli.saran.index');
    }
}
