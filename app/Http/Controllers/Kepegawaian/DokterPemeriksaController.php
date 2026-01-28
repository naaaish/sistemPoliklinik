<?php

namespace App\Http\Controllers\Kepegawaian;

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
        $q = $request->q;

        // ===== DOKTER =====
        $dokter = Dokter::query()
            ->when($q, fn($qr) => $qr->where('nama', 'like', "%$q%")->orWhere('jenis_dokter', 'like', "%$q%"))
            ->get();

        $pemeriksa = Pemeriksa::query()
            ->when($q, fn($qr) => $qr->where('nama_pemeriksa', 'like', "%$q%"))
            ->get();

        // ambil jadwal semua dokter yang tampil
        $dokterIds = $dokter->pluck('id_dokter')->all();

        $jadwalMap = JadwalDokter::whereIn('id_dokter', $dokterIds)
            ->orderByRaw("FIELD(hari,'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu')")
            ->orderBy('jam_mulai')
            ->get()
            ->groupBy('id_dokter');

        // gabungkan jadi $rows yang kamu pakai di blade
        $rows = collect();

        foreach ($dokter as $d) {
            $parts = [];
            foreach (($jadwalMap[$d->id_dokter] ?? collect()) as $j) {
                $parts[] = $j->hari.'|'.substr($j->jam_mulai,0,5).'|'.substr($j->jam_selesai,0,5);
            }

            $rows->push((object)[
                'tipe'   => 'dokter',
                'id'     => $d->id_dokter,
                'nama'   => $d->nama,
                'jenis'  => $d->jenis_dokter,
                'status' => $d->status,
                'jadwalStr' => implode(';;', $parts),
            ]);
        }

        foreach ($pemeriksa as $p) {
            $rows->push((object)[
                'tipe'   => 'pemeriksa',
                'id'     => $p->id_pemeriksa,
                'nama'   => $p->nama_pemeriksa,
                'jenis'  => 'Pemeriksa',
                'status' => $p->status,
                'jadwalStr' => 'Senin|07:00|16:00;;Selasa|07:00|16:00;;Rabu|07:00|16:00;;Kamis|07:00|16:00;;Jumat|07:00|16:00',
            ]);
        }

        return view('kepegawaian.dokter_pemeriksa.index', compact('rows','q'));
    }

    public function jadwalJson($tipe, $id)
    {
        if ($tipe === 'dokter') {
            $jadwal = JadwalDokter::where('id_dokter', $id)->get();
            return response()->json(['jadwal' => $jadwal]);
        }

        return response()->json([
            'jadwal' => [
                ['hari'=>'Senin','jam_mulai'=>'07:00','jam_selesai'=>'16:00'],
                ['hari'=>'Selasa','jam_mulai'=>'07:00','jam_selesai'=>'16:00'],
                ['hari'=>'Rabu','jam_mulai'=>'07:00','jam_selesai'=>'16:00'],
                ['hari'=>'Kamis','jam_mulai'=>'07:00','jam_selesai'=>'16:00'],
                ['hari'=>'Jumat','jam_mulai'=>'07:00','jam_selesai'=>'16:00'],
            ]
        ]);
    }

    public function jadwalDokterJson($id)
    {
        $items = JadwalDokter::where('id_dokter', $id)
            ->orderByRaw("FIELD(hari,'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu')")
            ->orderBy('jam_mulai')
            ->get()
            ->map(fn($j) => [
                'hari' => $j->hari,
                'jam_mulai' => substr((string)$j->jam_mulai, 0, 5),
                'jam_selesai' => substr((string)$j->jam_selesai, 0, 5),
            ])->values();

        return response()->json(['ok' => true, 'jadwal' => $items]);
    }

    public function jadwalView($tipe, $id)
    {
        if ($tipe === 'dokter') {
            $jadwal = JadwalDokter::where('id_dokter', $id)
                ->orderByRaw("FIELD(hari,'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu')")
                ->orderBy('jam_mulai')
                ->get();
        } else {
            // pemeriksa: jadwal fixed
            $jadwal = collect([
                (object)['hari'=>'Senin','jam_mulai'=>'07:00','jam_selesai'=>'16:00'],
                (object)['hari'=>'Selasa','jam_mulai'=>'07:00','jam_selesai'=>'16:00'],
                (object)['hari'=>'Rabu','jam_mulai'=>'07:00','jam_selesai'=>'16:00'],
                (object)['hari'=>'Kamis','jam_mulai'=>'07:00','jam_selesai'=>'16:00'],
                (object)['hari'=>'Jumat','jam_mulai'=>'07:00','jam_selesai'=>'16:00'],
            ]);
        }

        return view('kepegawwaian.dokter_pemeriksa.jadwal_view', compact('tipe','id','jadwal'));
    }

    public function storeDokter(Request $request)
    {
        $request->validate([
            'nama' => 'required',
            'jenis_dokter' => 'required',
            'status' => 'required',
            'jadwal' => 'required|array|min:1',
            'jadwal.*.hari' => 'required|string',
            'jadwal.*.jam_mulai' => 'required',
            'jadwal.*.jam_selesai' => 'required',
        ]);


        // Auto-generate ID
        $lastDokter = Dokter::orderBy('id_dokter', 'desc')->first();
        if ($lastDokter) {
            $lastNumber = (int) substr($lastDokter->id_dokter, 1);
            $newId = 'D' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newId = 'D001';
        }

        DB::transaction(function () use ($request, $newId) {
            Dokter::create([
                'id_dokter' => $newId,
                'nama' => $request->nama,
                'jenis_dokter' => $request->jenis_dokter,
                'status' => $request->status,
            ]);

            foreach ($request->jadwal as $j) {
                if (empty($j['hari']) || empty($j['jam_mulai']) || empty($j['jam_selesai'])) {
                    continue;
                }

                JadwalDokter::create([
                    'id_dokter' => $newId,
                    'hari' => $j['hari'],
                    'jam_mulai' => $j['jam_mulai'],
                    'jam_selesai' => $j['jam_selesai'],
                ]);
            }
        });
    

        return redirect()
        ->route('kepegawaian.dokter_pemeriksa.index')
        ->with('success', 'Data dokter berhasil ditambahkan');

    }

    public function updateDokter(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'jenis_dokter' => 'required|string|max:255',
            'status' => 'required|in:Aktif,Nonaktif',
            'jadwal' => 'nullable|array',
            'jadwal.*.hari' => 'required_with:jadwal|string|max:50',
            'jadwal.*.jam_mulai' => 'required_with:jadwal',
            'jadwal.*.jam_selesai' => 'required_with:jadwal',
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
                if (empty($j['hari']) || empty($j['jam_mulai']) || empty($j['jam_selesai'])) {
                    continue;
                }

                JadwalDokter::create([
                    'id_dokter' => $dokter->id_dokter,
                    'hari' => $j['hari'],
                    'jam_mulai' => $j['jam_mulai'],
                    'jam_selesai' => $j['jam_selesai'],
                ]);
            }
        });

        return redirect()
        ->route('kepegawaian.dokter_pemeriksa.index')
        ->with('success', 'Data dokter berhasil diperbarui');

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
            'nama_pemeriksa' => 'required',
            'status' => 'required',
        ]);

        // Auto-generate ID
        $lastPemeriksa = Pemeriksa::orderBy('id_pemeriksa', 'desc')->first();
        if ($lastPemeriksa) {
            $lastNumber = (int) substr($lastPemeriksa->id_pemeriksa, 1);
            $newId = 'P' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newId = 'P001';
        }

        Pemeriksa::create([
            'id_pemeriksa' => $newId,
            'nama_pemeriksa' => $request->nama_pemeriksa,
            'status' => $request->status,
        ]);

        return back()->with('success', 'Pemeriksa berhasil ditambahkan');
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

        return back()->with('success', 'Status dokter diperbarui.');
    }

    public function updateStatusPemeriksa(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:Aktif,Nonaktif']);

        Pemeriksa::where('id_pemeriksa', $id)->update([
            'status' => $request->status,
        ]);

        return back()->with('success', 'Status pemeriksa diperbarui.');
    }

}
