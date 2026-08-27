<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgencyProfile;
use App\Models\AuditLog;
use App\Models\Placement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class MentorController extends Controller
{
    /**
     * Manajemen Mentor Lapangan Internal Dinas
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isSuperAdmin = ($user->role === 'super_admin' || ($user->role === 'admin' && is_null($user->agency_profile_id)));
        $agencyId = $isSuperAdmin ? $request->agency_id : $user->agency_profile_id;

        $query = User::whereIn('role', ['mentor', 'pembimbing'])->with('agencyProfile');

        if ($agencyId) {
            $query->where('agency_profile_id', $agencyId);
        }

        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $like = \DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
            $query->where(function ($q) use ($search, $like) {
                $q->where('name', $like, "%{$search}%")
                  ->orWhere('email', $like, "%{$search}%");
            });
        }

        $mentors = $query->orderBy('name')->get()->map(function ($m) {
            $m->active_students_count = Placement::where(function($q) use ($m) {
                $q->where('mentor_id', $m->id)->orWhere('pembimbing_id', $m->id);
            })->whereHas('application', function ($aq) {
                $aq->whereIn('status', ['accepted', 'verified']);
            })->count();

            $m->completed_students_count = Placement::where(function($q) use ($m) {
                $q->where('mentor_id', $m->id)->orWhere('pembimbing_id', $m->id);
            })->whereHas('finalreport', function ($fq) {
                $fq->where('status', 'approved');
            })->count();

            return $m;
        });

        $agencies = AgencyProfile::all();
        $currentAgency = $agencyId ? AgencyProfile::find($agencyId) : null;

        return view('admin.mentors.index', compact('mentors', 'agencies', 'currentAgency', 'isSuperAdmin', 'agencyId'));
    }

    public function create()
    {
        $user = Auth::user();
        $isSuperAdmin = ($user->role === 'super_admin' || ($user->role === 'admin' && is_null($user->agency_profile_id)));
        $agencies = AgencyProfile::all();
        $currentAgency = $user->agency_profile_id ? AgencyProfile::find($user->agency_profile_id) : null;

        return view('admin.mentors.create', compact('agencies', 'currentAgency', 'isSuperAdmin'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $isSuperAdmin = ($user->role === 'super_admin' || ($user->role === 'admin' && is_null($user->agency_profile_id)));
        $agencyId = $isSuperAdmin ? $request->agency_profile_id : $user->agency_profile_id;

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'agency_profile_id' => $isSuperAdmin ? 'required|exists:agency_profiles,id' : 'nullable',
            'status' => 'nullable|string|in:active,on_leave,inactive',
        ]);

        $mentor = User::create([
            'name' => trim($request->name),
            'email' => strtolower(trim($request->email)),
            'password' => Hash::make('password'),
            'role' => 'mentor',
            'agency_profile_id' => $agencyId,
            'status' => $request->status ?? 'active',
            'email_verified_at' => now(),
        ]);

        AuditLog::record('MENTOR_CREATE', 'User', $mentor->id, [
            'name' => $mentor->name,
            'agency_id' => $agencyId,
        ]);

        return redirect()->route('admin.mentors.index')->with('success', "Mentor Lapangan '{$mentor->name}' berhasil didaftarkan!");
    }

    public function edit($id)
    {
        $user = Auth::user();
        $isSuperAdmin = ($user->role === 'super_admin' || ($user->role === 'admin' && is_null($user->agency_profile_id)));
        $mentor = User::whereIn('role', ['mentor', 'pembimbing'])->findOrFail($id);

        if (!$isSuperAdmin && $mentor->agency_profile_id !== $user->agency_profile_id) {
            abort(403, 'Anda tidak memiliki hak akses mengedit mentor dinas lain.');
        }

        $agencies = AgencyProfile::all();
        $currentAgency = $mentor->agency_profile_id ? AgencyProfile::find($mentor->agency_profile_id) : null;

        return view('admin.mentors.edit', compact('mentor', 'agencies', 'currentAgency', 'isSuperAdmin'));
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $isSuperAdmin = ($user->role === 'super_admin' || ($user->role === 'admin' && is_null($user->agency_profile_id)));

        $mentor = User::whereIn('role', ['mentor', 'pembimbing'])->findOrFail($id);

        if (!$isSuperAdmin && $mentor->agency_profile_id !== $user->agency_profile_id) {
            abort(403, 'Anda tidak memiliki hak akses mengedit mentor dinas lain.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $mentor->id,
            'agency_profile_id' => $isSuperAdmin ? 'required|exists:agency_profiles,id' : 'nullable',
            'status' => 'nullable|string|in:active,on_leave,inactive',
        ]);

        $mentor->update([
            'name' => trim($request->name),
            'email' => strtolower(trim($request->email)),
            'agency_profile_id' => $isSuperAdmin ? $request->agency_profile_id : $mentor->agency_profile_id,
            'status' => $request->status ?? 'active',
        ]);

        AuditLog::record('MENTOR_UPDATE', 'User', $mentor->id, [
            'name' => $mentor->name,
        ]);

        return redirect()->route('admin.mentors.index')->with('success', "Data Mentor Lapangan '{$mentor->name}' berhasil diperbarui!");
    }

    public function resetPassword($id)
    {
        $user = Auth::user();
        $isSuperAdmin = ($user->role === 'super_admin' || ($user->role === 'admin' && is_null($user->agency_profile_id)));

        $mentor = User::whereIn('role', ['mentor', 'pembimbing'])->findOrFail($id);

        if (!$isSuperAdmin && $mentor->agency_profile_id !== $user->agency_profile_id) {
            abort(403, 'Anda tidak memiliki hak akses mereset password mentor dinas lain.');
        }

        $mentor->update(['password' => Hash::make('password')]);

        AuditLog::record('MENTOR_PASSWORD_RESET', 'User', $mentor->id, [
            'name' => $mentor->name,
        ]);

        return redirect()->back()->with('success', "Password mentor '{$mentor->name}' berhasil direset ke 'password'.");
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $isSuperAdmin = ($user->role === 'super_admin' || ($user->role === 'admin' && is_null($user->agency_profile_id)));

        $mentor = User::whereIn('role', ['mentor', 'pembimbing'])->findOrFail($id);

        if (!$isSuperAdmin && $mentor->agency_profile_id !== $user->agency_profile_id) {
            abort(403, 'Anda tidak memiliki hak akses menghapus mentor dinas lain.');
        }

        // Cek bimbingan aktif
        $activeCount = Placement::where(function($q) use ($mentor) {
            $q->where('mentor_id', $mentor->id)->orWhere('pembimbing_id', $mentor->id);
        })->whereHas('application', function ($aq) {
            $aq->whereIn('status', ['accepted', 'verified']);
        })->count();

        if ($activeCount > 0) {
            return redirect()->back()->with('error', "Gagal menghapus: Mentor '{$mentor->name}' masih membimbing {$activeCount} mahasiswa aktif.");
        }

        $name = $mentor->name;
        $mentor->delete();

        AuditLog::record('MENTOR_DELETE', 'User', $id, ['name' => $name]);

        return redirect()->back()->with('success', "Mentor '{$name}' berhasil dihapus dari sistem.");
    }
}
