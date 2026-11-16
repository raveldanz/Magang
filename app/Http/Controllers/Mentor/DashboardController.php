<?php

namespace App\Http\Controllers\Mentor;

use App\Enums\ReviewStatus;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\FinalReport;
use App\Models\Placement;
use App\Models\University;
use App\Services\LogbookWeeklyBundler;
use App\Services\MentorTaskList;
use App\Services\StudentNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Halaman Dashboard Utama Pembimbing Lapangan / Mentor
     * Menampilkan metrik dan daftar mahasiswa bimbingan dengan segmentasi lifecycle.
     */
    public function index(Request $request, MentorTaskList $taskList)
    {
        $mentor = Auth::user();

        // Sinkronkan seluruh pengajuan magang yang tanggal mulainya telah tiba ke ACTIVE
        Application::syncStartedApplications();

        // Satu perhitungan untuk daftar tugas, kartu angka, dan tab, agar angka di layar selalu sama.
        ['placements' => $allBasePlacements, 'tasks' => $tasks, 'summary' => $summary] = $taskList->forMentor($mentor);

        $stats = [
            'total_students' => $summary['total_students'],
            'total_evaluated' => $summary['evaluated'],
            'total_pending_eval' => $summary['not_evaluated'],
            'evaluation_due' => $summary['evaluation_due'],
            'evaluation_overdue' => $summary['evaluation_overdue'],
            'total_reports_approved' => $summary['approved_reports'],
            'total_reports_pending' => $summary['pending_reports'],
            'active_students' => $summary['active_students'],
            'upcoming_students' => $summary['upcoming_students'],
            'completed_students' => $summary['completed_students'],
            'pending_logbooks' => $summary['pending_logbook_days'],
            'pending_logbook_students' => $summary['pending_logbook_students'],
        ];

        $actionAlerts = [
            'pending_reports' => $tasks->where('type', 'report')->values(),
            'urgent_ending' => $tasks->where('type', 'evaluation')->values(),
            'pending_logbooks_count' => $summary['pending_logbook_days'],
        ];

        // Query penempatan dengan relasi untuk daftar & paginasi (lingkup sama dengan daftar tugas)
        $query = Placement::whereIn('id', $allBasePlacements->pluck('id'))
            ->with([
                'application.user.studentProfile',
                'application.user.universityRelation',
                'application.unit.agencyProfile',
                'evaluation',
                'finalreport',
            ])
            ->withCount([
                'logbooks',
                'logbooks as pending_logbooks_count' => fn ($q) => $q->where('status', 'pending'),
            ]);

        $tab = $request->get('tab');
        if ($tab && in_array($tab, ['upcoming', 'completed', 'active'], true)) {
            $query->whereHas('application', fn ($q) => $q->whereIn('status', match ($tab) {
                'upcoming' => MentorTaskList::UPCOMING_STATUSES,
                'completed' => ['completed'],
                default => ['active'],
            }));
        }

        // Filter pencarian nama / NIM / jurusan / kampus
        $search = trim((string) ($request->get('search') ?? $request->get('q', '')));
        if ($search !== '') {
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

        $tasksByPlacement = $tasks->groupBy('placement_id');

        return view('mentor.dashboard', compact('placements', 'stats', 'universities', 'tab', 'search', 'actionAlerts', 'tasks', 'tasksByPlacement'));
    }

    /**
     * Detail Aktivitas & Logbook Mahasiswa Tertentu
     */
    public function showStudent($placementId, LogbookWeeklyBundler $bundler, MentorTaskList $taskList)
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

        // Daftar periksa 3 langkah & tugas berikutnya memakai algoritma yang sama dengan dasbor
        $checklist = $taskList->checklist($placement, $mentor);
        $nextTasks = $taskList->tasksForPlacement($placement, $mentor);

        return view('mentor.student-detail', compact('placement', 'weeklyBundles', 'canEvaluate', 'evaluationLockReason', 'checklist', 'nextTasks'));
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

        if ($placement->application?->statusValue() === 'completed') {
            return redirect()->back()->with('error', 'Laporan akhir tidak dapat diubah karena magang sudah dinyatakan selesai.');
        }

        // Menyetujui tanpa catatan baru: catatan revisi lama sudah selesai ditangani, jadi dihapus
        // agar mahasiswa tidak melihat "Disetujui" bersama teguran lama. Catatan pada laporan yang
        // memang sudah disetujui sebelumnya tetap dipertahankan.
        $previousStatus = $report->status instanceof \BackedEnum ? $report->status->value : strtolower((string) $report->status);
        $feedback = $request->filled('feedback')
            ? $request->feedback
            : ($previousStatus === 'approved' ? $report->feedback : null);

        $report->update([
            'status' => $request->status,
            'feedback' => $feedback,
        ]);

        // Jika laporan di-ACC, sinkronisasi otomatis status kelulusan (COMPLETED)
        if ($request->status === 'approved') {
            $placement->syncCompletionStatus();
        }

        StudentNotifier::finalReportReviewed($placement->application?->user, 'mentor', $request->status, $request->feedback);

        return redirect()->back()->with('success', $request->status === 'approved'
            ? 'Laporan akhir disetujui. Langkah berikutnya: isi nilai akhir mahasiswa.'
            : 'Laporan akhir dikembalikan ke mahasiswa untuk diperbaiki beserta catatan Anda.');
    }
}
