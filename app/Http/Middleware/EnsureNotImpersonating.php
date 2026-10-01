<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Chat hanya-baca selama Super Admin memakai "Login As": tidak boleh mengirim, menghapus,
 * melaporkan, atau mengubah grup atas nama pengguna lain.
 */
class EnsureNotImpersonating
{
    public const MESSAGE = 'Mode Login As: chat hanya dapat dibaca. Kembali ke akun Anda untuk mengirim pesan.';

    public function handle(Request $request, Closure $next): Response
    {
        abort_if($request->hasSession() && $request->session()->has('impersonator_id'), 403, self::MESSAGE);

        return $next($request);
    }
}
