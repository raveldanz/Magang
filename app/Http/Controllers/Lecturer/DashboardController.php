<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\Placement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Helper untuk membuat query placement terisolasi khusus kampus dosen yang sedang login
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
        ])->where(function ($q) use ($lecturer) {
            $q->where('academic_advisor_id', $lecturer->id)
              ->orWhereHas('application.user', function ($uQuery) use ($lecturer) {
                  if (!empty($lecturer->university)) {
                      $uQuery->where('university', $lecturer->university)
                             ->orWhereHas('studentProfile', function ($sp) use ($lecturer) {
                                 $sp->where('universitas', $lecturer->university);
                             });
                  }
              });
        });
    }

    /**
     * Dashboard DPL: Menampilkan ringkasan mahasiswa bimbingan kampus & status evaluasi
     */
    public function index(Request $request)
    {
        $lecturer = Auth::user();
        $query = $this->getLecturerPlacementsQuery();

        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $query->whereHas('application.user', function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                  ->orWhereHas('studentProfile', function ($sp) use ($search) {
                      $sp->whereRaw('LOWER(nim) LIKE ?', ["%{$search}%"])
                         ->orWhereRaw('LOWER(jurusan) LIKE ?', ["%{$search}%"]);
                  });
            });
        }

        if ($request->filled('agency_id')) {
            $query->whereHas('application.unit', function ($q) use ($request) {
                $q->where('agency_profile_id', $request->agency_id);
            });
        }

        $placements = $query->latest()->get();

        // Hitung metrik statistik kampus
        $totalStudents = $placements->count();
        $totalEvaluated = $placements->filter(function ($p) {
            return $p->evaluation && $p->evaluation->nilai_akademik > 0;
        })->count();
        $totalPendingEval = max(0, $totalStudents - $totalEvaluated);
        $totalReportsApproved = $placements->filter(function ($p) {
            return optional($p->finalreport)->status === 'approved';
        })->count();

        $stats = [
            'total_students' => $totalStudents,
            'total_evaluated' => $totalEvaluated,
            'total_pending_eval' => $totalPendingEval,
            'total_reports_approved' => $totalReportsApproved,
        ];

        return view('lecturer.dashboard', compact('placements', 'stats', 'lecturer'));
    }

    /**
     * Detail monitoring aktivitas, logbook, dan laporan akhir per mahasiswa
     */
    public function showStudent($placementId)
    {
        $lecturer = Auth::user();

        $placement = $this->getLecturerPlacementsQuery()->findOrFail($placementId);

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
