<?php

namespace App\Http\Controllers\Kepegawaian;

use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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
            'nik' => 'nullable',
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
        $keluarga = DB::table('keluarga')
            ->where('nip', $nip)
            ->orderBy('is_active', 'desc')   
            ->orderBy('urutan_anak', 'asc')  
            ->get();
        
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
            'nik' => 'nullable',
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
        $request->validate([
            'file' => 'required|mimes:csv,txt',
            'type' => 'required|in:pegawai,keluarga'
        ]);

        $file = fopen($request->file('file')->getRealPath(), 'r');
            fgetcsv($file);

        $rowCount = 0;
        $type = $request->input('type');
        while (($row = fgetcsv($file, 2000, ",")) !== false) {
            if (empty($row[0])) continue;

        try {
                DB::beginTransaction();
                while (($row = fgetcsv($file, 2000, ",")) !== FALSE) {
                    if (empty($row[0])) continue;

                    if ($type == 'pegawai') {
                        // LOGIK IMPORT PEGAWAI
                        DB::table('pegawai')->updateOrInsert(
                            ['nip' => $row[0]], 
                            [
                                'nama_pegawai'      => $row[1] ?? '',
                                'nik'               => $row[2] ?? '-',
                                'jenis_kelamin'     => $row[4] ?? '-',
                                'tgl_lahir'         => $row[5] ?? null,
                                'no_telp'           => $row[8] ?? '-',
                                'email'             => $row[9] ?? '-',
                                'alamat'            => $row[10] ?? '-',
                                'jabatan'           => $row[11] ?? '-',
                                'bagian'            => $row[12] ?? '-',
                                'is_active'         => 1,
                                'updated_at'        => now(),
                            ]
                        );
                    } else {
                        // LOGIK IMPORT KELUARGA
                        // Kolom CSV Keluarga: 0:NIP, 1:Hubungan, 2:Nama, 3:Tgl Lahir, 4:JK, 5:Anak Ke
                        $hubungan = strtolower($row[1]);
                        $urutan = ($hubungan == 'anak') ? ($row[5] ?? null) : null;
                        
                        // Generate ID Keluarga otomatis
                        $id_keluarga = $row[0] . '-' . strtoupper(substr($hubungan, 0, 1)) . ($urutan ?? rand(10, 99));

                        DB::table('keluarga')->updateOrInsert(
                            [
                                'nip' => $row[0], 
                                'hubungan_keluarga' => $hubungan,
                                'urutan_anak' => $urutan
                            ],
                            [
                                'id_keluarga'   => $id_keluarga,
                                'nama_keluarga' => $row[2],
                                'tgl_lahir'     => $row[3],
                                'jenis_kelamin' => $row[4],
                                'updated_at'    => now()
                            ]
                        );
                    }
                    $rowCount++;
                }
                DB::commit();
                fclose($file);

                return redirect()->back()->with('success', "Berhasil mengimport $rowCount data $type.");
            } catch (\Exception $e) {
                DB::rollBack();
                return redirect()->back()->with('error', 'Gagal import: ' . $e->getMessage());
            }
        }
    }
}