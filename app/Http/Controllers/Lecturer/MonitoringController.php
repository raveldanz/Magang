<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\AgencyProfile;
use App\Models\Placement;
use App\Models\Unit;
use App\Support\CsvSafe;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

        $baseQuery = Placement::where('academic_advisor_id', $lecturerId)
            ->whereHas('application', function ($q) {
                $q->whereNotIn('status', ['resigned', 'rejected']);
            });

        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $like = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
            $baseQuery->whereHas('application.user', function ($q) use ($search, $like) {
                $q->where('name', $like, "%{$search}%")
                    ->orWhereHas('studentProfile', function ($sp) use ($search, $like) {
                        $sp->where('nim', $like, "%{$search}%");
                    });
            });
        }

        if ($request->filled('unit_id')) {
            $baseQuery->whereHas('application', function ($q) use ($request) {
                $q->where('unit_id', $request->unit_id);
            });
        } elseif ($request->filled('agency_id')) {
            $baseQuery->whereHas('application.unit', function ($q) use ($request) {
                $q->where('agency_profile_id', $request->agency_id);
            });
        }

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'active' => (clone $baseQuery)->whereRelation('application', fn ($q) => $q->whereIn('status', ['active', 'accepted']))->count(),
            'completed' => (clone $baseQuery)->whereRelation('application', 'status', 'completed')->count(),
            'upcoming' => (clone $baseQuery)->whereRelation('application', 'status', 'accepted')->count(),
        ];

        $tab = $request->get('tab', 'active');

        $query = (clone $baseQuery)->with([
            'application.user.studentProfile',
            'application.user.universityRelation',
            'application.unit.agencyProfile',
            'application.group',
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
            default => $query->whereRelation('application', fn ($q) => $q->whereIn('status', ['active', 'accepted'])),
        };

        $placements = $query->latest()->paginate(10)->withQueryString();
        $placements->getCollection()->each(function ($p) {
            if ($p->evaluation) {
                $p->evaluation->setRelation('placement', $p);
            }
        });

        $agencies = AgencyProfile::all();
        $units = Unit::orderBy('name')->get();

        return view('lecturer.monitoring.index', compact('placements', 'lecturer', 'agencies', 'units', 'stats', 'tab'));
    }

    /**
     * Ekspor Rekapitulasi Mahasiswa Bimbingan DPL ke format CSV / Excel
     * Mendukung pemfilteran pencarian, unit kerja/instansi, dan status lifecycle
     */
    public function export(Request $request)
    {
        $lecturer = Auth::user();
        $lecturerId = $lecturer->id;

        $baseQuery = Placement::where('academic_advisor_id', $lecturerId)
            ->whereHas('application', function ($q) {
                $q->whereNotIn('status', ['resigned', 'rejected']);
            });

        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $like = \DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
            $baseQuery->whereHas('application.user', function ($q) use ($search, $like) {
                $q->where('name', $like, "%{$search}%")
                    ->orWhereHas('studentProfile', function ($sp) use ($search, $like) {
                        $sp->where('nim', $like, "%{$search}%");
                    });
            });
        }

        if ($request->filled('unit_id')) {
            $baseQuery->whereHas('application', function ($q) use ($request) {
                $q->where('unit_id', $request->unit_id);
            });
        } elseif ($request->filled('agency_id')) {
            $baseQuery->whereHas('application.unit', function ($q) use ($request) {
                $q->where('agency_profile_id', $request->agency_id);
            });
        }

        $tab = $request->get('tab', 'active');

        $query = (clone $baseQuery)->with([
            'application.user.studentProfile',
            'application.unit.agencyProfile',
            'application.group',
            'mentor',
            'pembimbing',
            'logbooks',
            'finalreport',
            'evaluation',
            'academicConsultations',
        ]);

        $query = match ($tab) {
            'completed' => $query->whereRelation('application', 'status', 'completed'),
            'upcoming' => $query->whereRelation('application', 'status', 'accepted'),
            'all' => $query,
            default => $query->whereRelation('application', fn ($q) => $q->whereIn('status', ['active', 'accepted'])),
        };

        $placements = $query->latest()->get();

        $filename = 'Rekap_Bimbingan_DPL_'.Str::slug($lecturer->name, '_').'_'.date('Ymd_His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($placements) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Excel compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // CSV Header
            fputcsv($handle, [
                'No',
                'Nama Mahasiswa',
                'NIM',
                'Tipe Pendaftaran',
                'Kelompok Magang',
                'Instansi Mitra',
                'Unit Kerja / Divisi',
                'Mentor Lapangan',
                'Status Magang',
                'Periode Mulai',
                'Periode Selesai',
                'Total Logbook',
                'Logbook Disetujui',
                'Status Laporan Akhir',
                'Nilai Mentor Lapangan',
                'Nilai Dosen Pembimbing',
                'Nilai Akhir Kumulatif',
                'Nilai Huruf',
                'Jumlah Sesi Bimbingan Dosen Pembimbing',
            ]);

            $no = 1;
            foreach ($placements as $p) {
                $student = $p->application?->user;
                $profile = $student?->studentProfile;
                $app = $p->application;
                $group = $app?->group;
                $tipePengajuan = $app?->group_id ? 'Kelompok ('.($app->role_in_group === 'leader' ? 'Ketua' : 'Anggota').')' : 'Individu';
                $groupName = $group?->name ?? '-';
                $unit = $p->application?->unit;
                $agency = $unit?->agencyProfile;
                $mentor = $p->mentor ?? $p->pembimbing;

                $totalLogbooks = $p->logbooks->count();
                $approvedLogbooks = $p->logbooks
                    ->filter(fn ($l) => ($l->status instanceof \BackedEnum ? $l->status->value : $l->status) === 'approved')
                    ->count();

                $rawReportStatus = $p->finalreport?->status;
                $reportStatusVal = $rawReportStatus instanceof \BackedEnum ? $rawReportStatus->value : $rawReportStatus;
                $reportStatus = match ($reportStatusVal) {
                    'approved' => 'Disetujui',
                    'submitted', 'pending' => 'Menunggu Review',
                    'rejected', 'revision' => 'Perlu Revisi',
                    default => 'Belum Unggah',
                };

                $eval = $p->evaluation;
                $rawMentorScore = $eval ? ($eval->nilai_pembimbing_calculated ?? $eval->final_score) : null;
                $rawDplScore = $eval?->nilai_dosen_calculated;
                $rawFinalScore = $eval ? ($eval->nilai_akhir ?? $eval->final_score) : null;

                $mentorScore = $rawMentorScore !== null ? number_format((float) $rawMentorScore, 1) : '-';
                $dplScore = $rawDplScore !== null ? number_format((float) $rawDplScore, 1) : '-';
                $finalScore = $rawFinalScore !== null ? number_format((float) $rawFinalScore, 1) : '-';
                $gradeLetter = $eval ? ($eval->grade_calculated ?? $eval->grade ?? '-') : '-';
                $consultationCount = $p->academicConsultations->count();

                $rawStatus = $p->application?->status;
                $statusStr = $rawStatus instanceof \BackedEnum ? $rawStatus->value : (string) ($rawStatus ?? 'N/A');

                $startDateVal = $p->application?->start_date ?? $p->start_date;
                $endDateVal = $p->application?->end_date ?? $p->end_date;

                fputcsv($handle, CsvSafe::row([
                    $no++,
                    $student?->name ?? 'N/A',
                    $profile?->nim ?? 'N/A',
                    $tipePengajuan,
                    $groupName,
                    $agency?->agency_name ?? 'N/A',
                    $unit?->name ?? 'N/A',
                    $mentor?->name ?? 'Belum Ditugaskan',
                    strtoupper($statusStr),
                    $startDateVal ? Carbon::parse($startDateVal)->format('d/m/Y') : '-',
                    $endDateVal ? Carbon::parse($endDateVal)->format('d/m/Y') : '-',
                    $totalLogbooks,
                    $approvedLogbooks,
                    $reportStatus,
                    $mentorScore,
                    $dplScore,
                    $finalScore,
                    $gradeLetter,
                    $consultationCount,
                ]));
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
