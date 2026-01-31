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

        $this->reSyncActiveStatus($request->nip);
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

    // --- LOGIKA UTAMA SINKRONISASI ---

    public function reSyncActiveStatus($nip)
    {
        $pegawai = DB::table('pegawai')->where('nip', $nip)->first();
        if (!$pegawai) return;

        // 1. Reset & Aktifkan Pasangan (Spouse)
        DB::table('keluarga')->where('nip', $nip)->update(['is_active' => 0]);
        DB::table('keluarga')
            ->where('nip', $nip)
            ->where('hubungan_keluarga', 'pasangan')
            ->limit(1)
            ->update(['is_active' => 1]);

        // 2. Ambil Semua Anak Urut Tua -> Muda
        $allAnak = DB::table('keluarga')
            ->where('nip', $nip)
            ->where('hubungan_keluarga', 'anak')
            ->orderBy('tgl_lahir', 'asc') 
            ->get();

        // 3. Pisahkan Logika Berdasarkan Status Pegawai
        if ($pegawai->is_active == 1) {
            $this->syncActiveEmployee($allAnak);
        } else {
            $this->syncRetiredEmployee($allAnak);
        }
    }

    /**
     * Logika Pegawai AKTIF: Menggunakan Sliding Window.
     * Jika anak ke-1 tidak aktif (umur > 25), anak ke-4 bisa naik jadi aktif.
     */
    private function syncActiveEmployee($allAnak)
    {
        $activeCount = 0;
        foreach ($allAnak as $index => $anak) {
            $umur = Carbon::parse($anak->tgl_lahir)->age;
            $statusBaru = 0;

            // Selama kuota jatah (3) masih ada dan umur memenuhi syarat
            if ($umur < 25 && $activeCount < 3) {
                $statusBaru = 1;
                $activeCount++;
            }

            DB::table('keluarga')->where('id_keluarga', $anak->id_keluarga)->update([
                'is_active' => $statusBaru,
                'urutan_anak' => $index + 1
            ]);
        }
    }

    /**
     * Logika PENSIUNAN: Tidak ada Sliding Window.
     * Hanya anak urutan 1, 2, 3 yang bisa aktif. 
     * Jika urutan 1 gugur, urutan 4 tetap tidak bisa masuk.
     */
    private function syncRetiredEmployee($allAnak)
    {
        foreach ($allAnak as $index => $anak) {
            $umur = Carbon::parse($anak->tgl_lahir)->age;
            $statusBaru = 0;

            // Hanya anak yang urutan lahirnya 1-3 (index 0,1,2) 
            // DAN umurnya masuk syarat yang bisa aktif.
            if ($index < 3 && $umur < 25) {
                $statusBaru = 1;
            }

            DB::table('keluarga')->where('id_keluarga', $anak->id_keluarga)->update([
                'is_active' => $statusBaru,
                'urutan_anak' => $index + 1
            ]);
        }
    }
}    