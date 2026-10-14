<?php

namespace App\Http\Controllers\Mentor;

use App\Enums\ReviewStatus;
use App\Http\Controllers\Controller;
use App\Models\FinalReport;
use App\Models\Logbook;
use App\Models\Placement;
use App\Models\University;
use App\Services\LogbookWeeklyBundler;
use App\Services\StudentNotifier;
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

        // Base query penempatan yang diplot ke mentor ini
        $baseQuery = Placement::where(function ($q) use ($mentor) {
            $q->where('mentor_id', $mentor->id)
                ->orWhere('pembimbing_id', $mentor->id);
        });

        // Multi-Tenant Isolation: Scoping ke instansi jika mentor terikat ke agency tertentu
        if ($mentor->agency_profile_id !== null) {
            $baseQuery->whereHas('application.unit', function ($q) use ($mentor) {
                $q->where('agency_profile_id', $mentor->agency_profile_id);
            });
        }

        // Jangan sertakan yang mengundurkan diri atau ditolak kecuali jika diminta khusus
        $tab = $request->get('tab');
        if ($tab && in_array($tab, ['upcoming', 'completed', 'active'])) {
            $baseQuery->whereRelation('application', 'status', match ($tab) {
                'upcoming' => 'accepted',
                'completed' => 'completed',
                default => 'active',
            });
        } else {
            $baseQuery->whereHas('application', function ($q) {
                $q->whereNotIn('status', ['resigned', 'rejected']);
            });
        }

        // Hitung statistik bimbingan mentor
        $totalStudents = (clone $baseQuery)->count();
        $totalEvaluated = (clone $baseQuery)->whereHas('evaluation', function ($q) {
            $q->where(function ($sq) {
                $sq->where('nilai_disiplin', '>', 0)
                    ->orWhere('nilai_kinerja', '>', 0)
                    ->orWhere('nilai_laporan', '>', 0);
            });
        })->count();
        $totalPendingEval = max(0, $totalStudents - $totalEvaluated);
        $totalReportsApproved = (clone $baseQuery)->whereHas('finalreport', fn ($q) => $q->where('status', 'approved'))->count();
        $totalReportsPending = (clone $baseQuery)->whereHas('finalreport', fn ($q) => $q->where('status', '!=', 'approved'))->count();

        $stats = [
            'total_students' => $totalStudents,
            'total_evaluated' => $totalEvaluated,
            'total_pending_eval' => $totalPendingEval,
            'total_reports_approved' => $totalReportsApproved,
            'total_reports_pending' => $totalReportsPending,
            'active_students' => $totalStudents,
            'upcoming_students' => 0,
            'completed_students' => 0,
            'pending_logbooks' => 0,
        ];

        // Query penempatan dengan relasi lengkap
        $query = (clone $baseQuery)->with([
            'application.user.studentProfile',
            'application.user.universityRelation',
            'application.unit.agencyProfile',
            'logbooks',
            'evaluation',
            'finalreport',
            'academicAdvisor',
        ]);

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
        $universities = University::orderBy('name')->get();

        return view('mentor.dashboard', compact('placements', 'stats', 'universities', 'tab', 'search'));
    }

    /**
     * Detail Aktivitas & Logbook Mahasiswa Tertentu
     */
    public function showStudent($placementId, LogbookWeeklyBundler $bundler)
    {
        $mentor = Auth::user();

        $placement = Placement::with([
            'application.user.studentProfile',
            'application.unit.agencyProfile',
            'logbooks' => function ($q) {
                $q->orderBy('date', 'desc')->orderBy('id', 'desc');
            },
            'evaluation',
            'finalreport',
            'academicAdvisor',
        ])->findOrFail($placementId);

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
            'feedback' => 'nullable|string|max:1000',
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
