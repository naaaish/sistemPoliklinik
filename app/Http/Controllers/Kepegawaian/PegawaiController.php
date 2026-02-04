<?php

namespace App\Http\Controllers\Kepegawaian;

use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class PegawaiController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->input('q');
        $perPage = $request->get('per_page', 10);

        $query = Pegawai::when($q, function ($query, $q) {
            $query->where('nama_pegawai', 'like', "%{$q}%");
        })->orderBy('nama_pegawai');

        if ($perPage === 'all') {
            $pegawai = $query->get();
            $isAll = true;
        } else {
            $pegawai = $query
                ->paginate((int)$perPage)
                ->withQueryString();
            $isAll = false;
        }

        return view('kepegawaian.pegawai.index', compact(
            'pegawai',
            'q',
            'perPage',
            'isAll'
        ));
    }

    public function create()
    {
        $pegawai = new Pegawai();
        $mode = 'create';
        return view('kepegawaian.pegawai.form', compact('pegawai', 'mode'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nip' => 'required|unique:pegawai,nip',
            'nama_pegawai' => 'required',
            'jenis_kelamin' => 'nullable',
            'tgl_lahir' => 'nullable|date',
            'no_telp' => 'nullable',
            'email' => 'nullable|email',
            'alamat' => 'nullable',
            'jabatan' => 'required',
            'bagian' => 'required',
            'is_active' => 'nullable',
        ]);

        // Set default is_active jika tidak ada
        $data['is_active'] = $request->input('is_active', 1);

        Pegawai::create($data);

        return redirect()->route('pegawai.show', $data['nip']) 
            ->with('success', 'Data pegawai berhasil ditambahkan!');
    }

    public function show($nip)
    {
        $pegawai = DB::table('pegawai')->where('nip', $nip)->firstOrFail();
        
        // Ambil data keluarga berdasarkan NIP pegawai
        // Urutkan: pasangan dulu, lalu anak berdasarkan tanggal lahir (tua ke muda)
        $keluarga = DB::table('keluarga')
            ->where('nip', $nip)
            ->orderByRaw("CASE WHEN hubungan_keluarga = 'pasangan' THEN 0 ELSE 1 END")
            ->orderBy('tgl_lahir', 'asc')
            ->get();
        
        // Tambahkan urutan anak untuk display
        $urutanAnak = 1;
        foreach ($keluarga as $k) {
            if ($k->hubungan_keluarga === 'anak') {
                $k->urutan_display = $urutanAnak++;
            } else {
                $k->urutan_display = null;
            }
        }
        
        return view('kepegawaian.pegawai.detail', compact('pegawai', 'keluarga'));
    }
    
    public function edit($nip)
    {
        $pegawai = Pegawai::where('nip', $nip)->firstOrFail();
        $mode = 'edit';
        return view('kepegawaian.pegawai.form', compact('pegawai', 'mode'));
    }

    public function update(Request $request, $nip)
    {
        $pegawai = Pegawai::where('nip', $nip)->firstOrFail();

        // Cek apakah ini request dari dropdown status (hanya is_active yang dikirim)
        $isStatusUpdate = $request->has('is_active') && !$request->has('nama_pegawai');

        if ($isStatusUpdate) {
            // Update hanya status is_active menggunakan query builder
            $newStatus = (int) $request->input('is_active');
            
            Log::info('Status Update', [
                'nip' => $nip,
                'old_status' => $pegawai->is_active,
                'new_status' => $newStatus
            ]);
            
            // Update langsung dengan query builder
            Pegawai::where('nip', $nip)->update(['is_active' => $newStatus]);
            
            // 🔥 JIKA PEGAWAI DINONAKTIFKAN, NONAKTIFKAN SEMUA KELUARGANYA
            if ($newStatus == 0) {
                DB::table('keluarga')
                    ->where('nip', $nip)
                    ->update(['is_active' => 0]);
            } else {
                // Jika diaktifkan kembali, jalankan sync untuk set ulang status keluarga
                $keluargaController = app(\App\Http\Controllers\Kepegawaian\KeluargaController::class);
                $keluargaController->reSyncActiveStatus($nip);
            }
            
            $statusText = $newStatus ? 'Aktif' : 'Non Aktif';
            return redirect()->back()
                ->with('success', "Status pegawai berhasil diubah menjadi {$statusText}!");
        }

        // Update full data dari form edit
        $data = $request->validate([
            'nama_pegawai' => 'required',
            'jenis_kelamin' => 'nullable',
            'tgl_lahir' => 'nullable|date',
            'no_telp' => 'nullable',
            'email' => 'nullable|email',
            'alamat' => 'nullable',
            'jabatan' => 'required',
            'bagian' => 'required',
            'is_active' => 'nullable',
        ]);


        // Update is_active
        if ($request->has('is_active')) {
            $data['is_active'] = (int) $request->input('is_active');
        }

        $pegawai->update($data);

        return redirect()->route('pegawai.show', $pegawai->nip) 
            ->with('success', 'Data pegawai berhasil diperbarui!');
    }

    public function importMulti(Request $request)
    {
        // 1. Tambahkan mimes xlsx dan xls agar Laravel tidak menolak file Excel
        $request->validate([
            'file' => 'required|mimes:csv,txt,xlsx,xls',
            'type' => 'required|in:pegawai,keluarga'
        ]);

        $file = $request->file('file');
        $type = $request->input('type');

        try {
            DB::beginTransaction();

            // 2. Gunakan IOFactory untuk membaca file (Otomatis deteksi Excel/CSV)
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(); // Mengubah baris Excel jadi array PHP

            $rowCount = 0;
            foreach ($rows as $index => $row) {
                // Lewati baris pertama (Header)
                if ($index === 0) continue;
                
                // Lewati jika kolom pertama (NIP) kosong
                if (empty($row[0])) continue;

                if ($type === 'pegawai') {
                    // Mapping: 0:NIP, 1:Nama, 2:NIK, 3:JK, 4:TglLahir, 5:Telp, 6:Email, 7:Alamat, 8:Jabatan, 9:Bagian
                    DB::table('pegawai')->updateOrInsert(
                        // B = NIP (index 1)
                        ['nip' => $row[1]],

                        [
                            // C = Nama
                            'nama_pegawai'  => $row[2],

                            // D = Gender
                            'jenis_kelamin' => $row[3] ?? null,

                            // E = Tanggal Lahir
                            'tgl_lahir'     => $this->transformDate($row[4]),

                            // F = Telp
                            'no_telp'       => $row[5] ?? null,

                            // tidak harus ada email
                            'email'         => null,

                            // G = Alamat
                            'alamat'        => $row[6] ?? null,

                            // H = Jabatan
                            'jabatan'       => $row[7] ?? null,

                            // I = Bagian
                            'bagian'        => $row[8] ?? null,

                            'is_active'     => 1,
                            'updated_at'    => now()
                        ]
                    );
                } else {
                    // ===== KELUARGA =====

                    // ambil & rapihin NIP
                    $nip = trim($row[1] ?? '');
                    if ($nip === '') continue;

                    // FOREIGN KEY GUARD
                    if (!DB::table('pegawai')->where('nip', $nip)->exists()) {
                        continue;
                    }

                    // ===== HUBUNGAN =====
                    $hubunganExcel = strtoupper(trim($row[3] ?? ''));

                    if (str_contains($hubunganExcel, 'ISTRI') || str_contains($hubunganExcel, 'SUAMI')) {
                        $hubunganFix = 'pasangan';
                        $kodeHubungan = 'P';
                    } elseif (str_contains($hubunganExcel, 'ANAK')) {
                        $hubunganFix = 'anak';
                        $kodeHubungan = 'A';
                    } else {
                        // DEFAULT AMAN
                        $hubunganFix = 'anak';
                        $kodeHubungan = 'A';
                    }

                    // ===== TANGGAL LAHIR =====
                    $tglLahir = $this->transformDate($row[4]);
                    if ($tglLahir === null) {
                        // tanggal lahir wajib → skip kalau invalid
                        continue;
                    }

                    // ===== JENIS KELAMIN =====
                    $jkMap = [
                        'L' => 'L',
                        'LAKI-LAKI' => 'L',
                        'LAKI LAKI' => 'L',
                        'PRIA' => 'L',

                        'P' => 'P',
                        'PEREMPUAN' => 'P',
                        'WANITA' => 'P',
                    ];

                    $jkExcel = strtoupper(trim($row[5] ?? ''));
                    if (!isset($jkMap[$jkExcel])) {
                        $jkExcel = 'L'; // default aman
                    }

                    // ===== ID KELUARGA =====
                    $id_keluarga = $nip . '-' . $kodeHubungan . '-' . rand(1000, 9999);

                    // ===== INSERT =====
                    DB::table('keluarga')->insert([
                        'id_keluarga'        => $id_keluarga,
                        'nip'                => $nip,
                        'hubungan_keluarga'  => $hubunganFix,
                        'nama_keluarga'      => $row[2],
                        'jenis_kelamin'      => $jkMap[$jkExcel],
                        'tgl_lahir'          => $tglLahir,
                        'is_active'          => 0,
                        'created_at'         => now(),
                        'updated_at'         => now(),
                    ]);
                    $keluargaController = app(\App\Http\Controllers\Kepegawaian\KeluargaController::class);

                    // ✅ 1. update urutan anak
                    $keluargaController->syncUrutanAnak($nip);

                    // ✅ 2. JALANKAN LOGIC AKTIF / NONAKTIF
                    $keluargaController->reSyncActiveStatus($nip);
                }


                $rowCount++;
            }

            DB::commit();
            return redirect()->back()->with('success', "Berhasil mengimport $rowCount data $type.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }

    /**
     * Helper untuk konversi tanggal Excel (angka serial) ke format Y-m-d
     */
    private function transformDate($value)
    {
        if (empty($value)) return null;

        try {
            // Jika Excel mengirim format angka (misal: 44561)
            if (is_numeric($value)) {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
            }
            
            // Jika formatnya sudah string tanggal (misal: 1990-01-01 atau 01-01-1990)
            return date('Y-m-d', strtotime($value));
        } catch (\Exception $e) {
            return null;
        }
    }
}