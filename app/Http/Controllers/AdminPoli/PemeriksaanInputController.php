<?php

namespace App\Http\Controllers\AdminPoli;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Pendaftaran;
use App\Models\Pemeriksaan;
use App\Models\Obat;
use App\Models\DiagnosaK3;
use App\Models\Saran;
use App\Models\Diagnosa;
use App\Models\DetailResep;

class PemeriksaanInputController extends Controller
{
    public function index()
    {
        // sesuai yang kamu tulis: list pasien/pendaftaran
        $pendaftaran = Pendaftaran::orderBy('tanggal_periksa', 'desc')->get();
        return view('adminpoli.pemeriksaan.index', compact('pendaftaran'));
    }

    public function create($pendaftaranId)
    {
        $pendaftaran = Pendaftaran::findOrFail($pendaftaranId);

        // kolom sesuai migration: obat.nama_obat, diagnosa_k3.nama_penyakit, saran.isi
        $penyakit   = Diagnosa::orderBy('diagnosa')->get();
        $obat = Obat::orderBy('nama_obat', 'asc')->get();
        $diagnosaK3 = DiagnosaK3::orderBy('nama_penyakit', 'asc')->get();
        $saran = Saran::orderBy('saran', 'asc')->get();

        return view('adminpoli.pemeriksaan.create', compact(
            'pendaftaran',
            'obat',
            'diagnosaK3',
            'saran',
            'penyakit',
        ));
    }

    public function store(Request $request, $pendaftaranId)
    {
        /**
         * PENTING:
         * - dari migration kamu, PK banyak yang string: id_obat, id_saran, id_diagnosa_k3, id_pendaftaran
         * - jadi validasi pakai string, bukan integer
         */
        $validated = $request->validate([
            // pemeriksaan kesehatan (semua boleh kosong)
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

            // penyakit & diagnosa (kalau kamu simpan sebagai tabel relasi, kirim ID)
            // ubah name input blade jadi penyakit_id[] dan diagnosa_id[] kalau memang tabelnya beda
            'penyakit_id'     => 'nullable|array',
            'penyakit_id.*'   => 'nullable|string',

            'diagnosa_id'     => 'nullable|array',
            'diagnosa_id.*'   => 'nullable|string',

            // kalau kamu tetap pakai diagnosa_k3 (dropdown dari diagnosa_k3)
            'diagnosa_k3_id'    => 'nullable|array',
            'diagnosa_k3_id.*'  => 'nullable|string',

            // saran (tabel saran id_saran string)
            'saran_id'    => 'nullable|array',
            'saran_id.*'  => 'nullable|string',

            // obat & detail resep (satuan ada di detailresep)
            'obat_id'        => 'nullable|array',
            'obat_id.*'      => 'nullable|string',
            'jumlah'         => 'nullable|array',
            'jumlah.*'       => 'nullable|numeric',
            'satuan'         => 'nullable|array',
            'satuan.*'       => 'nullable|string',
            'harga_satuan'   => 'nullable|array',
            'harga_satuan.*' => 'nullable|numeric',
        ]);

        // pastikan pendaftaran ada
        Pendaftaran::findOrFail($pendaftaranId);

        // ========= SIMPAN PEMERIKSAAN =========
        // ⚠️ sesuaikan nama kolom FK di tabel pemeriksaan kamu:
        // kalau kolomnya id_pendaftaran → pakai 'id_pendaftaran'
        // kalau kolomnya pendaftaran_id → pakai 'pendaftaran_id'
        $pemeriksaan = Pemeriksaan::create([
            'id_pendaftaran' => $pendaftaranId,

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

        // ========= SIMPAN RELASI PENYAKIT & DIAGNOSA =========
        // Aktifkan kalau kamu memang punya pivot & relasinya sudah dibuat di model
        // Contoh relasi di Pemeriksaan:
        // public function penyakits(){ return $this->belongsToMany(Penyakit::class, 'pemeriksaan_penyakit', 'id_pemeriksaan','id_penyakit'); }
        // public function diagnosas(){ return $this->belongsToMany(Diagnosa::class, 'pemeriksaan_diagnosa', 'id_pemeriksaan','id_diagnosa'); }

        /*
        $penyakitIds = array_values(array_filter($validated['penyakit_id'] ?? []));
        if ($penyakitIds) {
            $pemeriksaan->penyakits()->sync($penyakitIds);
        }

        $diagnosaIds = array_values(array_filter($validated['diagnosa_id'] ?? []));
        if ($diagnosaIds) {
            $pemeriksaan->diagnosas()->sync($diagnosaIds);
        }
        */

        // Kalau kamu masih pakai diagnosa_k3 & saran sebagai relasi many-to-many juga:
        /*
        $diagK3 = array_values(array_filter($validated['diagnosa_k3_id'] ?? []));
        if ($diagK3) $pemeriksaan->diagnosaK3s()->sync($diagK3);

        $saranIds = array_values(array_filter($validated['saran_id'] ?? []));
        if ($saranIds) $pemeriksaan->sarans()->sync($saranIds);
        */

        // ========= SIMPAN DETAIL RESEP (satuan di detailresep) =========
        $total = 0;

        $obatIds = $validated['obat_id'] ?? [];
        $jumlahs = $validated['jumlah'] ?? [];
        $satuans = $validated['satuan'] ?? [];
        $hargas  = $validated['harga_satuan'] ?? [];

        for ($i = 0; $i < count($obatIds); $i++) {
            if (empty($obatIds[$i])) continue;

            $qty = (int)($jumlahs[$i] ?? 0);
            $harga = (int)($hargas[$i] ?? 0);
            $subtotal = $qty * $harga;
            $total += $subtotal;

            // ini asumsi: detailresep punya kolom id_pemeriksaan langsung
            // kalau detailresep kamu pakai id_resep dulu, nanti aku sesuaikan
            DetailResep::create([
                'id_pemeriksaan' => $pemeriksaan->id_pemeriksaan ?? $pemeriksaan->getKey(), // sesuaikan PK
                'id_obat'        => $obatIds[$i],
                'jumlah'         => $qty,
                'satuan'         => $satuans[$i] ?? null,
                'harga_satuan'   => $harga,
                'subtotal'       => $subtotal,
            ]);
        }

        return redirect()
            ->route('adminpoli.dashboard')
            ->with('success', 'Hasil pemeriksaan berhasil disimpan.');
    }
}