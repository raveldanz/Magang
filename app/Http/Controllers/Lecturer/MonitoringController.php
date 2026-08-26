<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\AgencyProfile;
use App\Models\Placement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MonitoringController extends Controller
{
    /**
<<<<<<< HEAD
     * Menu Monitoring Rekapitulasi Logbook & Progres Laporan Mahasiswa Kampus
=======
     * Menu Monitoring Rekapitulasi Logbook & Progres Mahasiswa Kampus dengan Segregasi Lifecycle
     * Strictly Scoped ke Dosen Pembimbing Lapangan (academic_advisor_id) yang sedang login
>>>>>>> main
     */
    public function index(Request $request)
    {
        $lecturer = Auth::user();
<<<<<<< HEAD
=======
        $lecturerId = $lecturer->id;
>>>>>>> main

        $query = Placement::with([
            'application.user.studentProfile',
            'application.unit.agencyProfile',
            'mentor',
            'pembimbing',
            'logbooks',
            'finalreport',
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
=======
            'evaluation',
        ])->where('academic_advisor_id', $lecturerId);
>>>>>>> main

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('application.user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('studentProfile', function ($sp) use ($search) {
                      $sp->where('nim', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('agency_id')) {
            $query->whereHas('application.unit', function ($q) use ($request) {
                $q->where('agency_profile_id', $request->agency_id);
            });
        }

<<<<<<< HEAD
        $placements = $query->latest()->get();
        $agencies = AgencyProfile::all();

        return view('lecturer.monitoring.index', compact('placements', 'lecturer', 'agencies'));
=======
        $allPlacements = $query->latest()->get();

        // Pisahkan data bimbingan dosen berdasarkan lifecycle
        $activeStudents = $allPlacements->filter(fn($p) => $p->application?->lifecycle_status === 'ACTIVE');
        $completedStudents = $allPlacements->filter(fn($p) => $p->application?->lifecycle_status === 'COMPLETED');
        $upcomingStudents = $allPlacements->filter(fn($p) => $p->application?->lifecycle_status === 'ACCEPTED');

        $tab = $request->get('tab', 'active');
        $placements = match ($tab) {
            'completed' => $completedStudents,
            'upcoming' => $upcomingStudents,
            'all' => $allPlacements,
            default => $activeStudents,
        };

        $stats = [
            'total' => $allPlacements->count(),
            'active' => $activeStudents->count(),
            'completed' => $completedStudents->count(),
            'upcoming' => $upcomingStudents->count(),
        ];

        $agencies = AgencyProfile::all();

        return view('lecturer.monitoring.index', compact('placements', 'lecturer', 'agencies', 'stats', 'tab'));
>>>>>>> main
    }
}
