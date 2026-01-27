<?php

namespace App\Http\Controllers\AdminPoli;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\ObatExport;
use Maatwebsite\Excel\Facades\Excel;


class ObatController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('obat')->where('is_active', '1');

        if ($request->filled('q')) {
            $query->where('nama_obat', 'like', '%' . $request->q . '%');
        }

        $obat = $query
            ->select('id_obat', 'nama_obat', 'harga', 'exp_date', 'is_active')
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

        $nama = trim($request->nama_obat);

        // cek duplikat NAMA yang masih aktif (is_active = '1')
        $existsAktif = DB::table('obat')
            ->whereRaw('LOWER(nama_obat) = ?', [mb_strtolower($nama)])
            ->where('is_active', '1')
            ->exists();

        if ($existsAktif) {
            return redirect()->route('adminpoli.obat.index')
                ->withInput()
                ->with('error', 'Nama obat sudah ada. Gunakan nama lain.');
        }

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

        $nama = trim($request->nama_obat);

        $existsAktif = DB::table('obat')
            ->whereRaw('LOWER(nama_obat) = ?', [mb_strtolower($nama)])
            ->where('is_active', '1')
            ->where('id_obat', '!=', $id) // biar dia boleh sama dengan dirinya sendiri
            ->exists();

        if ($existsAktif) {
            return redirect()->route('adminpoli.obat.index')
                ->withInput()
                ->with('error', 'Nama obat sudah ada.');
        }

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
        DB::table('obat')
            ->where('id_obat', $id)
            ->update([
                'is_active' => '0',
                'updated_at' => now(),
            ]);

        return redirect()->route('adminpoli.obat.index')
            ->with('success', 'Obat berhasil dihapus');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:5120', // max 5MB
        ]);

        // baca file jadi array (1 sheet)
        $rows = Excel::toArray([], $request->file('file'))[0] ?? [];

        if (count($rows) <= 1) {
            return redirect()->route('adminpoli.obat.index')
                ->with('error', 'File kosong / format tidak sesuai.');
        }

        // anggap baris pertama header
        $header = array_map(fn($h) => Str::slug((string)$h, '_'), $rows[0]);

        // mapping nama kolom yang kita butuhin
        // contoh header yang diterima:
        // nama_obat | harga | exp_date
        $idxNama = array_search('nama_obat', $header);
        $idxHarga = array_search('harga', $header);
        $idxExp = array_search('exp_date', $header);

        if ($idxNama === false || $idxHarga === false || $idxExp === false) {
            return redirect()->route('adminpoli.obat.index')
                ->with('error', 'Header harus mengandung: nama_obat, harga, exp_date');
        }

        $inserted = 0;
        $skipped = 0;

        foreach (array_slice($rows, 1) as $r) {
            $nama = trim((string)($r[$idxNama] ?? ''));
            $harga = $r[$idxHarga] ?? null;
            $exp = $r[$idxExp] ?? null;

            if ($nama === '' || $harga === null || $exp === null) {
                $skipped++;
                continue;
            }

            // normalisasi harga (kalau ada "Rp", titik, koma)
            $hargaNum = (int) preg_replace('/[^0-9]/', '', (string)$harga);
            if ($hargaNum <= 0) {
                $skipped++;
                continue;
            }

            // exp_date harus lebih dari hari ini
            $expDate = date('Y-m-d', strtotime((string)$exp));
            if (!$expDate || $expDate <= date('Y-m-d')) {
                $skipped++;
                continue;
            }

            // aturan kamu: nama sama yang masih aktif = tidak boleh
            $existsAktif = DB::table('obat')
                ->whereRaw('LOWER(nama_obat) = ?', [mb_strtolower($nama)])
                ->where('is_active', '1') // enum '0'/'1'
                ->exists();

            if ($existsAktif) {
                $skipped++;
                continue;
            }

            // generate id_obat OBT-XXX (lanjut dari terbesar)
            $last = DB::table('obat')
                ->select('id_obat')
                ->orderByRaw("CAST(SUBSTRING(id_obat, 5) AS UNSIGNED) DESC")
                ->value('id_obat');

            $nextNum = $last ? ((int)substr($last, 4) + 1) : 1;
            $newId = 'OBT-' . str_pad((string)$nextNum, 3, '0', STR_PAD_LEFT);

            DB::table('obat')->insert([
                'id_obat' => $newId,
                'nama_obat' => $nama,
                'harga' => $hargaNum,
                'exp_date' => $expDate,
                'is_active' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $inserted++;
        }

        return redirect()->route('adminpoli.obat.index')
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
            'from.required' => 'Tanggal awal wajib diisi.',
            'to.required'   => 'Tanggal akhir wajib diisi.',
            'format.required' => 'Format wajib dipilih.',
        ]);

        $from = $request->from . ' 00:00:00';
        $to   = $request->to   . ' 23:59:59';

        // Filter rentang berdasarkan created_at (data masuk pada rentang tsb)
        $data = DB::table('obat')
            ->select('id_obat', 'nama_obat', 'harga', 'exp_date', 'created_at', 'is_active')
            ->where('is_active', 1)
            ->whereBetween('created_at', [$from, $to])
            ->orderBy('nama_obat')
            ->get();

        // ====== PREVIEW ======
        if ($request->action === 'preview') {
            return view('adminpoli.obat.preview', [
                'data'   => $data,
                'from'   => $request->from,
                'to'     => $request->to,
                'format' => $request->format,
                'count'  => $data->count(),
            ]);
        }

        // ====== DOWNLOAD ======
        $fileBase = 'data-obat_' . $request->from . '_sd_' . $request->to;

        if ($request->format === 'csv') {
            $filename = $fileBase . '.csv';

            return response()->streamDownload(function () use ($data) {
                $out = fopen('php://output', 'w');
                fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8

                fputcsv($out, ['ID Obat', 'Nama Obat', 'Harga', 'Exp Date']);

                foreach ($data as $row) {
                    fputcsv($out, [$row->id_obat, $row->nama_obat, $row->harga, $row->exp_date]);
                }

                fclose($out);
            }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
        }

        if ($request->format === 'excel') {
            $filename = $fileBase . '.xlsx';

            return Excel::download(
                new ObatExport($data, $request->from, $request->to),
                $filename
            );
        }

        // PDF (butuh dompdf)
        if ($request->format === 'pdf') {
            if (!class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
                return redirect()->route('adminpoli.obat.index')
                    ->with('error', 'Export PDF belum aktif (Dompdf belum terpasang).');
            }

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('adminpoli.obat.export_pdf', [
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
