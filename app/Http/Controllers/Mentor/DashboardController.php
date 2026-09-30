<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\FinalReport;
use App\Models\Placement;
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

        // Hitung statistik langsung dari database
        $totalStudents = (clone $baseQuery)->count();
        $activeCount = (clone $baseQuery)->whereRelation('application', 'status', 'active')->count();
        $upcomingCount = (clone $baseQuery)->whereRelation('application', 'status', 'accepted')->count();
        $completedCount = (clone $baseQuery)->whereRelation('application', 'status', 'completed')->count();

        $pendingLogbooksCount = \App\Models\Logbook::whereHas('placement', function ($q) use ($mentor) {
            $q->where(function ($sq) use ($mentor) {
                $sq->where('mentor_id', $mentor->id)
                   ->orWhere('pembimbing_id', $mentor->id);
            });
            if ($mentor->agency_profile_id !== null) {
                $q->whereHas('application.unit', function ($sq) use ($mentor) {
                    $sq->where('agency_profile_id', $mentor->agency_profile_id);
                });
            }
        })->where('status', 'pending')->count();

        $evaluatedStudentsCount = (clone $baseQuery)->has('evaluation')->count();
        $pendingEvaluationsCount = max(0, $totalStudents - $evaluatedStudentsCount);

        $stats = [
            'total_students' => $totalStudents,
            'active_students' => $activeCount,
            'upcoming_students' => $upcomingCount,
            'completed_students' => $completedCount,
            'pending_logbooks' => $pendingLogbooksCount,
            'evaluated_students' => $evaluatedStudentsCount,
            'pending_evaluations' => $pendingEvaluationsCount,
        ];

        $tab = $request->get('tab', 'active');

        // Query penempatan dengan relasi lengkap
        $query = (clone $baseQuery)->with([
            'application.user.studentProfile',
            'application.unit.agencyProfile',
            'logbooks',
            'evaluation',
            'finalreport',
            'academicAdvisor'
        ]);

        $query = match ($tab) {
            'upcoming' => $query->whereRelation('application', 'status', 'accepted'),
            'completed' => $query->whereRelation('application', 'status', 'completed'),
            'all' => $query,
            default => $query->whereRelation('application', 'status', 'active'),
        };

        $placements = $query->latest()->paginate(10)->withQueryString();

        return view('mentor.dashboard', compact('placements', 'stats', 'tab'));
    }

    /**
     * Detail Aktivitas & Logbook Mahasiswa Tertentu
     */
    public function showStudent($placementId)
    {
        $mentor = Auth::user();

        $placement = Placement::with([
            'application.user.studentProfile',
            'application.unit.agencyProfile',
            'logbooks' => function ($q) {
                $q->orderBy('date', 'desc');
            },
            'evaluation',
            'finalreport',
            'academicAdvisor'
        ])->where(function ($q) use ($mentor) {
            $q->where('mentor_id', $mentor->id)
              ->orWhere('pembimbing_id', $mentor->id);
        })->findOrFail($placementId);

        // Multi-Tenant Authorization Check
        if ($mentor->agency_profile_id !== null && optional($placement->application?->unit)->agency_profile_id !== $mentor->agency_profile_id) {
            abort(403, 'Anda tidak memiliki hak akses ke data bimbingan instansi lain.');
        }

        return view('mentor.student-detail', compact('placement'));
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
        if ($placement->mentor_id !== $mentor->id && $placement->pembimbing_id !== $mentor->id) {
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

        return redirect()->back()->with('success', 'Status laporan akhir mahasiswa berhasil diperbarui!');
    }
}
