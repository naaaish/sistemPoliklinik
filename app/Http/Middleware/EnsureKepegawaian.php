<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class EnsureKepegawaian
{
    public function handle($request, Closure $next)
    {
        if (!Auth::check() || Auth::user()->role !== 'adminKepegawaian') {
            abort(403, 'AKSES KHUSUS KEPEGAWAIAN');
        }

        return $next($request);
    }
}
