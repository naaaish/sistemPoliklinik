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

    /**
     * RESET PASSWORD USER
     * Method baru khusus untuk reset password saja
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt'
        ]);

        $file = fopen($request->file('file'), 'r');
        fgetcsv($file); // skip header

        $success = 0;
        $updated = 0;

        while (($row = fgetcsv($file, 1000, ',')) !== false) {

            if (empty($row[4])) continue; // NIP WAJIB ADA

            // cek user berdasarkan NIP
            $existingUser = DB::table('users')
                ->where('nip', $row[4])
                ->first();

            if ($existingUser) {
                // ================= UPDATE =================
                DB::table('users')
                    ->where('nip', $row[4])
                    ->update([
                        'username'   => $row[0],          // boleh berubah
                        'role'       => $row[2],
                        'nama_user'  => $row[3],
                        'updated_at' => now(),
                    ]);

                $updated++;
            } else {
                // ================= INSERT =================
                DB::table('users')->insert([
                    'username'   => $row[0],
                    'password'   => Hash::make($row[1]),
                    'role'       => $row[2],
                    'nama_user'  => $row[3],
                    'nip'        => $row[4],
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