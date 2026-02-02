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

    /**
     * IMPORT CSV
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt'
        ]);

        $file = fopen($request->file('file'), 'r');
        fgetcsv($file); // skip header

        $success = 0;
        $errors = [];

        while (($row = fgetcsv($file, 1000, ',')) !== false) {
            try {
                DB::table('users')->insert([
                    'username'   => $row[0],
                    'password'   => Hash::make($row[1]),
                    'role'       => $row[2],
                    'nama_user'  => $row[3],
                    'nip'        => $row[4] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $success++;
            } catch (\Exception $e) {
                $errors[] = "Gagal import user: " . $row[0];
            }
        }

        fclose($file);

        if ($success > 0) {
            return back()->with('success', "Berhasil import {$success} user");
        } else {
            return back()->with('error', 'Gagal import user. ' . implode(', ', $errors));
        }
    }
}