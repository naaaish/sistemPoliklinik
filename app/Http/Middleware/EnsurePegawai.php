<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class EnsurePegawai
{
    public function handle($request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $role = strtolower(Auth::user()->role);

        if ($role === 'pasien') {
            return $next($request);
        }

        if ($role === 'adminpoli') {
            return redirect()->route('poliklinik.dashboard');
        }

        if ($role === 'adminkepegawaian') {
            return redirect()->route('kepegawaian.dashboard');
        }

        return redirect()->route('login');
    }
}
