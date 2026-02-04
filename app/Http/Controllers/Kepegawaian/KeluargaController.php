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

        // Generate ID Keluarga
        $suffix = substr($request->hubungan_keluarga, 0, 1);
        $id_keluarga = $request->nip . '-' . strtoupper($suffix) . '-' . rand(1000, 9999);

        DB::table('keluarga')->insert([
            'id_keluarga' => $id_keluarga,
            'nip' => $request->nip,
            'hubungan_keluarga' => $request->hubungan_keluarga,
            'nama_keluarga' => $request->nama_keluarga,
            'tgl_lahir' => $request->tgl_lahir,
            'jenis_kelamin' => $request->jenis_kelamin,
            'is_active' => 0, // Default 0, nanti diaktifkan oleh reSync
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Jalankan sinkronisasi otomatis
        $this->reSyncActiveStatus($request->nip);
        $this->syncUrutanAnak($request->nip);


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
        ]);

        $keluargaLama = DB::table('keluarga')->where('id_keluarga', $id)->first();
        $hubungan = $request->hubungan_keluarga;

        // Validasi Pasangan jika diubah menjadi pasangan
        if ($hubungan === 'pasangan' && $keluargaLama->hubungan_keluarga !== 'pasangan') {
            $pasanganExists = DB::table('keluarga')
                ->where('nip', $keluargaLama->nip)
                ->where('hubungan_keluarga', 'pasangan')
                ->where('id_keluarga', '!=', $id)
                ->exists();

            if ($pasanganExists) {
                return redirect()->back()->withInput()->with('error', "Pegawai ini sudah memiliki data Pasangan terdaftar!");
            }
        }

        DB::table('keluarga')->where('id_keluarga', $id)->update([
            'hubungan_keluarga' => $hubungan,
            'nama_keluarga' => $request->nama_keluarga,
            'tgl_lahir' => $request->tgl_lahir,
            'jenis_kelamin' => $request->jenis_kelamin,
            'updated_at' => now(),
        ]);

        $this->reSyncActiveStatus($keluargaLama->nip);
        $this->syncUrutanAnak($keluargaLama->nip);


        return redirect()->route('pegawai.show', $keluargaLama->nip)
            ->with('success', 'Data keluarga berhasil diperbarui!');
    }

    public function create($nip)
    {
        $pegawai = DB::table('pegawai')->where('nip', $nip)->firstOrFail();
        $mode = 'create';
        return view('kepegawaian.pegawai.keluarga-form', compact('pegawai', 'mode'));
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

        // 2. Ambil Semua Anak Urut Tua -> Muda (berdasarkan tanggal lahir)
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


    public function syncUrutanAnak($nip)
    {
        $anakList = DB::table('keluarga')
            ->where('nip', $nip)
            ->where('hubungan_keluarga', 'anak')
            ->orderBy('tgl_lahir', 'asc') // sumber kebenaran
            ->get();

        $urutan = 1;
        foreach ($anakList as $anak) {
            DB::table('keluarga')
                ->where('id_keluarga', $anak->id_keluarga)
                ->update([
                    'urutan_anak' => $urutan
                ]);
            $urutan++;
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
                'is_active' => $statusBaru
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
                'is_active' => $statusBaru
            ]);
        }
    }

    /**
     * Helper function untuk mendapatkan urutan anak berdasarkan tanggal lahir
     * Digunakan untuk display di view
     */
    public function getUrutanAnak($nip, $id_keluarga)
    {
        $allAnak = DB::table('keluarga')
            ->where('nip', $nip)
            ->where('hubungan_keluarga', 'anak')
            ->orderBy('tgl_lahir', 'asc')
            ->pluck('id_keluarga')
            ->toArray();

        $urutan = array_search($id_keluarga, $allAnak);
        return $urutan !== false ? $urutan + 1 : null;
    }
}