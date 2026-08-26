<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
<<<<<<< HEAD
=======
use App\Models\AgencyProfile;
>>>>>>> main
use App\Models\Placement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
<<<<<<< HEAD
     * Helper untuk membuat query placement terisolasi khusus kampus dosen yang sedang login
=======
     * Helper untuk membuat query placement terisolasi khusus DPL yang sedang login
>>>>>>> main
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
<<<<<<< HEAD
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
=======
        ])->where('academic_advisor_id', $lecturer->id);
    }

    /**
     * Dashboard DPL: Menampilkan ringkasan mahasiswa bimbingan DPL & status evaluasi
>>>>>>> main
     */
    public function index(Request $request)
    {
        $lecturer = Auth::user();
        $query = $this->getLecturerPlacementsQuery();

        if ($request->filled('search')) {
<<<<<<< HEAD
            $search = strtolower($request->search);
            $query->whereHas('application.user', function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                  ->orWhereHas('studentProfile', function ($sp) use ($search) {
                      $sp->whereRaw('LOWER(nim) LIKE ?', ["%{$search}%"])
                         ->orWhereRaw('LOWER(jurusan) LIKE ?', ["%{$search}%"]);
=======
            $search = trim($request->search);
            $like = \DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

            $query->whereHas('application.user', function ($q) use ($search, $like) {
                $q->where('name', $like, "%{$search}%")
                  ->orWhereHas('studentProfile', function ($sp) use ($search, $like) {
                      $sp->where('nim', $like, "%{$search}%")
                         ->orWhere('jurusan', $like, "%{$search}%");
>>>>>>> main
                  });
            });
        }

        if ($request->filled('agency_id')) {
            $query->whereHas('application.unit', function ($q) use ($request) {
                $q->where('agency_profile_id', $request->agency_id);
            });
        }

        $placements = $query->latest()->get();

<<<<<<< HEAD
        // Hitung metrik statistik kampus
        $totalStudents = $placements->count();
        $totalEvaluated = $placements->filter(function ($p) {
            return $p->evaluation && $p->evaluation->nilai_akademik > 0;
=======
        // Hitung metrik statistik bimbingan DPL
        $totalStudents = $placements->count();
        $totalEvaluated = $placements->filter(function ($p) {
            return $p->evaluation && ($p->evaluation->nilai_akademik > 0 || $p->evaluation->nilai_dosen > 0);
>>>>>>> main
        })->count();
        $totalPendingEval = max(0, $totalStudents - $totalEvaluated);
        $totalReportsApproved = $placements->filter(function ($p) {
            return optional($p->finalreport)->status === 'approved';
        })->count();
<<<<<<< HEAD
=======
        $totalReportsPending = $placements->filter(function ($p) {
            return $p->finalreport && $p->finalreport->status !== 'approved';
        })->count();
>>>>>>> main

        $stats = [
            'total_students' => $totalStudents,
            'total_evaluated' => $totalEvaluated,
            'total_pending_eval' => $totalPendingEval,
            'total_reports_approved' => $totalReportsApproved,
<<<<<<< HEAD
        ];

        return view('lecturer.dashboard', compact('placements', 'stats', 'lecturer'));
    }

    /**
     * Detail monitoring aktivitas, logbook, dan laporan akhir per mahasiswa
=======
            'total_reports_pending' => $totalReportsPending,
        ];

        $agencies = AgencyProfile::all();

        return view('lecturer.dashboard', compact('placements', 'stats', 'lecturer', 'agencies'));
    }

    /**
     * Detail monitoring aktivitas, logbook, laporan akhir, dan form penilaian per mahasiswa bimbingan
>>>>>>> main
     */
    public function showStudent($placementId)
    {
        $lecturer = Auth::user();

<<<<<<< HEAD
        $placement = $this->getLecturerPlacementsQuery()->findOrFail($placementId);
=======
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
>>>>>>> main

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
