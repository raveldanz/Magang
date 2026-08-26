<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\AgencyProfile;
use App\Models\Placement;
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
        ])->where('academic_advisor_id', $lecturer->id);
    }

    /**
     * Dashboard DPL: Menampilkan ringkasan mahasiswa bimbingan DPL & status evaluasi
     */
    public function index(Request $request)
    {
        $lecturer = Auth::user();
        $query = $this->getLecturerPlacementsQuery();

        if ($request->filled('search')) {
            $search = trim($request->search);
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

        $placements = $query->latest()->get();

        // Hitung metrik statistik bimbingan DPL
        $totalStudents = $placements->count();
        $totalEvaluated = $placements->filter(function ($p) {
            return $p->evaluation && ($p->evaluation->nilai_akademik > 0 || $p->evaluation->nilai_dosen > 0);
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

        $agencies = AgencyProfile::all();

        return view('lecturer.dashboard', compact('placements', 'stats', 'lecturer', 'agencies'));
    }

    /**
     * Detail monitoring aktivitas, logbook, laporan akhir, dan form penilaian per mahasiswa bimbingan
     */
    public function showStudent($placementId)
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
        ])->find($placementId) ?? Placement::with([
            'application.user.studentProfile',
            'application.unit.agencyProfile',
            'mentor',
            'pembimbing',
            'logbooks',
            'finalreport',
            'evaluation',
        ])->where('application_id', $placementId)->firstOrFail();

        $isAssignedAdvisor = ($placement->academic_advisor_id === $lecturer->id);
        $isSuperAdmin = ($lecturer->role === 'super_admin' || ($lecturer->role === 'admin' && is_null($lecturer->agency_profile_id)));

        if (!$isAssignedAdvisor && !$isSuperAdmin) {
            abort(403, 'Akses Ditolak: Anda bukan Dosen Pembimbing Lapangan (DPL) yang ditugaskan untuk mahasiswa ini.');
        }

        $student = $placement->application->user;
        $profile = $student->studentProfile;
        $unit = $placement->application->unit;
        $agencyProfile = $unit?->agencyProfile ?? $placement->agencyProfile;
        $mentor = $placement->mentor ?? $placement->pembimbing;
        $logbooks = $placement->logbooks()->orderBy('date', 'desc')->get();
        $finalReport = $placement->finalreport;
        $evaluation = $placement->evaluation;

        return view('lecturer.student-detail', compact(
            'placement',
            'student',
            'profile',
            'unit',
            'agencyProfile',
            'mentor',
            'logbooks',
            'finalReport',
            'evaluation'
        ));
    }
}
