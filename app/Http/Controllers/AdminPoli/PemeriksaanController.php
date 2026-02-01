<?php

namespace App\Http\Controllers\AdminPoli;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

use App\Models\Pendaftaran;
use App\Models\Pemeriksaan;
use App\Models\DetailResep;
use App\Models\Obat;
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
            ->join('pegawai', 'pegawai.nip', '=', 'pendaftaran.nip')
            ->leftJoin('keluarga', 'keluarga.id_keluarga', '=', 'pendaftaran.id_keluarga')
            ->leftJoin('dokter', 'dokter.id_dokter', '=', 'pendaftaran.id_dokter')
            ->leftJoin('pemeriksa', 'pemeriksa.id_pemeriksa', '=', 'pendaftaran.id_pemeriksa')

            // search: input kamu "Masukkan nama pegawai yang dicari"
            ->when($q, function ($query) use ($q) {
                $query->where('pegawai.nama_pegawai', 'like', "%{$q}%")
                      ->orWhere('keluarga.nama_keluarga', 'like', "%{$q}%");
            })

            ->orderByDesc('pemeriksaan.created_at')

            ->select([
                'pendaftaran.id_pendaftaran as id_pendaftaran',
                DB::raw("CASE 
                    WHEN pendaftaran.tipe_pasien = 'keluarga' THEN keluarga.nama_keluarga
                    ELSE pegawai.nama_pegawai
                END as nama_pasien"),
                'pemeriksaan.created_at as tanggal_periksa',
            ])
            ->selectRaw("COALESCE(dokter.nama, pemeriksa.nama_pemeriksa, '-') as dokter_pemeriksa");
            
        $perPage = $request->get('per_page', 10);
        $allowed = ['10', '25', '50', '100', 'all'];
        if (!in_array((string) $perPage, $allowed)) $perPage = 10;

        $rows = ($perPage === 'all')
            ? $rows->get()
            : $rows->paginate((int) $perPage)->appends($request->query());

        // blade kamu pakai $pemeriksaan
        return view('adminpoli.pemeriksaan.index', [
            'pemeriksaan' => $rows,
            'perPage' => $perPage,
        ]);
    }


    /**
     * DETAIL hasil pemeriksaan (read-only / ringkasan)
     */
    public function show($pendaftaranId)
    {
        $pendaftaran = Pendaftaran::where('id_pendaftaran', $pendaftaranId)->firstOrFail();
        $hasil = Pemeriksaan::where('id_pendaftaran', $pendaftaranId)->firstOrFail();

        $penyakitTerpilih = DB::table('detail_pemeriksaan_penyakit as dp')
            ->join('diagnosa as d', 'd.id_diagnosa', '=', 'dp.id_diagnosa')
            ->where('dp.id_pemeriksaan', $hasil->id_pemeriksaan)
            ->pluck('d.diagnosa')
            ->toArray();

        $saranTerpilih = DB::table('detail_pemeriksaan_saran as ds')
            ->join('saran as s', 's.id_saran', '=', 'ds.id_saran')
            ->where('ds.id_pemeriksaan', $hasil->id_pemeriksaan)
            ->pluck('s.saran')
            ->toArray();

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
        $obat = Obat::where('is_active', 1)
            ->orderBy('nama_obat', 'asc')
            ->get();

        return view('adminpoli.pemeriksaan.show', compact(
            'pendaftaran',
            'hasil',
            'resep',
            'detailResep',
            'obat',
            'penyakitTerpilih',
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
        $obat = Obat::where('is_active', 1)
            ->orderBy('nama_obat', 'asc')
            ->get();
        $saran = Saran::where('is_active', 1)
            ->orderBy('saran', 'asc')
            ->get();
        $penyakit = Diagnosa::where('is_active', 1)
            ->orderBy('diagnosa', 'asc')
            ->get();

        return view('adminpoli.pemeriksaan.edit', compact(
            'pendaftaran',
            'hasil',
            'resep',
            'detailResep',
            'obat',
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
        $pendaftaran = Pendaftaran::where('id_pendaftaran', $pendaftaranId)->firstOrFail();
        $hasil = Pemeriksaan::where('id_pendaftaran', $pendaftaran->id_pendaftaran)->firstOrFail();

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
            'obat_id.*'      => ['nullable', Rule::exists('obat', 'id_obat')->where('is_active', 1)],
            'jumlah'         => 'nullable|array',
            'jumlah.*'       => 'nullable|numeric',
            'satuan'         => 'nullable|array',
            'satuan.*'       => 'nullable|string',
            'harga_satuan'   => 'nullable|array',
            'harga_satuan.*' => 'nullable|numeric',
        ]);

        return DB::transaction(function () use ($validated, $pendaftaranId) {

            $hasil = Pemeriksaan::where('id_pendaftaran', $pendaftaranId)->firstOrFail();

            // update data pemeriksaan (mapping ke kolom tabel kamu)
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

            // ===== RESEP & DETAIL_RESEP =====
            $obatIds = $validated['obat_id'] ?? [];
            $jumlahs = $validated['jumlah'] ?? [];
            $satuans = $validated['satuan'] ?? [];
            $hargas  = $validated['harga_satuan'] ?? [];

            // validasi: kalau obat dipilih, satuan wajib
            foreach ($obatIds as $i => $idObat) {
                if (!$idObat) continue;
                if (!isset($satuans[$i]) || trim((string)$satuans[$i]) === '') {
                    return back()
                        ->withInput()
                        ->withErrors(["satuan.$i" => "Satuan wajib diisi jika obat dipilih."]);
                }
            }

            // siapkan rows detail yang valid (skip baris kosong)
            $detailToInsert = [];
            $totalTagihan = 0;

            for ($i = 0; $i < count($obatIds); $i++) {
                $obatId = $obatIds[$i] ?? null;
                if (!$obatId) continue;

                $qty = (int)($jumlahs[$i] ?? 1);
                if ($qty <= 0) $qty = 1;

                $harga = (int)($hargas[$i] ?? 0);
                $satuan = trim((string)($satuans[$i] ?? ''));

                $subtotal = $qty * $harga;
                $totalTagihan += $subtotal;

                $detailToInsert[] = [
                    'id_obat'  => $obatId,
                    'jumlah'   => $qty,
                    'satuan'   => $satuan,
                    'subtotal' => $subtotal,
                ];
            }

            // ambil resep existing (kalau ada)
            $resep = Resep::where('id_pemeriksaan', $hasil->id_pemeriksaan)->first();

            // kalau tidak ada obat sama sekali → hapus resep & detail kalau ada, selesai
            if (count($detailToInsert) === 0) {
                if ($resep) {
                    DetailResep::where('id_resep', $resep->id_resep)->delete();
                    $resep->delete();
                }

                return redirect()
                    ->route('adminpoli.pemeriksaan.index')
                    ->with('success', 'Hasil pemeriksaan berhasil diupdate (tanpa resep).');
            }

            // kalau belum ada resep → buat
            if (!$resep) {
                $resep = Resep::create([
                    'id_resep'       => 'RS' . now()->format('ymdHis') . strtoupper(substr(uniqid(), -6)),
                    'id_pemeriksaan' => $hasil->id_pemeriksaan,
                    'total_tagihan'  => $totalTagihan,
                ]);
            } else {
                $resep->update(['total_tagihan' => $totalTagihan]);
                DetailResep::where('id_resep', $resep->id_resep)->delete();
            }

            // insert detail baru
            foreach ($detailToInsert as $row) {
                DetailResep::create([
                    'id_resep'  => $resep->id_resep,
                    'id_obat'   => $row['id_obat'],
                    'jumlah'    => $row['jumlah'],
                    'satuan'    => $row['satuan'],
                    'subtotal'  => $row['subtotal'],
                ]);
            }

            return redirect()
                ->route('adminpoli.pemeriksaan.index')
                ->with('success', 'Hasil pemeriksaan berhasil diupdate.');
        });
    }
    
}
