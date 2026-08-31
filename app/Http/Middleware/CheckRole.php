<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request with Super Admin bypass and role aliases.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!Auth::check()) {
            abort(403, 'Anda belum login.');
        }

        $user = Auth::user();
        $userRole = $user->role;

        // Super Admin bypass (kecuali jika sedang menyamar sebagai role lain)
        $isSuperAdmin = ($userRole === 'super_admin' || ($userRole === 'admin' && is_null($user->agency_profile_id)));

        if (in_array($userRole, $roles)) {
            return $next($request);
        }

        if ($isSuperAdmin && (in_array('admin', $roles) || in_array('super_admin', $roles))) {
            return $next($request);
        }

        // Aliases
        if (in_array('mentor', $roles) && in_array($userRole, ['mentor', 'pembimbing'])) {
            return $next($request);
        }
        if (in_array('pembimbing', $roles) && in_array($userRole, ['mentor', 'pembimbing'])) {
            return $next($request);
        }
        if (in_array('dosen', $roles) && in_array($userRole, ['dosen', 'academic_advisor'])) {
            return $next($request);
        }
        if (in_array('academic_advisor', $roles) && in_array($userRole, ['dosen', 'academic_advisor'])) {
            return $next($request);
        }

        // Jika sedang dalam mode penyamaran (impersonation) dan mengakses halaman luar wewenang role target,
        // redirect ke dashboard aktifnya agar tidak mengalami blank error 403.
        if ($request->session()->has('impersonator_id')) {
            $dest = match ($userRole) {
                'admin' => route('admin.dashboard'),
                'mentor', 'pembimbing' => route('mentor.dashboard'),
                'dosen', 'academic_advisor' => route('lecturer.dashboard'),
                'universitas' => route('university.dashboard'),
                default => route('dashboard'),
            };

            return redirect()->to($dest)
                ->with('error', 'Halaman yang Anda tuju tidak tersedia untuk peran ' . strtoupper($userRole) . '. Anda tetap dapat kembali ke Super Admin melalui bilah merah di atas.');
        }

        abort(403, 'Anda tidak memiliki hak akses ke halaman ini.');
    }
}