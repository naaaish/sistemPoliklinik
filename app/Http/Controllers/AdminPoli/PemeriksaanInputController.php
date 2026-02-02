<?php

namespace App\Http\Controllers\AdminPoli;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
USE Illuminate\Validation\Rule;
use App\Models\Pendaftaran;
use App\Models\Pemeriksaan;
use App\Models\Obat;
use App\Models\Saran;
use App\Models\Diagnosa;
use App\Models\DetailResep;
use App\Models\Resep;
use Illuminate\Validation\ValidationException;

class PemeriksaanInputController extends Controller
{
    public function index()
    {
        $pendaftaran = Pendaftaran::orderBy('tanggal', 'desc')->get();
        return view('adminpoli.pemeriksaan.index', compact('pendaftaran'));
    }

    public function create($pendaftaranId)
    {
        $pendaftaran = Pendaftaran::findOrFail($pendaftaranId);

        $penyakit = DB::table('diagnosa')
            ->select(
                'id_diagnosa',
                'diagnosa',
            )
            ->orderBy('diagnosa')
            ->get();

        $obat  = Obat::where('is_active', 1)
            ->orderBy('nama_obat', 'asc')
            ->get();

        $saran = Saran::where('is_active', 1)
            ->orderBy('saran', 'asc')
            ->get();

        $dokter = DB::table('dokter')->where('status', 'aktif')->orderBy('nama')->get();
        $pemeriksa = DB::table('pemeriksa')->where('status', 'aktif')->orderBy('id_pemeriksa')->get();

        return view('adminpoli.pemeriksaan.create', compact(
            'pendaftaran',
            'obat',
            'saran',
            'penyakit',
            'dokter',
            'pemeriksa'
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
            'id_nb'           => 'nullable|array',
            'id_nb.*'         => 'nullable|string',

            // resep
            'obat_id'        => 'nullable|array',
            'obat_id.*'      => ['nullable', Rule::exists('obat', 'id_obat')->where('is_active', 1)],
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
        $pendaftaran = Pendaftaran::findOrFail($pendaftaranId);

        $obatIdsRaw = $validated['obat_id'] ?? [];
        $adaObatInput = collect($obatIdsRaw)->filter(fn($x) => $x !== null && $x !== '' && $x !== '0')->isNotEmpty();

        $dokterIdAfter = null;

        if ($adaObatInput && $pendaftaran->tipe_pasien !== 'poliklinik') {

            $petugasAfter = (string) $request->input('petugas_after_obat', '');

            // ✅ FALLBACK: kalau field ga kepost tapi pendaftaran sudah ada dokter, pakai itu
            if (($petugasAfter === '' || !str_contains($petugasAfter, ':')) && !empty($pendaftaran->id_dokter)) {
                $petugasAfter = 'dokter:' . $pendaftaran->id_dokter;
            }

            if (!$petugasAfter || !str_contains($petugasAfter, ':')) {
                throw ValidationException::withMessages([
                    'petugas_after_obat' => 'Pilih dokter (wajib) jika ada obat.'
                ]);
            }

            [$tipeAfter, $idAfter] = explode(':', $petugasAfter, 2);

            if ($tipeAfter !== 'dokter') {
                throw ValidationException::withMessages([
                    'petugas_after_obat' => 'Jika ada obat, petugas harus Dokter.'
                ]);
            }

            $dokterIdAfter = $idAfter;
        }

        return DB::transaction(function () use ($validated, $pendaftaranId, $dokterIdAfter) {
            $idPemeriksaan = $this->generateIDPemeriksaan();

            $penyakitIds = array_values(array_filter($validated['penyakit_id'] ?? []));
            $idNbs       = $validated['id_nb'] ?? [];

            foreach ($penyakitIds as $i => $idDiag) {
                $idNb = $idNbs[$i] ?? null;
                if (!$idNb || trim((string)$idNb) === '') {
                    return back()->withInput()->withErrors(["id_nb.$i" => "ID NB wajib diisi untuk penyakit yang dipilih."]);
                }
            }

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
                $rows = [];
                foreach ($penyakitIds as $i => $idDiag) {
                    $rows[] = [
                        'id_pemeriksaan' => $pemeriksaan->id_pemeriksaan,
                        'id_diagnosa'    => $idDiag,
                        'id_nb'          => trim((string)($idNbs[$i] ?? '')),
                    ];
                }
                DB::table('detail_pemeriksaan_penyakit')->insert($rows);
            }

            $autoSaranIds = $this->generateSaranFromVitals($validated);

            // optional: filter biar cuma yang ada & aktif
            $autoSaranIds = Saran::whereIn('id_saran', $autoSaranIds)
                ->where('is_active', 1)
                ->pluck('id_saran')
                ->values()
                ->all();

            if (count($autoSaranIds) > 0) {
                $rowsSaran = array_map(fn($id) => [
                    'id_pemeriksaan' => $pemeriksaan->id_pemeriksaan,
                    'id_saran'       => $id,
                ], $autoSaranIds);

                DB::table('detail_pemeriksaan_saran')->insert($rowsSaran);
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

            $adaObat = count($detailRows) > 0;
            $pendaftaran = Pendaftaran::findOrFail($pendaftaranId);

            if ($adaObat) {
                $pendaftaran = Pendaftaran::findOrFail($pendaftaranId);

                // ====== PENGECUALIAN: POLIKLINIK ======
                if ($pendaftaran->tipe_pasien === 'poliklinik') {

                    // poliklinik boleh ada obat, tapi jenis tetap cek_kesehatan
                    $pendaftaran->jenis_pemeriksaan = 'cek_kesehatan';

                    // petugas tetap pemeriksa (ambil id paling awal dari pemeriksa aktif)
                    $firstPemeriksaId = DB::table('pemeriksa')
                        ->where('status', 'aktif')
                        ->orderBy('id_pemeriksa', 'asc')
                        ->value('id_pemeriksa');

                    $pendaftaran->id_dokter = null;
                    $pendaftaran->id_pemeriksa = $firstPemeriksaId ?: $pendaftaran->id_pemeriksa;

                    $pendaftaran->save();

                } else {

                    // ====== NON-POLIKLINIK ======
                    // Kalau awalnya cek_kesehatan lalu ada obat -> jadi periksa
                    if ($pendaftaran->jenis_pemeriksaan === 'cek_kesehatan') {
                        $pendaftaran->jenis_pemeriksaan = 'periksa';
                    }
                  
                    $pendaftaran->id_dokter = $dokterIdAfter;
                    $pendaftaran->id_pemeriksa = null;
                    $pendaftaran->save();
                }
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

    private function generateIDPemeriksaan() {
        $ids = DB::table('pemeriksaan')
            ->pluck('id_pemeriksaan'); 
        $max = 0; 
        foreach ($ids as $id) 
        {
            if (preg_match('/(\d+)$/', $id, $m)){ 
                $num = (int) $m[1]; 
                if ($num > $max) $max = $num; 
            } 
        } 
        $next = $max + 1;
        return 'PMX-00' . $next;
    }

    private function generateSaranFromVitals(array $v): array
    {
        // Ambil nilai dari request/validated
        $sistol  = $v['sistol'] ?? null;
        $diastol = $v['diastol'] ?? null;

        $gdp  = $v['gula_puasa'] ?? null;
        $gdpp = $v['gula_2jam_pp'] ?? null;
        $gds  = $v['gula_sewaktu'] ?? null;

        $asam = $v['asam_urat'] ?? null;
        $chol = $v['cholesterol'] ?? null;
        $tg   = $v['trigliseride'] ?? null;

        // kalau semua kosong/null/0 → TIDAK ADA SARAN
        $fields = [$sistol, $diastol, $gdp, $gdpp, $gds, $asam, $chol, $tg];

        $hasAny = false;
        foreach ($fields as $x) {
            // anggap 0 itu "kosong" (umumnya input lab 0 = tidak diisi)
            if ($x !== null && $x !== '' && is_numeric($x) && (float)$x > 0) {
                $hasAny = true;
                break;
            }
        }
        if (!$hasAny) return [];

        $hasil = [];

        // ===== TENSI =====
        if ((is_numeric($sistol) && $sistol > 140) || (is_numeric($diastol) && $diastol > 90)) {
            $hasil[] = 'SRN-TENS-01';
        }
        if (is_numeric($sistol) && $sistol > 0 && $sistol < 90) {
            $hasil[] = 'SRN-TENS-02';
        }

        // ===== GULA DARAH =====
        $gulaHigh = (is_numeric($gdp)  && $gdp  > 100)
            || (is_numeric($gdpp) && $gdpp > 140)
            || (is_numeric($gds)  && $gds  > 200);

        $gulaLow = (is_numeric($gdp)  && $gdp  > 0 && $gdp  < 70)
            || (is_numeric($gdpp) && $gdpp > 0 && $gdpp < 70)
            || (is_numeric($gds)  && $gds  > 0 && $gds  < 70);

        if ($gulaHigh) $hasil[] = 'SRN-GULA-01';
        if ($gulaLow)  $hasil[] = 'SRN-GULA-02';

        // ===== ASAM URAT =====
        if (is_numeric($asam) && $asam > 7.0) {
            $hasil[] = 'SRN-ASAM-01';
        }

        // ===== KOLESTEROL & TRIGLISERIDA =====
        if (is_numeric($chol) && $chol >= 200) {
            $hasil[] = 'SRN-LEMK-01';
        }
        if (is_numeric($tg) && $tg >= 150) {
            $hasil[] = 'SRN-LEMK-02';
        }

        // Kalau ada data tapi semua normal → baru normal
        if (empty($hasil)) {
            $hasil[] = 'SRN-NORM-01';
        }

        return array_values(array_unique($hasil));
    }
}