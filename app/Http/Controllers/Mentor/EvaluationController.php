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

        $custom = is_array($evaluation->custom_components) ? $evaluation->custom_components : [];

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
        } elseif ($request->has('custom_components') && is_array($request->custom_components)) {
            $mentorComps = [];
            $scores = [];
            foreach ($request->custom_components as $comp) {
                if (!empty($comp['name'])) {
                    $val = isset($comp['score']) && is_numeric($comp['score']) ? (float) $comp['score'] : null;
                    $mentorComps[] = [
                        'name' => trim($comp['name']),
                        'score' => $val,
                    ];
                    if ($val !== null) {
                        $scores[] = $val;
                    }
                }
            }
            if (!empty($mentorComps)) {
                $custom['mentor'] = $mentorComps;
                $evaluation->custom_components = $custom;
                $evaluation->mentor_aspects = $mentorComps;
            }
            $avg = count($scores) > 0 ? round(array_sum($scores) / count($scores), 2) : 0.0;
            $evaluation->nilai_disiplin = (int) round($scores[0] ?? $avg);
            $evaluation->nilai_kinerja = (int) round($scores[1] ?? $avg);
            $evaluation->nilai_laporan = (int) round($scores[2] ?? $avg);
        } else {
            $evaluation->nilai_disiplin = $request->nilai_disiplin;
            $evaluation->nilai_kinerja = $request->nilai_kinerja;
            $evaluation->nilai_laporan = $request->nilai_laporan;
            $univ = $placement->application?->user ? app(\App\Services\UniversityResolver::class)->forUser($placement->application->user) : null;
            $evaluation->mentor_aspects = [
                ['name' => $univ ? $univ->getDinasComponent(1) : 'Kedisiplinan & Tata Tertib Kerja', 'score' => (float) $request->nilai_disiplin],
                ['name' => $univ ? $univ->getDinasComponent(2) : 'Kinerja Teknis & Kerjasama Tim', 'score' => (float) $request->nilai_kinerja],
                ['name' => $univ ? $univ->getDinasComponent(3) : 'Inisiatif & Laporan Kedinasan', 'score' => (float) $request->nilai_laporan],
            ];
        }
        $evaluation->save();

        // Cek skema kampus mahasiswa
        $univ = $evaluation->getUniversity();
        $scheme = $univ->evaluation_scheme ?? 'mentor_only';
        $weightMentor = $univ ? (int) $univ->weight_mentor : 100;
        $weightLecturer = $univ ? (int) $univ->weight_lecturer : 0;

        if ($scheme === 'mentor_only' || $weightLecturer === 0) {
            $finalScore = $evaluation->nilai_pembimbing;
            $grade = Grade::letter($finalScore);

            $evaluation->update([
                'final_score' => $finalScore,
                'grade' => $grade,
            ]);

            // Cek kelengkapan evaluasi dan sinkronkan status COMPLETED
            $placement->syncCompletionStatus();
        } else {
            // Skema Dual Evaluation atau 100% Kampus
            $dosenScore = $evaluation->nilai_dosen_calculated ?? $evaluation->nilai_dosen ?? $evaluation->nilai_akademik;
            if ($weightMentor === 0) {
                if ($dosenScore > 0) {
                    $finalScore = $dosenScore;
                    $grade = Grade::letter($finalScore);
                    $evaluation->update([
                        'final_score' => $finalScore,
                        'grade' => $grade,
                    ]);
                    $placement->syncCompletionStatus();
                }
            } elseif ($dosenScore > 0) {
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
