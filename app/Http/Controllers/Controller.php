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
     * Supervisor yang terikat pada satu dinas hanya boleh membuka/mengesahkan data dinas itu.
     * Supervisor tanpa dinas (pengesah tingkat kota) dan Super Admin tidak dibatasi.
     */
    protected function ensureSupervisorAgencyScope(int|string|null $agencyProfileId): void
    {
        $user = Auth::user();

        if ($this->currentUserIsSuperAdmin($user) || $user?->role !== 'supervisor' || $user->agency_profile_id === null) {
            return;
        }

        abort_unless(
            $agencyProfileId !== null && (int) $agencyProfileId === (int) $user->agency_profile_id,
            403,
            'Anda hanya dapat memproses data magang di instansi Anda sendiri.'
        );
    }

    /**
     * Beri akun password sementara acak dan tampilkan sekali kepada staf yang melakukannya
     * (flash 'temporary_credentials', dirender oleh layouts/partials/temporary-credentials).
     */
    protected function issueTemporaryPasswordFor(User $user): string
    {
        $plain = $user->issueTemporaryPassword();

        $credentials = session('temporary_credentials', []);
        $credentials[] = ['name' => $user->name, 'email' => $user->email, 'password' => $plain];
        session()->flash('temporary_credentials', $credentials);

        return $plain;
    }

    /**
     * Kembalikan URL return_to hanya jika mengarah ke aplikasi ini sendiri (mencegah open redirect).
     */
    protected function safeReturnTo(?string $returnTo): ?string
    {
        if (empty($returnTo)) {
            return null;
        }

        // Karakter kontrol/spasi bisa dipakai menyelundupkan skema (mis. "java\tscript:")
        if (preg_match('/[\x00-\x20\x7F]/', $returnTo)) {
            return null;
        }

        // "/path" relatif aman, tetapi "//host" dan "/\host" dibaca browser sebagai URL ke host lain
        if (str_starts_with($returnTo, '/')) {
            return (str_starts_with($returnTo, '//') || str_starts_with($returnTo, '/\\')) ? null : $returnTo;
        }

        $scheme = strtolower((string) parse_url($returnTo, PHP_URL_SCHEME));
        if (! in_array($scheme, ['http', 'https'], true)) {
            return null;
        }

        $appHost = parse_url(url('/'), PHP_URL_HOST);
        $targetHost = parse_url($returnTo, PHP_URL_HOST);

        return ($targetHost && $appHost && strcasecmp($targetHost, $appHost) === 0) ? $returnTo : null;
    }
}
