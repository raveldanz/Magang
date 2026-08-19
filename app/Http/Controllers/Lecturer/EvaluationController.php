<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\Evaluation;
use App\Models\Placement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EvaluationController extends Controller
{
    /**
     * Helper untuk validasi bahwa penempatan mahasiswa benar berasal dari kampus dosen yang login
     */
    protected function getAuthorizedPlacement($placementId)
    {
        $lecturer = Auth::user();

        return Placement::with([
            'application.user.studentProfile',
            'application.unit.agencyProfile',
            'mentor',
            'pembimbing',
            'evaluation',
            'finalreport',
        ])->where(function ($q) use ($lecturer) {
            $q->where('academic_advisor_id', $lecturer->id)
              ->orWhereHas('application.user', function ($uQuery) use ($lecturer) {
                  if (!empty($lecturer->university)) {
                      $uQuery->where('university', $lecturer->university)
                             ->orWhereHas('studentProfile', function ($sp) use ($lecturer) {
                                 $sp->where('universitas', $lecturer->university);
                             });
                  }
              });
        })->findOrFail($placementId);
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
            'nilai_akademik' => 'required|numeric|min:0|max:100',
            'catatan_dosen' => 'nullable|string|max:1000',
        ], [
            'nilai_akademik.required' => 'Nilai akademik / bimbingan wajib diisi.',
            'nilai_akademik.min' => 'Nilai minimal adalah 0.',
            'nilai_akademik.max' => 'Nilai maksimal adalah 100.',
        ]);

        $evaluation = Evaluation::updateOrCreate(
            ['placement_id' => $placement->id],
            [
                'nilai_akademik' => $request->nilai_akademik,
                'catatan_dosen' => $request->catatan_dosen,
            ]
        );

        return redirect()->route('lecturer.dashboard')->with('success', "Nilai akademik mahasiswa '{$placement->application->user->name}' berhasil disimpan!");
    }
}
