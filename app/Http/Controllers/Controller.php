<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

abstract class Controller
{
    /**
     * Super Admin = role 'super_admin' ATAU role 'admin' tanpa agency_profile_id (Admin Sistem).
     */
    protected function currentUserIsSuperAdmin(?User $user = null): bool
    {
        $user ??= Auth::user();

        return $user instanceof User && $user->isSuperAdmin();
    }

    /**
     * Hentikan request dengan 403 jika pengguna aktif bukan Super Admin.
     */
    protected function ensureSuperAdmin(string $message = 'Hanya Super Administrator yang berhak melakukan tindakan ini.'): void
    {
        abort_unless($this->currentUserIsSuperAdmin(), 403, $message);
    }

    /**
     * Kembalikan URL return_to hanya jika mengarah ke aplikasi ini sendiri (mencegah open redirect).
     */
    protected function safeReturnTo(?string $returnTo): ?string
    {
        if (empty($returnTo)) {
            return null;
        }

        if (str_starts_with($returnTo, '/') && !str_starts_with($returnTo, '//')) {
            return $returnTo;
        }

        $appHost = parse_url(url('/'), PHP_URL_HOST);
        $targetHost = parse_url($returnTo, PHP_URL_HOST);

        return ($targetHost && $appHost && strcasecmp($targetHost, $appHost) === 0) ? $returnTo : null;
    }
}
