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
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if (!$user) {
            // belum login
            return redirect()->route('login')->with('warning', 'Silakan login terlebih dahulu.');
        }

        // Asumsi ada kolom 'role' di tabel users
        if ($user->role !== 'pegawai') {
            abort(403, 'Akses dibatasi untuk pegawai.');
        }

        return $next($request);
    }
}