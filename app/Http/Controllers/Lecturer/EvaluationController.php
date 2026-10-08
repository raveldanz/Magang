<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Evaluation;
use App\Models\FinalReport;
use App\Models\Placement;
use App\Models\University;
use App\Services\StudentNotifier;
use App\Services\UniversityResolver;
use App\Support\Grade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EvaluationController extends Controller
{
    /**
     * Helper untuk validasi bahwa penempatan mahasiswa benar dibimbing oleh DPL yang login
     */
    protected function getAuthorizedPlacement($placementId)
    {
        $lecturer = Auth::user();

        // Cari berdasarkan placement_id atau application_id sebagai fallback
        $placement = Placement::with([
            'application.user.studentProfile',
            'application.unit.agencyProfile',
            'mentor',
            'pembimbing',
            'evaluation',
            'finalreport',
        ])->find($placementId) ?? Placement::with([
            'application.user.studentProfile',
            'application.unit.agencyProfile',
            'mentor',
            'pembimbing',
            'evaluation',
            'finalreport',
        ])->where('application_id', $placementId)->firstOrFail();

        // Otorisasi: DPL yang ditugaskan (academic_advisor_id) atau Super Admin
        $isAssignedAdvisor = ($placement->academic_advisor_id === $lecturer->id);
        $isSuperAdmin = $lecturer->isSuperAdmin();

        if (! $isAssignedAdvisor && ! $isSuperAdmin) {
            abort(403, 'Akses Ditolak: Anda bukan Dosen Pembimbing Lapangan yang ditugaskan untuk mahasiswa ini.');
        }

        return $placement;
    }

    /**
     * Tampilkan formulir penilaian bimbingan & laporan akademik kampus
     */
    public function create($placementId)
    {
        $placement = $this->getAuthorizedPlacement($placementId);

        $student = $placement->application->user;
        $profile = $student->studentProfile;
        $unit = $placement->application->unit;
        $agencyProfile = $unit?->agencyProfile ?? $placement->agencyProfile;
        $mentor = $placement->mentor ?? $placement->pembimbing;
        $evaluation = $placement->evaluation;

        $univ = $evaluation?->getUniversity();
        if (! $univ && $student) {
            $univ = app(UniversityResolver::class)->forUser($student);
        }

        // Sama seperti mentor: form tetap bisa dibuka, tetapi terkunci bila magang belum berjalan / sudah selesai.
        $lockReason = $placement->evaluationLockReason();

        return view('lecturer.evaluation', compact(
            'lockReason',
            'placement',
            'student',
            'profile',
            'unit',
            'agencyProfile',
            'mentor',
            'evaluation',
            'univ'
        ));
    }

    /**
     * Simpan nilai bimbingan akademik dan catatan dosen kampus
     */
    public function store(Request $request, $placementId)
    {
        $placement = $this->getAuthorizedPlacement($placementId);

        if ($lockReason = $placement->evaluationLockReason()) {
            return redirect()->route('lecturer.students.show', $placement->id)->with('error', $lockReason);
        }

        $evaluation = Evaluation::firstOrNew(['placement_id' => $placement->id]);
        $univ = $evaluation->getUniversity();
        if (! $univ && $placement->application?->user) {
            $u = $placement->application->user;
            if ($u->university_id) {
                $univ = University::find($u->university_id);
            }
        }
        $scheme = $univ->evaluation_scheme ?? 'dual_evaluation';
        $isMentorOnly = ($scheme === 'mentor_only');

        $rules = [
            'catatan_dosen' => 'nullable|string|max:1500',
            'feedback_dosen' => 'nullable|string|max:1500',
        ];

        if (! $isMentorOnly) {
            $rules['score_mastery'] = 'required|numeric|min:0|max:100';
            $rules['score_report'] = 'required|numeric|min:0|max:100';
            $rules['score_attitude'] = 'required|numeric|min:0|max:100';
        }

        $request->validate($rules, [
            'score_mastery.required' => 'Nilai penguasaan materi wajib diisi.',
            'score_mastery.min' => 'Nilai minimal adalah 0.',
            'score_mastery.max' => 'Nilai maksimal adalah 100.',
            'score_report.required' => 'Nilai kualitas laporan wajib diisi.',
            'score_report.min' => 'Nilai minimal adalah 0.',
            'score_report.max' => 'Nilai maksimal adalah 100.',
            'score_attitude.required' => 'Nilai sikap dan komunikasi wajib diisi.',
            'score_attitude.min' => 'Nilai minimal adalah 0.',
            'score_attitude.max' => 'Nilai maksimal adalah 100.',
        ]);

        $feedback = $request->feedback_dosen ?? $request->catatan_dosen;
        $evaluation->catatan_dosen = $feedback;
        $evaluation->feedback_dosen = $feedback;

        $nilaiDosen = null;
        if (! $isMentorOnly) {
            $mastery = (float) $request->score_mastery;
            $report = (float) $request->score_report;
            $attitude = (float) $request->score_attitude;
            $nilaiDosen = round(($mastery + $report + $attitude) / 3, 2);

            $evaluation->nilai_disiplin = $evaluation->nilai_disiplin ?? 0;
            $evaluation->nilai_kinerja = $evaluation->nilai_kinerja ?? 0;
            $evaluation->nilai_laporan = $evaluation->nilai_laporan ?? 0;
            $evaluation->nilai_akademik = (int) round($nilaiDosen);
            $evaluation->score_mastery = $mastery;
            $evaluation->score_report = $report;
            $evaluation->score_attitude = $attitude;
            $evaluation->nilai_dosen = $nilaiDosen;
        }

        // Hitung Nilai Akhir dengan Pembobotan Kampus Adaptif
        $nilaiDinas = $evaluation->nilai_pembimbing ?? 0;
        if ($nilaiDinas > 0) {
            $weightMentor = $univ ? (int) $univ->weight_mentor : 40;
            $weightLecturer = $univ ? (int) $univ->weight_lecturer : 60;

            if ($isMentorOnly) {
                $final = $nilaiDinas;
            } else {
                $final = round((($weightMentor / 100) * $nilaiDinas) + (($weightLecturer / 100) * ($nilaiDosen ?? 0)), 2);
            }
            $evaluation->final_score = $final;
            $evaluation->grade = Grade::letter($final);
        }

        $evaluation->save();

        // Cek apakah mahasiswa otomatis berstatus COMPLETED
        $placement->syncCompletionStatus();

        // Catat Audit Trail
        AuditLog::record('LECTURER_EVALUATION_SUBMIT', 'Evaluation', $evaluation->id, [
            'student_name' => $placement->application->user->name ?? '-',
            'is_mentor_only' => $isMentorOnly,
            'nilai_dosen' => $nilaiDosen,
            'final_score' => $evaluation->final_score ?? null,
            'grade' => $evaluation->grade ?? null,
        ]);

        if (! $isMentorOnly) {
            StudentNotifier::evaluationSubmitted($placement->application?->user, 'lecturer');
        }

        $successMsg = $isMentorOnly
            ? 'Catatan bimbingan DPL berhasil disimpan!'
            : 'Nilai bimbingan akademik DPL dan catatan berhasil disimpan!';

        return redirect()->route('lecturer.students.show', $placement->id)
            ->with('success', $successMsg);
    }

    /**
     * Verifikasi Dokumen Laporan Akhir oleh DPL (APPROVED / REVISION)
     */
    public function updateFinalReportStatus(Request $request, $placementId)
    {
        $placement = $this->getAuthorizedPlacement($placementId);

        $request->validate([
            'status' => 'required|in:approved,revision,rejected,pending',
            'feedback' => 'nullable|string|max:1500',
        ]);

        $finalReport = FinalReport::firstOrCreate(
            ['placement_id' => $placement->id],
            [
                'file_path' => 'final_reports/default.pdf',
                'status' => 'pending',
            ]
        );

        $finalReport->update([
            'status' => $request->status,
            'feedback' => $request->feedback,
        ]);

        // Cek jika evaluasi dinas & dosen sudah lengkap dan laporan di-ACC -> otomatis status COMPLETED
        $placement->syncCompletionStatus();

        // Catat Audit Trail
        AuditLog::record('LECTURER_REPORT_APPROVAL', 'FinalReport', $finalReport->id, [
            'student_name' => $placement->application->user->name ?? '-',
            'status' => $request->status,
            'feedback' => $request->feedback,
        ]);

        if (in_array($request->status, ['approved', 'revision', 'rejected'], true)) {
            StudentNotifier::finalReportReviewed($placement->application?->user, 'lecturer', $request->status, $request->feedback);
        }

        $statusLabel = $request->status === 'approved' ? 'disetujui (ACC)' : 'diminta perbaikan (Revisi)';

        return redirect()->back()->with('success', "Status laporan akhir mahasiswa berhasil {$statusLabel}!");
    }

    /**
     * Cetak Berita Acara & Lembar Penilaian DPL Resmi (BAP Print-Ready)
     */
    public function printGradeSheet($placementId)
    {
        $placement = $this->getAuthorizedPlacement($placementId);
        $placement->ensureCertificateHash();

        $student = $placement->application->user;
        $profile = $student->studentProfile;
        $unit = $placement->application->unit;
        $agencyProfile = $unit?->agencyProfile ?? $placement->agencyProfile;
        $mentor = $placement->mentor ?? $placement->pembimbing;
        $dosen = $placement->academicAdvisor ?? Auth::user();
        $evaluation = $placement->evaluation;
        $finalReport = $placement->finalreport;
        $consultations = $placement->academicConsultations;

        $univ = $evaluation?->getUniversity();
        if (!$univ && $student) {
            if ($student->university_id) {
                $univ = \App\Models\University::find($student->university_id);
            } else {
                $name = $student->university ?? ($profile?->universitas ?? null);
                if ($name) {
                    $univ = \App\Models\University::where('name', 'like', "%{$name}%")->orWhere('code', 'like', "%{$name}%")->first();
                }
            }
        }

        $scheme = $univ->evaluation_scheme ?? 'dual_evaluation';
        $weightMentor = $univ ? (int)$univ->weight_mentor : 40;
        $weightLecturer = $univ ? (int)$univ->weight_lecturer : 60;

        return view('lecturer.grade-sheet', compact(
            'placement',
            'student',
            'profile',
            'unit',
            'agencyProfile',
            'mentor',
            'dosen',
            'evaluation',
            'finalReport',
            'consultations',
            'univ',
            'scheme',
            'weightMentor',
            'weightLecturer'
        ));
    }
}
