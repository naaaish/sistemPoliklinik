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
        $pendaftaran = Pendaftaran::orderBy('tanggal', 'desc')->get();
        return view('adminpoli.pemeriksaan.index', compact('pendaftaran'));
    }

    public function create($pendaftaranId)
    {
        $pendaftaran = Pendaftaran::findOrFail($pendaftaranId);

        // kolom sesuai migration: obat.nama_obat, diagnosa_k3.nama_penyakit, saran.isi
        $penyakit = DB::table('diagnosa')
            ->leftJoin('diagnosa_k3', 'diagnosa.id_nb', '=', 'diagnosa_k3.id_nb')
            ->select(
                'diagnosa.id_diagnosa',
                'diagnosa.diagnosa',
                'diagnosa.id_nb',
                'diagnosa_k3.nama_penyakit as nama_k3'
            )
            ->orderBy('diagnosa.diagnosa')
            ->get();

        $obat  = Obat::orderBy('nama_obat', 'asc')->get();
        $saran = Saran::orderBy('saran', 'asc')->get();

        return view('adminpoli.pemeriksaan.create', compact(
            'pendaftaran',
            'obat',
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

            'penyakit_id'     => 'nullable|array',
            'penyakit_id.*'   => 'nullable|string',
            'id_saran'    => 'nullable|array',
            'id_saran.*'  => 'nullable|string',

            // resep
            'obat_id'        => 'nullable|array',
            'obat_id.*'      => 'nullable|string',
            'jumlah'         => 'nullable|array',
            'jumlah.*'       => 'nullable|numeric',
            'satuan'         => 'nullable|array',
            'satuan.*'       => 'nullable|string',
        ]);

        $obatIds  = $validated['obat_id'] ?? [];
        $jumlahs  = $validated['jumlah'] ?? [];
        $satuans  = $validated['satuan'] ?? [];

        foreach ($obatIds as $i => $idObat) {
            if (!$idObat) continue; // skip baris kosong

            $qty    = $jumlahs[$i] ?? null;
            $satuan = $satuans[$i] ?? null;

            if ($qty === null || (float)$qty <= 0) {
                return back()->withInput()->withErrors(["jumlah.$i" => "Jumlah wajib diisi (min 1) jika obat dipilih."]);
            }

            if (!$satuan) {
                return back()->withInput()->withErrors(["satuan.$i" => "Satuan wajib diisi jika obat dipilih."]);
            }
        }

        // pastikan pendaftaran ada
        Pendaftaran::findOrFail($pendaftaranId);

        return DB::transaction(function () use ($validated, $pendaftaranId) {
            // ========= GENERATE ID (20 char) =========
            // 2(prefix) + 12(ymdHis) + 6(random) = 20
            $idPemeriksaan = 'PM' . date('ymdHis') . Str::upper(Str::random(6));

            $penyakitIds = array_values(array_filter($validated['penyakit_id'] ?? []));

            // AUTO ambil NB K3 dari tabel diagnosa
            $k3Ids = Diagnosa::whereIn('id_diagnosa', $penyakitIds)
                ->pluck('id_nb')
                ->filter()
                ->unique()
                ->values()
                ->all();

            $saranIdsUI  = array_values(array_filter($validated['id_saran'] ?? []));

            $autoSaranIds = [];
            if (count($penyakitIds) > 0) {
                // sesuaikan nama kolom jika beda: id_saran / saran_id
                $autoSaranIds = Saran::whereIn('id_diagnosa', $penyakitIds)
                    ->pluck('id_saran')
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();
            }

            // gabung UI + auto, lalu unique
            $saranIds = array_values(array_unique(array_merge($saranIdsUI, $autoSaranIds)));
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
            ]);
            
            // penyakit
            if (count($penyakitIds) > 0) {
                $rows = array_map(fn($id) => [
                    'id_pemeriksaan' => $pemeriksaan->id_pemeriksaan,
                    'id_diagnosa' => $id,
                ], $penyakitIds);

                DB::table('detail_pemeriksaan_penyakit')->insert($rows);
            }

            // diagnosa k3
            if (count($k3Ids) > 0) {
                $rows = array_map(fn($id) => [
                    'id_pemeriksaan' => $pemeriksaan->id_pemeriksaan,
                    'id_nb' => $id,
                ], $k3Ids);

                DB::table('detail_pemeriksaan_diagnosa_k3')->insert($rows);
            }

            // saran
            if (count($saranIds) > 0) {
                $rows = array_map(fn($id) => [
                    'id_pemeriksaan' => $pemeriksaan->id_pemeriksaan,
                    'id_saran' => $id,
                ], $saranIds);

                DB::table('detail_pemeriksaan_saran')->insert($rows);
            }

            // ========= SIMPAN RESEP + DETAIL_RESEP =========
            $obatIds = $validated['obat_id'] ?? [];
            $jumlahs = $validated['jumlah'] ?? [];
            $satuans = $validated['satuan'] ?? [];

            $obatIdsClean = array_values(array_filter($obatIds));

            // ambil harga dari tabel obat (sesuai show)
            $hargaMap = Obat::whereIn('id_obat', $obatIdsClean)
                ->pluck('harga', 'id_obat'); // key=id_obat, value=harga

            $detailRows = [];
            $totalTagihan = 0;

            $count = count($obatIds);
            for ($i = 0; $i < $count; $i++) {
                $idObat = $obatIds[$i] ?? null;
                if (!$idObat) continue;

                $qty    = (int)($jumlahs[$i] ?? 0);
                $satuan = $satuans[$i] ?? '';

                if ($qty <= 0) continue;

                $harga = (float)($hargaMap[$idObat] ?? 0); // harga dari DB
                $subtotal = $qty * $harga;

                $totalTagihan += $subtotal;

                $detailRows[] = [
                    'id_obat'  => $idObat,
                    'jumlah'   => $qty,
                    'satuan'   => $satuan,
                    'subtotal' => $subtotal,
                ];
            }

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