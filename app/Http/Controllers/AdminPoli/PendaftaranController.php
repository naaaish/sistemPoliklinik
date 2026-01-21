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

            'tipe_pasien' => ['required', 'in:pegawai,keluarga,pensiunan'],
            'nama_pasien' => ['required', 'string', 'max:255'],
            'hub_kel' => ['required', 'in:YBS,Pasangan,Anak'],
            'tgl_lahir' => ['required', 'date'],

            'id_keluarga' => ['nullable','string','max:32'],
            'jenis_pemeriksaan' => ['required', 'in:cek_kesehatan,berobat'],
            'petugas' => ['required', 'string'],

            'keluhan' => ['nullable', 'string'],
        ]);

        // cek pegawai ada
        $pegawai = DB::table('pegawai')->where('nip', $validated['nip'])->first();
        if (!$pegawai) {
            return back()->withInput()->withErrors(['nip' => 'NIP tidak ditemukan di data pegawai.']);
        }

        $idKeluarga = null;
        // pegawai/pensiunan => YBS, no keluarga
        if (in_array($validated['tipe_pasien'], ['pegawai','pensiunan'], true)) {
            if ($validated['hub_kel'] !== 'YBS') {
                return back()->withInput()->withErrors(['hub_kel' => 'Pegawai/Pensiunan harus YBS.']);
            }
            $idKeluarga = null;
        } else {
            // keluarga
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

            // mapping UI hub_kel -> keluarga.hubungan_keluarga
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

        // parse petugas: dokter:ID atau pemeriksa:ID
        $petugas = explode(':', $validated['petugas']);
        if (count($petugas) !== 2) {
            return back()->withInput()->withErrors(['petugas' => 'Format dokter/pemeriksa tidak valid.']);
        }
        [$petugasType, $petugasId] = $petugas;
        $petugasId = (string) $petugasId;

        // [$tipe, $id] = explode(':', $request->petugas);

        $idDokter = null;
        $idPemeriksa = null;
        if ($petugasType === 'dokter') $idDokter = $petugasId;
        if ($petugasType === 'pemeriksa') $idPemeriksa = $petugasId;

        // insert pasien dan pendaftaran dalam transaksi
        DB::transaction(function () use ($validated, $pegawai, $idDokter, $idPemeriksa) {

            $idKeluarga = null;

            // ===== kalau pasien KELUARGA: upsert ke tabel keluarga =====
            if ($validated['tipe_pasien'] === 'keluarga') {

                if ($validated['hub_kel'] === 'YBS') {
                    // keluarga tapi YBS itu tidak make sense → paksa error biar ga nyimpen data salah
                    throw new \Exception('Hubungan keluarga tidak valid untuk tipe pasien keluarga.');
                }

                // tentukan kode id_keluarga: I/S untuk pasangan, A untuk anak
                // karena form kamu belum ada pilihan suami/istri, kita ambil dari jenis_kelamin pegawai:
                // pegawai L -> pasangan dianggap Istri (I)
                // pegawai P -> pasangan dianggap Suami (S)
                $kode = 'A';
                $hubungan = 'anak';

                if ($validated['hub_kel'] === 'Pasangan') {
                    $hubungan = 'pasangan';
                    $kode = ($pegawai->jenis_kelamin === 'P') ? 'S' : 'I';
                }

                // cari angka urutan berikutnya (anak: 1..n, pasangan: 1)
                $nextAngka = 1;

                if ($hubungan === 'anak') {
                    // ambil max urutan anak per nip
                    $maxUrutan = DB::table('keluarga')
                        ->where('nip', $pegawai->nip)
                        ->where('hubungan_keluarga', 'anak')
                        ->max('urutan_anak');

                    $nextAngka = ((int) $maxUrutan) + 1;
                }

                // format id_keluarga sesuai request: nip-I/A/S-angka
                $idKeluarga = $pegawai->nip . '-' . $kode . '-' . $nextAngka;

                // upsert keluarga (kalau sudah ada id tsb, update datanya)
                DB::table('keluarga')->updateOrInsert(
                    ['id_keluarga' => $idKeluarga],
                    [
                        'nip' => $pegawai->nip,
                        'hubungan_keluarga' => $hubungan,
                        'urutan_anak' => $hubungan === 'anak' ? $nextAngka : null,
                        'nama_keluarga' => $validated['nama_pasien'],
                        'tgl_lahir' => $validated['tgl_lahir'], // NOTE: kalau ini sebenarnya tgl lahir keluarga, ubah input form ya
                        'jenis_kelamin' => ($kode === 'I') ? 'P' : (($kode === 'S') ? 'L' : 'L'),
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }

            // ===== insert pendaftaran =====
            $idPendaftaran = $this->generateIdPendaftaran();

            DB::table('pendaftaran')->insert([
                'id_pendaftaran' => $idPendaftaran,
                'tanggal' => $validated['tanggal'],
                'jenis_pemeriksaan' => $validated['jenis_pemeriksaan'],
                'keluhan' => $validated['keluhan'] ?? null,

                // NEW schema (tanpa pasien)
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