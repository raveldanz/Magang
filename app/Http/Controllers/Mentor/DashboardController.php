<?php

namespace App\Http\Controllers\Mentor;

use App\Enums\ReviewStatus;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\FinalReport;
use App\Models\Logbook;
use App\Models\Placement;
use App\Models\University;
use App\Services\LogbookWeeklyBundler;
use App\Services\StudentNotifier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Halaman Dashboard Utama Pembimbing Lapangan / Mentor
     * Menampilkan metrik dan daftar mahasiswa bimbingan dengan segmentasi lifecycle.
     */
    public function index(Request $request)
    {
        $mentor = Auth::user();

        // Sinkronkan seluruh pengajuan magang yang tanggal mulainya telah tiba ke ACTIVE
        Application::syncStartedApplications();

        // Base query penempatan yang menjadi wewenang mentor ini (bimbingan langsung + fallback unit)
        $baseQuery = Placement::fieldReviewableBy($mentor);

        // Jangan sertakan yang mengundurkan diri atau ditolak
        $baseQuery->whereHas('application', function ($q) {
            $q->whereNotIn('status', ['resigned', 'rejected']);
        });

        // Hitung statistik bimbingan mentor secara akurat
        $allBasePlacements = (clone $baseQuery)->with([
            'application.user',
            'application.unit',
            'evaluation',
            'finalreport',
        ])->get();

        $totalStudents = $allBasePlacements->count();
        $hasMentorScoreFn = static fn (Placement $p): bool => (bool) ($p->evaluation && (
            ($p->evaluation->nilai_disiplin ?? 0) > 0
            || ($p->evaluation->nilai_kinerja ?? 0) > 0
            || ($p->evaluation->nilai_laporan ?? 0) > 0
        ));

        $totalEvaluated = $allBasePlacements->filter($hasMentorScoreFn)->count();

        // Hanya hitung mahasiswa bimbingan langsung yang magangnya aktif/terbuka nilainya & belum dinilai
        $pendingEvalPlacements = $allBasePlacements->filter(
            fn (Placement $p) => $p->isAssignedFieldMentor($mentor)
                && $p->evaluationLockReason() === null
                && ! $hasMentorScoreFn($p)
        )->values();

        $totalPendingEval = $pendingEvalPlacements->count();
        $totalReportsApproved = $allBasePlacements->filter(fn (Placement $p) => $p->finalreport?->status === 'approved')->count();
        $totalReportsPending = $allBasePlacements->filter(fn (Placement $p) => $p->finalreport && $p->finalreport->status !== 'approved')->count();

        $activeStudents = $allBasePlacements->filter(fn (Placement $p) => $p->application?->statusValue() === 'active')->count();
        $upcomingStudents = $allBasePlacements->filter(fn (Placement $p) => $p->application?->statusValue() === 'accepted')->count();
        $completedStudents = $allBasePlacements->filter(fn (Placement $p) => $p->application?->statusValue() === 'completed')->count();

        $placementIds = $allBasePlacements->pluck('id');
        $pendingLogbooks = $placementIds->isNotEmpty()
            ? Logbook::whereIn('placement_id', $placementIds)->where('status', 'pending')->count()
            : 0;

        $stats = [
            'total_students' => $totalStudents,
            'total_evaluated' => $totalEvaluated,
            'total_pending_eval' => $totalPendingEval,
            'total_reports_approved' => $totalReportsApproved,
            'total_reports_pending' => $totalReportsPending,
            'active_students' => $activeStudents,
            'upcoming_students' => $upcomingStudents,
            'completed_students' => $completedStudents,
            'pending_logbooks' => $pendingLogbooks,
        ];

        // Prioritas Tindakan Segera (selaras dengan Dashboard Dosen Pembimbing)
        $pendingReports = $allBasePlacements->filter(
            fn (Placement $p) => $p->isAssignedFieldMentor($mentor) && $p->finalreport?->status === 'pending'
        )->values();

        $urgentEndingPlacements = $pendingEvalPlacements->filter(function (Placement $p) {
            $endDate = $p->application?->end_date ? Carbon::parse($p->application->end_date) : null;
            $isNearEnd = $endDate && $endDate->diffInDays(Carbon::now(), false) >= -14;
            $reportApproved = $p->finalreport?->status === 'approved';

            return $isNearEnd || $reportApproved;
        })->values();

        $actionAlerts = [
            'pending_reports' => $pendingReports,
            'urgent_ending' => $urgentEndingPlacements,
            'pending_logbooks_count' => $pendingLogbooks,
            'pending_eval_ready' => $pendingEvalPlacements,
        ];

        // Query penempatan dengan relasi lengkap untuk daftar & paginasi
        $query = (clone $baseQuery)
            ->with([
                'application.user.studentProfile',
                'application.user.universityRelation',
                'application.unit.agencyProfile',
                'logbooks',
                'evaluation',
                'finalreport',
                'academicAdvisor',
            ])
            ->withCount([
                'logbooks as pending_logbooks_count' => fn ($q) => $q->where('status', 'pending'),
            ]);

        $tab = $request->get('tab');
        if ($tab && in_array($tab, ['upcoming', 'completed', 'active'], true)) {
            $query->whereRelation('application', 'status', match ($tab) {
                'upcoming' => 'accepted',
                'completed' => 'completed',
                default => 'active',
            });
        }

        // Filter pencarian nama / NIM / jurusan / kampus
        $search = trim((string) ($request->get('search') ?? $request->get('q', '')));
        if ($search !== '') {
            $keyword = '%'.mb_strtolower($search).'%';
            $like = \DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
            $query->whereHas('application.user', function ($q) use ($search, $like) {
                $q->where('name', $like, "%{$search}%")
                    ->orWhereHas('studentProfile', function ($sq) use ($search, $like) {
                        $sq->where('nim', $like, "%{$search}%")
                            ->orWhere('jurusan', $like, "%{$search}%")
                            ->orWhere('universitas', $like, "%{$search}%");
                    });
            });
        }

        // Filter status laporan akhir
        if ($request->filled('report_status')) {
            $repStatus = strtolower($request->report_status);
            if (ReviewStatus::tryFrom($repStatus)) {
                $query->whereHas('finalreport', fn ($q) => $q->where('status', $repStatus));
            } elseif ($repStatus === 'none') {
                $query->whereDoesntHave('finalreport');
            }
        }

        // Filter perguruan tinggi
        if ($request->filled('university_id')) {
            $query->whereHas('application.user', function ($q) use ($request) {
                $q->where('university_id', $request->university_id);
            });
        }

        // 15 mahasiswa per halaman; filter & tab ikut terbawa saat pindah halaman
        $placements = $query->latest()->paginate(15)->withQueryString();

        // Hanya tampilkan daftar kampus yang ada di bimbingan mentor agar dropdown filter tetap ringkas
        $univIds = $allBasePlacements->pluck('application.user.university_id')->filter()->unique()->values();
        $universities = $univIds->isNotEmpty()
            ? University::whereIn('id', $univIds)->orderBy('name')->get()
            : collect();

        return view('mentor.dashboard', compact('placements', 'stats', 'universities', 'tab', 'search', 'actionAlerts'));
    }

    /**
     * Detail Aktivitas & Logbook Mahasiswa Tertentu
     */
    public function showStudent($placementId, LogbookWeeklyBundler $bundler)
    {
        $mentor = Auth::user();

        $placement = Placement::findOrFail($placementId);
        $placement->autoActivateIfStarted();

        $placement->load([
            'application.user.studentProfile',
            'application.unit.agencyProfile',
            'application.group',
            'logbooks' => function ($q) {
                $q->orderBy('date', 'desc')->orderBy('id', 'desc');
            },
            'evaluation',
            'finalreport',
            'internshipProjects',
            'academicAdvisor',
        ]);

        // Mentor yang ditugaskan, atau Kepala Unit selama mentor belum ditunjuk (validasi sementara).
        abort_unless($placement->canFieldReviewLogbook($mentor), 403, 'Anda tidak memiliki hak akses ke data bimbingan mahasiswa ini.');

        // Multi-Tenant Authorization Check
        if ($mentor->agency_profile_id !== null && optional($placement->application?->unit)->agency_profile_id !== $mentor->agency_profile_id) {
            abort(403, 'Anda tidak memiliki hak akses ke data bimbingan instansi lain.');
        }

        // Nilai & laporan akhir hanya boleh ditangani mentor yang ditugaskan (bukan Kepala Unit sementara).
        $canEvaluate = $placement->isAssignedFieldMentor($mentor);
        $evaluationLockReason = $placement->evaluationLockReason();

        $weeklyBundles = $bundler->bundle(
            logbooks: $placement->logbooks,
            statusColumn: 'status',
            feedbackColumn: 'feedback',
            otherStatusColumn: 'lecturer_status',
            otherFeedbackColumn: 'lecturer_feedback',
            perPage: null
        );

        return view('mentor.student-detail', compact('placement', 'weeklyBundles', 'canEvaluate', 'evaluationLockReason'));
    }

    /**
     * Approval / Revisi Laporan Akhir Mahasiswa
     */
    public function updateFinalReportStatus(Request $request, $reportId)
    {
        $mentor = Auth::user();

        $request->validate([
            'status' => 'required|in:approved,revision',
            'feedback' => 'nullable|required_if:status,revision|string|max:1000',
        ]);

        $report = FinalReport::with('placement.application.unit')->findOrFail($reportId);

        // Authorization Check
        $placement = $report->placement;
        if (! $placement->isAssignedFieldMentor($mentor)) {
            abort(403, 'Anda tidak memiliki hak akses untuk memverifikasi laporan ini.');
        }

        if ($mentor->agency_profile_id !== null && optional($placement->application?->unit)->agency_profile_id !== $mentor->agency_profile_id) {
            abort(403, 'Anda tidak memiliki hak akses ke laporan instansi lain.');
        }

        $report->update([
            'status' => $request->status,
            'feedback' => $request->feedback,
        ]);

        // Jika laporan di-ACC, sinkronisasi otomatis status kelulusan (COMPLETED)
        if ($request->status === 'approved') {
            $placement->syncCompletionStatus();
        }

        StudentNotifier::finalReportReviewed($placement->application?->user, 'mentor', $request->status, $request->feedback);

        return redirect()->back()->with('success', 'Status laporan akhir mahasiswa berhasil diperbarui!');
    }
}
