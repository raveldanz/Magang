<?php

namespace App\Http\Controllers\Lecturer;

use App\Enums\ReviewStatus;
use App\Http\Controllers\Controller;
use App\Models\AgencyProfile;
use App\Models\Placement;
use App\Services\LogbookWeeklyBundler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Helper untuk membuat query placement terisolasi khusus DPL yang sedang login
     */
    protected function getLecturerPlacementsQuery()
    {
        $lecturer = Auth::user();

        return Placement::with([
            'application.user.studentProfile',
            'application.unit.agencyProfile',
            'mentor',
            'pembimbing',
            'logbooks',
            'finalreport',
            'evaluation',
        ])->where('academic_advisor_id', $lecturer->id)
            ->whereHas('application', function ($q) {
                $q->whereNotIn('status', ['resigned', 'rejected']);
            });
    }

    /**
     * Dashboard DPL: Menampilkan ringkasan mahasiswa bimbingan DPL & status evaluasi
     */
    public function index(Request $request)
    {
        $lecturer = Auth::user();
        $query = $this->getLecturerPlacementsQuery();

        if ($request->filled('search')) {
            $search = strtolower(trim($request->search));
            $like = \DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

            $query->whereHas('application.user', function ($q) use ($search, $like) {
                $q->where('name', $like, "%{$search}%")
                    ->orWhereHas('studentProfile', function ($sp) use ($search, $like) {
                        $sp->where('nim', $like, "%{$search}%")
                            ->orWhere('jurusan', $like, "%{$search}%");
                    });
            });
        }

        if ($request->filled('agency_id')) {
            $query->whereHas('application.unit', function ($q) use ($request) {
                $q->where('agency_profile_id', $request->agency_id);
            });
        }

        if ($request->filled('report_status')) {
            $status = strtolower($request->report_status);
            // Filter per kode status laporan (sama dengan badge yang tampil)
            if (ReviewStatus::tryFrom($status)) {
                $query->whereHas('finalreport', fn ($q) => $q->where('status', $status));
            } elseif ($status === 'none') {
                $query->whereDoesntHave('finalreport');
            }
        }

        $placements = $query->latest()->get();

        // Hitung metrik statistik bimbingan DPL
        $totalStudents = $placements->count();
        $totalEvaluated = $placements->filter(function ($p) {
            $eval = $p->evaluation;
            if (! $eval) {
                return false;
            }
            $univ = $eval->getUniversity();
            if ($univ && $univ->evaluation_scheme === 'mentor_only') {
                return ($eval->nilai_pembimbing ?? 0) > 0;
            }

            return $eval->nilai_akademik > 0 || $eval->nilai_dosen > 0;
        })->count();
        $totalPendingEval = max(0, $totalStudents - $totalEvaluated);
        $totalReportsApproved = $placements->filter(function ($p) {
            return optional($p->finalreport)->status === 'approved';
        })->count();
        $totalReportsPending = $placements->filter(function ($p) {
            return $p->finalreport && $p->finalreport->status !== 'approved';
        })->count();

        $stats = [
            'total_students' => $totalStudents,
            'total_evaluated' => $totalEvaluated,
            'total_pending_eval' => $totalPendingEval,
            'total_reports_approved' => $totalReportsApproved,
            'total_reports_pending' => $totalReportsPending,
        ];

        // 1. SMART ACTION ALERTS (Peringatan Aksi Proaktif DPL)
        $pendingReports = $placements->filter(function ($p) {
            return $p->finalreport && $p->finalreport->status === 'pending';
        });

        $urgentEndingPlacements = $placements->filter(function ($p) {
            $eval = $p->evaluation;
            $hasScore = $eval && (($eval->nilai_akademik ?? 0) > 0 || ($eval->nilai_dosen ?? 0) > 0 || ($eval->dosenAspectScore('score_mastery') ?? 0) > 0);
            $app = $p->application;
            if (!$app || $app->status === 'completed' || $app->status === 'rejected') return false;

            $endDate = $app->end_date ? \Carbon\Carbon::parse($app->end_date) : null;
            $isNearEnd = $endDate && $endDate->diffInDays(\Carbon\Carbon::now(), false) >= -14;

            return !$hasScore && ($isNearEnd || $app->status === 'active');
        });

        $supervisedPlacementIds = $placements->pluck('id')->toArray();
        $pendingLogbooksCount = \App\Models\Logbook::whereIn('placement_id', $supervisedPlacementIds)
            ->where('lecturer_status', 'pending')
            ->count();

        $actionAlerts = [
            'pending_reports' => $pendingReports,
            'urgent_ending' => $urgentEndingPlacements,
            'pending_logbooks_count' => $pendingLogbooksCount,
        ];

        $agencies = AgencyProfile::all();

        return view('lecturer.dashboard', compact('placements', 'stats', 'lecturer', 'agencies', 'actionAlerts'));
    }

    /**
     * Detail monitoring aktivitas, logbook, laporan akhir, dan form penilaian per mahasiswa bimbingan
     */
    public function showStudent($placementId, LogbookWeeklyBundler $bundler)
    {
        $lecturer = Auth::user();

        // Cari placement berdasarkan ID langsung atau application_id sebagai fallback
        $placement = Placement::with([
            'application.user.studentProfile',
            'application.unit.agencyProfile',
            'mentor',
            'pembimbing',
            'logbooks',
            'finalreport',
            'evaluation',
            'academicConsultations',
        ])->find($placementId) ?? Placement::with([
            'application.user.studentProfile',
            'application.unit.agencyProfile',
            'mentor',
            'pembimbing',
            'logbooks',
            'finalreport',
            'evaluation',
            'academicConsultations',
        ])->where('application_id', $placementId)->firstOrFail();

        $isAssignedAdvisor = ($placement->academic_advisor_id === $lecturer->id);
        $isSuperAdmin = $lecturer->isSuperAdmin();

        if (! $isAssignedAdvisor && ! $isSuperAdmin) {
            abort(403, 'Akses Ditolak: Anda bukan Dosen Pembimbing Lapangan yang ditugaskan untuk mahasiswa ini.');
        }

        $student = $placement->application->user;
        $profile = $student->studentProfile;
        $unit = $placement->application->unit;
        $agencyProfile = $unit?->agencyProfile ?? $placement->agencyProfile;
        $mentor = $placement->mentor ?? $placement->pembimbing;
        $logbooks = $placement->logbooks()->orderBy('date', 'desc')->get();
        $finalReport = $placement->finalreport;
        $evaluation = $placement->evaluation;
        $consultations = $placement->academicConsultations;

        $weeklyBundles = $bundler->bundle(
            logbooks: $logbooks,
            statusColumn: 'lecturer_status',
            feedbackColumn: 'lecturer_feedback',
            otherStatusColumn: 'status',
            otherFeedbackColumn: 'feedback',
            perPage: null
        );

        $evaluationLockReason = $placement->evaluationLockReason();

        return view('lecturer.student-detail', compact(
            'evaluationLockReason',
            'placement',
            'student',
            'profile',
            'unit',
            'agencyProfile',
            'mentor',
            'logbooks',
            'weeklyBundles',
            'finalReport',
            'evaluation',
            'consultations'
        ));
    }

    /**
     * Simpan catatan sesi konsultasi bimbingan DPL
     */
    public function storeConsultation(Request $request, $placementId)
    {
        $lecturer = Auth::user();

        $placement = Placement::with('application.user')->find($placementId)
            ?? Placement::with('application.user')->where('application_id', $placementId)->firstOrFail();

        $isAssignedAdvisor = ($placement->academic_advisor_id === $lecturer->id);
        $isSuperAdmin = ($lecturer->role === 'super_admin' || ($lecturer->role === 'admin' && is_null($lecturer->agency_profile_id)));

        if (!$isAssignedAdvisor && !$isSuperAdmin) {
            abort(403, 'Akses Ditolak: Anda bukan DPL yang ditugaskan untuk mahasiswa ini.');
        }

        $request->validate([
            'consultation_date' => 'required|date',
            'stage' => 'required|string|max:100',
            'topic' => 'required|string|max:255',
            'notes' => 'required|string|max:2000',
        ]);

        $consultation = \App\Models\AcademicConsultation::create([
            'placement_id' => $placement->id,
            'academic_advisor_id' => $lecturer->id,
            'consultation_date' => $request->consultation_date,
            'stage' => $request->stage,
            'topic' => $request->topic,
            'notes' => $request->notes,
            'status' => 'completed',
        ]);

        \App\Models\AuditLog::record('LECTURER_CONSULTATION_ADD', 'AcademicConsultation', $consultation->id, [
            'student_name' => $placement->application?->user?->name ?? '-',
            'topic' => $request->topic,
            'consultation_date' => $request->consultation_date,
        ]);

        return redirect()->back()->with('success', 'Catatan sesi bimbingan DPL berhasil ditambahkan ke riwayat!');
    }

    /**
     * Hapus catatan sesi bimbingan DPL
     */
    public function destroyConsultation($id)
    {
        $lecturer = Auth::user();
        $consultation = \App\Models\AcademicConsultation::with('placement')->findOrFail($id);

        $isAdvisor = ($consultation->academic_advisor_id === $lecturer->id || optional($consultation->placement)->academic_advisor_id === $lecturer->id);
        $isSuperAdmin = ($lecturer->role === 'super_admin');

        if (!$isAdvisor && !$isSuperAdmin) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki wewenang menghapus catatan ini.');
        }

        $consultation->delete();

        return redirect()->back()->with('success', 'Catatan sesi bimbingan berhasil dihapus.');
    }
}
