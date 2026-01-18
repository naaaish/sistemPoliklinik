<?php

namespace App\Http\Controllers\AdminPoli;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Pendaftaran;
use App\Models\Pemeriksaan;
use App\Models\DetailResep;

// untuk dropdown di edit (kalau halaman edit butuh)
use App\Models\Obat;
use App\Models\DiagnosaK3;
use App\Models\Saran;
use App\Models\Diagnosa;

class PemeriksaanController extends Controller
{
    /**
     * LIST "Pemeriksaan Pasien"
     * Menampilkan pendaftaran yang SUDAH punya pemeriksaan
     * + bisa search (opsional) berdasarkan kolom yang kamu tentukan.
     */
    public function index(Request $request)
{
    $q = $request->q;

    $rows = \App\Models\Pendaftaran::query()
        ->join('pemeriksaan', 'pemeriksaan.id_pendaftaran', '=', 'pendaftaran.id_pendaftaran')
        ->join('pasien', 'pasien.id_pasien', '=', 'pendaftaran.id_pasien')
        ->leftJoin('dokter', 'dokter.id_dokter', '=', 'pendaftaran.id_dokter')
        ->leftJoin('pemeriksa', 'pemeriksa.id_pemeriksa', '=', 'pendaftaran.id_pemeriksa')

        // search: input kamu "Masukkan nama dokter yang dicari"
        ->when($q, function ($query) use ($q) {
            $query->where('dokter.nama', 'like', "%{$q}%")
                  ->orWhere('pemeriksa.nama_pemeriksa', 'like', "%{$q}%");
        })

        ->orderByDesc('pemeriksaan.created_at')

        ->select([
            'pendaftaran.id_pendaftaran as id_pendaftaran',
            'pasien.nama_pasien as nama_pasien',

            // tanggal periksa pakai timestamp pemeriksaan
            'pemeriksaan.created_at as tanggal_periksa',
        ])
        ->selectRaw("COALESCE(dokter.nama, pemeriksa.nama_pemeriksa, '-') as dokter_pemeriksa")
        ->get();

    // blade kamu pakai $pemeriksaan
    return view('adminpoli.pemeriksaan.index', [
        'pemeriksaan' => $rows,
    ]);
}


    /**
     * DETAIL hasil pemeriksaan (read-only / ringkasan)
     */
    public function show($pendaftaranId)
    {
        // karena id string, ambil dengan where
        $pendaftaran = Pendaftaran::where('id_pendaftaran', $pendaftaranId)->firstOrFail();

        $hasil = Pemeriksaan::where('id_pendaftaran', $pendaftaranId)->firstOrFail();

        $detailResep = DetailResep::where('id_pemeriksaan', $hasil->id_pemeriksaan)->get();

        return view('adminpoli.pemeriksaan.show', compact('pendaftaran', 'hasil', 'detailResep'));
    }

    /**
     * FORM edit hasil pemeriksaan
     */
    public function edit($pendaftaranId)
    {
        $pendaftaran = Pendaftaran::where('id_pendaftaran', $pendaftaranId)->firstOrFail();

        $hasil = Pemeriksaan::where('id_pendaftaran', $pendaftaranId)->firstOrFail();

        $detailResep = DetailResep::where('id_pemeriksaan', $hasil->id_pemeriksaan)->get();

        // dropdown data (kalau halaman edit kamu butuh)
        $obat = Obat::orderBy('nama_obat', 'asc')->get();
        $diagnosaK3 = DiagnosaK3::orderBy('nama_penyakit', 'asc')->get();
        $saran = Saran::orderBy('saran', 'asc')->get();
        $penyakit = Diagnosa::orderBy('diagnosa', 'asc')->get();

        return view('adminpoli.pemeriksaan.edit', compact(
            'pendaftaran',
            'hasil',
            'detailResep',
            'obat',
            'diagnosaK3',
            'saran',
            'penyakit'
        ));
    }

    /**
     * UPDATE hasil pemeriksaan + detail resep
     * (pakai transaksi biar aman)
     */
    public function update(Request $request, $pendaftaranId)
    {
        // pastikan pendaftaran ada (string)
        Pendaftaran::where('id_pendaftaran', $pendaftaranId)->firstOrFail();

        $validated = $request->validate([
            'sistol'        => 'nullable|numeric',
            'diastol'       => 'nullable|numeric',
            'nadi'          => 'nullable|numeric',
            'gula_puasa'    => 'nullable|numeric',
            'gula_2jam_pp'  => 'nullable|numeric',
            'gula_sewaktu'  => 'nullable|numeric',
            'asam_urat'     => 'nullable|numeric',
            'cholesterol'   => 'nullable|numeric',
            'trigliseride'  => 'nullable|numeric',
            'suhu'          => 'nullable|numeric',
            'berat_badan'   => 'nullable|numeric',
            'tinggi_badan'  => 'nullable|numeric',

            // detail resep
            'obat_id'        => 'nullable|array',
            'obat_id.*'      => 'nullable|string',
            'jumlah'         => 'nullable|array',
            'jumlah.*'       => 'nullable|numeric',
            'satuan'         => 'nullable|array',
            'satuan.*'       => 'nullable|string',
            'harga_satuan'   => 'nullable|array',
            'harga_satuan.*' => 'nullable|numeric',
        ]);

        return DB::transaction(function () use ($validated, $pendaftaranId) {

            $hasil = Pemeriksaan::where('id_pendaftaran', $pendaftaranId)->firstOrFail();

            $hasil->update([
                'sistol'       => $validated['sistol'] ?? null,
                'diastol'      => $validated['diastol'] ?? null,
                'nadi'         => $validated['nadi'] ?? null,
                'gula_puasa'   => $validated['gula_puasa'] ?? null,
                'gula_2jam_pp' => $validated['gula_2jam_pp'] ?? null,
                'gula_sewaktu' => $validated['gula_sewaktu'] ?? null,
                'asam_urat'    => $validated['asam_urat'] ?? null,
                'cholesterol'  => $validated['cholesterol'] ?? null,
                'trigliseride' => $validated['trigliseride'] ?? null,
                'suhu'         => $validated['suhu'] ?? null,
                'berat_badan'  => $validated['berat_badan'] ?? null,
                'tinggi_badan' => $validated['tinggi_badan'] ?? null,
            ]);

            // reset detail resep biar nggak numpuk
            DetailResep::where('id_pemeriksaan', $hasil->id_pemeriksaan)->delete();

            $obatIds = $validated['obat_id'] ?? [];
            $jumlahs = $validated['jumlah'] ?? [];
            $satuans = $validated['satuan'] ?? [];
            $hargas  = $validated['harga_satuan'] ?? [];

            for ($i = 0; $i < count($obatIds); $i++) {
                if (empty($obatIds[$i])) continue;

                $qty = (int)($jumlahs[$i] ?? 0);
                $harga = (int)($hargas[$i] ?? 0);

                // skip baris kosong
                if ($qty <= 0 && $harga <= 0) continue;

                DetailResep::create([
                    'id_pemeriksaan' => $hasil->id_pemeriksaan, // PK string
                    'id_obat'        => $obatIds[$i],
                    'jumlah'         => $qty,
                    'satuan'         => $satuans[$i] ?? null,
                    'harga_satuan'   => $harga,
                    'subtotal'       => $qty * $harga,
                ]);
            }

            return redirect()
                ->route('adminpoli.pemeriksaan.show', $pendaftaranId)
                ->with('success', 'Hasil pemeriksaan berhasil diupdate.');
        });
    }
}
