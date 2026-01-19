<?php

namespace App\Http\Controllers\AdminPoli;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

use App\Models\Pendaftaran;
use App\Models\Pemeriksaan;
use App\Models\Obat;
use App\Models\DiagnosaK3;
use App\Models\Saran;
use App\Models\Diagnosa;
use App\Models\DetailResep;
use App\Models\Resep;

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
        $saran = Saran::orderBy('saran', 'asc')->get();
        $diagnosaK3 = DiagnosaK3::where('tipe', 'penyakit')
            ->orderBy('nama_penyakit', 'asc')
            ->get();

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
        $validated = $request->validate([
            // pemeriksaan (nullable semua)
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

            // pilihan (UI kamu kirim array via chips)
            'penyakit_id'     => 'nullable|array',
            'penyakit_id.*'   => 'nullable|string',

            'diagnosa_k3_id'    => 'nullable|array',
            'diagnosa_k3_id.*'  => 'nullable|string',

            'saran_id'    => 'nullable|array',
            'saran_id.*'  => 'nullable|string',

            // resep
            'obat_id'        => 'nullable|array',
            'obat_id.*'      => 'nullable|string',
            'jumlah'         => 'nullable|array',
            'jumlah.*'       => 'nullable|numeric',
            'satuan'         => 'nullable|array',
            'satuan.*'       => 'nullable|string',
            'harga_satuan'   => 'nullable|array',
            'harga_satuan.*' => 'nullable|numeric',
        ]);

        $obatIds = $validated['obat_id'] ?? [];
        $satuans = $validated['satuan'] ?? [];

        foreach ($obatIds as $i => $idObat) {
            if (!$idObat) continue; // skip baris kosong

            $satuan = $satuans[$i] ?? null;
            if (!$satuan) {
                return back()
                    ->withInput()
                    ->withErrors(["satuan.$i" => "Satuan wajib diisi jika obat dipilih."]);
            }
        }

        // pastikan pendaftaran ada
        Pendaftaran::findOrFail($pendaftaranId);

        return DB::transaction(function () use ($validated, $pendaftaranId) {

            // ========= GENERATE ID (20 char) =========
            // 2(prefix) + 12(ymdHis) + 6(random) = 20
            $idPemeriksaan = 'PM' . date('ymdHis') . Str::upper(Str::random(6));

            $penyakitIds = array_values(array_filter($validated['penyakit_id'] ?? []));
            $k3Ids       = array_values(array_filter($validated['diagnosa_k3_id'] ?? []));
            $saranIds    = array_values(array_filter($validated['saran_id'] ?? []));

            // ========= SIMPAN PEMERIKSAAN =========
            $pemeriksaan = Pemeriksaan::create([
                'id_pemeriksaan' => $idPemeriksaan,
                'id_pendaftaran' => $pendaftaranId,

                'sistol'   => $validated['sistol'] ?? null,
                'diastol'  => $validated['diastol'] ?? null,
                'nadi'     => $validated['nadi'] ?? null,

                // mapping sesuai migration pemeriksaan
                'gd_puasa'   => $validated['gula_puasa'] ?? null,
                'gd_duajam'  => $validated['gula_2jam_pp'] ?? null,
                'gd_sewaktu' => $validated['gula_sewaktu'] ?? null,

                'asam_urat' => $validated['asam_urat'] ?? null,
                'chol'      => $validated['cholesterol'] ?? null,
                'tg'        => $validated['trigliseride'] ?? null,

                'suhu'   => $validated['suhu'] ?? null,
                'berat'  => $validated['berat_badan'] ?? null,
                'tinggi' => $validated['tinggi_badan'] ?? null,

                'id_diagnosa' => implode(',', $penyakitIds),
                'id_nb'       => implode(',', $k3Ids),
                'id_saran'    => implode(',', $saranIds),
            ]);

            // ========= SIMPAN RESEP + DETAIL_RESEP =========
            $obatIds = $validated['obat_id'] ?? [];
            $jumlahs = $validated['jumlah'] ?? [];
            $satuans = $validated['satuan'] ?? [];
            $hargas  = $validated['harga_satuan'] ?? [];

            $detailRows = [];
            $totalTagihan = 0;

            $count = count($obatIds);
            for ($i = 0; $i < $count; $i++) {
                $idObat = $obatIds[$i] ?? null;
                if (!$idObat) continue;

                $qty   = (int) ($jumlahs[$i] ?? 0);
                $harga = (float) ($hargas[$i] ?? 0);
                $satuan = $satuans[$i] ?? '';

                // skip kalau qty <= 0 (biar ga nyimpen baris kosong)
                if ($qty <= 0) continue;

                $subtotal = $qty * $harga;
                $totalTagihan += $subtotal;

                $detailRows[] = [
                    'id_obat'  => $idObat,
                    'jumlah'   => $qty,
                    'satuan'   => $satuan,
                    'subtotal' => $subtotal,
                ];
            }

            // hanya buat resep kalau ada minimal 1 detail
            if (count($detailRows) > 0) {
                $idResep = 'RS' . date('ymdHis') . Str::upper(Str::random(6));

                Resep::create([
                    'id_resep'       => $idResep,
                    'id_pemeriksaan' => $pemeriksaan->id_pemeriksaan,
                    'total_tagihan'  => $totalTagihan,
                ]);

                foreach ($detailRows as $row) {
                    DetailResep::create([
                        'id_resep'  => $idResep,
                        'id_obat'   => $row['id_obat'],
                        'jumlah'    => $row['jumlah'],
                        'satuan'    => $row['satuan'],
                        'subtotal'  => $row['subtotal'],
                    ]);
                }
            }

            return redirect()
                ->route('adminpoli.dashboard')
                ->with('success', 'Hasil pemeriksaan berhasil disimpan.');
        });
    }

}