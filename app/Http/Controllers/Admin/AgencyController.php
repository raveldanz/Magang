<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgencyProfile;
use App\Models\AuditLog;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgencyController extends Controller
{
    /**
     * Master Data Instansi Dinas Pemerintah Kota
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isSuperAdmin = ($user->role === 'super_admin' || ($user->role === 'admin' && is_null($user->agency_profile_id)));

        $query = AgencyProfile::with(['units', 'users'])->withCount('units');

        if ($request->filled('search')) {
            $search = $request->search;
            $like = \DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
            $query->where(function ($q) use ($search, $like) {
                $q->where('agency_name', $like, "%{$search}%")
                  ->orWhere('government_name', $like, "%{$search}%")
                  ->orWhere('email', $like, "%{$search}%")
                  ->orWhere('city', $like, "%{$search}%");
            });
        }

        $agencies = $query->get()->map(function ($ag) {
            $ag->total_quota = $ag->units->sum('quota');
            $ag->total_mentors = $ag->users->whereIn('role', ['mentor', 'pembimbing'])->count();
            $ag->total_admins = $ag->users->where('role', 'admin')->count();
            return $ag;
        });

        return view('admin.agencies.index', compact('agencies', 'isSuperAdmin'));
    }

    public function create()
    {
        return view('admin.agencies.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'agency_name' => 'required|string|max:255',
            'government_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:agency_profiles,email',
            'phone' => 'nullable|string|max:50',
            'address' => 'required|string',
            'signee_name' => 'nullable|string|max:255',
            'signee_nip' => 'nullable|string|max:50',
            'signee_position' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'city' => 'required|string|max:100',
        ]);

        $agency = AgencyProfile::create([
            'government_name' => $request->government_name ?? 'Pemerintah Kota Surabaya',
            'agency_name' => $request->agency_name,
            'email' => strtolower(trim($request->email)),
            'phone' => $request->phone,
            'address' => $request->address,
            'signee_name' => $request->signee_name ?? 'Kepala Dinas',
            'signee_nip' => $request->signee_nip ?? '-',
            'signee_position' => $request->signee_position ?? 'Kepala Dinas',
            'website' => $request->website,
            'city' => $request->city ?? 'Surabaya',
        ]);

        AuditLog::record('AGENCY_CREATE', 'AgencyProfile', $agency->id, [
            'name' => $agency->agency_name,
            'email' => $agency->email,
        ]);

        return redirect()->route('admin.agencies.index')
            ->with('success', "Instansi Dinas '{$agency->agency_name}' berhasil ditambahkan ke sistem!");
    }

    public function edit($id)
    {
        $agency = AgencyProfile::with('units')->findOrFail($id);
        return view('admin.agencies.edit', compact('agency'));
    }

    public function update(Request $request, $id)
    {
        $agency = AgencyProfile::findOrFail($id);

        $request->validate([
            'agency_name' => 'required|string|max:255',
            'government_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:agency_profiles,email,' . $agency->id,
            'phone' => 'nullable|string|max:50',
            'address' => 'required|string',
            'signee_name' => 'nullable|string|max:255',
            'signee_nip' => 'nullable|string|max:50',
            'signee_position' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'city' => 'required|string|max:100',
        ]);

        $agency->update([
            'government_name' => $request->government_name ?? $agency->government_name,
            'agency_name' => $request->agency_name,
            'email' => strtolower(trim($request->email)),
            'phone' => $request->phone,
            'address' => $request->address,
            'signee_name' => $request->signee_name ?? $agency->signee_name ?? 'Kepala Dinas',
            'signee_nip' => $request->signee_nip ?? $agency->signee_nip ?? '-',
            'signee_position' => $request->signee_position ?? $agency->signee_position ?? 'Kepala Dinas',
            'website' => $request->website,
            'city' => $request->city ?? $agency->city,
        ]);

        AuditLog::record('AGENCY_UPDATE', 'AgencyProfile', $agency->id, [
            'name' => $agency->agency_name,
        ]);

        return redirect()->route('admin.agencies.index')
            ->with('success', "Data Instansi '{$agency->agency_name}' berhasil diperbarui!");
    }

    public function destroy($id)
    {
        $agency = AgencyProfile::withCount(['units', 'users'])->findOrFail($id);

        if ($agency->units_count > 0 || $agency->users_count > 0) {
            return redirect()->back()->with('error', "Gagal menghapus: Instansi ini masih memiliki {$agency->units_count} unit kerja dan {$agency->users_count} akun terdaftar.");
        }

        $name = $agency->agency_name;
        $agency->delete();

        AuditLog::record('AGENCY_DELETE', 'AgencyProfile', $id, ['name' => $name]);

        return redirect()->route('admin.agencies.index')
            ->with('success', "Instansi '{$name}' berhasil dihapus dari sistem.");
    }
}
