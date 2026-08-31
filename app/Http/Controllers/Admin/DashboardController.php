<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\AuditLog;
use App\Models\Placement;
use App\Models\Unit;
use App\Models\University;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Executive Analytics & Global Visibility Dashboard (Super Admin & Admin Dinas)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isSuperAdmin = ($user->role === 'super_admin' || ($user->role === 'admin' && is_null($user->agency_profile_id)));
        $agencyId = $isSuperAdmin ? $request->agency_id : $user->agency_profile_id;

        // Base Query Applications
        $appQuery = Application::with(['user.studentProfile', 'unit.agencyProfile', 'placement.evaluation', 'placement.finalreport']);
        
        if ($agencyId) {
            $appQuery->whereHas('unit', function ($q) use ($agencyId) {
                $q->where('agency_profile_id', $agencyId);
            });
        }

        if ($request->filled('university_id')) {
            $univId = $request->university_id;
            $appQuery->whereHas('user', function ($uq) use ($univId) {
                $uq->where('university_id', $univId);
            });
        }

        $allApplications = $appQuery->latest()->get();

        // Metrik Agregat Mahasiswa
        $totalStudents = $allApplications->count();
        $totalPending = $allApplications->whereIn('status', ['pending', 'submitted'])->count();
        $totalAccepted = $allApplications->whereIn('status', ['accepted', 'verified'])->count();
        $totalRejected = $allApplications->where('status', 'rejected')->count();

        $today = now();
        $totalActive = $allApplications->filter(function ($app) use ($today) {
            $isAccepted = in_array(strtolower($app->status), ['accepted', 'verified']);
            $isStarted = $app->start_date && $today->gte(\Carbon\Carbon::parse($app->start_date));
            $isNotDone = !($app->finalReport && strtoupper($app->finalReport->status) === 'APPROVED' && optional($app->placement?->evaluation)->nilai_akademik > 0);
            return $isAccepted && $isStarted && $isNotDone;
        })->count();

        $totalCompleted = $allApplications->filter(function ($app) {
            return ($app->finalReport && strtoupper($app->finalReport->status) === 'APPROVED') ||
                   ($app->placement && optional($app->placement->finalreport)->status === 'approved' && optional($app->placement->evaluation)->nilai_akademik > 0);
        })->count();

        // Kuota Unit Magang
        $unitsQuery = Unit::with('agencyProfile');
        if ($agencyId) {
            $unitsQuery->where('agency_profile_id', $agencyId);
        }
        $units = $unitsQuery->get();
        $totalQuotaAvailable = $units->sum('quota');
        $totalUnits = $units->count();

        // Master Counters
        $totalAgencies = AgencyProfile::count();
        $totalUniversities = University::count();
        $totalUsers = User::count();
        $totalMentors = User::whereIn('role', ['mentor', 'pembimbing'])->when($agencyId, fn($q) => $q->where('agency_profile_id', $agencyId))->count();
        $totalLecturers = User::whereIn('role', ['dosen', 'academic_advisor'])->count();

        // Distribusi Sebaran Instansi (Jika Super Admin) atau Unit Divisi (Jika Admin Dinas)
        $agencies = AgencyProfile::all();
        $agencyStats = [];
        $unitStats = [];
        if ($isSuperAdmin) {
            foreach ($agencies as $ag) {
                $count = Application::whereHas('unit', fn($q) => $q->where('agency_profile_id', $ag->id))->count();
                $agQuota = Unit::where('agency_profile_id', $ag->id)->sum('quota');
                $agencyStats[] = [
                    'id' => $ag->id,
                    'name' => $ag->agency_name,
                    'count' => $count,
                    'quota' => $agQuota,
                    'percentage' => $totalStudents > 0 ? round(($count / $totalStudents) * 100, 1) : 0,
                ];
            }
        } else if ($agencyId) {
            foreach ($units as $u) {
                $count = Application::where('unit_id', $u->id)->count();
                $unitStats[] = [
                    'id' => $u->id,
                    'name' => $u->name,
                    'count' => $count,
                    'quota' => $u->quota,
                    'percentage' => $totalStudents > 0 ? round(($count / $totalStudents) * 100, 1) : 0,
                ];
            }
        }

        // Distribusi Kampus Universitas
        $universities = University::all();
        $universityStats = [];
        foreach ($universities as $un) {
            $count = Application::whereHas('user', function ($uq) use ($un) {
                $uq->where('university_id', $un->id)->orWhere('university', $un->name);
            })->when($agencyId, function ($aq) use ($agencyId) {
                $aq->whereHas('unit', fn($uq) => $uq->where('agency_profile_id', $agencyId));
            })->count();

            if ($count > 0 || $isSuperAdmin) {
                $universityStats[] = [
                    'id' => $un->id,
                    'name' => $un->name,
                    'code' => $un->code,
                    'count' => $count,
                    'percentage' => $totalStudents > 0 ? round(($count / $totalStudents) * 100, 1) : 0,
                ];
            }
        }

        // Aktivitas Audit Terkini
        $recentAuditLogs = AuditLog::latest()->take(8)->get();

        // Pengajuan Magang Terbaru
        $recentApplications = $allApplications->take(6);

        // Perguruan tinggi baru yang belum punya akun portal
        $pendingUniversities = University::whereDoesntHave('users', function ($q) {
            $q->where('role', 'universitas');
        })->withCount('students')->get();

        $stats = [
            'total_students' => $totalStudents,
            'total_pending' => $totalPending,
            'total_accepted' => $totalAccepted,
            'total_active' => $totalActive,
            'total_completed' => $totalCompleted,
            'total_rejected' => $totalRejected,
            'total_quota_available' => $totalQuotaAvailable,
            'total_units' => $totalUnits,
            'total_agencies' => $totalAgencies,
            'total_universities' => $totalUniversities,
            'total_users' => $totalUsers,
            'total_mentors' => $totalMentors,
            'total_lecturers' => $totalLecturers,
            'pending_universities_count' => $pendingUniversities->count(),
        ];

        $currentAgency = $agencyId ? AgencyProfile::find($agencyId) : null;

        return view('admin.dashboard', compact(
            'isSuperAdmin',
            'user',
            'stats',
            'agencies',
            'universities',
            'agencyStats',
            'unitStats',
            'universityStats',
            'recentApplications',
            'recentAuditLogs',
            'currentAgency',
            'agencyId',
            'pendingUniversities'
        ));
    }
}
