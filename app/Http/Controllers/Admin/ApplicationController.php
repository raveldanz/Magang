<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\AuditLog;
use App\Models\Placement;
use App\Models\Unit;
use App\Models\University;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        $isPgsql = DB::connection()->getDriverName() === 'pgsql';
        $currentDateSql = $isPgsql ? 'CURRENT_DATE' : 'CURDATE()';

        $query = Application::with([
            'user.studentProfile', 
            'unit.agencyProfile', 
            'documents', 
            'placement.evaluation', 
            'placement.finalreport', 
            'placement.mentor', 
            'placement.pembimbing', 
            'placement.academicAdvisor'
        ]);

        // Prioritas Pengurutan Berdasarkan Kebutuhan Tindakan (Action-Driven Priority):
        // 1. Mahasiswa Baru yang Perlu Verifikasi Berkas & Penerimaan (PENDING / VERIFIED / SUBMITTED) -> Teratas
        // 2. Mahasiswa yang Perlu Aksi Kelulusan (ACCEPTED dengan Laporan Disetujui *DAN* Nilai Evaluasi Lengkap) -> Siap Diluluskan
        // 3. Mahasiswa yang Sedang Magang Aktif (ACCEPTED normal, baik aktif berkegiatan atau masih menunggu penilaian)
        // 4. Mahasiswa yang Sudah Selesai & Lulus (COMPLETED) -> Dikebawahkan
        // 5. Berkas Ditolak atau Mengundurkan Diri (REJECTED / RESIGNED / CANCELED) -> Paling bawah
        $query->orderByRaw("
            CASE 
                WHEN applications.status IN ('pending', 'verified', 'submitted') THEN 1
                WHEN applications.status = 'accepted' 
                  AND EXISTS (
                      SELECT 1 FROM placements p 
                      JOIN final_reports fr ON fr.placement_id = p.id 
                      WHERE p.application_id = applications.id 
                        AND LOWER(fr.status) IN ('approved', 'disetujui')
                  )
                  AND EXISTS (
                      SELECT 1 FROM placements p 
                      JOIN evaluations ev ON ev.placement_id = p.id 
                      WHERE p.application_id = applications.id 
                        AND (
                            COALESCE(ev.final_score, 0) > 0 
                            OR (
                                COALESCE(ev.nilai_disiplin, 0) > 0 
                                AND COALESCE(ev.nilai_kinerja, 0) > 0 
                                AND COALESCE(ev.nilai_laporan, 0) > 0
                                AND (COALESCE(ev.nilai_dosen, 0) > 0 OR COALESCE(ev.nilai_akademik, 0) > 0)
                            )
                        )
                  ) THEN 2
                WHEN applications.status = 'accepted' THEN 3
                WHEN applications.status = 'completed' THEN 4
                WHEN applications.status IN ('rejected', 'resigned', 'canceled') THEN 5
                ELSE 6
            END ASC, applications.created_at DESC
        ");

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

        // 4. Filter Berdasarkan Universitas
        $selectedUniversity = null;
        if ($request->filled('university_id')) {
            $univId = $request->university_id;
            $univ = University::find($univId);
            $selectedUniversity = $univ;
            $like = \DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

            $query->whereHas('user', function ($uq) use ($univId, $univ, $like) {
                $uq->where('university_id', $univId);
                if ($univ) {
                    $uq->orWhere('university', $like, "%{$univ->name}%")
                       ->orWhereHas('studentProfile', fn($sp) => $sp->where('university_id', $univId)->orWhere('universitas', $like, "%{$univ->name}%"));
                }
            });
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
        $universities = University::orderBy('name')->get();

        // Paginasi 10 Data Per Halaman
        $applications = $query->paginate(10)->withQueryString();

        return view('admin.applications.index', compact(
            'applications', 
            'units', 
            'groupedUnits', 
            'agencies', 
            'universities',
            'selectedUniversity',
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
            'status' => 'required|in:pending,verified,accepted,rejected,completed,resigned',
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

        // Strict Validation for COMPLETED status
        if ($newStatus === 'completed' && $oldStatus !== 'completed') {
            if (!$application->can_complete) {
                $missing = [];
                if (!$application->has_approved_report) {
                    $missing[] = "Laporan Akhir belum disetujui";
                }
                if (!$application->has_complete_evaluation) {
                    $missing[] = "Penilaian (Mentor & DPL) belum tuntas diisi";
                }
                $missingStr = implode(' dan ', $missing);
                return redirect()->back()->with('error', "Gagal menyelesaikan magang: {$missingStr}.");
            }
        }

        // Time-Aware Quota Lifecycle Engine: Cek irisan tanggal
        if ($newStatus === 'accepted' && $oldStatus !== 'accepted') {
            if ($unit) {
                $overlappingInterns = $unit->applications()
                    ->where('status', 'accepted')
                    ->where('id', '!=', $application->id)
                    ->where(function ($query) use ($application) {
                        $query->where('start_date', '<=', $application->end_date)
                              ->where('end_date', '>=', $application->start_date);
                    })->count();

                if ($overlappingInterns >= $unit->quota) {
                    return redirect()->back()->with('error', "Gagal menerima pengajuan: Kuota unit kerja '{$unit->name}' penuh pada rentang tanggal magang pemohon (Kapasitas: {$unit->quota}, Terisi: {$overlappingInterns} pada rentang tersebut).");
                }
            }
        }
        
        $year = date('Y');
        $paddedId = str_pad($application->id, 3, '0', STR_PAD_LEFT);
        $autoLetterNumber = "500.12.1/{$paddedId}/436.7.14/{$year}";
        $letterToken = $application->letter_token ?: Str::random(32);
        $assignedMentorId = $request->mentor_id ?? $request->pembimbing_id;

        DB::transaction(function () use ($application, $newStatus, $oldStatus, $request, $autoLetterNumber, $letterToken, $paddedId, $year, $unit, $assignedMentorId, $isSuperAdmin) {
            $application->update([
                'status' => $newStatus,
                'rejection_note' => $newStatus === 'rejected' ? ($request->rejection_reason ?? $request->rejection_note) : null,
                'rejection_reason' => $newStatus === 'rejected' ? ($request->rejection_reason ?? $request->rejection_note) : null,
                'letter_number' => in_array($newStatus, ['accepted', 'completed']) ? ($request->letter_number ?: ($application->letter_number ?: $autoLetterNumber)) : null,
                'letter_date' => in_array($newStatus, ['accepted', 'completed']) ? ($request->letter_date ?: ($application->letter_date ?: date('Y-m-d'))) : null,
                'letter_token' => in_array($newStatus, ['accepted', 'completed']) ? $letterToken : $application->letter_token,
            ]);

            $placementData = [];
            if ($assignedMentorId) {
                $placementData['mentor_id'] = $assignedMentorId;
                $placementData['pembimbing_id'] = $assignedMentorId;
            }

            if ($request->filled('academic_advisor_id')) {
                $placementData['academic_advisor_id'] = $request->academic_advisor_id;
            }

            if (in_array($newStatus, ['accepted', 'completed'])) {
                $existingPlacement = Placement::where('application_id', $application->id)->first();
                $placementData['certificate_hash'] = $existingPlacement?->certificate_hash ?: Str::random(32);
                $placementData['certificate_number'] = $existingPlacement?->certificate_number ?: "SERT/{$paddedId}/PEMKOT-SBY/{$year}";
            }

            if (!empty($placementData) || in_array($newStatus, ['accepted', 'completed'])) {
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
        });

        return redirect()->route('admin.applications.index')->with('success', 'Status pengajuan & penempatan berhasil diperbarui!');
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
            ->whereIn('status', ['accepted', 'completed'])
            ->findOrFail($id);

        // Multi-Tenant Authorization Check
        if (!$isSuperAdmin && $user->agency_profile_id !== null && optional($application->unit)->agency_profile_id !== $user->agency_profile_id) {
            abort(403, 'Anda tidak memiliki hak akses ke surat pengajuan instansi lain.');
        }

        return view('letters.acceptance', compact('application'));
    }
}