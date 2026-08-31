<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Evaluation;
use App\Models\FinalReport;
use App\Models\Placement;
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
        $isSuperAdmin = ($lecturer->role === 'super_admin' || ($lecturer->role === 'admin' && is_null($lecturer->agency_profile_id)));

        if (!$isAssignedAdvisor && !$isSuperAdmin) {
            abort(403, 'Akses Ditolak: Anda bukan Dosen Pembimbing Lapangan (DPL) yang ditugaskan untuk mahasiswa ini.');
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

        // Jika universitas memberlakukan 100% Mentor Dinas, arahkan langsung ke halaman detail mahasiswa
        if ($univ && $univ->evaluation_scheme === 'mentor_only') {
            return redirect()->route('lecturer.students.show', $placementId);
        }

        return view('lecturer.evaluation', compact(
            'placement',
            'student',
            'profile',
            'unit',
            'agencyProfile',
            'mentor',
            'evaluation'
        ));
    }

    /**
     * Simpan nilai bimbingan akademik dan catatan dosen kampus
     */
    public function store(Request $request, $placementId)
    {
        $placement = $this->getAuthorizedPlacement($placementId);

        $request->validate([
            'score_mastery' => 'nullable|numeric|min:0|max:100',
            'score_report' => 'nullable|numeric|min:0|max:100',
            'score_attitude' => 'nullable|numeric|min:0|max:100',
            'nilai_akademik' => 'nullable|numeric|min:0|max:100',
            'catatan_dosen' => 'nullable|string|max:1500',
            'feedback_dosen' => 'nullable|string|max:1500',
        ]);

        $evaluation = Evaluation::firstOrNew(['placement_id' => $placement->id]);
        $univ = $evaluation->getUniversity();
        if (!$univ && $placement->application?->user) {
            $u = $placement->application->user;
            if ($u->university_id) {
                $univ = \App\Models\University::find($u->university_id);
            }
        }
        $scheme = $univ->evaluation_scheme ?? 'dual_evaluation';
        $isMentorOnly = ($scheme === 'mentor_only');

        $feedback = $request->feedback_dosen ?? $request->catatan_dosen;
        $evaluation->catatan_dosen = $feedback;
        $evaluation->feedback_dosen = $feedback;

        $nilaiDosen = null;
        if (!$isMentorOnly) {
            if ($request->filled('score_mastery') && $request->filled('score_report') && $request->filled('score_attitude')) {
                $mastery = (float)$request->score_mastery;
                $report = (float)$request->score_report;
                $attitude = (float)$request->score_attitude;
                $nilaiDosen = round(($mastery + $report + $attitude) / 3, 2);
            } else {
                $nilaiDosen = (float)($request->nilai_akademik ?? 85);
                $mastery = $nilaiDosen;
                $report = $nilaiDosen;
                $attitude = $nilaiDosen;
            }

            $evaluation->nilai_disiplin = $evaluation->nilai_disiplin ?? 0;
            $evaluation->nilai_kinerja = $evaluation->nilai_kinerja ?? 0;
            $evaluation->nilai_laporan = $evaluation->nilai_laporan ?? 0;
            $evaluation->nilai_akademik = (int)round($nilaiDosen);
            $evaluation->score_mastery = $mastery;
            $evaluation->score_report = $report;
            $evaluation->score_attitude = $attitude;
            $evaluation->nilai_dosen = $nilaiDosen;
        }

        // Hitung Nilai Akhir dengan Pembobotan Kampus Adaptif
        $nilaiDinas = $evaluation->nilai_pembimbing ?? 0;
        if ($nilaiDinas > 0) {
            $weightMentor = $univ ? (int)$univ->weight_mentor : 40;
            $weightLecturer = $univ ? (int)$univ->weight_lecturer : 60;

            if ($isMentorOnly) {
                $final = $nilaiDinas;
            } else {
                $final = round((($weightMentor / 100) * $nilaiDinas) + (($weightLecturer / 100) * ($nilaiDosen ?? 0)), 2);
            }
            $evaluation->final_score = $final;

            if ($final >= 85) $grade = 'A';
            elseif ($final >= 75) $grade = 'AB';
            elseif ($final >= 65) $grade = 'B';
            elseif ($final >= 55) $grade = 'BC';
            elseif ($final >= 40) $grade = 'C';
            else $grade = 'E';

            $evaluation->grade = $grade;
        }

        $evaluation->save();

        // Cek apakah mahasiswa otomatis berstatus COMPLETED
        $finalReport = $placement->finalreport;
        if ($finalReport && $finalReport->status === 'approved' && $nilaiDinas > 0 && ($isMentorOnly || ($nilaiDosen && $nilaiDosen > 0))) {
            $placement->application->update(['status' => 'completed']);
        }

        // Catat Audit Trail
        AuditLog::record('LECTURER_EVALUATION_SUBMIT', 'Evaluation', $evaluation->id, [
            'student_name' => $placement->application->user->name ?? '-',
            'is_mentor_only' => $isMentorOnly,
            'nilai_dosen' => $nilaiDosen,
            'final_score' => $evaluation->final_score ?? null,
            'grade' => $evaluation->grade ?? null,
        ]);

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
        $eval = $placement->evaluation;
        if ($request->status === 'approved' && $eval && $eval->nilai_pembimbing > 0 && $eval->nilai_dosen_calculated > 0) {
            $placement->application->update(['status' => 'completed']);
        }

        // Catat Audit Trail
        AuditLog::record('LECTURER_REPORT_APPROVAL', 'FinalReport', $finalReport->id, [
            'student_name' => $placement->application->user->name ?? '-',
            'status' => $request->status,
            'feedback' => $request->feedback,
        ]);

        $statusLabel = $request->status === 'approved' ? 'disetujui (ACC)' : 'diminta perbaikan (Revisi)';

        return redirect()->back()->with('success', "Status laporan akhir mahasiswa berhasil {$statusLabel}!");
    }
}
