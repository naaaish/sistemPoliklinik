<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnsureAdminPoli
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $role = strtolower(Auth::user()->role ?? '');

        if ($role === 'adminpoli') {
            return $next($request);
        }

        // kalau bukan adminpoli, lempar ke dashboard sesuai role (atau 403)
        if ($role === 'adminkepegawaian') {
            return redirect()->route('kepegawaian.dashboard');
        }

        if ($role === 'pasien') {
            return redirect()->route('poliklinik.dashboard');
        }

        return redirect()->route('login');
    }
}
