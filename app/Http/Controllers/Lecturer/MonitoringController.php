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
     * Menu Monitoring Rekapitulasi Logbook & Progres Mahasiswa Kampus dengan Segregasi Lifecycle
     * Strictly Scoped ke Dosen Pembimbing Lapangan (academic_advisor_id) yang sedang login
     */
    public function index(Request $request)
    {
        $lecturer = Auth::user();
        $lecturerId = $lecturer->id;

        $query = Placement::with([
            'application.user.studentProfile',
            'application.unit.agencyProfile',
            'mentor',
            'pembimbing',
            'logbooks',
            'finalreport',
            'evaluation',
        ])->where('academic_advisor_id', $lecturerId);

        if ($request->filled('search')) {
            $search = strtolower($request->search);
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
    }
}
