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
        $jenisList = Pemeriksa::orderBy('nama_pemeriksa')->get();

        $dokter = Dokter::with('jadwal')
            ->when($q, fn($qr) => $qr->where('nama', 'like', "%{$q}%"))
            ->get()
            ->map(function ($d) {
                return (object)[
                    'tipe'   => 'dokter',
                    'id'     => $d->id_dokter,
                    'nama'   => $d->nama,
                    'jenis'  => $d->jenis_dokter,
                    'status' => $d->status ?? 'Aktif',
                ];
            });

        $pemeriksa = Pemeriksa::query()
            ->when($q, fn($qr) => $qr->where('nama_pemeriksa', 'like', "%{$q}%"))
            ->get()
            ->map(function ($p) {
                return (object)[
                    'tipe'   => 'pemeriksa',
                    'id'     => $p->id_pemeriksa,
                    'nama'   => $p->nama_pemeriksa,
                    'jenis'  => 'Pemeriksa',
                    'status' => $p->status ?? 'Aktif',
                ];
            });

        // gabung jadi satu list
        $rows = $dokter->merge($pemeriksa)->sortBy('nama')->values();

        return view('adminpoli.dokter_pemeriksa.index', compact('rows','q','jenisList'));
    }

    public function jadwalJson($tipe, $id)
    {
        if ($tipe === 'dokter') {
            $dokter = Dokter::where('id_dokter', $id)->firstOrFail();

            $items = JadwalDokter::where('id_dokter', $id)
                ->orderByRaw("FIELD(hari,'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu')")
                ->orderBy('jam_mulai')
                ->get()
                ->map(fn($j) => [
                    'hari' => $j->hari,
                    'jam_mulai' => substr((string)$j->jam_mulai, 0, 5),
                    'jam_selesai' => substr((string)$j->jam_selesai, 0, 5),
                ])->values();

            return response()->json([
                'ok' => true,
                'tipe' => 'dokter',
                'nama' => $dokter->nama,
                'jadwal' => $items,
            ]);
        }

        $p = Pemeriksa::where('id_pemeriksa', $id)->firstOrFail();

        $fixed = [
            ['hari' => 'Senin',  'jam_mulai' => '07:00', 'jam_selesai' => '16:00'],
            ['hari' => 'Selasa', 'jam_mulai' => '07:00', 'jam_selesai' => '16:00'],
            ['hari' => 'Rabu',   'jam_mulai' => '07:00', 'jam_selesai' => '16:00'],
            ['hari' => 'Kamis',  'jam_mulai' => '07:00', 'jam_selesai' => '16:00'],
            ['hari' => 'Jumat',  'jam_mulai' => '07:00', 'jam_selesai' => '16:00'],
        ];

        return response()->json([
            'ok' => true,
            'tipe' => 'pemeriksa',
            'nama' => $p->nama_pemeriksa,
            'jadwal' => $fixed,
        ]);
    }


    // =========================
    // CRUD DOKTER
    // =========================
    public function storeDokter(Request $request)
    {
        $request->validate([
            'id_dokter' => 'required|string|max:20|unique:dokter,id_dokter',
            'nama' => 'required|string|max:255',
            'jenis_dokter' => 'required|string|max:255',
            'status' => 'required|in:Aktif,Nonaktif',
            'jadwal' => 'nullable|array',
            'jadwal.*.hari' => 'required_with:jadwal|string|max:50',
            'jadwal.*.jam_mulai' => 'required_with:jadwal|date_format:H:i',
            'jadwal.*.jam_selesai' => 'required_with:jadwal|date_format:H:i',
        ]);

        DB::transaction(function () use ($request) {
            Dokter::create([
                'id_dokter' => $request->id_dokter,
                'nama' => $request->nama,
                'jenis_dokter' => $request->jenis_dokter,
                'status' => $request->status,
            ]);

            foreach (($request->jadwal ?? []) as $j) {
                if (!$j['hari'] || !$j['jam_mulai'] || !$j['jam_selesai']) continue;

                JadwalDokter::create([
                    'id_dokter' => $request->id_dokter,
                    'hari' => $j['hari'],
                    'jam_mulai' => $j['jam_mulai'],
                    'jam_selesai' => $j['jam_selesai'],
                ]);
            }
        });

        return back()->with('success', 'Dokter berhasil ditambahkan.');
    }

    public function updateDokter(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'jenis_dokter' => 'required|string|max:255',
            'status' => 'required|in:Aktif,Nonaktif',
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
                'status' => $request->status,
            ]);

            JadwalDokter::where('id_dokter', $dokter->id_dokter)->delete();

            foreach (($request->jadwal ?? []) as $j) {
                if (!$j['hari'] || !$j['jam_mulai'] || !$j['jam_selesai']) continue;

                JadwalDokter::create([
                    'id_dokter' => $dokter->id_dokter,
                    'hari' => $j['hari'],
                    'jam_mulai' => $j['jam_mulai'],
                    'jam_selesai' => $j['jam_selesai'],
                ]);
            }
        });

        return back()->with('success', 'Dokter berhasil diperbarui.');
    }

    public function destroyDokter($id)
    {
        DB::transaction(function () use ($id) {
            JadwalDokter::where('id_dokter', $id)->delete();
            Dokter::where('id_dokter', $id)->delete();
        });

        return back()->with('success', 'Dokter berhasil dihapus.');
    }

    // =========================
    // CRUD PEMERIKSA
    // =========================
    public function storePemeriksa(Request $request)
    {
        $request->validate([
            'id_pemeriksa' => 'required|string|max:20|unique:pemeriksa,id_pemeriksa',
            'nama_pemeriksa' => 'required|string|max:255',
            'status' => 'required|in:Aktif,Nonaktif',
        ]);

        Pemeriksa::create([
            'id_pemeriksa' => $request->id_pemeriksa,
            'nama_pemeriksa' => $request->nama_pemeriksa,
            'status' => $request->status,
        ]);

        return back()->with('success', 'Pemeriksa berhasil ditambahkan.');
    }

    public function updatePemeriksa(Request $request, $id)
    {
        $request->validate([
            'nama_pemeriksa' => 'required|string|max:255',
            'status' => 'required|in:Aktif,Nonaktif',
        ]);

        Pemeriksa::where('id_pemeriksa', $id)->update([
            'nama_pemeriksa' => $request->nama_pemeriksa,
            'status' => $request->status,
        ]);

        return back()->with('success', 'Pemeriksa berhasil diperbarui.');
    }

    public function destroyPemeriksa($id)
    {
        Pemeriksa::where('id_pemeriksa', $id)->delete();
        return back()->with('success', 'Pemeriksa berhasil dihapus.');
    }

    public function updateStatusDokter(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:Aktif,Nonaktif']);

        Dokter::where('id_dokter', $id)->update([
            'status' => $request->status,
        ]);

        return response()->json(['ok' => true]);
    }

    public function updateStatusPemeriksa(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:Aktif,Nonaktif']);

        Pemeriksa::where('id_pemeriksa', $id)->update([
            'status' => $request->status,
        ]);

        return response()->json(['ok' => true]);
    }

}
