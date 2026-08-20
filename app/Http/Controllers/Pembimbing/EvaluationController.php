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

        Evaluation::updateOrCreate(
            ['placement_id' => $placement->id],
            [
                'nilai_disiplin' => $request->nilai_disiplin,
                'nilai_kinerja' => $request->nilai_kinerja,
                'nilai_laporan' => $request->nilai_laporan,
                'catatan' => $request->catatan,
            ]
        );

        return redirect()->route('pembimbing.student.detail', $placement->id)->with('success', 'Penilaian berhasil disimpan!');
    }
}
