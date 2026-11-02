<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\AuditLog;
use App\Models\Certificate;
use App\Models\Evaluation;
use App\Models\Placement;
use App\Models\Unit;
use App\Models\User;
use App\Services\NotificationService;
use App\Support\Grade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CertificateController extends Controller
{
    // Menampilkan daftar mahasiswa yang siap cetak sertifikat (Multi-Tenant Scoped)
    public function index()
    {
        $user = Auth::user();

        // 1. Sinkronisasi otomatis mahasiswa yang sudah ACC laporan, tuntas evaluasi, dan terisi logbook agar statusnya lulus (completed)
        $candidates = Placement::whereHas('application', function ($q) {
            $q->whereIn('status', ['accepted', 'active']);
        })->whereHas('finalreport', function ($subQuery) {
            $subQuery->where('status', 'approved');
        })->whereHas('evaluation')
            ->whereHas('logbooks')
            ->get();

        foreach ($candidates as $cand) {
            $cand->syncCompletionStatus();
        }

        // 2. Ambil aplikasi yang sudah berstatus completed dan siap cetak sertifikat
        $query = Application::with([
            'user.studentProfile',
            'unit.agencyProfile',
            'placement.evaluation',
            'placement.finalreport',
            'placement.pembimbing',
        ])
            ->where('status', 'completed')
            ->whereHas('placement', function ($query) {
                $query->whereHas('evaluation')
                    ->whereHas('finalreport', function ($subQuery) {
                        $subQuery->where('status', 'approved');
                    })
                    ->whereHas('logbooks');
            });

        // Multi-Tenant & Isolasi Bidang:
        if ($user && ! $user->isSuperAdmin() && $user->unit_id) {
            $query->where('unit_id', $user->unit_id);
        } elseif ($user && $user->agency_profile_id !== null) {
            $query->whereHas('unit', function ($q) use ($user) {
                $q->where('agency_profile_id', $user->agency_profile_id);
            });
        }

        // Pencarian Cepat Nama Mahasiswa / Kampus / NIM
        $search = request('search');
        if (! empty($search)) {
            $like = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
            $query->whereHas('user', function ($uq) use ($search, $like) {
                $uq->where('name', $like, "%{$search}%")
                    ->orWhereHas('studentProfile', fn ($sp) => $sp->where('universitas', $like, "%{$search}%")->orWhere('nim', $like, "%{$search}%"));
            });
        }

        // Certificate Gate: hanya tampilkan yang benar-benar memenuhi seluruh syarat penerbitan
        $eligibleApps = $query->latest('id')->get()->filter(fn (Application $app) => $app->isCertificateEligible())->values();

        // Pagination 10 mahasiswa per halaman agar tampilan tetap ringan & rapi saat data berjumlah banyak
        $perPage = 10;
        $page = (int) request()->get('page', 1);
        $applications = new \Illuminate\Pagination\LengthAwarePaginator(
            $eligibleApps->forPage($page, $perPage)->values(),
            $eligibleApps->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        $activeTheme = \App\Models\CertificateTheme::getActive();
        $allThemes = \App\Models\CertificateTheme::orderByDesc('year')->get();

        return view('admin.certificates.index', compact('applications', 'activeTheme', 'allThemes', 'eligibleApps'));
    }

    // Pratinjau & Cetak E-Sertifikat Lengkap (Format Resmi Mahasiswa & Transkrip 2 Halaman)
    public function show($placementId)
    {
        $user = Auth::user();
        $placement = Placement::with('application')->findOrFail($placementId);
        if ($user && ! $user->isSuperAdmin() && $user->unit_id && optional($placement->application)->unit_id !== $user->unit_id) {
            abort(403, 'Anda tidak memiliki hak akses ke sertifikat bidang lain.');
        }

        $data = \App\Http\Controllers\Student\CertificateController::getCertificateData($placementId, $user);
        $data['certificateRecord'] = \App\Models\Certificate::where('placement_id', $placementId)->first();

        return view('certificates.internship_certificate', $data);
    }

    // Generate & Cetak E-Sertifikat
    public function generate($placementId)
    {
        $placement = Placement::with(['application.user'])->findOrFail($placementId);

        if (! $placement->application || ! $placement->application->isCertificateEligible()) {
            abort(403, 'Sertifikat belum dapat diterbitkan karena syarat kelulusan belum terpenuhi.');
        }

        $user = Auth::user();
        if ($user && ! $user->isSuperAdmin() && $user->unit_id && optional($placement->application)->unit_id !== $user->unit_id) {
            abort(403, 'Anda tidak memiliki hak akses untuk menerbitkan sertifikat bidang lain.');
        }

        $year = date('Y');
        if ($placement->application && $placement->application->end_date) {
            $year = date('Y', strtotime($placement->application->end_date));
        }
        $theme = \App\Models\CertificateTheme::getActive((int) $year);
        $paddedId = str_pad($placement->id, 4, '0', STR_PAD_LEFT);
        $certNumber = $placement->certificate_number ?: $theme->formatNumber($paddedId, (int) $year);

        if (empty($placement->certificate_number) || empty($placement->certificate_hash)) {
            $placement->certificate_number = $certNumber;
            $placement->certificate_hash = $placement->certificate_hash ?: \Illuminate\Support\Str::random(32);
            $placement->save();
        }

        $certificate = \App\Models\Certificate::firstOrCreate(
            ['placement_id' => $placement->id],
            [
                'certificate_number' => $certNumber,
                'file_path' => 'certificates/' . str_replace('/', '_', $certNumber) . '.pdf',
                'approval_status' => 'waiting_approval',
                'issued_at' => now()->toDateString(),
            ]
        );

        if ($certificate->wasRecentlyCreated || $certificate->approval_status === 'waiting_approval') {
            \App\Services\NotificationService::notifySupervisorForCertificate($certificate);
        }

        $data = \App\Http\Controllers\Student\CertificateController::getCertificateData($placementId, $user);
        $data['certificateRecord'] = $certificate;

        return view('certificates.internship_certificate', $data)
            ->with('success', 'Sertifikat magang berhasil diterbitkan dan diteruskan ke Atasan (Tim Kerja Umum & Kepegawaian) untuk disahkan (ACC).');
    }

    /**
     * Halaman Khusus Pusat Perbaikan & Revisi Sertifikat Magang
     */
    public function revision($placementId)
    {
        $placement = Placement::with([
            'application.user.studentProfile',
            'application.unit.agencyProfile',
            'evaluation',
            'finalreport',
            'logbooks',
            'certificate',
            'mentor',
            'pembimbing',
            'academicAdvisor',
        ])->findOrFail($placementId);

        $application = $placement->application;
        $user = Auth::user();
        if ($user && ! $user->isSuperAdmin() && $user->unit_id && optional($application)->unit_id !== $user->unit_id) {
            abort(403, 'Anda tidak memiliki hak akses untuk merevisi sertifikat bidang lain.');
        }

        $student = $application?->user;
        $profile = $student?->studentProfile;
        $evaluation = $placement->evaluation;
        $certificate = $placement->certificate;

        if (! $certificate) {
            $year = date('Y');
            $paddedId = str_pad($placement->id, 3, '0', STR_PAD_LEFT);
            $certificate = Certificate::firstOrCreate(
                ['placement_id' => $placement->id],
                [
                    'certificate_number' => $placement->certificate_number ?: "SERT/{$paddedId}/DISPUSIP-SBY/{$year}",
                    'file_path' => 'certificates/' . $placement->id . '.pdf',
                    'approval_status' => 'revision',
                    'issued_at' => now()->toDateString(),
                    'notes' => 'Memerlukan peninjauan dan perbaikan data dari Admin.',
                ]
            );
        }

        $agencyProfileId = $application->unit?->agency_profile_id ?? Auth::user()->agency_profile_id ?? 1;

        $mentors = User::where('role', 'mentor')
            ->when($agencyProfileId, fn ($q) => $q->where('agency_profile_id', $agencyProfileId))
            ->when(! $user->isSuperAdmin() && $user->unit_id, fn ($q) => $q->where('unit_id', $user->unit_id))
            ->orderBy('name')
            ->get();

        $units = (! $user->isSuperAdmin() && $user->unit_id)
            ? Unit::where('id', $user->unit_id)->get()
            : Unit::where('agency_profile_id', $agencyProfileId)->orderBy('name')->get();

        $logbookCount = $placement->logbooks()->count();
        $verifiedLogbookCount = $placement->logbooks()->where('status', 'approved')->count();

        return view('admin.certificates.revision', compact(
            'placement',
            'application',
            'student',
            'profile',
            'evaluation',
            'certificate',
            'mentors',
            'units',
            'logbookCount',
            'verifiedLogbookCount'
        ));
    }

    /**
     * Simpan Perubahan Terintegrasi (Nilai, Biodata Mahasiswa, Periode, dll.) & Ajukan Ulang ke Atasan
     */
    public function updateRevision(Request $request, $placementId)
    {
        $placement = Placement::with([
            'application.user.studentProfile',
            'application.unit',
            'evaluation',
            'certificate',
        ])->findOrFail($placementId);

        $request->validate([
            'student_name' => 'required|string|max:255',
            'nim' => 'required|string|max:50',
            'universitas' => 'required|string|max:255',
            'jurusan' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'nilai_disiplin' => 'required|numeric|min:0|max:100',
            'nilai_kinerja' => 'required|numeric|min:0|max:100',
            'nilai_laporan' => 'required|numeric|min:0|max:100',
            'nilai_akademik' => 'nullable|numeric|min:0|max:100',
            'resolution_note' => 'nullable|string|max:1000',
            'action' => 'required|in:resubmit,save_draft',
        ]);

        $user = Auth::user();
        $application = $placement->application;
        if ($user && ! $user->isSuperAdmin() && $user->unit_id && optional($application)->unit_id !== $user->unit_id) {
            abort(403, 'Anda tidak memiliki hak akses untuk merevisi sertifikat bidang lain.');
        }

        $student = $application->user;
        $profile = $student->studentProfile;

        DB::transaction(function () use ($request, $placement, $application, $student, $profile, $user) {
            // 1. Update Aspek Data Mahasiswa & Magang
            $student->update([
                'name' => $request->student_name,
            ]);

            if ($profile) {
                $profile->update([
                    'nim' => $request->nim,
                    'universitas' => $request->universitas,
                    'jurusan' => $request->jurusan,
                ]);
            }

            $application->update([
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'unit_id' => (! $user->isSuperAdmin() && $user->unit_id) ? $user->unit_id : ($request->unit_id ?: $application->unit_id),
            ]);

            if ($request->filled('mentor_id')) {
                $placement->update([
                    'mentor_id' => $request->mentor_id,
                    'pembimbing_id' => $request->mentor_id,
                ]);
            }

            // 2. Update Aspek Nilai Evaluasi (Hitung ulang adaptif)
            $disiplin = (float) $request->nilai_disiplin;
            $kinerja = (float) $request->nilai_kinerja;
            $laporan = (float) $request->nilai_laporan;
            $akademik = $request->filled('nilai_akademik') ? (float) $request->nilai_akademik : 0;

            $dinasAvg = round(($disiplin + $kinerja + $laporan) / 3, 2);

            $evaluation = $placement->evaluation ?? new Evaluation(['placement_id' => $placement->id]);
            $univ = $evaluation->getUniversity();
            $scheme = $univ->evaluation_scheme ?? 'mentor_only';
            $weightMentor = $univ ? (int) $univ->weight_mentor : 100;
            $weightLecturer = $univ ? (int) $univ->weight_lecturer : 0;

            if ($scheme === 'mentor_only' || $weightLecturer === 0) {
                $finalScore = $dinasAvg;
            } elseif ($weightMentor === 0) {
                $finalScore = $akademik > 0 ? $akademik : $dinasAvg;
            } else {
                $finalScore = round(($dinasAvg * ($weightMentor / 100)) + ($akademik * ($weightLecturer / 100)), 2);
            }

            $grade = Grade::letter($finalScore);

            $evaluation->fill([
                'placement_id' => $placement->id,
                'nilai_disiplin' => (int) round($disiplin),
                'nilai_kinerja' => (int) round($kinerja),
                'nilai_laporan' => (int) round($laporan),
                'nilai_akademik' => (int) round($akademik),
                'nilai_dosen' => $akademik,
                'score_attitude' => $disiplin,
                'score_mastery' => $kinerja,
                'score_report' => $laporan,
                'final_score' => $finalScore,
                'grade' => $grade,
                'catatan' => $request->catatan_evaluasi ?: $evaluation->catatan,
            ]);
            $evaluation->save();

            // 3. Update Status Sertifikat
            $certificate = $placement->certificate ?? Certificate::firstOrCreate(
                ['placement_id' => $placement->id],
                [
                    'certificate_number' => $placement->certificate_number ?: "SERT/" . str_pad($placement->id, 3, '0', STR_PAD_LEFT) . "/DISPUSIP-SBY/" . date('Y'),
                    'file_path' => 'certificates/' . $placement->id . '.pdf',
                    'issued_at' => now()->toDateString(),
                ]
            );

            if ($request->action === 'resubmit') {
                $resolutionText = $request->resolution_note 
                    ? "Perbaikan oleh {$user->name}: " . $request->resolution_note
                    : "Telah diperbaiki oleh {$user->name} sesuai catatan atasan.";

                $certificate->update([
                    'approval_status' => 'waiting_approval',
                    'notes' => $resolutionText,
                ]);

                NotificationService::notifySupervisorForCertificate($certificate);

                AuditLog::record('CERTIFICATE_REVISION_RESUBMITTED', 'Certificate', $certificate->id, [
                    'student_name' => $student->name,
                    'old_status' => 'revision',
                    'new_status' => 'waiting_approval',
                    'final_score' => $finalScore,
                    'grade' => $grade,
                    'resubmitted_by' => $user->name,
                    'resolution_note' => $request->resolution_note,
                ]);
            } else {
                AuditLog::record('CERTIFICATE_REVISION_DRAFT_SAVED', 'Certificate', $certificate->id, [
                    'student_name' => $student->name,
                    'final_score' => $finalScore,
                    'grade' => $grade,
                    'updated_by' => $user->name,
                ]);
            }
        });

        if ($request->action === 'resubmit') {
            return redirect()->route('admin.certificates.index')
                ->with('success', "Seluruh data dan nilai evaluasi magang atas nama {$student->name} berhasil diperbarui dan telah diajukan ulang ke Atasan untuk pengesahan (ACC)!");
        }

        return redirect()->back()
            ->with('success', "Draf perbaikan berhasil disimpan. Anda dapat memeriksa pratinjau atau mengajukan ulang ke Atasan kapan saja.");
    }

    /**
     * Simpan / Perbarui Pengaturan Tema Warna & Pejabat TTE Sertifikat Tahunan
     */
    public function updateTheme(Request $request)
    {
        $user = Auth::user();
        if ($user && ! $user->isSuperAdmin() && $user->unit_id) {
            abort(403, 'Hanya Admin Utama atau Super Admin yang dapat mengubah tema sertifikat dinas.');
        }

        $validated = $request->validate([
            'year' => 'required|integer|min:2020|max:2099',
            'theme_name' => 'required|string|max:255',
            'primary_color' => 'required|string|max:20',
            'secondary_color' => 'required|string|max:20',
            'accent_color' => 'required|string|max:20',
            'number_format_pattern' => 'required|string|max:255',
            'signee_name' => 'required|string|max:255',
            'signee_position' => 'required|string|max:255',
            'signee_rank' => 'nullable|string|max:255',
            'signee_nip' => 'nullable|string|max:50',
            'mandate_text' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'is_active' => 'nullable',
        ]);

        $theme = \App\Models\CertificateTheme::updateOrCreate(
            ['year' => (int) $validated['year']],
            [
                'theme_name' => $validated['theme_name'],
                'primary_color' => $validated['primary_color'],
                'secondary_color' => $validated['secondary_color'],
                'accent_color' => $validated['accent_color'],
                'number_format_pattern' => $validated['number_format_pattern'],
                'signee_name' => $validated['signee_name'],
                'signee_position' => $validated['signee_position'],
                'signee_rank' => $validated['signee_rank'] ?? 'PEMBINA TK I',
                'signee_nip' => $validated['signee_nip'] ?? '',
                'mandate_text' => $validated['mandate_text'] ?: 'a.n. Kepala Dinas Perpustakaan dan Kearsipan Kota Surabaya',
                'city' => $validated['city'] ?: 'Surabaya',
                'is_active' => true,
            ]
        );

        if ($request->boolean('is_active', true)) {
            \App\Models\CertificateTheme::where('id', '!=', $theme->id)->update(['is_active' => false]);
            $theme->update(['is_active' => true]);
        }

        AuditLog::record('CERTIFICATE_THEME_UPDATED', 'CertificateTheme', $theme->id, [
            'year' => $theme->year,
            'theme_name' => $theme->theme_name,
            'updated_by' => $user->name,
        ]);

        return redirect()->route('admin.certificates.index', ['tab' => 'settings'])
            ->with('success', "Tema Desain & Data TTE Sertifikat Tahun {$theme->year} ({$theme->theme_name}) berhasil disimpan!");
    }

    /**
     * Aktifkan salah satu tema tahunan sebagai tema aktif
     */
    public function activateTheme($id)
    {
        $user = Auth::user();
        if ($user && ! $user->isSuperAdmin() && $user->unit_id) {
            abort(403, 'Hanya Admin Utama atau Super Admin yang dapat mengubah tema sertifikat dinas.');
        }

        $theme = \App\Models\CertificateTheme::findOrFail($id);
        \App\Models\CertificateTheme::where('id', '!=', $theme->id)->update(['is_active' => false]);
        $theme->update(['is_active' => true]);

        AuditLog::record('CERTIFICATE_THEME_ACTIVATED', 'CertificateTheme', $theme->id, [
            'year' => $theme->year,
            'theme_name' => $theme->theme_name,
            'activated_by' => $user->name,
        ]);

        return redirect()->route('admin.certificates.index', ['tab' => 'settings'])
            ->with('success', "Tema Tahun {$theme->year} ({$theme->theme_name}) sekarang menjadi tema sertifikat aktif!");
    }
}
