<?php

namespace App\Http\Controllers\AdminPoli;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PendaftaranController extends Controller
{
    public function create()
    {
        $dokter = DB::table('dokter')
            ->where('status', 'aktif')
            ->orderBy('nama')
            ->get();

        $pemeriksa = DB::table('pemeriksa')
            ->where('status', 'aktif')
            ->orderBy('id_pemeriksa')
            ->get();

        $defaultPemeriksa = $pemeriksa->first();
        $defaultPemeriksaId = $defaultPemeriksa?->id_pemeriksa;

        return view('adminpoli.pendaftaran.create', compact('dokter', 'pemeriksa', 'defaultPemeriksaId'));
    }

    public function searchPegawai(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $query = DB::table('pegawai');

        if ($q === '') {
            $rows = $query->select('nip', 'nama_pegawai', 'bagian', 'tgl_lahir')
                ->orderBy('nip')
                ->limit(5)
                ->get();

            return response()->json(['ok' => true, 'data' => $rows]);
        }

        $rows = $query->select('nip', 'nama_pegawai', 'bagian', 'tgl_lahir')
            ->where('nip', 'like', "%{$q}%")
            ->orWhere('nama_pegawai', 'like', "%{$q}%")
            ->limit(5)
            ->get();

        return response()->json(['ok' => true, 'data' => $rows]);
    }

    public function getPegawaiByNip($nip)
    {
        $pegawai = DB::table('pegawai')
            ->where('nip', $nip)
            ->select('nip', 'nama_pegawai', 'bagian', 'tgl_lahir')
            ->first();

        if (!$pegawai) {
            return response()->json([
                'ok' => false,
                'message' => 'NIP tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'ok' => true,
            'data' => $pegawai
        ]);
    }

    public function getKeluargaByNip($nip)
    {
        $pegawai = DB::table('pegawai')->where('nip', $nip)->first();
        if (!$pegawai) {
            return response()->json(['ok' => false, 'message' => 'NIP tidak ditemukan'], 404);
        }

        $keluarga = DB::table('keluarga')
            ->where('nip', $nip)
            ->orderByRaw("CASE WHEN hubungan_keluarga = 'pasangan' THEN 0 ELSE 1 END")
            ->orderBy('urutan_anak')
            ->get();

        // hitung umur + covered anak max 3 (umur <= 23)
        $coveredChildIds = [];
        foreach ($keluarga->where('hubungan_keluarga', 'anak') as $row) {
            $umur = Carbon::parse($row->tgl_lahir)->age;
            if ($umur <= 23 && count($coveredChildIds) < 3) {
                $coveredChildIds[] = $row->id_keluarga;
            }
        }

        $data = $keluarga->map(function ($r) use ($coveredChildIds) {
            $umur = Carbon::parse($r->tgl_lahir)->age;

            $covered = true;
            if ($r->hubungan_keluarga === 'anak') {
                $covered = in_array($r->id_keluarga, $coveredChildIds, true);
            }

            return [
                'id_keluarga' => $r->id_keluarga,
                'nama' => $r->nama_keluarga,
                'hubungan_keluarga' => $r->hubungan_keluarga,
                'tgl_lahir' => $r->tgl_lahir,
                'umur' => $umur,
                'covered' => $covered,
            ];
        })->values();

        return response()->json(['ok' => true, 'data' => $data]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
            'tipe_pasien' => ['required', 'in:pegawai,keluarga,pensiunan,unit_lain,ojt,poliklinik'],

            // hanya dipakai saat pegawai/keluarga/pensiunan
            'nip' => ['nullable', 'string', 'max:32'],

            // dipakai semua tipe (ojt/unit_lain wajib, poliklinik auto '-')
            'nama_pasien' => ['nullable', 'string', 'max:255'],

            // hanya untuk keluarga/pensiunan
            'hub_kel' => ['nullable', 'in:YBS,Pasangan,Anak'],
            'id_keluarga' => ['nullable', 'string', 'max:32'],

            'jenis_pemeriksaan' => ['required', 'in:cek_kesehatan,periksa,konsultasi'],
            'petugas' => ['required', 'string'], // dokter:ID atau pemeriksa:ID
            'keluhan' => ['nullable', 'string'],
        ]);

        $tipe = $validated['tipe_pasien'];

        // ========== Parse petugas ==========
        $petugas = explode(':', (string) $validated['petugas']);
        if (count($petugas) !== 2) {
            return back()->withInput()->withErrors(['petugas' => 'Format dokter/pemeriksa tidak valid.']);
        }

        [$petugasType, $petugasId] = $petugas;
        $petugasId = (string) $petugasId;

        $idDokter = null;
        $idPemeriksa = null;
        if ($petugasType === 'dokter') $idDokter = $petugasId;
        if ($petugasType === 'pemeriksa') $idPemeriksa = $petugasId;

        // poliklinik harus pemeriksa
        if ($tipe === 'poliklinik' && $petugasType !== 'pemeriksa') {
            return back()->withInput()->withErrors(['petugas' => 'Poliklinik harus memilih Pemeriksa.']);
        }

        // ========== Normalisasi field per tipe ==========
        $nipFinal = null;
        $idKeluarga = null;
        $namaFinal = $validated['nama_pasien'] ?? null;

        // --- POLIKLINIK ---
        if ($tipe === 'poliklinik') {
            $nipFinal = null;       // poliklinik bukan pegawai
            $idKeluarga = null;
            $namaFinal = '-';
        }

        // --- OJT / UNIT LAIN ---
        if (in_array($tipe, ['ojt', 'unit_lain'], true)) {
            if (empty($namaFinal)) {
                return back()->withInput()->withErrors([
                    'nama_pasien' => 'Nama pasien wajib diisi untuk OJT / Unit Lain.'
                ]);
            }
            $nipFinal = null;
            $idKeluarga = null;
        }

        // --- PEGAWAI ---
        if ($tipe === 'pegawai') {
            if (empty($validated['nip'])) {
                return back()->withInput()->withErrors(['nip' => 'NIP wajib diisi untuk Pegawai.']);
            }

            $pegawai = DB::table('pegawai')->where('nip', $validated['nip'])->first();
            if (!$pegawai) {
                return back()->withInput()->withErrors(['nip' => 'NIP tidak ditemukan di data pegawai.']);
            }

            $nipFinal = $pegawai->nip;

            // kalau kamu mau “autoload nama pasien” dari pegawai:
            $namaFinal = $pegawai->nama_pegawai ?? $namaFinal;
        }

        // --- KELUARGA PEGAWAI ---
        if ($tipe === 'keluarga') {
            if (empty($validated['nip'])) {
                return back()->withInput()->withErrors(['nip' => 'Pilih YBS (NIP pegawai).']);
            }
            if (($validated['hub_kel'] ?? null) === 'YBS') {
                return back()->withInput()->withErrors(['hub_kel' => 'Tipe Keluarga tidak boleh YBS.']);
            }
            if (empty($validated['id_keluarga'])) {
                return back()->withInput()->withErrors(['id_keluarga' => 'Pilih anggota keluarga.']);
            }

            $pegawai = DB::table('pegawai')->where('nip', $validated['nip'])->first();
            if (!$pegawai) {
                return back()->withInput()->withErrors(['nip' => 'NIP YBS tidak ditemukan di data pegawai.']);
            }

            $kel = DB::table('keluarga')
                ->where('id_keluarga', $validated['id_keluarga'])
                ->where('nip', $pegawai->nip)
                ->first();

            if (!$kel) {
                return back()->withInput()->withErrors(['id_keluarga' => 'Anggota keluarga tidak valid.']);
            }

            $expected = ($validated['hub_kel'] === 'Pasangan') ? 'pasangan' : 'anak';
            if ($kel->hubungan_keluarga !== $expected) {
                return back()->withInput()->withErrors(['hub_kel' => 'Hubungan keluarga tidak sesuai data keluarga.']);
            }

            // aturan covered anak: max 3 anak umur <= 23
            if ($kel->hubungan_keluarga === 'anak') {
                $anak = DB::table('keluarga')
                    ->where('nip', $pegawai->nip)
                    ->where('hubungan_keluarga', 'anak')
                    ->orderBy('urutan_anak')
                    ->get();

                $coveredIds = [];
                foreach ($anak as $a) {
                    $umur = \Carbon\Carbon::parse($a->tgl_lahir)->age;
                    if ($umur <= 23 && count($coveredIds) < 3) $coveredIds[] = $a->id_keluarga;
                }

                if (!in_array($kel->id_keluarga, $coveredIds, true)) {
                    return back()->withInput()->withErrors([
                        'id_keluarga' => 'Anak ini tidak termasuk tanggungan (max 3 anak umur <= 23).'
                    ]);
                }
            }

            $nipFinal = $pegawai->nip;
            $idKeluarga = $kel->id_keluarga;

            // autoload nama pasien keluarga
            $namaFinal = $kel->nama_keluarga ?? $namaFinal;
        }

        // --- PENSIUNAN ---
        // (asumsi: pensiunan masih pakai tabel pegawai juga, hanya beda tipe_pasien)
        if ($tipe === 'pensiunan') {
            if (empty($validated['nip'])) {
                return back()->withInput()->withErrors(['nip' => 'Pilih YBS (NIP pensiunan).']);
            }

            $pegawai = DB::table('pegawai')->where('nip', $validated['nip'])->first();
            if (!$pegawai) {
                return back()->withInput()->withErrors(['nip' => 'NIP pensiunan tidak ditemukan.']);
            }

            $nipFinal = $pegawai->nip;

            $hub = $validated['hub_kel'] ?? 'YBS';
            if ($hub === 'YBS') {
                $idKeluarga = null;
                $namaFinal = $pegawai->nama_pegawai ?? $namaFinal;
            } else {
                if (empty($validated['id_keluarga'])) {
                    return back()->withInput()->withErrors(['id_keluarga' => 'Pilih anggota keluarga.']);
                }

                $kel = DB::table('keluarga')
                    ->where('id_keluarga', $validated['id_keluarga'])
                    ->where('nip', $pegawai->nip)
                    ->first();

                if (!$kel) {
                    return back()->withInput()->withErrors(['id_keluarga' => 'Anggota keluarga tidak valid.']);
                }

                $expected = ($hub === 'Pasangan') ? 'pasangan' : 'anak';
                if ($kel->hubungan_keluarga !== $expected) {
                    return back()->withInput()->withErrors(['hub_kel' => 'Hubungan keluarga tidak sesuai data keluarga.']);
                }

                if ($kel->hubungan_keluarga === 'anak') {
                    $anak = DB::table('keluarga')
                        ->where('nip', $pegawai->nip)
                        ->where('hubungan_keluarga', 'anak')
                        ->orderBy('urutan_anak')
                        ->get();

                    $coveredIds = [];
                    foreach ($anak as $a) {
                        $umur = \Carbon\Carbon::parse($a->tgl_lahir)->age;
                        if ($umur <= 23 && count($coveredIds) < 3) $coveredIds[] = $a->id_keluarga;
                    }

                    if (!in_array($kel->id_keluarga, $coveredIds, true)) {
                        return back()->withInput()->withErrors([
                            'id_keluarga' => 'Anak ini tidak termasuk tanggungan (max 3 anak umur <= 23).'
                        ]);
                    }
                }

                $idKeluarga = $kel->id_keluarga;
                $namaFinal = $kel->nama_keluarga ?? $namaFinal;
            }
        }

        // ========== Insert pendaftaran ==========
        DB::transaction(function () use ($validated, $nipFinal, $idKeluarga, $namaFinal, $idDokter, $idPemeriksa) {
            $idPendaftaran = $this->generateIdPendaftaran();

            DB::table('pendaftaran')->insert([
                'id_pendaftaran' => $idPendaftaran,
                'tanggal' => $validated['tanggal'],
                'jenis_pemeriksaan' => $validated['jenis_pemeriksaan'],
                'keluhan' => $validated['keluhan'] ?? null,

                'tipe_pasien' => $validated['tipe_pasien'],
                'nama_pasien' => $namaFinal,      // <<< ini yang bikin “autoload nama” bener
                'nip' => $nipFinal,               // nullable untuk ojt/unit_lain/poliklinik
                'id_keluarga' => $idKeluarga,

                'id_dokter' => $idDokter,
                'id_pemeriksa' => $idPemeriksa,

                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return redirect()->route('adminpoli.dashboard')
            ->with('success', 'Pendaftaran berhasil disimpan.');
    }

    private function generateIdPendaftaran() {
    $ids = DB::table('pendaftaran')
        ->pluck('id_pendaftaran'); 
    $max = 0; 
    foreach ($ids as $id) 
    {
        if (preg_match('/(\d+)$/', $id, $m)){ 
            $num = (int) $m[1]; 
            if ($num > $max) $max = $num; 
        } 
    } 
    $next = $max + 1;
    return 'REG-00' . $next;
    }

}