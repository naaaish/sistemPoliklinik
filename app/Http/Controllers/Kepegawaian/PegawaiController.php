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
        
        $pegawai = Pegawai::when($q, function($query, $q) {
            return $query->where('nama_pegawai', 'like', "%{$q}%");
        })->get();

        return view('kepegawaian.pegawai.index', compact('pegawai', 'q'));
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
            'agama' => 'nullable',
            'tgl_lahir' => 'nullable|date',
            'tgl_masuk' => 'nullable|date',
            'status_pernikahan' => 'nullable',
            'no_telp' => 'nullable',
            'email' => 'nullable|email',
            'alamat' => 'nullable',
            'jabatan' => 'required',
            'bagian' => 'required',
            'pendidikan_terakhir' => 'nullable',
            'institusi' => 'nullable',
            'thn_lulus' => 'nullable',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'is_active' => 'nullable',
        ]);

        // Handle foto upload
        if ($request->hasFile('foto')) {
            $foto = $request->file('foto');
            $filename = time() . '_' . $foto->getClientOriginalName();
            $foto->storeAs('public/foto_pegawai', $filename);
            $data['foto'] = $filename;
        }

        // Set default is_active jika tidak ada
        $data['is_active'] = $request->input('is_active', 1);

        Pegawai::create($data);

        return redirect()->route('pegawai.index')
            ->with('success', 'Data pegawai berhasil ditambahkan!');
    }

    public function show($nip)
    {
        $pegawai = Pegawai::where('nip', $nip)->firstOrFail();
        
        // Hitung masa kerja
        $masaKerja = \Carbon\Carbon::parse($pegawai->tgl_masuk)->diff(\Carbon\Carbon::now());
        $years = $masaKerja->y;
        $months = $masaKerja->m;

        return view('kepegawaian.pegawai.detail', compact('pegawai', 'years', 'months'));
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
            
            $statusText = $newStatus ? 'Aktif' : 'Non Aktif';
            return redirect()->back()
                ->with('success', "Status pegawai berhasil diubah menjadi {$statusText}!");
        }

        // Update full data dari form edit
        $data = $request->validate([
            'nama_pegawai' => 'required',
            'nik' => 'nullable',
            'jenis_kelamin' => 'nullable',
            'agama' => 'nullable',
            'tgl_lahir' => 'nullable|date',
            'tgl_masuk' => 'nullable|date',
            'status' => 'nullable',
            'status_pernikahan' => 'nullable',
            'no_telp' => 'nullable',
            'email' => 'nullable|email',
            'alamat' => 'nullable',
            'jabatan' => 'required',
            'bagian' => 'required',
            'pendidikan_terakhir' => 'nullable',
            'institusi' => 'nullable',
            'thn_lulus' => 'nullable',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'is_active' => 'nullable',
        ]);

        // Handle foto upload
        if ($request->hasFile('foto')) {
            // Hapus foto lama jika ada
            if ($pegawai->foto) {
                Storage::delete('public/foto_pegawai/' . $pegawai->foto);
            }

            $foto = $request->file('foto');
            $filename = time() . '_' . $foto->getClientOriginalName();
            $foto->storeAs('public/foto_pegawai', $filename);
            $data['foto'] = $filename;
        }

        // Update is_active
        if ($request->has('is_active')) {
            $data['is_active'] = (int) $request->input('is_active');
        }

        $pegawai->update($data);

        return redirect()->route('pegawai.index')
            ->with('success', 'Data pegawai berhasil diperbarui!');
    }

    public function destroy($nip)
    {
        $pegawai = Pegawai::where('nip', $nip)->firstOrFail();

        // Hapus foto jika ada
        if ($pegawai->foto) {
            Storage::delete('public/foto_pegawai/' . $pegawai->foto);
        }

        $pegawai->delete();

        return redirect()->route('pegawai.index')
            ->with('success', 'Data pegawai berhasil dihapus!');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt'
        ]);

        $file = fopen($request->file('file')->getRealPath(), 'r');
        fgetcsv($file); // Lewati header

        $rowCount = 0;
        while (($row = fgetcsv($file, 2000, ",")) !== false) {
            if (empty($row[0])) continue;

            // Kita petakan kolom satu per satu agar tidak tertukar
            // Asumsi urutan CSV mengikuti urutan Database kamu
            DB::table('pegawai')->updateOrInsert(
                ['nip' => $row[0]], 
                [
                    'nama_pegawai'      => $row[1] ?? '',
                    'nik'               => $row[2] ?? '-',
                    'agama'             => $row[3] ?? '-',
                    'jenis_kelamin'     => $row[4] ?? '-',
                    'tgl_lahir'         => $row[5] ?? null,
                    'tgl_masuk'         => $row[6] ?? null,
                    'status_pernikahan' => $row[7] ?? '-',
                    'no_telp'           => $row[8] ?? '-',
                    'email'             => $row[9] ?? '-',
                    'alamat'            => $row[10] ?? '-',
                    'jabatan'           => $row[11] ?? '-',
                    'bagian'            => $row[12] ?? '-',
                    'pendidikan_terakhir' => $row[13] ?? '-',
                    'institusi'         => $row[14] ?? '-',
                    'thn_lulus'         => $row[15] ?? null,
                    'is_active'         => 1,
                    'updated_at'        => now(),
                ]
            );
            $rowCount++;
        }
        fclose($file);

        return redirect()->route('kepegawaian.pegawai')
            ->with('success', "Berhasil mengimport $rowCount data pegawai!");
    }

}