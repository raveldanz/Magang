<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgencyProfile;
use App\Models\AuditLog;
use App\Models\University;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        if ($request->filled('agency_id')) {
            $query->where('agency_profile_id', $request->agency_id);
        }

        // Filter Universitas
        if ($request->filled('university_id')) {
            $univId = $request->university_id;
            $univ = University::find($univId);
            $univName = $univ?->name;
            $like = \DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

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
            $search = $request->search;
            $like = \DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
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
            'currentUser'
        ));
    }

    /**
     * Form Tambah User Baru
     */
    public function create()
    {
        $agencies = AgencyProfile::all();
        $universities = University::all();
        return view('admin.users.create', compact('agencies', 'universities'));
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

        return redirect()->route('admin.users.index')
            ->with('success', "Akun '{$user->name}' ({$user->role}) berhasil dibuat!");
    }

    /**
     * Form Edit User
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        $agencies = AgencyProfile::all();
        $universities = University::all();
        return view('admin.users.edit', compact('user', 'agencies', 'universities'));
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

        return redirect()->route('admin.users.index')
            ->with('success', "Data pengguna '{$user->name}' berhasil diperbarui!");
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
     * Hapus Pengguna
     */
    public function destroy($id)
    {
        $currentUser = Auth::user();
        $user = User::findOrFail($id);

        if ($user->id === $currentUser->id) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus akun Anda sendiri.');
        }

        // Cek proteksi relasi aktif
        if ($user->applications()->count() > 0 || $user->academicPlacements()->count() > 0 || $user->mentorPlacements()->count() > 0) {
            return redirect()->back()->with('error', "Gagal menghapus: Akun '{$user->name}' memiliki data relasi pengajuan atau penempatan aktif.");
        }

        $name = $user->name;
        $user->delete();

        AuditLog::record('USER_DELETE', 'User', $id, ['name' => $name]);

        return redirect()->route('admin.users.index')
            ->with('success', "Akun '{$name}' berhasil dihapus dari sistem.");
    }
}
