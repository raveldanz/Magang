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
        $isSuperAdmin = ($currentUser && ($currentUser->role === 'super_admin' || ($currentUser->role === 'admin' && is_null($currentUser->agency_profile_id))));

        if (!$isSuperAdmin && !$originalSuperAdminId) {
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
        $isTargetSuperAdmin = ($targetUser->role === 'super_admin' || ($targetUser->role === 'admin' && is_null($targetUser->agency_profile_id)));
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

        // Login sebagai target user
        Auth::loginUsingId($targetUser->id);

        // Simpan data impersonator di session setelah login
        $request->session()->put('impersonator_id', $impersonatorUser->id);
        $request->session()->put('impersonator_name', $impersonatorUser->name);
        $request->session()->put('impersonator_email', $impersonatorUser->email);
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
            ->with('info', "Anda sekarang masuk sebagai {$targetUser->name} (" . strtoupper($role) . ")");
    }

    /**
     * Mengakhiri Sesi Impersonasi dan Kembali ke Akun Super Admin
     */
    public function leave(Request $request)
    {
        if (!$request->session()->has('impersonator_id')) {
            return redirect()->route('dashboard');
        }

        $originalId = $request->session()->get('impersonator_id');
        $originalUser = User::findOrFail($originalId);
        $impersonatedUser = Auth::user();

        // Catat Audit Log
        AuditLog::record('IMPERSONATE_END', 'User', $impersonatedUser?->id, [
            'original_id' => $originalUser->id,
            'original_name' => $originalUser->name,
            'impersonated_id' => $impersonatedUser?->id,
            'impersonated_name' => $impersonatedUser?->name,
        ]);

        // Login kembali ke super admin
        Auth::loginUsingId($originalUser->id);

        // Hapus session impersonasi
        $request->session()->forget(['impersonator_id', 'impersonator_name', 'impersonator_email']);
        $request->session()->save();

        return redirect()->route('admin.users.index')
            ->with('success', "Sesi penyamaran berakhir. Selamat datang kembali, {$originalUser->name}!");
    }
}

