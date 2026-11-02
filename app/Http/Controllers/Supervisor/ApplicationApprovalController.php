<?php

namespace App\Http\Controllers\Supervisor;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\AuditLog;
use App\Models\Placement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ApplicationApprovalController extends Controller
{
    /**
     * Kotak Masuk Persetujuan Pengajuan Magang (Double ACC)
     */
    public function index(Request $request)
    {
        $statusFilter = $request->query('status', 'waiting_approval');

        $query = Application::with([
            'user.studentProfile',
            'unit.agencyProfile',
            'placement.mentor',
            'placement.pembimbing',
        ]);

        if ($statusFilter === 'waiting_approval') {
            $query->where('status', ApplicationStatus::WAITING_APPROVAL->value);
        } elseif ($statusFilter === 'approved') {
            $query->whereIn('status', [ApplicationStatus::ACCEPTED->value, ApplicationStatus::ACTIVE->value, ApplicationStatus::COMPLETED->value]);
        } elseif ($statusFilter === 'all') {
            // all
        } else {
            $query->where('status', $statusFilter);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $like = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
            $query->whereHas('user', function ($uq) use ($s, $like) {
                $uq->where('name', $like, "%{$s}%")
                    ->orWhereHas('studentProfile', fn ($sp) => $sp->where('universitas', $like, "%{$s}%")->orWhere('nim', $like, "%{$s}%"));
            });
        }

        if ($request->filled('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        $applications = $query->latest()->paginate(10)->withQueryString();

        $dispusip = AgencyProfile::where('agency_name', 'like', '%Perpustakaan%')->first() ?? AgencyProfile::first();
        $units = \App\Models\Unit::when($dispusip, fn ($q) => $q->where('agency_profile_id', $dispusip->id))
            ->orderBy('name')
            ->get();

        $waitingCount = Application::where('status', ApplicationStatus::WAITING_APPROVAL->value)->count();
        $approvedCount = Application::whereIn('status', [ApplicationStatus::ACCEPTED->value, ApplicationStatus::ACTIVE->value, ApplicationStatus::COMPLETED->value])->count();

        return view('supervisor.applications.index', compact(
            'applications',
            'statusFilter',
            'units',
            'waitingCount',
            'approvedCount'
        ));
    }

    /**
     * Pratinjau Detail Pengajuan Magang untuk Verifikasi Atasan
     */
    public function show($id)
    {
        $application = Application::with([
            'user.studentProfile',
            'unit.agencyProfile',
            'group.applications.user.studentProfile',
            'documents',
            'placement.mentor',
            'placement.pembimbing',
            'placement.academicAdvisor',
        ])->findOrFail($id);

        return view('supervisor.applications.show', compact('application'));
    }

    /**
     * Aksi ACC: Setujui & Terbitkan Surat Balasan Resmi
     */
    public function approve(Request $request, $id)
    {
        $application = Application::with(['unit', 'placement', 'user', 'group.applications'])->findOrFail($id);
        $user = Auth::user();

        // Status baru: jika sudah melewati start_date, langsung 'active'; jika belum, 'accepted'
        $now = now()->toDateString();
        $newStatus = ($application->start_date && $application->start_date <= $now && (! $application->end_date || $application->end_date >= $now))
            ? ApplicationStatus::ACTIVE->value
            : ApplicationStatus::ACCEPTED->value;

        $year = date('Y');
        $paddedId = str_pad($application->id, 3, '0', STR_PAD_LEFT);
        $autoLetterNumber = $application->letter_number ?: "500.12.1/{$paddedId}/436.7.14/{$year}";
        $letterToken = $application->letter_token ?: Str::random(32);
        $letterDate = $application->letter_date ?: date('Y-m-d');

        DB::transaction(function () use ($application, $newStatus, $autoLetterNumber, $letterToken, $letterDate, $paddedId, $year, $user) {
            $oldStatus = $application->status instanceof ApplicationStatus ? $application->status->value : (string) $application->status;

            // Jika pengajuan kelompok, sinkronkan seluruh anggota kelompok bersama-sama
            if ($application->group_id) {
                $group = \App\Models\InternshipGroup::find($application->group_id);
                if ($group) {
                    $group->update([
                        'status' => $newStatus,
                        'letter_number' => $autoLetterNumber,
                        'letter_date' => $letterDate,
                        'letter_token' => $group->letter_token ?: $letterToken,
                    ]);

                    // Ambil penempatan existing sebagai template mentor/dosen bila ada
                    $leadPlacement = Placement::where('application_id', $application->id)->first();
                    if (! $leadPlacement && $group->leader_user_id) {
                        $leaderApp = Application::where('group_id', $group->id)->where('role_in_group', 'leader')->first();
                        if ($leaderApp) {
                            $leadPlacement = Placement::where('application_id', $leaderApp->id)->first();
                        }
                    }

                    $allGroupApps = Application::where('group_id', $group->id)->get();
                    foreach ($allGroupApps as $memberApp) {
                        $memberPaddedId = str_pad($memberApp->id, 3, '0', STR_PAD_LEFT);
                        $memberApp->update([
                            'status' => $newStatus,
                            'letter_number' => $autoLetterNumber,
                            'letter_date' => $letterDate,
                            'letter_token' => $memberApp->letter_token ?: Str::random(32),
                            'unit_id' => $memberApp->unit_id ?: $application->unit_id,
                            'start_date' => $memberApp->start_date ?: $application->start_date,
                            'end_date' => $memberApp->end_date ?: $application->end_date,
                            'rejection_note' => null,
                            'rejection_reason' => null,
                        ]);

                        $memberPlacement = Placement::where('application_id', $memberApp->id)->first();
                        $memberPlacementData = [
                            'certificate_hash' => $memberPlacement?->certificate_hash ?: Str::random(32),
                            'certificate_number' => $memberPlacement?->certificate_number ?: "SERT/{$memberPaddedId}/DISPUSIP-SBY/{$year}",
                        ];

                        $mentorId = $memberPlacement?->mentor_id ?: $leadPlacement?->mentor_id;
                        if ($mentorId) {
                            $memberPlacementData['mentor_id'] = $mentorId;
                            $memberPlacementData['pembimbing_id'] = $mentorId;
                        }

                        $advisorId = $memberPlacement?->academic_advisor_id ?: $leadPlacement?->academic_advisor_id;
                        if ($advisorId) {
                            $memberPlacementData['academic_advisor_id'] = $advisorId;
                        }

                        Placement::updateOrCreate(
                            ['application_id' => $memberApp->id],
                            $memberPlacementData
                        );
                    }
                }
            } else {
                // Pengajuan Individu
                $application->update([
                    'status' => $newStatus,
                    'letter_number' => $autoLetterNumber,
                    'letter_date' => $letterDate,
                    'letter_token' => $letterToken,
                    'rejection_note' => null,
                    'rejection_reason' => null,
                ]);

                // Pastikan Placement aktif dan memiliki nomor sertifikat & token verifikasi
                $existingPlacement = Placement::where('application_id', $application->id)->first();
                $placementData = [
                    'certificate_hash' => $existingPlacement?->certificate_hash ?: Str::random(32),
                    'certificate_number' => $existingPlacement?->certificate_number ?: "SERT/{$paddedId}/DISPUSIP-SBY/{$year}",
                ];

                Placement::updateOrCreate(
                    ['application_id' => $application->id],
                    $placementData
                );
            }

            // Audit Trail
            AuditLog::record('SUPERVISOR_APPLICATION_APPROVED', 'Application', $application->id, [
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'student_name' => $application->user?->name,
                'unit_name' => $application->unit?->name,
                'is_group' => (bool) $application->group_id,
                'group_id' => $application->group_id,
                'approved_by' => $user->name,
                'approved_by_id' => $user->id,
            ]);
        });

        $group = $application->group_id ? \App\Models\InternshipGroup::find($application->group_id) : null;
        $successMsg = $group
            ? "Pengajuan magang kelompok '{$group->name}' ({$group->applications()->count()} anggota) berhasil disetujui (ACC) dan Surat Balasan resmi telah diterbitkan!"
            : "Pengajuan magang atas nama {$application->user?->name} berhasil disetujui (ACC) dan Surat Balasan resmi telah diterbitkan!";

        return redirect()->route('supervisor.applications.index')
            ->with('success', $successMsg);
    }

    /**
     * Aksi Revisi / Penolakan: Kembalikan Berkas ke Admin Dinas dengan Catatan Penolakan
     */
    public function revise(Request $request, $id)
    {
        $request->validate([
            'notes' => 'required|string|min:5',
        ], [
            'notes.required' => 'Catatan penolakan wajib diisi agar Admin Dinas mengetahui alasannya.',
            'notes.min' => 'Catatan penolakan minimal 5 karakter.',
        ]);

        $application = Application::with(['user'])->findOrFail($id);
        $user = Auth::user();

        $oldStatus = $application->status instanceof ApplicationStatus ? $application->status->value : (string) $application->status;

        $rejectionText = "Catatan Penolakan Atasan: " . $request->notes;

        $application->update([
            'status' => ApplicationStatus::PENDING->value,
            'rejection_note' => $rejectionText,
            'rejection_reason' => $rejectionText,
        ]);

        if ($application->group_id) {
            $group = \App\Models\InternshipGroup::find($application->group_id);
            if ($group) {
                $group->update([
                    'status' => 'pending',
                    'rejection_reason' => $rejectionText,
                ]);
                Application::where('group_id', $group->id)
                    ->where('id', '!=', $application->id)
                    ->where('status', '!=', 'rejected')
                    ->update([
                        'status' => ApplicationStatus::PENDING->value,
                        'rejection_note' => $rejectionText,
                        'rejection_reason' => $rejectionText,
                    ]);
            }
        }

        AuditLog::record('SUPERVISOR_APPLICATION_REVISED', 'Application', $application->id, [
            'old_status' => $oldStatus,
            'new_status' => ApplicationStatus::PENDING->value,
            'student_name' => $application->user?->name,
            'notes' => $request->notes,
            'revised_by' => $user->name,
            'revised_by_id' => $user->id,
        ]);

        return redirect()->route('supervisor.applications.index')
            ->with('success', "Pengajuan magang atas nama {$application->user?->name} telah ditolak dan dikembalikan ke Admin Dinas.");
    }
}
