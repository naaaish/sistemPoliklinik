<?php

namespace App\Http\Controllers\AdminPoli;

use App\Http\Controllers\Controller;
use App\Models\Dokter;
use App\Models\JadwalDokter;
use App\Models\Pemeriksa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DokterPemeriksaController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->query('q');

        $dokter = Dokter::with('jadwal')
            ->when($q, function ($query) use ($q) {
                $query->where('nama', 'like', "%{$q}%");
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // dropdown jenis (opsional, kalau tabel pemeriksa dipakai)
        $jenisList = Pemeriksa::orderBy('nama_pemeriksa')->get();

        return view('adminpoli.dokter_pemeriksa.index', compact('dokter', 'q', 'jenisList'));

    }

    public function store(Request $request)
    {
        $request->validate([
            'id_dokter' => 'required|string|max:20|unique:dokter,id_dokter',
            'nama' => 'required|string|max:255',
            'jenis_dokter' => 'required|string|max:255',
            'status' => 'nullable|string|max:50', // opsional
            'jadwal' => 'nullable|array',
            'jadwal.*.hari' => 'required_with:jadwal|string|max:50',
            'jadwal.*.jam_mulai' => 'required_with:jadwal|date_format:H:i',
            'jadwal.*.jam_selesai' => 'required_with:jadwal|date_format:H:i',
        ]);

        DB::transaction(function () use ($request) {
            $dokter = Dokter::create([
                'id_dokter' => $request->id_dokter,
                'nama' => $request->nama,
                'jenis_dokter' => $request->jenis_dokter,
                'status' => $request->status ?? 'Aktif', // opsional
            ]);

            $jadwal = $request->jadwal ?? [];
            foreach ($jadwal as $j) {
                // skip baris kosong
                if (empty($j['hari']) || empty($j['jam_mulai']) || empty($j['jam_selesai'])) continue;

                JadwalDokter::create([
                    'id_dokter' => $dokter->id_dokter,
                    'hari' => $j['hari'],
                    'jam_mulai' => $j['jam_mulai'],
                    'jam_selesai' => $j['jam_selesai'],
                ]);
            }
        });

        return redirect()->route('dokter_pemeriksa.index')->with('success', 'Dokter/Pemeriksa berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'jenis_dokter' => 'required|string|max:255',
            'status' => 'nullable|string|max:50', // opsional
            'jadwal' => 'nullable|array',
            'jadwal.*.hari' => 'required_with:jadwal|string|max:50',
            'jadwal.*.jam_mulai' => 'required_with:jadwal|date_format:H:i',
            'jadwal.*.jam_selesai' => 'required_with:jadwal|date_format:H:i',
        ]);

        DB::transaction(function () use ($request, $id) {
            $dokter = Dokter::where('id_dokter', $id)->firstOrFail();

            $dokter->update([
                'nama' => $request->nama,
                'jenis_dokter' => $request->jenis_dokter,
                'status' => $request->status ?? $dokter->status ?? 'Aktif', // opsional
            ]);

            // jadwal bisa >1: supaya simpel dan konsisten, kita replace seluruh jadwal
            JadwalDokter::where('id_dokter', $dokter->id_dokter)->delete();

            $jadwal = $request->jadwal ?? [];
            foreach ($jadwal as $j) {
                if (empty($j['hari']) || empty($j['jam_mulai']) || empty($j['jam_selesai'])) continue;

                JadwalDokter::create([
                    'id_dokter' => $dokter->id_dokter,
                    'hari' => $j['hari'],
                    'jam_mulai' => $j['jam_mulai'],
                    'jam_selesai' => $j['jam_selesai'],
                ]);
            }
        });

        return redirect()->route('dokter_pemeriksa.index')->with('success', 'Data berhasil diperbarui.');
    }

    public function destroy($id)
    {
        DB::transaction(function () use ($id) {
            $dokter = Dokter::where('id_dokter', $id)->firstOrFail();
            JadwalDokter::where('id_dokter', $dokter->id_dokter)->delete();
            $dokter->delete();
        });

        return redirect()->route('dokter_pemeriksa.index')->with('success', 'Data berhasil dihapus.');
    }

    public function jadwalJson($id)
    {
        $dokter = Dokter::with('jadwal')->where('id_dokter', $id)->firstOrFail();

        $items = $dokter->jadwal->map(function ($j) {
            return [
                'hari' => $j->hari,
                'jam_mulai' => substr($j->jam_mulai, 0, 5),
                'jam_selesai' => substr($j->jam_selesai, 0, 5),
            ];
        });

        return response()->json([
            'id_dokter' => $dokter->id_dokter,
            'nama' => $dokter->nama,
            'jadwal' => $items,
        ]);
    }
}
