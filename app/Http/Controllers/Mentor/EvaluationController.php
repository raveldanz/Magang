<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Evaluation;
use App\Models\Placement;
use App\Services\StudentNotifier;
use App\Support\Grade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EvaluationController extends Controller
{
    /**
     * Tampilkan formulir penilaian evaluasi akhir mahasiswa magang
     */
    public function create($placementId)
    {
        $mentor = Auth::user();

        $placement = Placement::with([
            'application.user.studentProfile',
            'application.unit.agencyProfile',
            'evaluation',
            'finalreport',
        ])->where(function ($q) use ($mentor) {
            $q->where('mentor_id', $mentor->id)
                ->orWhere('pembimbing_id', $mentor->id);
        })->findOrFail($placementId);

        // Multi-Tenant Authorization Check
        if ($mentor->agency_profile_id !== null && optional($placement->application?->unit)->agency_profile_id !== $mentor->agency_profile_id) {
            abort(403, 'Anda tidak memiliki hak akses untuk menilai mahasiswa instansi lain.');
        }

        // Form tetap bisa dibuka untuk melihat nilai, tetapi terkunci bila magang belum berjalan / sudah selesai.
        $lockReason = $placement->evaluationLockReason();

        return view('mentor.evaluation', compact('placement', 'lockReason'));
    }

    /**
     * Simpan atau perbarui nilai evaluasi akhir (Disiplin, Kinerja, Laporan)
     */
    public function store(Request $request, $placementId)
    {
        $mentor = Auth::user();

        $placement = Placement::with('application.unit')
            ->where(function ($q) use ($mentor) {
                $q->where('mentor_id', $mentor->id)
                    ->orWhere('pembimbing_id', $mentor->id);
            })->findOrFail($placementId);

        // Multi-Tenant Authorization Check
        if ($mentor->agency_profile_id !== null && optional($placement->application?->unit)->agency_profile_id !== $mentor->agency_profile_id) {
            abort(403, 'Anda tidak memiliki hak akses untuk menilai mahasiswa instansi lain.');
        }

        if ($lockReason = $placement->evaluationLockReason()) {
            return redirect()->route('mentor.students.show', $placement->id)->with('error', $lockReason);
        }

        if ($request->has('aspects')) {
            $rules = [
                'aspects' => 'required|array|min:1',
                'aspects.*.name' => 'required|string|max:150',
                'aspects.*.score' => 'required|numeric|min:0|max:100',
                'catatan' => 'nullable|string|max:1500',
            ];
        } else {
            $rules = [
                'nilai_disiplin' => 'required|numeric|min:0|max:100',
                'nilai_kinerja' => 'required|numeric|min:0|max:100',
                'nilai_laporan' => 'required|numeric|min:0|max:100',
                'catatan' => 'nullable|string|max:1500',
            ];
        }

        $request->validate($rules, [
            'aspects.required' => 'Setidaknya satu aspek penilaian wajib diisi.',
            'aspects.min' => 'Setidaknya satu aspek penilaian wajib diisi.',
            'aspects.*.name.required' => 'Nama aspek penilaian tidak boleh kosong.',
            'aspects.*.score.required' => 'Nilai aspek penilaian wajib diisi.',
            'aspects.*.score.min' => 'Nilai minimal adalah 0.',
            'aspects.*.score.max' => 'Nilai maksimal adalah 100.',
            'nilai_disiplin.required' => 'Nilai disiplin wajib diisi.',
            'nilai_disiplin.min' => 'Nilai minimal adalah 0.',
            'nilai_disiplin.max' => 'Nilai maksimal adalah 100.',
            'nilai_kinerja.required' => 'Nilai kinerja wajib diisi.',
            'nilai_kinerja.min' => 'Nilai minimal adalah 0.',
            'nilai_kinerja.max' => 'Nilai maksimal adalah 100.',
            'nilai_laporan.required' => 'Nilai laporan akhir wajib diisi.',
            'nilai_laporan.min' => 'Nilai minimal adalah 0.',
            'nilai_laporan.max' => 'Nilai maksimal adalah 100.',
        ]);

        $evaluation = Evaluation::firstOrNew(['placement_id' => $placement->id]);
        $evaluation->catatan = $request->catatan;

        if ($request->has('aspects')) {
            $rawAspects = (array) $request->input('aspects', []);
            $cleanAspects = [];
            $scores = [];
            foreach ($rawAspects as $a) {
                $name = trim($a['name'] ?? '');
                if ($name !== '' && isset($a['score']) && is_numeric($a['score'])) {
                    $val = (float) $a['score'];
                    $cleanAspects[] = ['name' => $name, 'score' => $val];
                    $scores[] = $val;
                }
            }
            $avg = count($scores) > 0 ? round(array_sum($scores) / count($scores), 2) : 0.0;
            $evaluation->mentor_aspects = $cleanAspects;
            $evaluation->nilai_disiplin = (int) round($scores[0] ?? $avg);
            $evaluation->nilai_kinerja = (int) round($scores[1] ?? $avg);
            $evaluation->nilai_laporan = (int) round($scores[2] ?? $avg);
        } else {
            $evaluation->nilai_disiplin = $request->nilai_disiplin;
            $evaluation->nilai_kinerja = $request->nilai_kinerja;
            $evaluation->nilai_laporan = $request->nilai_laporan;
            $evaluation->mentor_aspects = [
                ['name' => 'Kedisiplinan & Tata Tertib Kerja', 'score' => (float) $request->nilai_disiplin],
                ['name' => 'Kinerja Teknis & Kerjasama Tim', 'score' => (float) $request->nilai_kinerja],
                ['name' => 'Inisiatif & Laporan Kedinasan', 'score' => (float) $request->nilai_laporan],
            ];
        }

        $evaluation->save();

        // Cek skema kampus mahasiswa
        $univ = $evaluation->getUniversity();
        $scheme = $univ->evaluation_scheme ?? 'dual_evaluation';

        if ($scheme === 'mentor_only') {
            $finalScore = $evaluation->nilai_pembimbing;
            $grade = Grade::letter($finalScore);

            $evaluation->update([
                'final_score' => $finalScore,
                'grade' => $grade,
            ]);

            // Cek kelengkapan evaluasi dan sinkronkan status COMPLETED
            $placement->syncCompletionStatus();
        } else {
            // Skema Dual Evaluation: hitung ulang jika nilai dosen sudah ada
            $dosenScore = $evaluation->nilai_dosen_calculated ?? $evaluation->nilai_dosen ?? $evaluation->nilai_akademik;
            if ($dosenScore > 0) {
                $weightMentor = $univ ? (int) $univ->weight_mentor : 40;
                $weightLecturer = $univ ? (int) $univ->weight_lecturer : 60;
                $finalScore = round((($weightMentor / 100) * $evaluation->nilai_pembimbing) + (($weightLecturer / 100) * $dosenScore), 2);
                $grade = Grade::letter($finalScore);

                $evaluation->update([
                    'final_score' => $finalScore,
                    'grade' => $grade,
                ]);

                $placement->syncCompletionStatus();
            }
        }

        AuditLog::record('MENTOR_EVALUATION_SUBMIT', 'Placement', $placement->id, [
            'student_name' => $placement->application?->user?->name,
            'nilai_pembimbing' => $evaluation->nilai_pembimbing,
            'nilai_disiplin' => $request->nilai_disiplin,
            'nilai_kinerja' => $request->nilai_kinerja,
            'nilai_laporan' => $request->nilai_laporan,
        ]);

        StudentNotifier::evaluationSubmitted($placement->application?->user, 'mentor');

        return redirect()->route('mentor.students.show', $placement->id)
            ->with('success', 'Nilai evaluasi mahasiswa berhasil disimpan.');
    }
}
