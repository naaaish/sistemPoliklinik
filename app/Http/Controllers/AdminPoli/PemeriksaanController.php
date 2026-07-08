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
                'pendaftaran.tanggal as tanggal_periksa',
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
        $pendaftaran = Pendaftaran::query()
    ->leftJoin('pegawai', 'pegawai.nip', '=', 'pendaftaran.nip')
    ->leftJoin('keluarga', 'keluarga.id_keluarga', '=', 'pendaftaran.id_keluarga')
    ->select(
        'pendaftaran.*',
        DB::raw("
            CASE
                WHEN pendaftaran.tipe_pasien = 'keluarga'
                THEN keluarga.nama_keluarga
                ELSE pegawai.nama_pegawai
            END AS nama_pasien
        ")
    )
    ->where('pendaftaran.id_pendaftaran', $pendaftaranId)
    ->firstOrFail();
        $hasil = Pemeriksaan::where('id_pendaftaran', $pendaftaranId)->firstOrFail();

        // ===== detail penyakit: (buat render card + id_nb editable) =====
        $penyakitDetail = DB::table('detail_pemeriksaan_penyakit as dp')
            ->join('diagnosa as d', 'd.id_diagnosa', '=', 'dp.id_diagnosa')
            ->where('dp.id_pemeriksaan', $hasil->id_pemeriksaan)
            ->select([
                'dp.id_diagnosa',
                'dp.id_nb',
                'd.diagnosa',
            ])
            ->orderBy('d.diagnosa')
            ->get();

        // ===== saran (optional tampil list aja) =====
        $saranTerpilih = DB::table('detail_pemeriksaan_saran as ds')
            ->join('saran as s', 's.id_saran', '=', 'ds.id_saran')
            ->where('ds.id_pemeriksaan', $hasil->id_pemeriksaan)
            ->pluck('s.saran')
            ->toArray();

        // ===== resep berdasarkan id_pemeriksaan =====
        $resep = Resep::where('id_pemeriksaan', $hasil->id_pemeriksaan)->first();

        // ===== detail resep join obat =====
        $detailResep = collect();
        if ($resep) {
            $detailResep = DetailResep::where('id_resep', $resep->id_resep)
                ->join('obat', 'obat.id_obat', '=', 'detail_resep.id_obat')
                ->select([
                    'detail_resep.*',
                    'obat.nama_obat',
                    DB::raw('COALESCE(obat.harga, 0) as harga_satuan'),
                    DB::raw('COALESCE(detail_resep.satuan, "") as satuan_default'),
                ])
                ->get();
        }

        // ===== master dropdown =====
        $obat = Obat::where('is_active', 1)->orderBy('nama_obat', 'asc')->get();
        $penyakit = Diagnosa::where('is_active', 1)->orderBy('diagnosa', 'asc')->get();

        // ===== dokter/pemeriksa buat baris petugas =====
        $dokter = DB::table('dokter')->where('status', 'aktif')->orderBy('nama')->get();
        $pemeriksa = DB::table('pemeriksa')->where('status', 'aktif')->orderBy('id_pemeriksa')->get();

        $saran = Saran::query()
            ->where('is_active', 1)
            ->orderBy('kategori_saran', 'asc')
            ->get(['id_saran', 'kategori_saran', 'saran']);
           
        $saranDetail = DB::table('detail_pemeriksaan_saran as ds')
            ->join('saran as s', 's.id_saran', '=', 'ds.id_saran')
            ->where('ds.id_pemeriksaan', $hasil->id_pemeriksaan)
            ->orderBy('s.kategori_saran', 'asc')
            ->get(['s.id_saran','s.kategori_saran','s.saran']);

        return view('adminpoli.pemeriksaan.show', compact(
            'pendaftaran',
            'hasil',
            'resep',
            'detailResep',
            'obat',
            'saranTerpilih',
            'penyakit',
            'penyakitDetail',
            'saranDetail',
            'saran',
            'dokter',
            'pemeriksa'
        ));
    }
//delete pemeriksaan + detail resep + saran + diagnosa (kalau ada)
  public function destroy($idPemeriksaan)
{
    try {
        DB::transaction(function () use ($idPemeriksaan) {

            $resepIds = DB::table('resep')
                ->where('id_pemeriksaan', $idPemeriksaan)
                ->pluck('id_resep');

            if ($resepIds->isNotEmpty()) {
                DB::table('detail_resep')->whereIn('id_resep', $resepIds)->delete();
            }

            DB::table('resep')->where('id_pemeriksaan', $idPemeriksaan)->delete();

            $del = DB::table('pemeriksaan')->where('id_pemeriksaan', $idPemeriksaan)->delete();
            if ($del === 0) {
                throw new \Exception("Pemeriksaan $idPemeriksaan tidak ditemukan.");
            }
        });

        return redirect()->route('adminpoli.pemeriksaan.index')
            ->with('success', 'Pemeriksaan berhasil dihapus');

    } catch (\Throwable $e) {
        return back()->with('error', 'Gagal menghapus: '.$e->getMessage());
    }
}
    /**
     * FORM edit hasil pemeriksaan
     */
    public function edit($pendaftaranId)
    {
        $pendaftaran = Pendaftaran::where('id_pendaftaran', $pendaftaranId)->firstOrFail();
        $hasil = Pemeriksaan::where('id_pendaftaran', $pendaftaranId)->firstOrFail();

$pendaftaran->tanggal = $validated['tanggal'];
$pendaftaran->jenis_pemeriksaan = $validated['jenis_pemeriksaan'];
        $resep = Resep::where('id_pemeriksaan', $hasil->id_pemeriksaan)->first();

        $detailResep = collect();
        if ($resep) {
            $detailResep = DetailResep::where('id_resep', $resep->id_resep)
                ->join('obat', 'obat.id_obat', '=', 'detail_resep.id_obat')
                ->select([
                    'detail_resep.*',
                    'obat.nama_obat',
                    DB::raw('COALESCE(obat.harga, 0) as harga_satuan'),
                    DB::raw('COALESCE(detail_resep.satuan, "") as satuan_default'),
                ])
                ->get();
        }

        $penyakitDetail = DB::table('detail_pemeriksaan_penyakit as dp')
            ->join('diagnosa as d', 'd.id_diagnosa', '=', 'dp.id_diagnosa')
            ->where('dp.id_pemeriksaan', $hasil->id_pemeriksaan)
            ->select(['dp.id_diagnosa','dp.id_nb','d.diagnosa'])
            ->orderBy('d.diagnosa')
            ->get();

        $obat = Obat::where('is_active', 1)->orderBy('nama_obat', 'asc')->get();
        $saran = Saran::where('is_active', 1)->orderBy('kategori_saran', 'asc')->get(['id_saran', 'kategori_saran', 'saran']);
        $penyakit = Diagnosa::where('is_active', 1)->orderBy('diagnosa', 'asc')->get();

        $saranDetail = DB::table('detail_pemeriksaan_saran as ds')
            ->join('saran as s', 's.id_saran', '=', 'ds.id_saran')
            ->where('ds.id_pemeriksaan', $hasil->id_pemeriksaan)
            ->orderBy('s.kategori_saran', 'asc')
            ->get([
                's.id_saran',
                's.kategori_saran',
                's.saran',
            ]);

        $saranSelectedIds = $saranDetail->pluck('id_saran')->all();
        $dokter = DB::table('dokter')->where('status', 'aktif')->orderBy('nama')->get();
        $pemeriksa = DB::table('pemeriksa')->where('status', 'aktif')->orderBy('id_pemeriksa')->get();

        return view('adminpoli.pemeriksaan.edit', compact(
            'pendaftaran','hasil','resep','detailResep','obat','saran','penyakit',
            'saranDetail','saranSelectedIds',
            'penyakitDetail','dokter','pemeriksa'
        ));
    }

    /**
     * UPDATE hasil pemeriksaan + detail resep
     * (pakai transaksi biar aman)
     */
    public function update(Request $request, $pendaftaranId)
{
    $validated = $request->validate([
        'tanggal'       => 'required|date',
        'created_at' => 'nullable|date',
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

        // penyakit
        'penyakit_id'     => 'nullable|array',
        'penyakit_id.*'   => ['nullable', Rule::exists('diagnosa', 'id_diagnosa')->where('is_active', 1)],
        'id_nb'           => 'nullable|array',
        'id_nb.*'         => 'nullable|string',

        // saran
        'id_saran'       => 'nullable|array',
        'id_saran.*'     => ['nullable', Rule::exists('saran', 'id_saran')->where('is_active', 1)],

        // petugas
        'petugas_after_obat' => 'nullable|string',

        'jenis_pemeriksaan' => ['required','in:cek_kesehatan,periksa,konsultasi'],

        //tindakan / saran tambahan
        'tindakan_saran' => 'nullable|string',
    ]);

    return DB::transaction(function () use ($validated, $pendaftaranId) {
    
        $hasil = Pemeriksaan::where('id_pendaftaran', $pendaftaranId)->firstOrFail();
        $pendaftaran = Pendaftaran::where('id_pendaftaran', $pendaftaranId)->firstOrFail();
        
// ✅ SIMPAN JENIS PEMERIKSAAN DARI FORM
$pendaftaran->jenis_pemeriksaan = $validated['jenis_pemeriksaan'];
$pendaftaran->tanggal = $validated['tanggal'];
        // =========================
        // UPDATE HASIL PEMERIKSAAN
        // =========================
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

             'tindakan_saran' => $validated['tindakan_saran'] ?? null,
             

            'created_at' => $validated['created_at'] ?? $hasil->created_at,
        ]);

        // =========================
        // UPDATE PENYAKIT
        // =========================
        $penyakitIds = array_values(array_filter($validated['penyakit_id'] ?? []));
        $idNbs       = $validated['id_nb'] ?? [];

        foreach ($penyakitIds as $i => $idDiag) {
            $idNb = $idNbs[$i] ?? null;
            if (!$idNb || trim((string)$idNb) === '') {
                return back()->withInput()->withErrors([
                    "id_nb.$i" => "ID NB wajib diisi untuk penyakit yang dipilih."
                ]);
            }
        }

        DB::table('detail_pemeriksaan_penyakit')
            ->where('id_pemeriksaan', $hasil->id_pemeriksaan)
            ->delete();

        if (!empty($penyakitIds)) {
            $rows = [];
            foreach ($penyakitIds as $i => $idDiag) {
                $rows[] = [
                    'id_pemeriksaan' => $hasil->id_pemeriksaan,
                    'id_diagnosa'    => $idDiag,
                    'id_nb'          => trim((string)($idNbs[$i] ?? '')),
                ];
            }
            DB::table('detail_pemeriksaan_penyakit')->insert($rows);
        }

        // =========================
        // UPDATE SARAN
        // =========================
        $idSaran = $validated['id_saran'] ?? [];
        $idSaran = array_values(array_unique(array_filter($idSaran)));

        DB::table('detail_pemeriksaan_saran')
            ->where('id_pemeriksaan', $hasil->id_pemeriksaan)
            ->delete();

        if (!empty($idSaran)) {
            $rows = [];
            foreach ($idSaran as $sid) {
                $rows[] = [
                    'id_pemeriksaan' => $hasil->id_pemeriksaan,
                    'id_saran'       => $sid,
                ];
            }
            DB::table('detail_pemeriksaan_saran')->insert($rows);
        }

        // =========================
        // RESEP & DETAIL_RESEP
        // =========================
        $obatIds = $validated['obat_id'] ?? [];
        $jumlahs = $validated['jumlah'] ?? [];
        $satuans = $validated['satuan'] ?? [];
        $hargas  = $validated['harga_satuan'] ?? [];

        foreach ($obatIds as $i => $idObat) {
            if (!$idObat) continue;
            if (!isset($satuans[$i]) || trim((string)$satuans[$i]) === '') {
                return back()->withInput()->withErrors([
                    "satuan.$i" => "Satuan wajib diisi jika obat dipilih."
                ]);
            }
        }

        $detailToInsert = [];
        $totalTagihan = 0;

        for ($i = 0; $i < count($obatIds); $i++) {
            $obatId = $obatIds[$i] ?? null;
            if (!$obatId) continue;

            $qty = (int)($jumlahs[$i] ?? 1);
            if ($qty <= 0) $qty = 1;

            $harga  = (int)($hargas[$i] ?? 0);
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

        $resep = Resep::where('id_pemeriksaan', $hasil->id_pemeriksaan)->first();

        // =========================
        // PETUGAS (INI FIX UTAMA)
        // =========================
       $petugasAfter = (string)($validated['petugas_after_obat'] ?? '');
$adaObat = count($detailToInsert) > 0;

$tipeAfter = null;
$idAfter = null;

if ($petugasAfter && str_contains($petugasAfter, ':')) {
    [$tipeAfter, $idAfter] = explode(':', $petugasAfter, 2);
    $tipeAfter = trim((string)$tipeAfter);
    $idAfter   = trim((string)$idAfter);

    if ($idAfter === '') { // kosong -> anggap tidak ada pilihan
        $tipeAfter = null;
        $idAfter = null;
    }
} else {
    $tipeAfter = null;
    $idAfter = null;
}

// kalau user memilih petugas -> simpan ke model (tanpa cast int)
if ($tipeAfter === 'dokter' && $idAfter !== null) {
    $exists = DB::table('dokter')->where('id_dokter', $idAfter)->exists();
    if (!$exists) {
        return back()->withInput()->withErrors([
            'petugas_after_obat' => 'Pilih dokter yang valid.'
        ]);
    }
    $pendaftaran->id_dokter = $idAfter;
    $pendaftaran->id_pemeriksa = null;

} elseif ($tipeAfter === 'pemeriksa' && $idAfter !== null) {
    $exists = DB::table('pemeriksa')->where('id_pemeriksa', $idAfter)->exists();
    if (!$exists) {
        return back()->withInput()->withErrors([
            'petugas_after_obat' => 'Pilih pemeriksa yang valid.'
        ]);
    }
    $pendaftaran->id_pemeriksa = $idAfter;
    $pendaftaran->id_dokter = null;
}

// Wajib dokter jika: non-poliklinik + awal cek_kesehatan + ada obat
if ($adaObat && $pendaftaran->tipe_pasien !== 'poliklinik' && $pendaftaran->jenis_pemeriksaan === 'cek_kesehatan') {
    // RULE: kalau ada obat dan NON-poliklinik → wajib dokter
if ($adaObat && $pendaftaran->tipe_pasien !== 'poliklinik') {

    if (!($tipeAfter === 'dokter' && $idAfter)) {
        return back()->withInput()->withErrors([
            'petugas_after_obat' => 'Jika ada obat, wajib pilih dokter.'
        ]);
    }

    // ❌ jangan override jenis lagi
    // $pendaftaran->jenis_pemeriksaan = 'periksa';
}

    if (!($tipeAfter === 'dokter' && $idAfter !== null)) {
        return back()->withInput()->withErrors([
            'petugas_after_obat' => 'Pilih dokter yang valid.'
        ]);
    }
}

// Poliklinik: tetap cek_kesehatan, jika tidak memilih petugas -> default pemeriksa
if ($adaObat && $pendaftaran->tipe_pasien === 'poliklinik') {
    $pendaftaran->jenis_pemeriksaan = 'cek_kesehatan';

    if (empty($petugasAfter)) {
        $firstPemeriksaId = DB::table('pemeriksa')
            ->where('status', 'aktif')
            ->orderBy('id_pemeriksa', 'asc')
            ->value('id_pemeriksa');

        $pendaftaran->id_dokter = null;
        $pendaftaran->id_pemeriksa = $firstPemeriksaId ?: $pendaftaran->id_pemeriksa;
        $pendaftaran->jenis_pemeriksaan = $request->jenis_pemeriksaan;
    }
}

        // SAVE PENDAFTARAN SEKALI SAJA
        $pendaftaran->save();

        // =========================
        // Kalau tidak ada obat -> hapus resep & selesai
        // =========================
        if (count($detailToInsert) === 0) {
            if ($resep) {
                DetailResep::where('id_resep', $resep->id_resep)->delete();
                $resep->delete();
            }

            return redirect()
                ->route('adminpoli.pemeriksaan.index')
                ->with('success', 'Hasil pemeriksaan berhasil diupdate (tanpa resep).');
        }

        // =========================
        // CREATE / UPDATE RESEP
        // =========================
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
