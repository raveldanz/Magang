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
     * Menu Monitoring Rekapitulasi Logbook & Progres Laporan Mahasiswa Kampus
     */
    public function index(Request $request)
    {
        $lecturer = Auth::user();

        $query = Placement::with([
            'application.user.studentProfile',
            'application.unit.agencyProfile',
            'mentor',
            'pembimbing',
            'logbooks',
            'finalreport',
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

        $placements = $query->latest()->get();
        $agencies = AgencyProfile::all();

        return view('lecturer.monitoring.index', compact('placements', 'lecturer', 'agencies'));
    }
}
