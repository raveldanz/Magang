<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'role' => \App\Http\Middleware\CheckRole::class,
        'active' => \App\Http\Middleware\EnsureAccountIsActive::class,
        ]);

    // Akun berstatus Nonaktif diputus sesinya di seluruh halaman web (termasuk sesi "Ingat saya")
    $middleware->web(append: [
        \App\Http\Middleware\EnsureAccountIsActive::class,
    ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Endpoint chat dipanggil via fetch dari halaman web; error validasi/otorisasi harus berupa JSON
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || ($request->is('chat/*') && $request->expectsJson()),
        );
    })->create();
