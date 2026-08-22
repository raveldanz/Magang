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
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            abort(403, 'Anda belum login.');
        }

        $user = Auth::user();
        $userRole = $user->role;

        // Super Admin (role 'super_admin' ATAU role 'admin' dengan agency_profile_id null)
        $isSuperAdmin = ($userRole === 'super_admin' || ($userRole === 'admin' && is_null($user->agency_profile_id)));

        // 1. Direct match
        if (in_array($userRole, $roles)) {
            return $next($request);
        }

        // 2. Super Admin bypass to any admin or super_admin routes
        if ($isSuperAdmin && (in_array('admin', $roles) || in_array('super_admin', $roles))) {
            return $next($request);
        }

        // 3. Role aliases
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

        abort(403, 'Anda tidak memiliki hak akses ke halaman ini.');
    }
}
