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
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt'
        ]);
        

        $filePath  = $request->file('file')->getRealPath();
        $delimiter = $this->detectDelimiter($filePath);

        $file = fopen($filePath, 'r');
        fgetcsv($file, 1000, $delimiter); // skip header

        $success = 0;
        $updated = 0;

        while (($row = fgetcsv($file, 1000, $delimiter)) !== false) {

            // pastikan kolom cukup
            if (count($row) < 5) continue;

            // rapihin data
            $username  = trim($row[0]);
            $username  = preg_replace('/^\xEF\xBB\xBF/', '', $username); // bersihin BOM
            $password  = trim($row[1]);
            $role      = trim($row[2]);
            $nama_user = trim($row[3]);
            $nip       = trim($row[4]);

            if ($username === '' || $nip === '') continue;

            // 🔑 CEK USER BERDASARKAN NIP ATAU USERNAME
            $existingUser = DB::table('users')
                ->where('nip', $nip)
                ->orWhere('username', $username)
                ->first();

            if ($existingUser) {
                // ============ UPDATE ============
                DB::table('users')
                    ->where('id', $existingUser->id)
                    ->update([
                        'username'   => $username,
                        'role'       => $role,
                        'nama_user'  => $nama_user,
                        'nip'        => $nip,
                        'updated_at' => now(),
                    ]);
                $updated++;
            } else {
                // ============ INSERT ============
                DB::table('users')->insert([
                    'username'   => $username,
                    'password'   => Hash::make($password),
                    'role'       => $role,
                    'nama_user'  => $nama_user,
                    'nip'        => $nip,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $success++;
            }
        }

        fclose($file);

        return back()->with(
            'success',
            "Import selesai: {$success} data baru, {$updated} data diperbarui"
        );
    }
}