<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Akun yang masih memakai password bawaan / password sementara dari staf wajib mengganti
 * passwordnya sebelum memakai fitur lain. Diaktifkan lewat config('auth.force_password_change')
 * (bawaan: aktif di production; nonaktif di local/testing agar akun demo tetap mudah dipakai).
 */
class EnsurePasswordChanged
{
    public const MESSAGE = 'Demi keamanan, ganti password sementara/bawaan Anda terlebih dahulu sebelum melanjutkan.';

    /** Route yang tetap boleh diakses selama password belum diganti. */
    private const ALLOWED_ROUTES = [
        'profile.edit',
        'password.update',
        'logout',
        'verification.notice',
        'verification.verify',
        'verification.send',
        'password.confirm',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! config('auth.force_password_change') || ! $user || ! $user->must_change_password) {
            return $next($request);
        }

        // Super Admin yang sedang "Login As" tidak dipaksa mengganti password akun yang diperiksanya
        if ($request->hasSession() && $request->session()->has('impersonator_id')) {
            return $next($request);
        }

        if ($request->routeIs(...self::ALLOWED_ROUTES)) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['status' => 'error', 'message' => self::MESSAGE], 403);
        }

        return redirect()->route('profile.edit')->with('error', self::MESSAGE);
    }
}
