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
        $petugasId = (int) $petugasId;

        [$tipe, $id] = explode(':', $request->petugas);

        $idDokter = null;
        $idPemeriksa = null;
        if ($petugasType === 'dokter') $idDokter = $id;
        if ($petugasType === 'pemeriksa') $idPemeriksa = $id;

        // insert pasien dan pendaftaran dalam transaksi
        DB::transaction(function () use ($validated, $idDokter, $idPemeriksa) {
            // cari pasien existing (nip + nama_pasien + tgl_lahir)
            $pasien = DB::table('pasien')
                ->where('nip', $validated['nip'])
                ->where('nama_pasien', $validated['nama_pasien'])
                ->whereDate('tgl_lahir', $validated['tgl_lahir'])
                ->first();

            if ($pasien) {
                $idPasien = $pasien->id_pasien;
                DB::table('pasien')->where('id_pasien', $idPasien)->update([
                    'tipe_pasien' => $validated['tipe_pasien'],
                    'hub_kel' => $validated['hub_kel'],
                ]);
            } else {
                $idPasien = $this->generateIdPasien();
                DB::table('pasien')->insert([
                    'id_pasien' => $idPasien,
                    'nip' => $validated['nip'],
                    'nama_pasien' => $validated['nama_pasien'],
                    'tipe_pasien' => $validated['tipe_pasien'],
                    'hub_kel' => $validated['hub_kel'],
                    'tgl_lahir' => $validated['tgl_lahir'],
                ]);
            }

            
            $idPendaftaran = $this->generateIdPendaftaran();
            // insert pendaftaran (hapus kolom yang tidak ada di DB kamu)
            DB::table('pendaftaran')->insert([
                'id_pendaftaran' => $idPendaftaran,
                'tanggal' => $validated['tanggal'],
                'keluhan' => $validated['keluhan'],
                'id_pasien' => $idPasien,
                'id_dokter' => $idDokter,
                'id_pemeriksa' => $idPemeriksa,
            ]);
        });

        return redirect()->route('adminpoli.dashboard')
            ->with('success', 'Pendaftaran berhasil disimpan.');
    }

    private function generateIdPasien()
    {
        $last = DB::table('pasien')
            ->orderBy('id_pasien', 'desc')
            ->value('id_pasien');

        if (!$last) {
            return 'PSN0001';
        }

        $number = (int) substr($last, 3);
        $number++;

        return 'PSN' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    private function generateIdPendaftaran()
    {
        $last = DB::table('pendaftaran')
            ->orderBy('id_pendaftaran', 'desc')
            ->value('id_pendaftaran');

        if (!$last) {
            return 'REG0001';
        }

        $number = (int) substr($last, 3);
        $number++;

        return 'REG' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}
