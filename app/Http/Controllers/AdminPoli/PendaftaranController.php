<?php

namespace App\Http\Controllers\AdminPoli;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            ->select('nip', 'nama_pegawai', 'bidang', 'tgl_lahir')
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
            'nip' => ['required', 'string'],

            'nama_pegawai' => ['required', 'string', 'max:255'],
            'bidang' => ['required', 'string', 'max:255'],
            'tgl_lahir' => ['required', 'date'],

            'nama_pasien' => ['required', 'string', 'max:255'],
            'tipe_pasien' => ['required', 'in:pegawai,keluarga,pensiunan'],
            'hub_kel' => ['required', 'in:YBS,Pasangan,Anak'],

            'jenis_pemeriksaan' => ['required', 'in:cek_kesehatan,berobat'],
            'petugas' => ['required', 'string'],

            'keluhan' => ['nullable', 'string'],
        ]);

        // cek pegawai ada
        $pegawai = DB::table('pegawai')->where('nip', $validated['nip'])->first();
        if (!$pegawai) {
            return back()->withInput()->withErrors(['nip' => 'NIP tidak ditemukan di data pegawai.']);
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