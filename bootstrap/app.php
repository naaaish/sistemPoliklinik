<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\EnsurePegawai;  
use App\Http\Middleware\EnsureKepegawaian;  
use App\Http\Middleware\EnsureAdminPoli;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function ($middleware) {
    $middleware->alias([
        'ensurePegawai' => EnsurePegawai::class,
        'ensureKepegawaian' => EnsureKepegawaian::class,
        'ensureAdminPoli' => EnsureAdminPoli::class,
    ]);
})

    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();


