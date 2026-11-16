<?php

use App\Http\Middleware\CheckRole;
use App\Http\Middleware\EnsureAccountIsActive;
use App\Http\Middleware\EnsurePasswordChanged;
use App\Support\UploadLimits;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => CheckRole::class,
            'active' => EnsureAccountIsActive::class,
        ]);

        // Akun berstatus Nonaktif diputus sesinya di seluruh halaman web (termasuk sesi "Ingat saya")
        $middleware->web(append: [
            EnsureAccountIsActive::class,
            EnsurePasswordChanged::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // PostgreSQL menolak nilai bukan angka (atau terlalu besar) pada kolom ID dengan SQLSTATE 22P02/22003,
        // sehingga alamat seperti /projects/abc berujung galat 500. SQLite diam-diam mengembalikan "tidak ada".
        // Samakan keduanya menjadi 404.
        $exceptions->map(QueryException::class, function (QueryException $e) {
            return in_array((string) $e->getCode(), ['22P02', '22003'], true)
                ? new NotFoundHttpException('Data tidak ditemukan.', $e)
                : $e;
        });

        // Tangani berkas unggahan yang melebihi batas upload PHP (post_max_size) agar tidak memunculkan HTTP 413
        $exceptions->render(function (PostTooLargeException $e, Request $request) {
            $limitMb = round(UploadLimits::phpPostMaxKb() / 1024, 1);
            $message = 'Total berkas yang dikirim melebihi batas server ('.$limitMb.' MB), sehingga data belum tersimpan.';

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => $message], 413);
            }

            // PHP sudah membuang seluruh isi form, jadi tidak ada old input yang bisa dikembalikan.
            // Isian teks dipulihkan di browser oleh form[data-autosave] (resources/js/forms.js).
            return back()->with('error', $message.' Isian yang sudah diketik dipulihkan otomatis bila tersedia; pilih berkas yang lebih kecil lalu kirim lagi.');
        });

        // Endpoint chat dipanggil via fetch dari halaman web; error validasi/otorisasi harus berupa JSON
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || ($request->is('chat/*') && $request->expectsJson()),
        );
    })->create();
