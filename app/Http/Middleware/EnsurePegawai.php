<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsurePegawai
{
    /**
     * Handle an incoming request.
     * Pastikan user login dan role = 'pegawai'
     */
    public function handle($request, Closure $next, ...$guards)
    {
        if (Auth::check()) {
            $user = Auth::user();

            if ($user->role === 'pasien') {
                return redirect()->route('pasien.riwayat');
            }

            if ($user->role === 'adminPoli') {
                return redirect()->route('poliklinik.dashboard');
            }

            if ($user->role === 'adminKepegawaian') {
                return redirect()->route('kepegawaian.dashboard');
            }

            return redirect('/');
        }

        return $next($request);
    }

}