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

        if ($request->filled('agency_id')) {
            $baseQuery->whereHas('application.unit', function ($q) use ($request) {
                $q->where('agency_profile_id', $request->agency_id);
            });
        }

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'active' => (clone $baseQuery)->whereRelation('application', fn($q) => $q->whereIn('status', ['active', 'accepted']))->count(),
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
            default => $query->whereRelation('application', fn($q) => $q->whereIn('status', ['active', 'accepted'])),
        };

        $placements = $query->latest()->paginate(10)->withQueryString();

        $agencies = AgencyProfile::all();

        return view('lecturer.monitoring.index', compact('placements', 'lecturer', 'agencies', 'stats', 'tab'));
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

        if ($request->filled('agency_id')) {
            $baseQuery->whereHas('application.unit', function ($q) use ($request) {
                $q->where('agency_profile_id', $request->agency_id);
            });
        }

        $tab = $request->get('tab', 'active');

        $query = (clone $baseQuery)->with([
            'application.user.studentProfile',
            'application.unit.agencyProfile',
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
            default => $query->whereRelation('application', fn($q) => $q->whereIn('status', ['active', 'accepted'])),
        };

        $placements = $query->latest()->get();

        $filename = 'Rekap_Bimbingan_DPL_' . \Illuminate\Support\Str::slug($lecturer->name, '_') . '_' . date('Ymd_His') . '.csv';

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
                'Nilai DPL',
                'Nilai Akhir Kumulatif',
                'Nilai Huruf',
                'Jumlah Sesi Bimbingan DPL',
            ]);

            $no = 1;
            foreach ($placements as $p) {
                $student = $p->application?->user;
                $profile = $student?->studentProfile;
                $unit = $p->application?->unit;
                $agency = $unit?->agencyProfile;
                $mentor = $p->mentor ?? $p->pembimbing;
                
                $totalLogbooks = $p->logbooks->count();
                $approvedLogbooks = $p->logbooks->where('status', 'approved')->count();
                
                $reportStatus = match($p->finalreport?->status) {
                    'approved' => 'Disetujui',
                    'submitted' => 'Menunggu Review',
                    'rejected' => 'Perlu Revisi',
                    default => 'Belum Unggah',
                };

                $mentorScore = $p->evaluation?->final_score !== null ? number_format((float)$p->evaluation->final_score, 1) : '-';
                $dplScore = $p->evaluation?->dosen_evaluation_score !== null ? number_format((float)$p->evaluation->dosen_evaluation_score, 1) : '-';
                $finalScore = $p->evaluation?->calculated_final_score !== null ? number_format((float)$p->evaluation->calculated_final_score, 1) : '-';
                $gradeLetter = $p->evaluation?->grade_letter ?? '-';
                $consultationCount = $p->academicConsultations->count();

                $rawStatus = $p->application?->status;
                $statusStr = $rawStatus instanceof \BackedEnum ? $rawStatus->value : (string) ($rawStatus ?? 'N/A');

                fputcsv($handle, [
                    $no++,
                    $student?->name ?? 'N/A',
                    $profile?->nim ?? 'N/A',
                    $agency?->agency_name ?? 'N/A',
                    $unit?->name ?? 'N/A',
                    $mentor?->name ?? 'Belum Ditugaskan',
                    strtoupper($statusStr),
                    $p->start_date ? \Carbon\Carbon::parse($p->start_date)->format('d/m/Y') : '-',
                    $p->end_date ? \Carbon\Carbon::parse($p->end_date)->format('d/m/Y') : '-',
                    $totalLogbooks,
                    $approvedLogbooks,
                    $reportStatus,
                    $mentorScore,
                    $dplScore,
                    $finalScore,
                    $gradeLetter,
                    $consultationCount,
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
