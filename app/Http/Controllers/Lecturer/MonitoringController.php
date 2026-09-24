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

        $baseQuery = Placement::where('academic_advisor_id', $lecturerId);

        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $baseQuery->whereHas('application.user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('studentProfile', function ($sp) use ($search) {
                      $sp->where('nim', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('agency_id')) {
            $baseQuery->whereHas('application.unit', function ($q) use ($request) {
                $q->where('agency_profile_id', $request->agency_id);
            });
        }

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'active' => (clone $baseQuery)->whereRelation('application', 'status', 'active')->count(),
            'completed' => (clone $baseQuery)->whereRelation('application', 'status', 'completed')->count(),
            'upcoming' => (clone $baseQuery)->whereRelation('application', 'status', 'accepted')->count(),
        ];

        $tab = $request->get('tab', 'active');

        $query = (clone $baseQuery)->with([
            'application.user.studentProfile',
            'application.unit.agencyProfile',
            'mentor',
            'pembimbing',
            'logbooks',
            'finalreport',
            'evaluation',
        ]);

        $query = match ($tab) {
            'completed' => $query->whereRelation('application', 'status', 'completed'),
            'upcoming' => $query->whereRelation('application', 'status', 'accepted'),
            'all' => $query,
            default => $query->whereRelation('application', 'status', 'active'),
        };

        $placements = $query->latest()->paginate(10)->withQueryString();

        $agencies = AgencyProfile::all();

        return view('lecturer.monitoring.index', compact('placements', 'lecturer', 'agencies', 'stats', 'tab'));
    }
}
