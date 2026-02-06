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
        $query = DB::table('pegawai')->where('is_active', 1);

        if ($q === '') {
            $rows = $query->select('nip', 'nama_pegawai', 'bagian', 'tgl_lahir')
                ->orderBy('nip')
                ->limit(5)
                ->get();

            return response()->json(['ok' => true, 'data' => $rows]);
        }

        $rows = $query->select('nip', 'nama_pegawai', 'bagian', 'tgl_lahir')
            ->where(function ($w) use ($q) {
                $w->where('nip', 'like', "%{$q}%")
                ->orWhere('nama_pegawai', 'like', "%{$q}%");
            })
            ->orderBy('nip')
            ->limit(5)
            ->get();


        return response()->json(['ok' => true, 'data' => $rows]);
    }

    public function getPegawaiByNip($nip)
    {
        $pegawai = DB::table('pegawai')
            ->where('nip', $nip)
            ->where('is_active', 1)
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
        $pegawai = DB::table('pegawai')
            ->where('nip', $nip)
            ->where('is_active', 1)
            ->first();
        if (!$pegawai) {
            return response()->json(['ok' => false, 'message' => 'NIP tidak ditemukan'], 404);
        }

        $keluarga = DB::table('keluarga')
            ->where('nip', $nip)
            ->where('is_active', 1)
            ->orderByRaw("CASE WHEN hubungan_keluarga = 'pasangan' THEN 0 ELSE 1 END")
            ->orderBy('urutan_anak')
            ->get();

        $data = $keluarga->map(function ($r) {
            $umur = Carbon::parse($r->tgl_lahir)->age;

            return [
                'id_keluarga' => $r->id_keluarga,
                'nama' => $r->nama_keluarga,
                'hubungan_keluarga' => $r->hubungan_keluarga,
                'tgl_lahir' => $r->tgl_lahir,
                'umur' => $umur,
            ];
        })->values();

        return response()->json(['ok' => true, 'data' => $data]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
            'nip' => ['required', 'string'],

            'nama_pegawai' => ['nullable', 'string', 'max:255'],
            'bagian' => ['nullable', 'string', 'max:255'],

            'tipe_pasien' => ['required', 'in:pegawai,keluarga,pensiunan,unit lain,ojt,poliklinik'],
            'nama_pasien' => ['required', 'string', 'max:255'],
            'hub_kel' => ['required', 'in:YBS,Pasangan,Anak'],
            'tgl_lahir' => ['nullable', 'date'],

            'id_keluarga' => ['nullable','string','max:32'],
            'jenis_pemeriksaan' => ['required', 'in:cek_kesehatan,periksa,konsultasi'],
            'petugas' => ['required', 'string'],

            'keluhan' => ['nullable', 'string'],
        ]);

        $isPoli = ($validated['nip'] === '001');

        if ($isPoli) {
            $validated['nip'] = '001';

            $validated['nama_pegawai'] = '-';
            $validated['bagian'] = '-';
            $validated['nama_pasien'] = '-';
            $validated['tipe_pasien'] = 'poliklinik';

            $validated['hub_kel'] = 'YBS';
            $validated['id_keluarga'] = null;
            $validated['tgl_lahir'] = null;
        }


        $idPemeriksaFirst = 'PMR001';
        if ($validated['jenis_pemeriksaan'] === 'cek_kesehatan') {
            $validated['petugas'] = 'pemeriksa:' . $idPemeriksaFirst;
        }

        $pegawai = DB::table('pegawai')->where('is_active', 1)->where('nip', $validated['nip'])->first();
        if (!$pegawai) {
            return back()->withInput()->withErrors(['nip' => 'NIP tidak ditemukan di data pegawai.']);
        }

        $idKeluarga = null;
        
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
                ->where('is_active', 1)
                ->first();

            if (!$kel) {
                return back()->withInput()->withErrors(['id_keluarga' => 'Anggota keluarga tidak valid.']);
            }

            $expected = ($validated['hub_kel'] === 'Pasangan') ? 'pasangan' : 'anak';
            if ($kel->hubungan_keluarga !== $expected) {
                return back()->withInput()->withErrors(['hub_kel' => 'Hubungan keluarga tidak sesuai data keluarga.']);
            }

            $idKeluarga = $kel->id_keluarga;
        }

        // 3) Pensiunan: boleh YBS atau keluarga (Pasangan/Anak)
        if ($validated['tipe_pasien'] === 'pensiunan') {
            if ($validated['hub_kel'] === 'YBS') {
                $idKeluarga = null;
            } else {
                if (empty($validated['id_keluarga'])) {
                    return back()->withInput()->withErrors(['id_keluarga' => 'Pilih anggota keluarga.']);
                }

                $kel = DB::table('keluarga')
                    ->where('id_keluarga', $validated['id_keluarga'])
                    ->where('nip', $pegawai->nip)
                    ->where('is_active', 1)
                    ->first();

                if (!$kel) {
                    return back()->withInput()->withErrors(['id_keluarga' => 'Anggota keluarga tidak valid.']);
                }

                $expected = ($validated['hub_kel'] === 'Pasangan') ? 'pasangan' : 'anak';
                if ($kel->hubungan_keluarga !== $expected) {
                    return back()->withInput()->withErrors(['hub_kel' => 'Hubungan keluarga tidak sesuai data keluarga.']);
                }

                $idKeluarga = $kel->id_keluarga;
            }
        }

        if (in_array($validated['tipe_pasien'], ['pegawai','unit lain','ojt','poliklinik'], true)) {
            if ($validated['hub_kel'] !== 'YBS') {
                return back()->withInput()->withErrors(['hub_kel' => 'Tipe ini harus YBS.']);
            }
            $idKeluarga = null;
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