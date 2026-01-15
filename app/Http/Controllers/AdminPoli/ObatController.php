<?php

namespace App\Http\Controllers\AdminPoli;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;


class ObatController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('obat');

        if ($request->filled('q')) {
            $query->where('nama_obat', 'like', '%' . $request->q . '%');
        }

        $obat = $query
            ->select('id_obat', 'nama_obat', 'harga', 'exp_date')
            ->orderBy('nama_obat')
            ->get();

        // ===== PREVIEW COUNT (DOWNLOAD) =====
        $previewCount = null;

        if ($request->filled('from') && $request->filled('to')) {
            $previewCount = DB::table('obat')
                ->whereBetween('created_at', [
                    $request->from . ' 00:00:00',
                    $request->to   . ' 23:59:59',
                ])
                ->count();
        }

        return view('adminpoli.obat.index', compact('obat', 'previewCount'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_obat' => 'required|string|max:255',
            'harga'     => 'required|numeric|min:1',
            'exp_date'  => 'required|date|after:today',
        ],
        [
            'exp_date.after' => 'Tanggal kadaluarsa harus lebih dari hari ini.',
            'harga.min'      => 'Harga tidak boleh kurang dari 1.',
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
            'harga'     => 'required|numeric|min:1',
            'exp_date'  => 'required|date|after:today',
        ],
        [
            'exp_date.after' => 'Tanggal kadaluarsa harus lebih dari hari ini.',
            'harga.min'      => 'Harga tidak boleh kurang dari 1.',
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
        $request->validate([
            'from'   => ['required', 'date'],
            'to'     => ['required', 'date', 'after_or_equal:from'],
            'format' => ['required', 'in:csv,excel,pdf'],
        ], [
            'from.required' => 'Tanggal awal wajib diisi.',
            'to.required'   => 'Tanggal akhir wajib diisi.',
            'format.required' => 'Format download wajib dipilih.',
        ]);

        $from = $request->from . ' 00:00:00';
        $to   = $request->to   . ' 23:59:59';

        // Filter rentang: pakai created_at (karena ini yang paling masuk akal untuk "rentang data")
        $data = DB::table('obat')
            ->select('id_obat', 'nama_obat', 'harga', 'exp_date', 'created_at')
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('nama_obat')
            ->get();

        $fileBase = 'data-obat_' . $request->from . '_sd_' . $request->to;

        if ($request->format === 'csv') {
            $filename = $fileBase . '.csv';

            return response()->streamDownload(function () use ($data) {
                $out = fopen('php://output', 'w');

                // BOM biar Excel kebaca UTF-8
                fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

                fputcsv($out, ['ID Obat', 'Nama Obat', 'Harga', 'Exp Date', 'Created At']);

                foreach ($data as $row) {
                    fputcsv($out, [
                        $row->id_obat,
                        $row->nama_obat,
                        $row->harga,
                        $row->exp_date,
                        $row->created_at,
                    ]);
                }

                fclose($out);
            }, $filename, [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]);
        }

        if ($request->format === 'excel') {
            // Tanpa library tambahan: export "Excel" berupa HTML table .xls (Excel bisa buka)
            $filename = $fileBase . '.xls';

            $html = view('adminpoli.obat.export_excel', [
                'data' => $data,
                'from' => $request->from,
                'to'   => $request->to,
            ])->render();

            return response($html, 200, [
                'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ]);
        }

        // PDF: butuh dompdf (barryvdh/laravel-dompdf). Kita buat cek agar tidak error.
        if ($request->format === 'pdf') {
            if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class) && !class_exists(\Barryvdh\DomPDF\Facade\PDF::class)) {
                return redirect()->route('adminpoli.obat.index')
                    ->with('error', 'Export PDF belum aktif (Dompdf belum terpasang).');
            }

            // Support dua alias facade: Pdf / PDF
            $pdfFacade = class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)
                ? \Barryvdh\DomPDF\Facade\Pdf::class
                : \Barryvdh\DomPDF\Facade\PDF::class;

            $pdf = $pdfFacade::loadView('adminpoli.obat.export_pdf', [
                'data' => $data,
                'from' => $request->from,
                'to'   => $request->to,
            ])->setPaper('A4', 'portrait');

            return $pdf->download($fileBase . '.pdf');
        }

        return redirect()->route('adminpoli.obat.index')->with('error', 'Format tidak dikenali.');
    }

    public function show($id)
    {
        // kalau kamu memang tidak butuh halaman detail, cukup redirect
        return redirect()->route('adminpoli.obat.index');
    }
}
