<?php

namespace App\Http\Controllers\Kepegawaian;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class KeluargaController extends Controller
{
    public function store(Request $request) {
        $request->validate([
            'nip' => 'required',
            'nama_keluarga' => 'required',
            'hubungan_keluarga' => 'required',
            'tgl_lahir' => 'required|date',
            'jenis_kelamin' => 'required',
            'urutan_anak' => 'nullable|integer'
        ]);

        // 1. CEK PASANGAN KEDUA (Gak boleh dobel)
        if ($request->hubungan_keluarga === 'pasangan') {
            $cekPasangan = DB::table('keluarga')
                ->where('nip', $request->nip)
                ->where('hubungan_keluarga', 'pasangan')
                ->exists();
            if ($cekPasangan) {
                return redirect()->back()->withInput()->with('error', "Pegawai ini sudah memiliki pasangan terdaftar!");
            }
        }

        // 2. HITUNG LOGIC TANGGUNGAN
        $isActive = 1;
        if ($request->hubungan_keluarga === 'anak') {
            $umur = Carbon::parse($request->tgl_lahir)->age;
            // Logic: Non-aktif jika anak > 3 ATAU umur >= 23
            if ($request->urutan_anak > 3 || $umur >= 23) {
                $isActive = 0;
            }
        }

        // Lanjutkan simpan data...
        DB::table('keluarga')->insert([
            'id_keluarga' => $request->nip . '-' . strtoupper(substr($request->hubungan_keluarga, 0, 1)) . '-' . rand(10,99),
            'nip' => $request->nip,
            'hubungan_keluarga' => $request->hubungan_keluarga,
            'nama_keluarga' => $request->nama_keluarga,
            'tgl_lahir' => $request->tgl_lahir,
            'jenis_kelamin' => $request->jenis_kelamin,
            'urutan_anak' => $request->urutan_anak,
            'is_active' => $isActive, // <--- Simpan statusnya
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('pegawai.show', $request->nip)->with('success', 'Data keluarga berhasil ditambah!');
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_keluarga' => 'required',
            'hubungan_keluarga' => 'required',
            'tgl_lahir' => 'required|date',
            'jenis_kelamin' => 'required',
            'urutan_anak' => 'nullable|integer'
        ]);

        $keluargaLama = DB::table('keluarga')->where('id_keluarga', $id)->first();
        $hubungan = $request->hubungan_keluarga;

        // Validasi Duplikat Anak saat Update
        if ($hubungan === 'anak') {
            $isDuplicate = DB::table('keluarga')
                ->where('nip', $keluargaLama->nip)
                ->where('hubungan_keluarga', 'anak')
                ->where('urutan_anak', $request->urutan_anak)
                ->where('id_keluarga', '!=', $id)
                ->exists();

            if ($isDuplicate) {
                return redirect()->back()->withInput()->with('error', "Nomor Anak ke-{$request->urutan_anak} sudah terdaftar!");
            }
        }

        // Re-Kalkulasi Status Tanggungan saat Update
        $isActive = 1;
        if ($hubungan === 'anak') {
            $umur = Carbon::parse($request->tgl_lahir)->age;
            if ($request->urutan_anak > 3 || $umur >= 23) {
                $isActive = 0;
            }
        }

        DB::table('keluarga')->where('id_keluarga', $id)->update([
            'hubungan_keluarga' => $hubungan,
            'nama_keluarga' => $request->nama_keluarga,
            'tgl_lahir' => $request->tgl_lahir,
            'jenis_kelamin' => $request->jenis_kelamin,
            'urutan_anak' => $hubungan === 'anak' ? $request->urutan_anak : null,
            'is_active' => $isActive,
            'updated_at' => now(),
        ]);

        return redirect()->route('pegawai.show', $keluargaLama->nip)
            ->with('success', 'Data keluarga berhasil diperbarui!');
    }

    public function create($nip)
    {
        $pegawai = DB::table('pegawai')->where('nip', $nip)->firstOrFail();
        
        // Hitung jumlah anak yang sudah ada, lalu tambah 1 untuk saran urutan berikutnya
        $nextChildNumber = DB::table('keluarga')
            ->where('nip', $nip)
            ->where('hubungan_keluarga', 'anak')
            ->count() + 1;

        $mode = 'create';
        return view('kepegawaian.pegawai.keluarga-form', compact('pegawai', 'mode', 'nextChildNumber'));
    }

    public function edit($id)
    {
        $keluarga = DB::table('keluarga')->where('id_keluarga', $id)->firstOrFail();
        $pegawai = DB::table('pegawai')->where('nip', $keluarga->nip)->first();
        $mode = 'edit';
        return view('kepegawaian.pegawai.keluarga-form', compact('pegawai', 'keluarga', 'mode'));
    }

}
