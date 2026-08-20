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
     * Menampilkan ringkasan metrik dan daftar mahasiswa bimbingan aktif.
     */
    public function index()
    {
        $mentor = Auth::user();

        // Ambil data penempatan mahasiswa yang diplot ke mentor ini
        $query = Placement::with([
            'application.user.studentProfile',
            'application.unit.agencyProfile',
            'logbooks',
            'evaluation',
            'finalreport'
        ])->where(function ($q) use ($mentor) {
            $q->where('mentor_id', $mentor->id)
              ->orWhere('pembimbing_id', $mentor->id);
        });

        // Multi-Tenant Isolation: Scoping ke instansi jika mentor terikat ke agency tertentu
        if ($mentor->agency_profile_id !== null) {
            $query->whereHas('application.unit', function ($q) use ($mentor) {
                $q->where('agency_profile_id', $mentor->agency_profile_id);
            });
        }

        $placements = $query->get();

        // Hitung statistik ringkasan untuk dashboard cards
        $totalStudents = $placements->count();
        $pendingLogbooksCount = $placements->sum(function ($placement) {
            return $placement->logbooks->where('status', 'pending')->count();
        });
        $evaluatedStudentsCount = $placements->filter(function ($placement) {
            return $placement->evaluation !== null;
        })->count();
        $pendingEvaluationsCount = $totalStudents - $evaluatedStudentsCount;

        $stats = [
            'total_students' => $totalStudents,
            'pending_logbooks' => $pendingLogbooksCount,
            'evaluated_students' => $evaluatedStudentsCount,
            'pending_evaluations' => $pendingEvaluationsCount,
        ];

        return view('mentor.dashboard', compact('placements', 'stats'));
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

        return redirect()->back()->with('success', 'Status laporan akhir mahasiswa berhasil diperbarui!');
    }
}
