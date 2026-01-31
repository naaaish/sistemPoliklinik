<?php

namespace App\Http\Controllers\AdminPoli;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SaranExport;


class SaranController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('saran')
            ->where('is_active', '1')
            ->orderBy('created_at', 'desc');;

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($w) use ($q) {
                $w->where('saran', 'like', '%' . $q . '%')
                  ->orWhere('id_saran', 'like', '%' . $q . '%');
            });
        }

        $perPageRaw = $request->input('per_page', 10);
        $allowedPerPage = [10, 25, 50, 100];
        $isAll = ($perPageRaw === 'all');

        $perPage = (int) $perPageRaw;
        if (!in_array($perPage, $allowedPerPage)) $perPage = 10;

        $baseSelect = $query
            ->select(
                'id_saran',
                'saran as saran_text',
                'is_active',
                'created_at'
            )
            ->orderBy('created_at', 'desc');

        if ($isAll) {
            $saran = $baseSelect->get();
        } else {
            $saran = $baseSelect
                ->paginate($perPage)
                ->appends($request->query());
        }

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
        return view('adminpoli.saran.index', compact('saran', 'previewCount', 'perPageRaw'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'saran'      => 'required|string',
        ]);

        $saranText = trim($request->saran);

        // cek duplikat saran aktif (biar konsisten seperti obat)
        $existsAktif = DB::table('saran')
            ->whereRaw('LOWER(saran) = ?', [mb_strtolower($saranText)])
            ->where('is_active', '1')
            ->exists();

        if ($existsAktif) {
            return redirect()->route('adminpoli.saran.index')
                ->withInput()
                ->with('error', 'Saran ini sudah ada.');
        }

        // Ambil id_saran terakhir berdasarkan urutan terbesar (SRN-xxx)
        $lastId = DB::table('saran')
            ->where('id_saran', 'like', 'SRN-%')
            ->orderByRaw("CAST(SUBSTRING(id_saran, 5) AS UNSIGNED) DESC")
            ->value('id_saran');

        $nextNumber = 1;
        if ($lastId) {
            $nextNumber = (int) substr($lastId, 4) + 1; // "SRN-" = 4 char
        }

        $newId = 'SRN-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
        DB::table('saran')->insert([
            'id_saran'   => $newId,
            'saran'      => $saranText,
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
        ]);

        $saranText = trim($request->saran);

        $existsAktif = DB::table('saran')
            ->whereRaw('LOWER(saran) = ?', [mb_strtolower($saranText)])
            ->where('is_active', '1')
            ->where('id_saran', '!=', $id)
            ->exists();

        if ($existsAktif) {
            return redirect()->route('adminpoli.saran.index')
                ->withInput()
                ->with('error', 'Saran ini sudah ada.');
        }

        DB::table('saran')
            ->where('id_saran', $id)
            ->update([
                'saran'       => $saranText,
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
        // saran
        $idxSaran = array_search('saran', $header);

        if ($idxSaran === false) {
            return redirect()->route('adminpoli.saran.index')
                ->with('error', 'Header harus mengandung: saran');
        }

        $inserted = 0;
        $skipped  = 0;

        foreach (array_slice($rows, 1) as $r) {
            $saranText = trim((string)($r[$idxSaran] ?? ''));

            if ($saranText === '') {
                $skipped++;
                continue;
            }
            
            $existsAktif = DB::table('saran')
                ->whereRaw('LOWER(saran) = ?', [mb_strtolower($saranText)])
                ->where('is_active', '1')
                ->exists();

            if ($existsAktif) {
                $skipped++;
                continue;
            }

            // generate id_saran SRN-XXX
            $last = DB::table('saran')
                ->select('id_saran')
                ->orderByRaw("CAST(SUBSTRING(id_saran, 5) AS UNSIGNED) DESC")
                ->value('id_saran');

            $nextNum = $last ? ((int)substr($last, 4) + 1) : 1;
            $newId   = 'SRN-' . str_pad((string)$nextNum, 3, '0', STR_PAD_LEFT);

            DB::table('saran')->insert([
                'id_saran'    => $newId,
                'saran'       => $saranText,
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
            ->select(
                'id_saran',
                'saran',
                'created_at',
                'is_active'
            )
            ->where('is_active', '1')
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('created_at', 'desc')
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

                fputcsv($out, ['ID Saran', 'Saran']);

                foreach ($data as $row) {
                    fputcsv($out, [$row->id_saran, $row->saran]);
                }

                fclose($out);
            }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
        }

        if ($request->format === 'excel') {
            $filename = $fileBase . '.xlsx';

            return Excel::download(
                new SaranExport($data, $request->from, $request->to),
                $filename
            );
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
