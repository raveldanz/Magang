<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgencyProfile;
use App\Models\AuditLog;
use App\Models\University;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Master Data Seluruh Pengguna Sistem SIP-MAGANG (Multi-Role)
     */
    public function index(Request $request)
    {
        $currentUser = Auth::user();
        $isSuperAdmin = ($currentUser->role === 'super_admin' || ($currentUser->role === 'admin' && is_null($currentUser->agency_profile_id)));

        $query = User::with(['agencyProfile', 'university', 'studentProfile']);

        // Filter Role
        if ($request->filled('role')) {
            $roleFilter = $request->role;
            if ($roleFilter === 'mentor') {
                $query->whereIn('role', ['mentor', 'pembimbing']);
            } elseif ($roleFilter === 'dosen') {
                $query->whereIn('role', ['dosen', 'academic_advisor']);
            } else {
                $query->where('role', $roleFilter);
            }
        }

        // Filter Instansi
        $selectedAgency = null;
        if ($request->filled('agency_id')) {
            $query->where('agency_profile_id', $request->agency_id);
            $selectedAgency = AgencyProfile::find($request->agency_id);
        }

        // Filter Universitas
        $selectedUniversity = null;
        if ($request->filled('university_id')) {
            $univId = $request->university_id;
            $univ = University::find($univId);
            $selectedUniversity = $univ;
            $univName = $univ?->name;
            $like = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

            $query->where(function ($q) use ($univId, $univName, $like) {
                $q->where('university_id', $univId);
                if ($univName) {
                    $q->orWhere('university', $like, "%{$univName}%")
                      ->orWhereHas('studentProfile', fn($sp) => $sp->where('universitas', $like, "%{$univName}%"));
                }
            });
        }

        // Search
        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $like = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
            $query->where(function ($q) use ($search, $like) {
                $q->where('name', $like, "%{$search}%")
                  ->orWhere('email', $like, "%{$search}%")
                  ->orWhereHas('studentProfile', fn($sp) => $sp->where('nim', $like, "%{$search}%"));
            });
        }

        $users = $query->latest()->paginate(15)->withQueryString();
        $agencies = AgencyProfile::all();
        $universities = University::all();

        return view('admin.users.index', compact(
            'users',
            'agencies',
            'universities',
            'isSuperAdmin',
            'currentUser',
            'selectedAgency',
            'selectedUniversity'
        ));
    }

    /**
     * Form Tambah User Baru
     */
    public function create(Request $request)
    {
        $agencies = AgencyProfile::all();
        $universities = University::all();
        $returnTo = $request->query('return_to', url()->previous());

        return view('admin.users.create', compact('agencies', 'universities', 'returnTo'));
    }

    /**
     * Simpan User Baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'role' => 'required|in:admin,mentor,dosen,universitas,mahasiswa',
            'password' => 'nullable|string|min:6',
            'agency_profile_id' => 'nullable|exists:agency_profiles,id',
            'university_id' => 'nullable|exists:universities,id',
            'status' => 'nullable|string|in:active,on_leave,inactive',
            'return_to' => 'nullable|string',
        ]);

        $password = $request->filled('password') ? $request->password : 'password';

        $user = User::create([
            'name' => $request->name,
            'email' => strtolower(trim($request->email)),
            'password' => Hash::make($password),
            'role' => $request->role,
            'agency_profile_id' => in_array($request->role, ['admin', 'mentor']) ? $request->agency_profile_id : null,
            'university_id' => in_array($request->role, ['universitas', 'dosen', 'mahasiswa']) ? $request->university_id : null,
            'status' => $request->status ?? 'active',
            'email_verified_at' => now(),
        ]);

        AuditLog::record('USER_CREATE', 'User', $user->id, [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ]);

        $successMsg = "Akun '{$user->name}' ({$user->role}) berhasil dibuat!";

        // 1. Prioritas return_to dari halaman pemanggil
        if ($request->filled('return_to') && !str_contains($request->return_to, 'users/create')) {
            return redirect($request->return_to)->with('success', $successMsg);
        }

        // 2. Fallback: Balik ke Instansi jika memiliki agency_profile_id
        if ($user->agency_profile_id) {
            return redirect()->route('admin.agencies.show', $user->agency_profile_id)->with('success', $successMsg);
        }

        // 3. Fallback: Balik ke Perguruan Tinggi jika memiliki university_id
        if ($user->university_id && in_array($user->role, ['universitas', 'dosen'])) {
            return redirect()->route('admin.universities.show', $user->university_id)->with('success', $successMsg);
        }

        // 4. Fallback: Halaman Master Pengguna
        return redirect()->route('admin.users.index')->with('success', $successMsg);
    }

    /**
     * Form Edit User
     */
    public function edit(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $agencies = AgencyProfile::all();
        $universities = University::all();
        $returnTo = $request->query('return_to', url()->previous());

        return view('admin.users.edit', compact('user', 'agencies', 'universities', 'returnTo'));
    }

    /**
     * Update Data User
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,mentor,dosen,universitas,mahasiswa,super_admin',
            'agency_profile_id' => 'nullable|exists:agency_profiles,id',
            'university_id' => 'nullable|exists:universities,id',
            'status' => 'nullable|string|in:active,on_leave,inactive',
            'password' => 'nullable|string|min:6',
            'return_to' => 'nullable|string',
        ]);

        $updateData = [
            'name' => $request->name,
            'email' => strtolower(trim($request->email)),
            'role' => $request->role,
            'agency_profile_id' => in_array($request->role, ['admin', 'mentor']) ? $request->agency_profile_id : null,
            'university_id' => in_array($request->role, ['universitas', 'dosen', 'mahasiswa']) ? $request->university_id : null,
            'status' => $request->status ?? 'active',
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        AuditLog::record('USER_UPDATE', 'User', $user->id, [
            'name' => $user->name,
            'role' => $user->role,
        ]);

        $successMsg = "Data pengguna '{$user->name}' berhasil diperbarui!";

        // 1. Prioritas return_to dari halaman pemanggil
        if ($request->filled('return_to') && !str_contains($request->return_to, 'users/' . $id . '/edit')) {
            return redirect($request->return_to)->with('success', $successMsg);
        }

        // 2. Fallback: Balik ke Instansi jika memiliki agency_profile_id
        if ($user->agency_profile_id) {
            return redirect()->route('admin.agencies.show', $user->agency_profile_id)->with('success', $successMsg);
        }

        // 3. Fallback: Balik ke Perguruan Tinggi jika memiliki university_id
        if ($user->university_id && in_array($user->role, ['universitas', 'dosen'])) {
            return redirect()->route('admin.universities.show', $user->university_id)->with('success', $successMsg);
        }

        // 4. Fallback: Halaman Master Pengguna
        return redirect()->route('admin.users.index')->with('success', $successMsg);
    }

    /**
     * Reset Password Akun ke Default ('password')
     */
    public function resetPassword($id)
    {
        $user = User::findOrFail($id);
        $user->update(['password' => Hash::make('password')]);

        AuditLog::record('USER_PASSWORD_RESET', 'User', $user->id, [
            'name' => $user->name,
            'email' => $user->email,
        ]);

        return redirect()->back()
            ->with('success', "Password untuk '{$user->name}' berhasil direset ke 'password'.");
    }

    /**
     * Reset Password Massal (Bulk Reset Password ke 'password')
     */
    public function bulkResetPassword(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'required|integer|exists:users,id',
        ]);

        $currentUser = Auth::user();
        $targetIds = array_unique($request->user_ids);

        // Filter keamanan: larang reset akun sendiri atau akun Super Admin lain
        $users = User::whereIn('id', $targetIds)
            ->where('id', '!=', $currentUser->id)
            ->where(function ($q) {
                $q->where('role', '!=', 'super_admin')
                  ->where(function ($sq) {
                      $sq->where('role', '!=', 'admin')
                         ->orWhereNotNull('agency_profile_id');
                  });
            })
            ->get();

        if ($users->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada akun valid yang dapat direset.');
        }

        $resetCount = 0;
        $affectedNames = [];
        $hashedPassword = Hash::make('password');

        foreach ($users as $user) {
            $user->update(['password' => $hashedPassword]);
            $resetCount++;
            $affectedNames[] = "{$user->name} ({$user->email})";
        }

        AuditLog::record('USER_BULK_PASSWORD_RESET', 'User', null, [
            'total_reset' => $resetCount,
            'affected_users' => array_slice($affectedNames, 0, 50),
        ]);

        return redirect()->back()
            ->with('success', "Berhasil mereset password untuk {$resetCount} akun pengguna ke nilai default ('password').");
    }

    /**
     * Hapus Satu Pengguna (Single Delete)
     */
    public function destroy(Request $request, $id)
    {
        $currentUser = Auth::user();
        $user = User::findOrFail($id);

        // Proteksi: tidak bisa hapus diri sendiri
        if ($user->id === $currentUser->id) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus akun Anda sendiri.');
        }

        // Proteksi: tidak bisa hapus Super Admin
        if ($user->role === 'super_admin') {
            return redirect()->back()->with('error', 'Akun Super Admin tidak dapat dihapus.');
        }

        // Proteksi: tidak bisa hapus Admin Sistem (admin tanpa agency_profile_id)
        if ($user->role === 'admin' && is_null($user->agency_profile_id)) {
            return redirect()->back()->with('error', 'Akun Admin Sistem tidak dapat dihapus melalui panel ini.');
        }

        // Proteksi: cek relasi data aktif (pengajuan, penempatan dosen/mentor)
        $hasRelations = ($user->applications()->count() > 0
            || $user->academicPlacements()->count() > 0
            || $user->mentorPlacements()->count() > 0);

        if ($hasRelations) {
            return redirect()->back()->with('error', "Akun '{$user->name}' tidak dapat dihapus karena memiliki relasi data magang/penempatan aktif.");
        }

        $deletedName = $user->name;
        $deletedEmail = $user->email;
        $deletedRole = $user->role;
        $agencyId = $user->agency_profile_id;
        $universityId = $user->university_id;

        AuditLog::record('USER_DELETE', 'User', $user->id, [
            'name'  => $deletedName,
            'email' => $deletedEmail,
            'role'  => $deletedRole,
        ]);

        $user->delete();

        $successMsg = "Akun '{$deletedName}' ({$deletedRole}) berhasil dihapus.";

        // Kembalikan ke halaman asal jika ada parameter return_to atau redirect back
        if ($request->filled('return_to')) {
            return redirect($request->return_to)->with('success', $successMsg);
        }

        return redirect()->back()->with('success', $successMsg);
    }

    /**
     * Hapus Pengguna Massal (Bulk Delete)
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'required|integer|exists:users,id',
        ]);

        $currentUser = Auth::user();
        $targetIds = array_unique($request->user_ids);

        // Filter keamanan: larang hapus akun sendiri atau akun Super Admin lain
        $users = User::whereIn('id', $targetIds)
            ->where('id', '!=', $currentUser->id)
            ->where(function ($q) {
                $q->where('role', '!=', 'super_admin')
                  ->where(function ($sq) {
                      $sq->where('role', '!=', 'admin')
                         ->orWhereNotNull('agency_profile_id');
                  });
            })
            ->get();

        if ($users->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada akun yang diizinkan untuk dihapus (akun Super Admin dilindungi).');
        }

        $deletedCount = 0;
        $skippedCount = 0;
        $deletedNames = [];
        $skippedNames = [];

        foreach ($users as $user) {
            // Cek proteksi relasi aktif (pengajuan magang, penempatan mentor/dosen)
            $hasRelations = ($user->applications()->count() > 0 
                || $user->academicPlacements()->count() > 0 
                || $user->mentorPlacements()->count() > 0);

            if ($hasRelations) {
                $skippedCount++;
                $skippedNames[] = $user->name;
                continue;
            }

            $deletedNames[] = "{$user->name} ({$user->email})";
            $user->delete();
            $deletedCount++;
        }

        if ($deletedCount > 0) {
            AuditLog::record('USER_BULK_DELETE', 'User', null, [
                'total_deleted' => $deletedCount,
                'total_skipped' => $skippedCount,
                'deleted_users' => array_slice($deletedNames, 0, 50),
            ]);
        }

        $message = "Berhasil menghapus {$deletedCount} akun pengguna.";
        if ($skippedCount > 0) {
            $message .= " Namun {$skippedCount} akun dilewati karena memiliki relasi data magang/penempatan aktif (" . implode(', ', array_slice($skippedNames, 0, 3)) . ").";
            return redirect()->back()->with('warning', $message);
        }

        return redirect()->back()->with('success', $message);
    }
}