<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonationController extends Controller
{
    /**
     * Memulai Sesi Impersonasi ("Login As User")
     */
    public function impersonate(Request $request, $userId)
    {
        $currentUser = Auth::user();
        $originalSuperAdminId = $request->session()->get('impersonator_id');

        // Verifikasi hak Super Admin (baik akun asli maupun sesi penyamaran aktif dari Super Admin)
        $isSuperAdmin = ($currentUser?->isSuperAdmin() ?? false);

        if (! $isSuperAdmin && ! $originalSuperAdminId) {
            abort(403, 'Hanya Super Administrator yang berhak menggunakan fitur penyamaran (Login As).');
        }

        // Tentukan ID Super Admin yang asli
        $impersonatorId = $originalSuperAdminId ?? $currentUser->id;
        $impersonatorUser = User::findOrFail($impersonatorId);

        $targetUser = User::findOrFail($userId);

        // Jika menargetkan akun Super Admin asli sendiri, kembalikan ke akun Super Admin
        if ($targetUser->id === $impersonatorId) {
            return $this->leave($request);
        }

        // Larang impersonate sesama Super Admin
        $isTargetSuperAdmin = $targetUser->isSuperAdmin();
        if ($isTargetSuperAdmin) {
            return redirect()->back()->with('error', 'Tidak dapat melakukan penyamaran (impersonasi) ke akun Super Admin lain.');
        }

        // Catat di Audit Log sebelum berganti sesi
        AuditLog::record('IMPERSONATE_START', 'User', $targetUser->id, [
            'impersonator_id' => $impersonatorUser->id,
            'impersonator_name' => $impersonatorUser->name,
            'target_id' => $targetUser->id,
            'target_name' => $targetUser->name,
            'target_email' => $targetUser->email,
            'target_role' => $targetUser->role,
        ]);

        // Simpan URL asal untuk redirect kembali setelah sesi impersonasi selesai
        $returnTo = $request->input('return_to')
            ?? $request->headers->get('referer')
            ?? url()->previous();

        if ($returnTo && (str_contains($returnTo, '/login') || str_contains($returnTo, '/impersonate'))) {
            $returnTo = null;
        }

        // Login sebagai target user + buat ID sesi baru (mencegah session fixation)
        Auth::loginUsingId($targetUser->id);
        $request->session()->regenerate();

        // Simpan data impersonator di session setelah login
        $request->session()->put('impersonator_id', $impersonatorUser->id);
        $request->session()->put('impersonator_name', $impersonatorUser->name);
        $request->session()->put('impersonator_email', $impersonatorUser->email);
        if ($returnTo) {
            $request->session()->put('impersonator_return_to', $returnTo);
        }
        $request->session()->save();

        // Redirect ke dashboard masing-masing role
        $role = $targetUser->role;
        $targetRoute = match ($role) {
            'admin' => route('admin.dashboard'),
            'mentor', 'pembimbing' => route('mentor.dashboard'),
            'dosen', 'academic_advisor' => route('lecturer.dashboard'),
            'universitas' => route('university.dashboard'),
            default => route('dashboard'),
        };

        return redirect()->to($targetRoute)
            ->with('info', "Anda sekarang masuk sebagai {$targetUser->name} (".strtoupper($role).')');
    }

    /**
     * Mengakhiri Sesi Impersonasi dan Kembali ke Akun Super Admin
     */
    public function leave(Request $request)
    {
        if (! $request->session()->has('impersonator_id')) {
            return redirect()->route('dashboard');
        }

        $originalId = $request->session()->get('impersonator_id');
        $originalUser = User::findOrFail($originalId);
        $impersonatedUser = Auth::user();

        // Tentukan URL tujuan kembali sebelum sesi penyamaran dihapus
        $returnUrl = $request->session()->get('impersonator_return_to');

        // Fallback kontekstual cerdas jika return_to belum tersimpan di session
        if (! $returnUrl) {
            if ($impersonatedUser?->agency_profile_id) {
                $returnUrl = route('admin.agencies.show', $impersonatedUser->agency_profile_id);
            } elseif ($impersonatedUser?->university_id) {
                $returnUrl = route('admin.universities.show', $impersonatedUser->university_id);
            } elseif ($impersonatedUser && in_array($impersonatedUser->role, ['mentor', 'pembimbing'])) {
                $returnUrl = route('admin.mentors.index');
            } else {
                $returnUrl = route('admin.dashboard');
            }
        }

        // Catat Audit Log
        AuditLog::record('IMPERSONATE_END', 'User', $impersonatedUser?->id, [
            'original_id' => $originalUser->id,
            'original_name' => $originalUser->name,
            'impersonated_id' => $impersonatedUser?->id,
            'impersonated_name' => $impersonatedUser?->name,
        ]);

        // Login kembali ke super admin + buat ID sesi baru (mencegah session fixation)
        Auth::loginUsingId($originalUser->id);
        $request->session()->regenerate();

        // Hapus session impersonasi
        $request->session()->forget(['impersonator_id', 'impersonator_name', 'impersonator_email', 'impersonator_return_to']);
        $request->session()->save();

        return redirect()->to($returnUrl)
            ->with('success', "Sesi penyamaran berakhir. Selamat datang kembali, {$originalUser->name}!");
    }
}
