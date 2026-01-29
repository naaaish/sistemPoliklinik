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
        // dropdown dokter & pemeriksa
        $dokter = DB::table('dokter')->orderBy('nama')->get();
        $pemeriksa = DB::table('pemeriksa')->orderBy('nama_pemeriksa')->get();

        return view('adminpoli.pendaftaran.create', compact('dokter', 'pemeriksa'));
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
            'nip' => ['required', 'string'],

            'nama_pegawai' => ['required', 'string', 'max:255'],
            'bagian' => ['required', 'string', 'max:255'],

            'tipe_pasien' => ['required', 'in:pegawai,keluarga,pensiunan,unit_lain,ojt,poliklinik'],
            'nama_pasien' => ['required', 'string', 'max:255'],
            'hub_kel' => ['required', 'in:YBS,Pasangan,Anak'],
            'tgl_lahir' => ['nullable', 'date'],

            'id_keluarga' => ['nullable','string','max:32'],
            'jenis_pemeriksaan' => ['required', 'in:cek_kesehatan,periksa,konsultasi'],
            'petugas' => ['required', 'string'],

            'keluhan' => ['nullable', 'string'],
        ]);

        $isPoli = ($validated['tipe_pasien'] === 'poliklinik');

        if ($isPoli) {
            $validated['nip'] = '001';

            $validated['nama_pegawai'] = $validated['nama_pegawai'] ?: 'Poliklinik';
            $validated['bagian'] = $validated['bagian'] ?: 'Poliklinik';

            $validated['hub_kel'] = 'YBS';
            $validated['id_keluarga'] = null;
            $validated['tgl_lahir'] = $validated['tgl_lahir'] ?: null;
        }

        $BU_META_ID = 'PMR001';
        if ($validated['jenis_pemeriksaan'] === 'cek_kesehatan') {
            $validated['petugas'] = 'pemeriksa:' . $BU_META_ID;
        }

        $pegawai = DB::table('pegawai')->where('nip', $validated['nip'])->first();
        if (!$pegawai) {
            return back()->withInput()->withErrors(['nip' => 'NIP tidak ditemukan di data pegawai.']);
        }

        $idKeluarga = null;

        // 1) Pegawai: wajib YBS
        if ($validated['tipe_pasien'] === 'pegawai') {
            if ($validated['hub_kel'] !== 'YBS') {
                return back()->withInput()->withErrors(['hub_kel' => 'Tipe Pegawai harus YBS.']);
            }
            $idKeluarga = null;
        }

        // 2) Keluarga: wajib Pasangan/Anak + wajib id_keluarga
        if ($validated['tipe_pasien'] === 'keluarga') {
            if ($validated['hub_kel'] === 'YBS') {
                return back()->withInput()->withErrors(['hub_kel' => 'Tipe Keluarga tidak boleh YBS.']);
            }
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
                    if ($umur <= 23 && count($coveredIds) < 3) {
                        $coveredIds[] = $a->id_keluarga;
                    }
                }

                if (!in_array($kel->id_keluarga, $coveredIds, true)) {
                    return back()->withInput()->withErrors([
                        'id_keluarga' => 'Anak ini tidak termasuk tanggungan (max 3 anak umur <= 23).'
                    ]);
                }
            }

            $idKeluarga = $kel->id_keluarga;
        }

        // 3) Pensiunan: boleh YBS atau keluarga (Pasangan/Anak)
        if ($validated['tipe_pasien'] === 'pensiunan') {
            if ($validated['hub_kel'] === 'YBS') {
                $idKeluarga = null;
            } else {
                // sama kayak keluarga: wajib pilih id_keluarga + validasi covered anak
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

                $expected = ($validated['hub_kel'] === 'Pasangan') ? 'pasangan' : 'anak';
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
                        if ($umur <= 23 && count($coveredIds) < 3) {
                            $coveredIds[] = $a->id_keluarga;
                        }
                    }

                    if (!in_array($kel->id_keluarga, $coveredIds, true)) {
                        return back()->withInput()->withErrors([
                            'id_keluarga' => 'Anak ini tidak termasuk tanggungan (max 3 anak umur <= 23).'
                        ]);
                    }
                }

                $idKeluarga = $kel->id_keluarga;
            }
        }

        // ===== parse petugas: dokter:ID atau pemeriksa:ID =====
        $petugas = explode(':', $validated['petugas']);
        if (count($petugas) !== 2) {
            return back()->withInput()->withErrors(['petugas' => 'Format dokter/pemeriksa tidak valid.']);
        }

        [$petugasType, $petugasId] = $petugas;
        $petugasId = (string) $petugasId;

        $idDokter = null;
        $idPemeriksa = null;
        if ($petugasType === 'dokter') $idDokter = $petugasId;
        if ($petugasType === 'pemeriksa') $idPemeriksa = $petugasId;

        // ===== insert pendaftaran =====
        DB::transaction(function () use ($validated, $pegawai, $idKeluarga, $idDokter, $idPemeriksa) {
            $idPendaftaran = $this->generateIdPendaftaran();

            DB::table('pendaftaran')->insert([
                'id_pendaftaran' => $idPendaftaran,
                'tanggal' => $validated['tanggal'],
                'jenis_pemeriksaan' => $validated['jenis_pemeriksaan'],
                'keluhan' => $validated['keluhan'] ?? null,

                'tipe_pasien' => $validated['tipe_pasien'],
                'nip' => $pegawai->nip,
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

    public function autoloadPasien(Request $request)
    {
        $nip  = trim((string) $request->query('nip', ''));
        $tipe = trim((string) $request->query('tipe', ''));

        if ($nip === '' || $tipe === '') {
            return response()->json(['ok' => false, 'message' => 'nip/tipe wajib'], 422);
        }

        // 1) POLIKLINIK
        if ($tipe === 'poliklinik') {
            return response()->json([
                'ok' => true,
                'mode' => 'poliklinik',
                'pegawai' => [
                    'nip' => '001',
                    'nama_pegawai' => 'Poliklinik',
                    'bagian' => 'Poliklinik',
                    'tgl_lahir' => null,
                ],
                'pasien' => [
                    'nama_pasien' => 'Poliklinik',
                    'hub_kel' => 'YBS',
                    'id_keluarga' => null,
                    'tgl_lahir' => null,
                ],
                'keluarga_list' => [],
            ]);
        }

        // 2) selain poliklinik: ambil pegawai berdasar nip
        // (kalau nantinya Unit lain/OJT bukan dari tabel pegawai, bagian ini tinggal diganti switch ke tabel lain)
        $pegawai = DB::table('pegawai')
            ->where('nip', $nip)
            ->select('nip', 'nama_pegawai', 'bagian', 'tgl_lahir')
            ->first();

        if (!$pegawai) {
            return response()->json(['ok' => false, 'message' => 'NIP tidak ditemukan'], 404);
        }

        // default pasien = YBS
        $pasien = [
            'nama_pasien' => $pegawai->nama_pegawai,
            'hub_kel' => 'YBS',
            'id_keluarga' => null,
            'tgl_lahir' => $pegawai->tgl_lahir,
        ];

        $keluargaList = [];

        // 3) KELUARGA / PENSIUNAN (kalau pilih keluarga, frontend butuh list keluarga)
        if (in_array($tipe, ['keluarga','pensiunan'], true)) {
            $keluargaList = DB::table('keluarga')
                ->where('nip', $nip)
                ->orderByRaw("CASE WHEN hubungan_keluarga = 'pasangan' THEN 0 ELSE 1 END")
                ->orderBy('urutan_anak')
                ->get()
                ->map(function($r){
                    return [
                        'id_keluarga' => $r->id_keluarga,
                        'nama' => $r->nama_keluarga,
                        'hubungan_keluarga' => $r->hubungan_keluarga, // pasangan/anak
                        'tgl_lahir' => $r->tgl_lahir,
                    ];
                })
                ->values();
        }

        if (in_array($validated['tipe_pasien'], ['pegawai','unit_lain','ojt','poliklinik'], true)) {
    if ($validated['hub_kel'] !== 'YBS') {
        return back()->withInput()->withErrors(['hub_kel' => 'Tipe ini harus YBS.']);
    }
    $idKeluarga = null;
}


        return response()->json([
            'ok' => true,
            'mode' => $tipe,
            'pegawai' => $pegawai,
            'pasien' => $pasien,
            'keluarga_list' => $keluargaList,
        ]);
    }

    private function generateIdPendaftaran()
    {
        // ambil semua id, cari angka terbesar di belakang (aman meski formatnya REG-26011403 / REG0007 / dll)
        $ids = DB::table('pendaftaran')->pluck('id_pendaftaran');

        $max = 0;
        foreach ($ids as $id) {
            // ambil digit terakhir berurutan (contoh REG-26011403 => 26011403, REG0007 => 0007)
            if (preg_match('/(\d+)$/', $id, $m)) {
                $num = (int) $m[1];
                if ($num > $max) $max = $num;
            }
        }

        $next = $max + 1;

        // opsi B: format REG0001 dst
        // kalau next besar (misal 26011404), ini bakal jadi REG26011404 (tetap unik).
        // kalau kamu mau dipaksa 4 digit doang, bilang ya.
        if ($next <= 9999) {
            return 'REG' . str_pad($next, 4, '0', STR_PAD_LEFT);
        }

        return 'REG' . $next;
    }
}