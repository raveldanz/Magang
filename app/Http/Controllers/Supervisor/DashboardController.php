<?php

namespace App\Http\Controllers\Supervisor;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\Certificate;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Tampilan Monitoring Tim Kerja & Kepegawaian (Senior-Friendly UI)
     */
    public function index(Request $request)
    {
        $dispusip = AgencyProfile::where('agency_name', 'like', '%Perpustakaan%')->first()
            ?? AgencyProfile::first();

        // 4 Bidang Resmi Dispusip
        $units = Unit::with([
            'headUser',
            'mentors' => function ($q) {
                $q->withCount([
                    'mentorPlacements as active_students_count' => function ($pq) {
                        $pq->whereHas('application', fn ($aq) => $aq->whereIn('status', ['accepted', 'active']));
                    },
                ]);
            },
            'applications' => function ($q) {
                $q->select('id', 'unit_id', 'status', 'start_date', 'end_date');
            },
        ])
            ->where('agency_profile_id', $dispusip?->id ?? 1)
            ->get();

        // Ringkasan Statistik Senior-Friendly
        $pendingApplicationsCount = Application::where('status', ApplicationStatus::WAITING_APPROVAL->value)
            ->when($dispusip, fn ($q) => $q->whereHas('unit', fn ($uq) => $uq->where('agency_profile_id', $dispusip->id)))
            ->count();

        $pendingCertificatesCount = Certificate::where('approval_status', 'waiting_approval')
            ->count();

        $totalActiveInterns = Application::whereIn('status', [ApplicationStatus::ACCEPTED->value, ApplicationStatus::ACTIVE->value])
            ->when($dispusip, fn ($q) => $q->whereHas('unit', fn ($uq) => $uq->where('agency_profile_id', $dispusip->id)))
            ->count();

        $totalCompletedInterns = Application::where('status', ApplicationStatus::COMPLETED->value)
            ->when($dispusip, fn ($q) => $q->whereHas('unit', fn ($uq) => $uq->where('agency_profile_id', $dispusip->id)))
            ->count();

        $totalMentors = User::where('role', 'mentor')
            ->when($dispusip, fn ($q) => $q->where('agency_profile_id', $dispusip->id))
            ->count();

        return view('supervisor.dashboard', compact(
            'dispusip',
            'units',
            'pendingApplicationsCount',
            'pendingCertificatesCount',
            'totalActiveInterns',
            'totalCompletedInterns',
            'totalMentors'
        ));
    }
}
