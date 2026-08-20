<?php

namespace App\Http\Controllers\University;

use App\Http\Controllers\Controller;
use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\Placement;
use App\Models\University;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Dashboard Portal Resmi Universitas
     * Menampilkan total mahasiswa magang asal kampus, status, dan sebaran dinas penempatan
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Cari master universitas terkait
        $university = $user->university_id 
            ? University::find($user->university_id) 
            : University::where('name', $user->university)->orWhere('code', $user->university)->first();

        // 1. Query Mahasiswa yang Memiliki Penempatan (Placement)
        $placementsQuery = Placement::with([
            'application.user.studentProfile',
            'application.unit.agencyProfile',
            'mentor',
            'pembimbing',
            'academicAdvisor',
            'logbooks',
            'finalreport',
            'evaluation'
        ])->whereHas('application.user', function ($uq) use ($user, $university) {
            if ($user->university_id) {
                $uq->where('university_id', $user->university_id);
            } elseif ($university) {
                $uq->where('university', $university->name);
            }
        });

        // 2. Query Semua Pengajuan Mahasiswa Asal Kampus Ini
        $applicationsQuery = Application::with([
            'user.studentProfile',
            'unit.agencyProfile',
            'placement.mentor',
            'placement.academicAdvisor',
            'placement.evaluation'
        ])->whereHas('user', function ($uq) use ($user, $university) {
            if ($user->university_id) {
                $uq->where('university_id', $user->university_id);
            } elseif ($university) {
                $uq->where('university', $university->name);
            }
        });

        // Filter Search & Agency
        if ($request->filled('search')) {
            $search = $request->search;
            $applicationsQuery->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('studentProfile', function ($sp) use ($search) {
                      $sp->where('nim', 'like', "%{$search}%")
                         ->orWhere('jurusan', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('agency_id')) {
            $applicationsQuery->whereHas('unit', function ($q) use ($request) {
                $q->where('agency_profile_id', $request->agency_id);
            });
        }

        $allApplications = $applicationsQuery->latest()->get();
        $allPlacements = $placementsQuery->latest()->get();

        // Metrik Statistik Kampus
        $totalStudents = $allApplications->count();
        $totalAccepted = $allApplications->where('status', 'accepted')->count();
        $totalCompleted = $allPlacements->filter(function ($p) {
            return optional($p->finalreport)->status === 'approved' && optional($p->evaluation)->nilai_akademik > 0;
        })->count();
        $totalPending = $allApplications->where('status', 'pending')->count();

        // Sebaran Dinas / Instansi Penempatan
        $agencies = AgencyProfile::all();
        $agencyDistribution = [];

        foreach ($agencies as $agency) {
            $count = $allApplications->filter(function ($app) use ($agency) {
                return optional($app->unit)->agency_profile_id === $agency->id;
            })->count();

            $agencyDistribution[] = [
                'id' => $agency->id,
                'name' => $agency->agency_name,
                'count' => $count,
                'percentage' => $totalStudents > 0 ? round(($count / $totalStudents) * 100, 1) : 0,
            ];
        }

        $stats = [
            'total_students' => $totalStudents,
            'total_accepted' => $totalAccepted,
            'total_completed' => $totalCompleted,
            'total_pending' => $totalPending,
        ];

        return view('university.dashboard', compact(
            'user',
            'university',
            'allApplications',
            'stats',
            'agencyDistribution',
            'agencies'
        ));
    }

    /**
     * Detail Mahasiswa Kampus untuk Pemantauan Universitas
     */
    public function showStudent($placementId)
    {
        $user = Auth::user();
        $university = $user->university_id ? University::find($user->university_id) : null;

        $placement = Placement::with([
            'application.user.studentProfile',
            'application.unit.agencyProfile',
            'mentor',
            'pembimbing',
            'academicAdvisor',
            'logbooks' => function ($q) {
                $q->orderBy('date', 'desc');
            },
            'finalreport',
            'evaluation'
        ])->findOrFail($placementId);

        $student = $placement->application->user;

        // Otorisasi: Pastikan mahasiswa berasal dari universitas yang sama
        $isSameUniv = ($user->university_id !== null && $student->university_id === $user->university_id);
        if (!$isSameUniv && $university) {
            $isSameUniv = ($student->university === $university->name || optional($student->studentProfile)->universitas === $university->name);
        }

        if (!$isSameUniv) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat data mahasiswa kampus lain.');
        }

        return view('lecturer.student-detail', [
            'placement' => $placement,
            'student' => $student,
            'profile' => $student->studentProfile,
            'unit' => $placement->application->unit,
            'agencyProfile' => $placement->application->unit?->agencyProfile,
            'mentor' => $placement->mentor ?? $placement->pembimbing,
            'logbooks' => $placement->logbooks,
            'finalReport' => $placement->finalreport,
            'evaluation' => $placement->evaluation,
        ]);
    }
}
