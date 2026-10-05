<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\AuditLog;
use App\Models\Unit;
use App\Models\University;
use App\Models\User;
use App\Services\SystemHealth;
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
        $isSuperAdmin = $user->isSuperAdmin();
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

        // Metrik Agregat Mahasiswa (Direct Database Query Aggregation)
        $totalStudents = (clone $appQuery)->count();
        $totalPending = (clone $appQuery)->whereIn('status', ['pending', 'verified'])->count();
        $totalAccepted = (clone $appQuery)->where('status', 'accepted')->count();
        $totalActive = (clone $appQuery)->where('status', 'active')->count();
        $totalCompleted = (clone $appQuery)->where('status', 'completed')->count();
        $totalRejected = (clone $appQuery)->where('status', 'rejected')->count();
        $totalResigned = (clone $appQuery)->where('status', 'resigned')->count();

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
        $totalMentors = User::whereIn('role', ['mentor', 'pembimbing'])->when($agencyId, fn ($q) => $q->where('agency_profile_id', $agencyId))->count();
        $totalLecturers = User::whereIn('role', ['dosen', 'academic_advisor'])->count();

        // Distribusi Sebaran Instansi (Jika Super Admin) atau Unit Divisi (Jika Admin Dinas)
        // Agregasi dalam satu query GROUP BY per jenis (sebelumnya 1-2 query per instansi/unit/kampus).
        $agencies = AgencyProfile::all();
        $agencyStats = [];
        $unitStats = [];
        $percent = fn (int $count) => $totalStudents > 0 ? round(($count / $totalStudents) * 100, 1) : 0;

        if ($isSuperAdmin) {
            $countsByAgency = Application::query()
                ->join('units', 'units.id', '=', 'applications.unit_id')
                ->groupBy('units.agency_profile_id')
                ->selectRaw('units.agency_profile_id as agency_id, COUNT(*) as total')
                ->pluck('total', 'agency_id');
            $quotaByAgency = Unit::query()
                ->groupBy('agency_profile_id')
                ->selectRaw('agency_profile_id, SUM(quota) as total')
                ->pluck('total', 'agency_profile_id');

            foreach ($agencies as $ag) {
                $count = (int) ($countsByAgency[$ag->id] ?? 0);
                $agencyStats[] = [
                    'id' => $ag->id,
                    'name' => $ag->agency_name,
                    'count' => $count,
                    'quota' => (int) ($quotaByAgency[$ag->id] ?? 0),
                    'percentage' => $percent($count),
                ];
            }
        } elseif ($agencyId) {
            $countsByUnit = Application::query()
                ->whereIn('unit_id', $units->pluck('id'))
                ->groupBy('unit_id')
                ->selectRaw('unit_id, COUNT(*) as total')
                ->pluck('total', 'unit_id');

            foreach ($units as $u) {
                $count = (int) ($countsByUnit[$u->id] ?? 0);
                $unitStats[] = [
                    'id' => $u->id,
                    'name' => $u->name,
                    'count' => $count,
                    'quota' => $u->quota,
                    'percentage' => $percent($count),
                ];
            }
        }

        // Distribusi Kampus Universitas (berdasarkan users.university_id — sudah dirapikan lewat migrasi backfill)
        $universities = University::all();
        $countsByUniversity = Application::query()
            ->join('users', 'users.id', '=', 'applications.user_id')
            ->when($agencyId, fn ($q) => $q->join('units', 'units.id', '=', 'applications.unit_id')
                ->where('units.agency_profile_id', $agencyId))
            ->whereNotNull('users.university_id')
            ->groupBy('users.university_id')
            ->selectRaw('users.university_id as university_id, COUNT(*) as total')
            ->pluck('total', 'university_id');

        $universityStats = [];
        foreach ($universities as $un) {
            $count = (int) ($countsByUniversity[$un->id] ?? 0);
            if ($count > 0 || $isSuperAdmin) {
                $universityStats[] = [
                    'id' => $un->id,
                    'name' => $un->name,
                    'code' => $un->code,
                    'count' => $count,
                    'percentage' => $percent($count),
                ];
            }
        }

        // Aktivitas Audit Terkini
        $recentAuditLogs = AuditLog::latest()->take(8)->get();

        // Pengajuan Magang Terbaru
        $recentApplications = (clone $appQuery)->latest()->take(6)->get();

        // Perguruan tinggi baru yang belum punya akun portal
        $pendingUniversities = University::whereDoesntHave('users', function ($q) {
            $q->where('role', 'universitas');
        })->withCount('students')->get();

        // Instansi dinas baru yang belum punya akun admin dinas
        $pendingAgencies = AgencyProfile::whereDoesntHave('users', function ($q) {
            $q->where('role', 'admin');
        })->withCount('units')->get();

        // Fase 1: kampus baru hasil input mandiri mahasiswa (menunggu verifikasi Super Admin)
        $unverifiedUniversities = $isSuperAdmin
            ? University::pendingVerification()->withCount('students')->latest()->take(10)->get()
            : collect();

        // Kesehatan proses latar belakang (scheduler & queue worker) — hanya untuk Super Admin
        // Di environment local (development) scheduler & queue worker biasanya tidak dijalankan,
        // jadi peringatan ini hanya ditampilkan di server (staging/production).
        $systemIssues = ($isSuperAdmin && ! app()->environment('local'))
            ? app(SystemHealth::class)->status()['issues']
            : [];

        $stats = [
            'total_students' => $totalStudents,
            'total_pending' => $totalPending,
            'total_accepted' => $totalAccepted,
            'total_active' => $totalActive,
            'total_completed' => $totalCompleted,
            'total_rejected' => $totalRejected,
            'total_resigned' => $totalResigned,
            'total_quota_available' => $totalQuotaAvailable,
            'total_units' => $totalUnits,
            'total_agencies' => $totalAgencies,
            'total_universities' => $totalUniversities,
            'total_users' => $totalUsers,
            'total_mentors' => $totalMentors,
            'total_lecturers' => $totalLecturers,
            'pending_universities_count' => $pendingUniversities->count(),
            'pending_agencies_count' => $pendingAgencies->count(),
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
            'pendingUniversities',
            'pendingAgencies',
            'unverifiedUniversities',
            'systemIssues'
        ));
    }
}
