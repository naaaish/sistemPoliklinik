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

        $nip = $request->nip;
        $hubungan = $request->hubungan_keluarga;

        // 1. 🔒 VALIDASI PASANGAN (Maksimal 1)
        if ($hubungan === 'pasangan') {
            $pasanganExists = DB::table('keluarga')
                ->where('nip', $nip)
                ->where('hubungan_keluarga', 'pasangan')
                ->exists();

            if ($pasanganExists) {
                return redirect()->back()->withInput()->with('error', "Pegawai ini sudah memiliki data Pasangan terdaftar!");
            }
        }

        // 2. 🔒 VALIDASI ANAK (Mencegah Duplikat Nomor Anak)
        if ($hubungan === 'anak') {
            $exists = DB::table('keluarga')
                ->where('nip', $nip)
                ->where('hubungan_keluarga', 'anak')
                ->where('urutan_anak', $request->urutan_anak)
                ->exists();

            if ($exists) {
                return redirect()->back()->withInput()->with('error', "Pegawai ini sudah memiliki data Anak ke-{$request->urutan_anak}!");
            }
        }
        // 3. ⚙️ LOGIKA STATUS TANGGUNGAN (Maksimal 3 Anak & Umur < 23)
        $isActive = 1; // Default Aktif
        if ($hubungan === 'anak') {
            $umur = Carbon::parse($request->tgl_lahir)->age;
            
            // Cek jika urutan anak lebih dari 3 ATAU umur >= 23
            if ($request->urutan_anak > 3 || $umur >= 23) {
                $isActive = 0; // Set Non-Aktif (Tidak ditanggung)
            }
        }

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
            'is_active' => 0, // Default 0, nanti diaktifkan oleh reSync
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Jalankan sinkronisasi otomatis
        $this->reSyncActiveStatus($request->nip);

        return redirect()->route('pegawai.show', $request->nip)
            ->with('success', 'Anggota keluarga berhasil ditambahkan!');
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
                
        $keluarga = DB::table('keluarga')->where('id_keluarga', $id)->first();

        DB::table('keluarga')->where('id_keluarga', $id)->update([
            'hubungan_keluarga' => $hubungan,
            'nama_keluarga' => $request->nama_keluarga,
            'tgl_lahir' => $request->tgl_lahir,
            'jenis_kelamin' => $request->jenis_kelamin,
            'urutan_anak' => $hubungan === 'anak' ? $request->urutan_anak : null,
            'is_active' => $isActive,
            'updated_at' => now(),
        ]);

        $this->reSyncActiveStatus($keluarga->nip);

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
    // 3. LOGIC UTAMA (KUNCI AGAR ANAK KE-4 BUREM)
    private function reSyncActiveStatus($nip)
    {
        // STEP A: Matikan semua status aktif untuk pegawai ini
        DB::table('keluarga')->where('nip', $nip)->update(['is_active' => 0]);

        // STEP B: Nyalakan Pasangan (Maksimal 1)
        DB::table('keluarga')
            ->where('nip', $nip)
            ->where('hubungan_keluarga', 'pasangan')
            ->orderBy('created_at', 'asc')
            ->limit(1)
            ->update(['is_active' => 1]);

        // STEP C: Ambil ID 3 Anak yang berhak (Urutan 1-3 & Umur < 23)
        $anakBerhakIds = DB::table('keluarga')
            ->where('nip', $nip)
            ->where('hubungan_keluarga', 'anak')
            ->whereRaw("TIMESTAMPDIFF(YEAR, tgl_lahir, CURDATE()) < 23")
            ->orderBy('urutan_anak', 'asc') // Urutan 1, 2, 3 diutamakan
            ->limit(3) // HANYA 3 ANAK
            ->pluck('id_keluarga');

        // STEP D: Nyalakan status aktif hanya untuk ID yang terpilih
        if ($anakBerhakIds->isNotEmpty()) {
            DB::table('keluarga')
                ->whereIn('id_keluarga', $anakBerhakIds)
                ->update(['is_active' => 1]);
        }
    } 
}
