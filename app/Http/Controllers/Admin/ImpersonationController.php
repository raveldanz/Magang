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

        // Verifikasi hak Super Admin
        $isSuperAdmin = ($currentUser->role === 'super_admin' || ($currentUser->role === 'admin' && is_null($currentUser->agency_profile_id)));

        if (!$isSuperAdmin) {
            abort(403, 'Hanya Super Administrator yang berhak menggunakan fitur impersonasi.');
        }

        $targetUser = User::findOrFail($userId);

        // Larang impersonate diri sendiri atau sesama Super Admin
        $isTargetSuperAdmin = ($targetUser->role === 'super_admin' || ($targetUser->role === 'admin' && is_null($targetUser->agency_profile_id)));
        if ($targetUser->id === $currentUser->id || $isTargetSuperAdmin) {
            return redirect()->back()->with('error', 'Tidak dapat melakukan penyamaran (impersonasi) ke akun Super Admin.');
        }

        // Catat di Audit Log sebelum berganti sesi
        AuditLog::record('IMPERSONATE_START', 'User', $targetUser->id, [
            'impersonator_id' => $currentUser->id,
            'impersonator_name' => $currentUser->name,
            'target_id' => $targetUser->id,
            'target_name' => $targetUser->name,
            'target_email' => $targetUser->email,
            'target_role' => $targetUser->role,
        ]);

        // Simpan data impersonator di session
        session([
            'impersonator_id' => $currentUser->id,
            'impersonator_name' => $currentUser->name,
            'impersonator_email' => $currentUser->email,
        ]);

        // Login sebagai target user
        Auth::loginUsingId($targetUser->id);

        // Redirect ke dashboard masing-masing role
        $role = $targetUser->role;
        if ($role === 'admin') {
            return redirect()->route('admin.applications.index')
                ->with('info', "Anda sekarang masuk sebagai {$targetUser->name} ({$role})");
        } elseif ($role === 'mentor' || $role === 'pembimbing') {
            return redirect()->route('mentor.dashboard')
                ->with('info', "Anda sekarang masuk sebagai {$targetUser->name} ({$role})");
        } elseif ($role === 'dosen' || $role === 'academic_advisor') {
            return redirect()->route('lecturer.dashboard')
                ->with('info', "Anda sekarang masuk sebagai {$targetUser->name} ({$role})");
        } elseif ($role === 'universitas') {
            return redirect()->route('university.dashboard')
                ->with('info', "Anda sekarang masuk sebagai {$targetUser->name} ({$role})");
        } else {
            return redirect()->route('dashboard')
                ->with('info', "Anda sekarang masuk sebagai {$targetUser->name} ({$role})");
        }
    }

    /**
     * Mengakhiri Sesi Impersonasi dan Kembali ke Akun Super Admin
     */
    public function leave(Request $request)
    {
        if (!session()->has('impersonator_id')) {
            abort(403, 'Tidak ada sesi penyamaran yang sedang aktif.');
        }

        $originalId = session('impersonator_id');
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
        session()->forget(['impersonator_id', 'impersonator_name', 'impersonator_email']);

        return redirect()->route('admin.users.index')
            ->with('success', "Sesi penyamaran berakhir. Selamat datang kembali, {$originalUser->name}!");
    }
}
