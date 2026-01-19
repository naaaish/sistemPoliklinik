<?php

namespace App\Http\Controllers\AdminPoli;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Pendaftaran;
use App\Models\Pemeriksaan;
use App\Models\DetailResep;
use App\Models\Obat;
use App\Models\DiagnosaK3;
use App\Models\Saran;
use App\Models\Diagnosa;
use App\Models\Resep;


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
        $pendaftaran = Pendaftaran::where('id_pendaftaran', $pendaftaranId)->firstOrFail();
        $hasil = Pemeriksaan::where('id_pendaftaran', $pendaftaranId)->firstOrFail();

        $penyakitIds = $this->parseIds($hasil->id_diagnosa);
        $penyakitTerpilih = $penyakitIds
            ? Diagnosa::whereIn('id_diagnosa', $penyakitIds)->pluck('diagnosa')->toArray()
            : [];

        $k3Ids = $this->parseIds($hasil->id_nb);
        $diagnosaK3Terpilih = $k3Ids
            ? DiagnosaK3::whereIn('id_nb', $k3Ids)->pluck('nama_penyakit')->toArray()
            : [];

        $saranIds = $this->parseIds($hasil->id_saran);
        $saranTerpilih = $saranIds
            ? Saran::whereIn('id_saran', $saranIds)->pluck('saran')->toArray()
            : [];


        // ===== resep berdasarkan id_pemeriksaan =====
        $resep = Resep::where('id_pemeriksaan', $hasil->id_pemeriksaan)->first();

        // ===== detail resep berdasarkan id_resep (join obat biar ada nama & harga) =====
        $detailResep = collect();
        if ($resep) {
            $detailResep = DetailResep::where('id_resep', $resep->id_resep)
                ->join('obat', 'obat.id_obat', '=', 'detail_resep.id_obat')
                ->select([
                    'detail_resep.*',
                    'obat.nama_obat as nama_obat',
                    'obat.harga as harga_satuan',
                ])
                ->get();
        }

        // ===== master obat (buat dropdown obat editable di show) =====
        $obat = Obat::orderBy('nama_obat', 'asc')->get();

        return view('adminpoli.pemeriksaan.show', compact(
            'pendaftaran',
            'hasil',
            'resep',
            'detailResep',
            'obat',
            'penyakitTerpilih',
            'diagnosaK3Terpilih',
            'saranTerpilih'
        ));
    }

    /**
     * FORM edit hasil pemeriksaan
     */
    public function edit($pendaftaranId)
    {
        $pendaftaran = Pendaftaran::where('id_pendaftaran', $pendaftaranId)->firstOrFail();

        $hasil = Pemeriksaan::where('id_pendaftaran', $pendaftaranId)->firstOrFail();

        $resep = Resep::where('id_pemeriksaan', $hasil->id_pemeriksaan)->first();

        $detailResep = collect();
        if ($resep) {
            $detailResep = DetailResep::where('id_resep', $resep->id_resep)
                ->join('obat', 'obat.id_obat', '=', 'detail_resep.id_obat')
                ->select([
                    'detail_resep.*',
                    'obat.nama_obat',
                ])
                ->get();
        }

        // dropdown data (kalau halaman edit kamu butuh)
        $obat = Obat::orderBy('nama_obat', 'asc')->get();
        $diagnosaK3 = DiagnosaK3::orderBy('nama_penyakit', 'asc')->get();
        $saran = Saran::orderBy('saran', 'asc')->get();
        $penyakit = Diagnosa::orderBy('diagnosa', 'asc')->get();

        return view('adminpoli.pemeriksaan.edit', compact(
            'pendaftaran',
            'hasil',
            'resep',
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

        return DB::transaction(function () use ($validated, $pendaftaranId) {

            $hasil = Pemeriksaan::where('id_pendaftaran', $pendaftaranId)->firstOrFail();

            // mapping request -> kolom tabel pemeriksaan
            $hasil->update([
                'sistol'     => $validated['sistol'] ?? null,
                'diastol'    => $validated['diastol'] ?? null,
                'nadi'       => $validated['nadi'] ?? null,

                'gd_puasa'   => $validated['gula_puasa'] ?? null,
                'gd_duajam'  => $validated['gula_2jam_pp'] ?? null,
                'gd_sewaktu' => $validated['gula_sewaktu'] ?? null,

                'asam_urat'  => $validated['asam_urat'] ?? null,
                'chol'       => $validated['cholesterol'] ?? null,
                'tg'         => $validated['trigliseride'] ?? null,

                'suhu'       => $validated['suhu'] ?? null,
                'berat'      => $validated['berat_badan'] ?? null,
                'tinggi'     => $validated['tinggi_badan'] ?? null,
            ]);

            // ====== RESEP & DETAIL_RESEP ======
            $obatIds = $validated['obat_id'] ?? [];
            $jumlahs = $validated['jumlah'] ?? [];
            $satuans = $validated['satuan'] ?? [];
            $hargas  = $validated['harga_satuan'] ?? [];

            // validasi: kalau obat dipilih, satuan wajib ada
            foreach ($obatIds as $i => $idObat) {
                if (!$idObat) continue;
                if (empty($satuans[$i])) {
                    return back()->withInput()->withErrors(["satuan.$i" => "Satuan wajib diisi jika obat dipilih."]);
                }
            }

            $resep = Resep::where('id_pemeriksaan', $hasil->id_pemeriksaan)->first();

            // hitung total & siapkan detail rows
            $detailRows = [];
            $totalTagihan = 0;

            for ($i = 0; $i < count($obatIds); $i++) {
                $idObat = $obatIds[$i] ?? null;
                if (!$idObat) continue;

                $qty   = (int)($jumlahs[$i] ?? 0);
                $harga = (float)($hargas[$i] ?? 0);
                $satuan = $satuans[$i] ?? '';

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

            // kalau tidak ada detail, hapus resep (jika ada) supaya bersih
            if (count($detailRows) === 0) {
                if ($resep) {
                    DetailResep::where('id_resep', $resep->id_resep)->delete();
                    $resep->delete();
                }

                return redirect()
                    ->route('adminpoli.pemeriksaan.show', $pendaftaranId)
                    ->with('success', 'Hasil pemeriksaan berhasil diupdate (tanpa resep).');
            }

            // kalau belum ada resep, buat baru
            if (!$resep) {
                $idResep = 'RS' . date('ymdHis') . \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(6));

                $resep = Resep::create([
                    'id_resep'       => $idResep,
                    'id_pemeriksaan' => $hasil->id_pemeriksaan,
                    'total_tagihan'  => $totalTagihan,
                ]);
            } else {
                // update total tagihan
                $resep->update(['total_tagihan' => $totalTagihan]);

                // reset detail lama
                DetailResep::where('id_resep', $resep->id_resep)->delete();
            }

            // insert detail baru
            foreach ($detailRows as $row) {
                DetailResep::create([
                    'id_resep'  => $resep->id_resep,
                    'id_obat'   => $row['id_obat'],
                    'jumlah'    => $row['jumlah'],
                    'satuan'    => $row['satuan'],
                    'subtotal'  => $row['subtotal'],
                ]);
            }

            return redirect()
                ->route('adminpoli.pemeriksaan.show', $pendaftaranId)
                ->with('success', 'Hasil pemeriksaan berhasil diupdate.');
        });
    }

    private function parseIds($val): array
    {
        if (!$val) return [];

        // kalau JSON array
        if (is_string($val) && strlen($val) && $val[0] === '[') {
            $arr = json_decode($val, true);
            return is_array($arr) ? array_values(array_filter($arr)) : [];
        }

        // default CSV
        return array_values(array_filter(array_map('trim', explode(',', (string)$val))));
    }

}
