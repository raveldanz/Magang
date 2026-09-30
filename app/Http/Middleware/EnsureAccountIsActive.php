<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Memutus sesi akun berstatus Nonaktif (termasuk sesi "Ingat saya" dan token API),
 * karena akun pembimbing/mahasiswa yang punya riwayat magang tidak dihapus demi arsip —
 * status Nonaktif adalah cara resmi mencabut aksesnya.
 */
class EnsureAccountIsActive
{
    public const MESSAGE = 'Akun Anda telah dinonaktifkan oleh administrator. Hubungi admin dinas atau kampus untuk mengaktifkan kembali.';

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Super Admin yang sedang "Login As" tetap boleh memeriksa akun nonaktif
        $isImpersonating = $request->hasSession() && $request->session()->has('impersonator_id');

        if (!$user || !$user->isInactive() || $isImpersonating) {
            return $next($request);
        }

        if ($request->is('api/*') || !$request->hasSession()) {
            $token = $user->currentAccessToken();
            if ($token && method_exists($token, 'delete')) {
                $token->delete();
            }

            return response()->json(['status' => 'error', 'message' => self::MESSAGE], 403);
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->withErrors(['email' => self::MESSAGE]);
    }
}
