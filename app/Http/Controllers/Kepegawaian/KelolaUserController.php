<?php

namespace App\Http\Controllers\Kepegawaian;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class KelolaUserController extends Controller
{
    /**
     * LIST USER + SEARCH + PAGINATION
     */
    public function index(Request $request)
    {
        $q = $request->q;
        $perPage = $request->per_page ?? 10;

        $users = DB::table('users')
            ->select('id','username','role','nama_user','nip')
            ->when($q, function ($query) use ($q) {
                $query->where('username', 'like', "%$q%")
                      ->orWhere('nama_user', 'like', "%$q%")
                      ->orWhere('nip', 'like', "%$q%");
            })
            ->orderBy('role')
            ->orderBy('nama_user')
            ->paginate($perPage)
            ->appends($request->except('page'));

        return view('kepegawaian.kelolaUser.index', compact('users','q'));
    }

    /**
     * DETAIL USER (UNTUK MODAL)
     * READ-ONLY - Hanya untuk ditampilkan
     */
    public function show($id)
    {
        $user = DB::table('users')
            ->select('id','username','role','nama_user','nip')
            ->where('id', $id)
            ->first();

        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        return response()->json($user);
    }

    private function detectDelimiter($filePath)
    {
        $delimiters = [',', ';'];
        $firstLine = '';

        $handle = fopen($filePath, 'r');
        if ($handle) {
            $firstLine = fgets($handle);
            fclose($handle);
        }

        $bestDelimiter = ',';
        $maxCount = 0;

        foreach ($delimiters as $delimiter) {
            $count = substr_count($firstLine, $delimiter);
            if ($count > $maxCount) {
                $maxCount = $count;
                $bestDelimiter = $delimiter;
            }
        }

        return $bestDelimiter;
    }

    /**
     * RESET PASSWORD USER
     * Method baru khusus untuk reset password saja
     */

    public function resetPassword(Request $request, $id)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'password' => 'required|min:6|confirmed',
            'password_confirmation' => 'required|min:6',
        ], [
            'password.required' => 'Password wajib diisi',
            'password.min' => 'Password minimal 6 karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
            'password_confirmation.required' => 'Konfirmasi password wajib diisi',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        // Cek user exist
        $user = DB::table('users')->where('id', $id)->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'User tidak ditemukan'
            ], 404);
        }

        // Update password
        try {
            DB::table('users')
                ->where('id', $id)
                ->update([
                    'password' => Hash::make($request->password),
                    'updated_at' => now(),
                ]);

            return response()->json([
                'status' => true,
                'message' => 'Password berhasil direset untuk user: ' . $user->username
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Gagal mereset password: ' . $e->getMessage()
            ], 500);
        }
    }
    public function import(Request $request)
    {
        // ⏱️ cegah timeout & memory limit
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $request->validate([
            'file' => 'required|mimes:csv,txt'
        ]);

        $filePath  = $request->file('file')->getRealPath();
        $delimiter = $this->detectDelimiter($filePath);

        $file = fopen($filePath, 'r');
        fgetcsv($file, 1000, $delimiter); // skip header

        $success = 0;
        $skipped = 0;
        $processedNips = []; // Array untuk track NIP yang sudah diproses dalam batch ini

        DB::beginTransaction();

        try {
            while (($row = fgetcsv($file, 1000, $delimiter)) !== false) {

                // minimal kolom: username, password, role, nama_user, nip
                if (count($row) < 5) continue;

                // rapihin & bersihin data
                $username  = trim(preg_replace('/^\xEF\xBB\xBF/', '', $row[0]));
                $password  = trim($row[1]);
                $role      = trim($row[2]);
                $nama_user = trim($row[3]);
                $nip       = trim($row[4]);

                if ($username === '' || $nip === '') continue;

                // ✅ Skip jika NIP sudah diproses dalam batch ini (untuk handle duplicate dalam CSV yang sama)
                if (in_array($nip, $processedNips)) {
                    $skipped++;
                    continue;
                }

                // ✅ Cek apakah NIP sudah ada di database
                $existingUser = DB::table('users')
                    ->where('nip', $nip)
                    ->first();

                if ($existingUser) {
                    // ===== SKIP - Data dengan NIP ini sudah ada =====
                    $skipped++;
                    continue;
                }

                // ===== INSERT - Data baru =====
                DB::table('users')->insert([
                    'username'   => $username,
                    // bcrypt diringanin khusus import
                    'password'   => bcrypt($password ?: 'password123', ['rounds' => 8]),
                    'role'       => $role,
                    'nama_user'  => $nama_user,
                    'nip'        => $nip,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $success++;
                
                // ✅ Tandai NIP ini sudah diproses
                $processedNips[] = $nip;
            }

            fclose($file);
            DB::commit();

            return back()->with(
                'success',
                "Import selesai: {$success} data baru berhasil ditambahkan, {$skipped} data dilewati (NIP sudah ada)"
            );

        } catch (\Exception $e) {
            fclose($file);
            DB::rollBack();

            return back()->with(
                'error',
                'Gagal import: ' . $e->getMessage()
            );
        }
    }
}