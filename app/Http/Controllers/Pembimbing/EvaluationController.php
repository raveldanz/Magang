<?php

namespace App\Http\Controllers\Pembimbing;

use App\Http\Controllers\Controller;
use App\Models\Evaluation;
use App\Models\Placement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EvaluationController extends Controller
{
    // Tampilkan Form Penilaian
    public function create($placementId)
    {
        $placement = Placement::with(['application.user.studentProfile', 'evaluation'])
            ->where('pembimbing_id', Auth::id())
            ->findOrFail($placementId);

        return view('pembimbing.evaluation', compact('placement'));
    }

    // Simpan atau Update Penilaian
    public function store(Request $request, $placementId)
    {
        $placement = Placement::where('pembimbing_id', Auth::id())->findOrFail($placementId);

        $request->validate([
            'nilai_disiplin' => 'required|integer|min:0|max:100',
            'nilai_kinerja' => 'required|integer|min:0|max:100',
            'nilai_laporan' => 'required|integer|min:0|max:100',
            'catatan' => 'nullable|string',
        ]);

        $evaluation = Evaluation::updateOrCreate(
            ['placement_id' => $placement->id],
            [
                'nilai_disiplin' => $request->nilai_disiplin,
                'nilai_kinerja' => $request->nilai_kinerja,
                'nilai_laporan' => $request->nilai_laporan,
                'catatan' => $request->catatan,
            ]
        );

        // Hitung final_score sesuai skema kampus
        $univ = $evaluation->getUniversity();
        $scheme = $univ->evaluation_scheme ?? 'dual_evaluation';

        if ($scheme === 'mentor_only') {
            $finalScore = $evaluation->nilai_pembimbing;
            if ($finalScore >= 85) $grade = 'A';
            elseif ($finalScore >= 75) $grade = 'AB';
            elseif ($finalScore >= 65) $grade = 'B';
            elseif ($finalScore >= 55) $grade = 'BC';
            elseif ($finalScore >= 40) $grade = 'C';
            else $grade = 'E';

            $evaluation->update(['final_score' => $finalScore, 'grade' => $grade]);
            $placement->syncCompletionStatus();
        } else {
            $dosenScore = $evaluation->nilai_dosen_calculated ?? $evaluation->nilai_dosen ?? $evaluation->nilai_akademik;
            if ($dosenScore > 0) {
                $weightMentor = $univ ? (int)$univ->weight_mentor : 40;
                $weightLecturer = $univ ? (int)$univ->weight_lecturer : 60;
                $finalScore = round((($weightMentor / 100) * $evaluation->nilai_pembimbing) + (($weightLecturer / 100) * $dosenScore), 2);

                if ($finalScore >= 85) $grade = 'A';
                elseif ($finalScore >= 75) $grade = 'AB';
                elseif ($finalScore >= 65) $grade = 'B';
                elseif ($finalScore >= 55) $grade = 'BC';
                elseif ($finalScore >= 40) $grade = 'C';
                else $grade = 'E';

                $evaluation->update(['final_score' => $finalScore, 'grade' => $grade]);
                $placement->syncCompletionStatus();
            }
        }

        return redirect()->route('pembimbing.student.detail', $placement->id)->with('success', 'Penilaian berhasil disimpan!');
    }
}
