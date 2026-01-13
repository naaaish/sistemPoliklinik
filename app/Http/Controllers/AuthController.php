<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Form login
    public function showLogin()
    {
        // Jika sudah login, langsung lempar ke halaman sesuai role agar tidak login dua kali
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user()->role);
        }
        return view('auth.login');
    }

    // Proses login
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $role = strtolower(Auth::user()->role);

            if ($role === 'pasien') {
                return redirect()->route('pasien.riwayat');
            }

            if ($role === 'adminpoli') {
                return redirect()->route('poliklinik.dashboard');
            }

            if ($role === 'adminkepegawaian') {
                return redirect()->route('kepegawaian.dashboard');
            }

            return redirect('/');
        }

        return back()->withErrors(['username' => 'Login gagal']);
    }


    /**
     * Helper untuk mengatur arah redirect berdasarkan role
     */
    private function redirectBasedOnRole($role)
    {
        $role = strtolower($role); // Pastikan huruf kecil

        if ($role === 'pasien') {
            return redirect()->route('pasien.riwayat');
        }

        if ($role === 'adminpoli') {
            return redirect()->route('poliklinik.dashboard');
        }

        if ($role === 'adminkepegawaian') {
            return redirect()->route('kepegawaian.dashboard');
        }

        // fallback jika role tidak dikenali
        return redirect('/');
    }

    // Logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Logout berhasil!');
    }
}