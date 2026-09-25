<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\AuditLog;
use App\Models\Placement;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class AgencyController extends Controller
{
    /**
     * Master Data Instansi Dinas Pemerintah Kota
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isSuperAdmin = ($user->role === 'super_admin' || ($user->role === 'admin' && is_null($user->agency_profile_id)));

        $query = AgencyProfile::with(['units', 'users', 'agencyAdmin'])
            ->withCount('units')
            ->withExists(['users as has_admin_account' => function ($q) {
                $q->where('role', 'admin');
            }])
            ->orderBy('has_admin_account', 'asc')
            ->orderBy('updated_at', 'desc');

        if ($request->filled('search')) {
            $search = strtolower($request->search);
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

        // Count un-provisioned agency accounts
        $unregisteredCount = AgencyProfile::whereDoesntHave('users', function ($q) {
            $q->where('role', 'admin');
        })->count();

        // Macro Statistics se-Pemerintah Kota Surabaya
        $macroStats = [
            'total_agencies' => AgencyProfile::count(),
            'total_units' => Unit::count(),
            'total_quota' => Unit::sum('quota'),
            'total_filled' => Application::where('status', 'accepted')->count(),
            'total_staff' => User::whereNotNull('agency_profile_id')->whereIn('role', ['admin', 'mentor', 'pembimbing'])->count(),
        ];

        return view('admin.agencies.index', compact('agencies', 'isSuperAdmin', 'unregisteredCount', 'macroStats'));
    }

    /**
     * Pusat Kendali & Manajemen Alur Terpadu Instansi Dinas (Agency Management Hub)
     */
    public function show($id)
    {
        $user = Auth::user();
        $isSuperAdmin = ($user->role === 'super_admin' || ($user->role === 'admin' && is_null($user->agency_profile_id)));

        // Multi-Tenant Check: Non-superadmin hanya boleh mengakses dinasnya sendiri
        if (!$isSuperAdmin && (int)$user->agency_profile_id !== (int)$id) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengelola instansi ini.');
        }

        $agency = AgencyProfile::with([
            'units' => function ($uq) {
                $uq->withCount(['applications as accepted_count' => function ($aq) {
                    $aq->where('status', 'accepted');
                }])->orderBy('name');
            },
            'users' => function ($uq) {
                $uq->whereIn('role', ['admin', 'mentor', 'pembimbing'])->orderBy('name');
            },
        ])->findOrFail($id);

        // Ambil data pengajuan magang yang masuk ke unit-unit dinas ini
        $unitIds = $agency->units->pluck('id');
        $applications = Application::with([
            'user.studentProfile',
            'unit',
            'placement.mentor',
            'placement.pembimbing'
        ])
            ->whereIn('unit_id', $unitIds)
            ->latest()
            ->get();

        // Ambil mahasiswa yang berstatus aktif (accepted)
        $activePlacements = Placement::with([
            'application.user.studentProfile',
            'application.unit',
            'mentor',
            'pembimbing',
            'logbooks'
        ])
            ->whereHas('application', function ($aq) use ($unitIds) {
                $aq->whereIn('unit_id', $unitIds)->where('status', 'accepted');
            })
            ->latest()
            ->get();

        // Metrik khusus dinas ini
        $totalUnits = $agency->units->count();
        $totalQuota = $agency->units->sum('quota');
        $totalFilled = $agency->units->sum('accepted_count');
        $totalRemaining = max(0, $totalQuota - $totalFilled);

        $adminUsers = $agency->users->where('role', 'admin')->values();
        $mentorUsers = $agency->users->whereIn('role', ['mentor', 'pembimbing'])->values();

        $pendingAppsCount = $applications->where('status', 'submitted')->count();
        $acceptedAppsCount = $applications->where('status', 'accepted')->count();

        $stats = [
            'total_units' => $totalUnits,
            'total_quota' => $totalQuota,
            'total_filled' => $totalFilled,
            'total_remaining' => $totalRemaining,
            'total_admins' => $adminUsers->count(),
            'total_mentors' => $mentorUsers->count(),
            'total_applications' => $applications->count(),
            'pending_applications' => $pendingAppsCount,
            'accepted_applications' => $acceptedAppsCount,
            'active_students' => $activePlacements->count(),
        ];

        return view('admin.agencies.show', compact(
            'agency',
            'isSuperAdmin',
            'stats',
            'adminUsers',
            'mentorUsers',
            'applications',
            'activePlacements'
        ));
    }

    public function create()
    {
        $this->ensureSuperAdmin('Hanya Super Administrator yang dapat menambah instansi baru.');
        return view('admin.agencies.create');
    }

    public function store(Request $request)
    {
        $this->ensureSuperAdmin('Hanya Super Administrator yang dapat menambah instansi baru.');
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
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
        ]);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $cleanName = strtolower(preg_replace('/[^A-Za-z0-9]/', '', $request->agency_name ?? 'agency'));
            $filename = $cleanName . '_' . time() . '.' . $file->getClientOriginalExtension();
            $targetDir = public_path('images/logos');
            if (!File::exists($targetDir)) {
                File::makeDirectory($targetDir, 0755, true);
            }
            $file->move($targetDir, $filename);
            $logoPath = 'images/logos/' . $filename;
        }

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
            'logo' => $logoPath,
        ]);

        AuditLog::record('AGENCY_CREATE', 'AgencyProfile', $agency->id, [
            'name' => $agency->agency_name,
            'email' => $agency->email,
        ]);

        return redirect()->route('admin.agencies.index')
            ->with('success', "Instansi Dinas '{$agency->agency_name}' berhasil ditambahkan ke sistem! Anda dapat segera membuatkan akun Admin Dinas melalui tombol 'Buat Akun'.");
    }

    public function edit($id)
    {
        abort_unless($this->currentUserIsSuperAdmin() || (int) Auth::user()->agency_profile_id === (int) $id, 403, 'Anda tidak memiliki hak akses untuk mengelola instansi ini.');
        $agency = AgencyProfile::with('units')->findOrFail($id);
        return view('admin.agencies.edit', compact('agency'));
    }

    public function update(Request $request, $id)
    {
        abort_unless($this->currentUserIsSuperAdmin() || (int) Auth::user()->agency_profile_id === (int) $id, 403, 'Anda tidak memiliki hak akses untuk mengelola instansi ini.');
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
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
        ]);

        $data = [
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
        ];

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $cleanName = strtolower(preg_replace('/[^A-Za-z0-9]/', '', $request->agency_name ?? 'agency'));
            $filename = $cleanName . '_' . time() . '.' . $file->getClientOriginalExtension();
            $targetDir = public_path('images/logos');
            if (!File::exists($targetDir)) {
                File::makeDirectory($targetDir, 0755, true);
            }
            $file->move($targetDir, $filename);
            $data['logo'] = 'images/logos/' . $filename;
        }

        $agency->update($data);

        AuditLog::record('AGENCY_UPDATE', 'AgencyProfile', $agency->id, [
            'name' => $agency->agency_name,
        ]);

        return redirect()->route('admin.agencies.index')
            ->with('success', "Data Instansi '{$agency->agency_name}' berhasil diperbarui!");
    }

    public function destroy($id)
    {
        $this->ensureSuperAdmin('Hanya Super Administrator yang dapat menghapus instansi.');
        $agency = AgencyProfile::with(['units'])->findOrFail($id);

        // 1. Proteksi Unit dengan Mahasiswa Aktif
        $activeUnitAppsCount = \App\Models\Application::whereIn('unit_id', $agency->units->pluck('id'))
            ->whereIn('status', ['accepted', 'verified'])
            ->count();

        if ($activeUnitAppsCount > 0) {
            return redirect()->back()->with('error', "Gagal menghapus: Instansi '{$agency->agency_name}' masih memiliki {$activeUnitAppsCount} mahasiswa aktif pada unit kerjanya.");
        }

        // 2. Proteksi Mentor dengan Bimbingan Aktif
        $activeMentorsCount = \App\Models\Placement::whereIn('mentor_id', User::where('agency_profile_id', $agency->id)->whereIn('role', ['mentor', 'pembimbing'])->pluck('id'))
            ->whereHas('application', function ($aq) {
                $aq->whereIn('status', ['accepted', 'verified']);
            })->count();

        if ($activeMentorsCount > 0) {
            return redirect()->back()->with('error', "Gagal menghapus: Terdapat {$activeMentorsCount} mahasiswa aktif yang sedang dibimbing oleh mentor dinas ini.");
        }

        $name = $agency->agency_name;

        \DB::transaction(function () use ($agency) {
            // Hapus unit kerja kosong
            $agency->units()->delete();

            // Hapus akun admin dinas yang terafiliasi dengan instansi ini
            User::where('agency_profile_id', $agency->id)
                ->where('role', 'admin')
                ->delete();

            // Lepaskan relasi mentor non-aktif jika ada
            User::where('agency_profile_id', $agency->id)
                ->whereIn('role', ['mentor', 'pembimbing'])
                ->update(['agency_profile_id' => null]);

            // Hapus data instansi
            $agency->delete();
        });

        AuditLog::record('AGENCY_DELETE', 'AgencyProfile', $id, ['name' => $name]);

        return redirect()->route('admin.agencies.index')
            ->with('success', "Instansi '{$name}' dan akun admin dinasnya berhasil dihapus.");
    }

    /**
     * Buat Akun Admin Dinas untuk Instansi tertentu
     */
    public function createAccount(Request $request, $id)
    {
        $this->ensureSuperAdmin('Hanya Super Administrator yang dapat membuat akun Admin Dinas.');
        $agency = AgencyProfile::findOrFail($id);

        $existingAdmin = User::where('role', 'admin')
            ->where('agency_profile_id', $agency->id)
            ->first();

        if ($existingAdmin) {
            return redirect()->back()->with('error', "Instansi '{$agency->agency_name}' sudah memiliki akun Admin Dinas aktif ({$existingAdmin->email}).");
        }

        $cleanName = strtolower(preg_replace('/[^A-Za-z0-9]/', '', $agency->agency_name ?: 'agency'));
        $defaultEmail = $agency->email ?: ('admin.' . $cleanName . '@surabaya.go.id');

        $email = $defaultEmail;
        $counter = 1;
        while (User::where('email', $email)->exists()) {
            $email = 'admin.' . $cleanName . $counter . '@surabaya.go.id';
            $counter++;
        }

        $password = 'password';

        $user = User::create([
            'name' => 'Admin ' . $agency->agency_name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'admin',
            'agency_profile_id' => $agency->id,
            'email_verified_at' => now(),
        ]);

        AuditLog::record('AGENCY_ACCOUNT_CREATE', 'User', $user->id, [
            'agency_name' => $agency->agency_name,
            'email' => $email,
        ]);

        session()->flash('new_agency_credential', [
            'agency_name' => $agency->agency_name,
            'name' => $user->name,
            'user_id' => $user->id,
            'email' => $email,
            'password' => $password,
            'login_url' => url('/login'),
        ]);

        return redirect()->route('admin.agencies.index')
            ->with('success', "Akun Admin Dinas untuk '{$agency->agency_name}' berhasil dibuat! Email: {$email} | Password: {$password}");
    }
}
