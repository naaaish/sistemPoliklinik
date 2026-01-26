<?php

namespace App\Http\Controllers\Kepegawaian;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KeluargaController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
                'nip' => 'required',
                'nama_keluarga' => 'required',
                'hubungan_keluarga' => 'required',
                'tgl_lahir' => 'required|date',
                'jenis_kelamin' => 'required',
                'urutan_anak' => 'nullable|integer'
            ]);

            // VALIDASI STORE: Cek apakah NIP + Anak ke-X sudah ada
            if ($request->hubungan_keluarga === 'anak') {
                $exists = DB::table('keluarga')
                    ->where('nip', $request->nip)
                    ->where('hubungan_keluarga', 'anak')
                    ->where('urutan_anak', $request->urutan_anak)
                    ->exists();

                if ($exists) {
                    return redirect()->back()->withInput()->with('error', "Pegawai ini sudah memiliki data Anak ke-{$request->urutan_anak}!");
                }
            }

        // Generate ID Keluarga
        $suffix = substr($request->hubungan_keluarga, 0, 1) . ($request->urutan_anak ?? '');
        $id_keluarga = $request->nip . '-' . strtoupper($suffix) . '-' . rand(10, 99);

        DB::table('keluarga')->insert([
            'id_keluarga' => $id_keluarga,
            'nip' => $request->nip,
            'hubungan_keluarga' => $request->hubungan_keluarga,
            'nama_keluarga' => $request->nama_keluarga,
            'tgl_lahir' => $request->tgl_lahir,
            'jenis_kelamin' => $request->jenis_kelamin,
            'urutan_anak' => $request->urutan_anak,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('pegawai.show', $request->nip)
            ->with('success', 'Anggota keluarga berhasil ditambahkan!');
    }

    public function destroy($id)
    {
        DB::table('keluarga')->where('id_keluarga', $id)->delete();
        return redirect()->back()->with('success', 'Data keluarga berhasil dihapus!');
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

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_keluarga' => 'required',
            'hubungan_keluarga' => 'required',
            'tgl_lahir' => 'required|date',
            'jenis_kelamin' => 'required',
            'urutan_anak' => 'nullable|integer'
        ]);

        // Ambil data NIP dari anggota keluarga yang sedang diedit
        $keluargaLama = DB::table('keluarga')->where('id_keluarga', $id)->first();

        // 🔒 PROTEKSI: Cek jika nomor anak sudah dipakai anak lain di NIP yang sama
        if ($request->hubungan_keluarga === 'anak') {
            $isDuplicate = DB::table('keluarga')
                ->where('nip', $keluargaLama->nip)
                ->where('hubungan_keluarga', 'anak')
                ->where('urutan_anak', $request->urutan_anak)
                ->where('id_keluarga', '!=', $id) // Abaikan record diri sendiri
                ->exists();

            if ($isDuplicate) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', "Nomor Anak ke-{$request->urutan_anak} sudah terdaftar!");
            }
        }

        // Eksekusi Update
        DB::table('keluarga')->where('id_keluarga', $id)->update([
            'hubungan_keluarga' => $request->hubungan_keluarga,
            'nama_keluarga' => $request->nama_keluarga,
            'tgl_lahir' => $request->tgl_lahir,
            'jenis_kelamin' => $request->jenis_kelamin,
            'urutan_anak' => $request->hubungan_keluarga === 'anak' ? $request->urutan_anak : null,
            'updated_at' => now(),
        ]);

        return redirect()->route('pegawai.show', $keluargaLama->nip)
            ->with('success', 'Data keluarga berhasil diperbarui!');
    }
}