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
use Illuminate\Support\Str;

class ApplicationController extends Controller
{
    /**
     * Menampilkan semua daftar pengajuan magang masuk (dengan Multi-Tenant Scoping, Bypass Super Admin, Filter & Paginasi)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isSuperAdmin = ($user->role === 'super_admin' || ($user->role === 'admin' && is_null($user->agency_profile_id)));
        $agencyId = $isSuperAdmin ? $request->agency_id : $user->agency_profile_id;

        $query = Application::with([
            'user.studentProfile', 
            'unit.agencyProfile', 
            'documents', 
            'placement.evaluation', 
            'placement.finalreport',
            'placement.mentor',
            'placement.pembimbing',
            'placement.academicAdvisor'
        ])->latest();

        // Multi-Tenant Isolation: Admin instansi hanya melihat pengajuan pada unit instansinya sendiri
        if ($agencyId) {
            $query->whereHas('unit', function ($q) use ($agencyId) {
                $q->where('agency_profile_id', $agencyId);
            });
        }

        // 1. Pencarian berdasarkan Nama Mahasiswa, NIM, atau Universitas
        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $like = \DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
            $query->whereHas('user', function ($q) use ($search, $like) {
                $q->where('name', $like, "%{$search}%")
                  ->orWhereHas('studentProfile', function ($spQuery) use ($search, $like) {
                      $spQuery->where('universitas', $like, "%{$search}%")
                              ->orWhere('nim', $like, "%{$search}%");
                  });
            });
        }

        // 2. Filter Berdasarkan Status Pengajuan
        if ($request->filled('status')) {
            $query->where('status', strtolower($request->status));
        }

        // 3. Filter Berdasarkan Unit / Divisi Kerja
        if ($request->filled('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        // Query Unit untuk Filter Dropdown (Scoped per instansi untuk Admin Dinas, atau All untuk Superadmin)
        if ($agencyId) {
            $units = Unit::where('agency_profile_id', $agencyId)->get();
            $groupedUnits = null;
        } else {
            $units = Unit::with('agencyProfile')->get();
            $groupedUnits = $units->groupBy(function ($u) {
                return $u->agencyProfile->agency_name ?? 'Pemerintah Kota Surabaya';
            });
        }

        $agencies = AgencyProfile::all();

        // Paginasi 10 Data Per Halaman
        $applications = $query->paginate(10)->withQueryString();

        return view('admin.applications.index', compact(
            'applications', 
            'units', 
            'groupedUnits', 
            'agencies', 
            'isSuperAdmin', 
            'agencyId'
        ));
    }

    /**
     * Detail pengajuan magang & dokumen
     */
    public function show($id)
    {
        $user = Auth::user();
        $isSuperAdmin = ($user->role === 'super_admin' || ($user->role === 'admin' && is_null($user->agency_profile_id)));

        $application = Application::with([
            'user.studentProfile', 
            'unit.agencyProfile', 
            'documents', 
            'placement.pembimbing',
            'placement.mentor',
            'placement.academicAdvisor',
            'placement.evaluation',
            'placement.finalreport'
        ])->findOrFail($id);

        // Multi-Tenant Authorization Check
        if (!$isSuperAdmin && $user->agency_profile_id !== null && optional($application->unit)->agency_profile_id !== $user->agency_profile_id) {
            abort(403, 'Anda tidak memiliki hak akses ke data pengajuan instansi lain.');
        }
        
        // Dropdown 'Pilih Pembimbing Lapangan' HANYA memuat user role 'mentor' yang terdaftar di instansi yang bersangkutan
        $targetAgencyId = $application->unit?->agency_profile_id ?? $user?->agency_profile_id;
        $pembimbingQuery = User::whereIn('role', ['mentor', 'pembimbing']);
        
        if ($targetAgencyId !== null) {
            $pembimbingQuery->where('agency_profile_id', $targetAgencyId);
        }
        
        $pembimbings = $pembimbingQuery->orderBy('name')->get();

        // Dropdown Dosen Kampus untuk Super Admin Override
        $dosens = User::whereIn('role', ['dosen', 'academic_advisor'])
            ->when($application->user?->university_id, fn($q) => $q->where('university_id', $application->user->university_id))
            ->orderBy('name')
            ->get();

        return view('admin.applications.show', compact('application', 'pembimbings', 'dosens', 'isSuperAdmin'));
    }

    /**
     * Update status pengajuan (Verifikasi / Seleksi / Quota Lifecycle)
     */
    public function updateStatus(Request $request, $id)
    {
        $user = Auth::user();
        $isSuperAdmin = ($user->role === 'super_admin' || ($user->role === 'admin' && is_null($user->agency_profile_id)));

        $statusInput = strtolower($request->status);
        $request->merge(['status' => $statusInput]);

        $request->validate([
            'status' => 'required|in:pending,verified,accepted,rejected',
            'rejection_note' => 'nullable|string',
            'mentor_id' => 'nullable|exists:users,id',
            'pembimbing_id' => 'nullable|exists:users,id',
            'academic_advisor_id' => 'nullable|exists:users,id',
            'letter_number' => 'nullable|string|max:100',
            'letter_date' => 'nullable|date',
            'override_reason' => 'nullable|string',
        ]);

        $application = Application::with(['unit', 'placement', 'user'])->findOrFail($id);
        $oldStatus = strtolower($application->status);
        $newStatus = $request->status;

        // Multi-Tenant Authorization Check
        if (!$isSuperAdmin && $user->agency_profile_id !== null && optional($application->unit)->agency_profile_id !== $user->agency_profile_id) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah pengajuan instansi lain.');
        }

        $unit = $application->unit;

        // Dynamic Quota Lifecycle Engine: Validasi kapasitas kuota sebelum menerima pengajuan
        if ($newStatus === 'accepted' && $oldStatus !== 'accepted') {
            if ($unit && $unit->remaining_quota <= 0) {
                $acceptedCount = $unit->applications()->where('status', 'accepted')->count();
                return redirect()->back()->with('error', "Gagal menerima pengajuan: Kuota unit kerja '{$unit->name}' telah penuh (Kapasitas: {$unit->quota}, Terisi: {$acceptedCount}). Silakan tambah kapasitas kuota unit kerja terlebih dahulu jika ingin menerima mahasiswa tambahan.");
            }
        }
        
        $year = date('Y');
        $paddedId = str_pad($application->id, 3, '0', STR_PAD_LEFT);
        $autoLetterNumber = "500.12.1/{$paddedId}/436.7.14/{$year}";
        $letterToken = $application->letter_token ?: Str::random(32);

        $application->update([
            'status' => $newStatus,
            'rejection_note' => $newStatus === 'rejected' ? ($request->rejection_reason ?? $request->rejection_note) : null,
            'rejection_reason' => $newStatus === 'rejected' ? ($request->rejection_reason ?? $request->rejection_note) : null,
            'letter_number' => $newStatus === 'accepted' ? ($request->letter_number ?: ($application->letter_number ?: $autoLetterNumber)) : null,
            'letter_date' => $newStatus === 'accepted' ? ($request->letter_date ?: ($application->letter_date ?: date('Y-m-d'))) : null,
            'letter_token' => $newStatus === 'accepted' ? $letterToken : $application->letter_token,
        ]);

        $assignedMentorId = $request->mentor_id ?? $request->pembimbing_id;

        $placementData = [];
        if ($assignedMentorId) {
            $placementData['mentor_id'] = $assignedMentorId;
            $placementData['pembimbing_id'] = $assignedMentorId;
        }

        if ($request->filled('academic_advisor_id')) {
            $placementData['academic_advisor_id'] = $request->academic_advisor_id;
        }

        if ($newStatus === 'accepted') {
            $existingPlacement = Placement::where('application_id', $application->id)->first();
            $placementData['certificate_hash'] = $existingPlacement?->certificate_hash ?: Str::random(32);
            $placementData['certificate_number'] = $existingPlacement?->certificate_number ?: "SERT/{$paddedId}/PEMKOT-SBY/{$year}";
        }

        if (!empty($placementData) || $newStatus === 'accepted') {
            Placement::updateOrCreate(
                ['application_id' => $application->id],
                $placementData
            );
        }

        // Catat Audit Trail
        AuditLog::record('APPLICATION_STATUS_UPDATE', 'Application', $application->id, [
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'student_name' => $application->user?->name,
            'unit_name' => $unit?->name,
            'assigned_mentor_id' => $assignedMentorId,
            'override_reason' => $request->override_reason,
            'is_super_admin' => $isSuperAdmin,
        ]);

        return redirect()->route('admin.applications.index')->with('success', 'Status pengajuan & kuota unit berhasil diperbarui!');
    }

    /**
     * Cetak / Pratinjau Surat Balasan Penerimaan untuk Admin
     */
    public function downloadLetter($id)
    {
        $user = Auth::user();
        $isSuperAdmin = ($user->role === 'super_admin' || ($user->role === 'admin' && is_null($user->agency_profile_id)));

        $application = Application::with([
            'user.studentProfile', 
            'unit.agencyProfile', 
            'placement.pembimbing',
            'placement.mentor'
        ])
            ->where('status', 'accepted')
            ->findOrFail($id);

        // Multi-Tenant Authorization Check
        if (!$isSuperAdmin && $user->agency_profile_id !== null && optional($application->unit)->agency_profile_id !== $user->agency_profile_id) {
            abort(403, 'Anda tidak memiliki hak akses ke surat pengajuan instansi lain.');
        }

        return view('letters.acceptance', compact('application'));
    }
}