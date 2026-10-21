<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\AgencyProfile;
use App\Models\FinalReport;
use App\Models\InternshipProject;
use App\Models\Placement;
use App\Models\Unit;
use Illuminate\Http\Request;

class StudentReportController extends Controller
{
    /**
     * Tampilkan Rekapitulasi Luaran dan Laporan Mahasiswa (Pemantauan Eksekutif Tanpa Menilai)
     */
    public function index(Request $request)
    {
        $dispusip = AgencyProfile::where('agency_name', 'like', '%Perpustakaan%')->first()
            ?? AgencyProfile::first();

        // 4 Bidang Devisi Dispusip
        $units = Unit::where('agency_profile_id', $dispusip?->id ?? 1)
            ->orderBy('id')
            ->get();

        $query = Placement::with([
            'application.user.studentProfile',
            'application.unit',
            'mentor',
            'academicAdvisor',
            'finalreport',
            'internshipProjects',
            'evaluation',
        ])
            ->whereHas('application', function ($q) use ($dispusip) {
                if ($dispusip) {
                    $q->whereHas('unit', fn ($uq) => $uq->where('agency_profile_id', $dispusip->id));
                }
                $q->whereNotIn('status', ['rejected', 'resigned']);
            });

        // Filter Bidang Devisi
        if ($request->filled('unit_id')) {
            $query->whereHas('application', fn ($q) => $q->where('unit_id', $request->unit_id));
        }

        // Filter Pencarian
        if ($request->filled('search')) {
            $search = trim($request->search);
            $like = \Illuminate\Support\Facades\DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
            $query->whereHas('application.user', function ($q) use ($search, $like) {
                $q->where('name', $like, "%{$search}%")
                    ->orWhereHas('studentProfile', function ($sq) use ($search, $like) {
                        $sq->where('nim', $like, "%{$search}%")
                            ->orWhere('universitas', $like, "%{$search}%")
                            ->orWhere('jurusan', $like, "%{$search}%");
                    });
            });
        }

        // Filter Status Naskah Laporan
        if ($request->filled('status')) {
            $status = $request->status;
            if ($status === 'approved') {
                $query->whereHas('finalreport', fn ($q) => $q->where('status', 'approved'));
            } elseif ($status === 'pending') {
                $query->whereHas('finalreport', fn ($q) => $q->where('status', 'pending'));
            } elseif ($status === 'revision') {
                $query->whereHas('finalreport', fn ($q) => $q->where('status', 'revision'));
            } elseif ($status === 'not_uploaded') {
                $query->whereDoesntHave('finalreport');
            }
        }

        $placements = $query->latest('id')->paginate(15)->withQueryString();

        // Metrik Ringkas & Bersih
        $totalPlacements = Placement::whereHas('application', function ($q) use ($dispusip) {
            if ($dispusip) {
                $q->whereHas('unit', fn ($uq) => $uq->where('agency_profile_id', $dispusip->id));
            }
            $q->whereNotIn('status', ['rejected', 'resigned']);
        })->count();

        $totalProjects = InternshipProject::whereHas('placement.application', function ($q) use ($dispusip) {
            if ($dispusip) {
                $q->whereHas('unit', fn ($uq) => $uq->where('agency_profile_id', $dispusip->id));
            }
        })->count();

        $totalApprovedReports = FinalReport::where('status', 'approved')
            ->whereHas('placement.application', function ($q) use ($dispusip) {
                if ($dispusip) {
                    $q->whereHas('unit', fn ($uq) => $uq->where('agency_profile_id', $dispusip->id));
                }
            })->count();

        $unitFilter = $request->unit_id;
        $statusFilter = $request->status;
        $searchQuery = $request->search;

        return view('supervisor.reports.index', compact(
            'placements',
            'units',
            'totalPlacements',
            'totalProjects',
            'totalApprovedReports',
            'unitFilter',
            'statusFilter',
            'searchQuery'
        ));
    }

    /**
     * Tampilkan Detail Kinerja & Luaran Portofolio Per Mahasiswa
     */
    public function show($placementId)
    {
        $placement = Placement::with([
            'application.user.studentProfile',
            'application.unit.agencyProfile',
            'mentor',
            'academicAdvisor',
            'finalreport',
            'internshipProjects',
            'evaluation',
            'logbooks',
        ])->findOrFail($placementId);

        $student = $placement->application?->user;
        $profile = $student?->studentProfile;
        $unit = $placement->application?->unit;
        $mentor = $placement->mentor;
        $academicAdvisor = $placement->academicAdvisor;
        $finalReport = $placement->finalreport;
        $projects = $placement->internshipProjects;
        $evaluation = $placement->evaluation;

        $logbookCount = $placement->logbooks->count();
        $approvedLogbookCount = $placement->logbooks->where('status', 'approved')->count();

        return view('supervisor.reports.show', compact(
            'placement',
            'student',
            'profile',
            'unit',
            'mentor',
            'academicAdvisor',
            'finalReport',
            'projects',
            'evaluation',
            'logbookCount',
            'approvedLogbookCount'
        ));
    }
}
