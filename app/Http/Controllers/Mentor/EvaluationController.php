<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\Evaluation;
use App\Models\Placement;
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

        return view('mentor.evaluation', compact('placement'));
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

        $request->validate([
            'nilai_disiplin' => 'required|numeric|min:0|max:100',
            'nilai_kinerja'  => 'required|numeric|min:0|max:100',
            'nilai_laporan'  => 'required|numeric|min:0|max:100',
            'catatan'        => 'nullable|string|max:1500',
        ], [
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

        Evaluation::updateOrCreate(
            ['placement_id' => $placement->id],
            [
                'nilai_disiplin' => $request->nilai_disiplin,
                'nilai_kinerja'  => $request->nilai_kinerja,
                'nilai_laporan'  => $request->nilai_laporan,
                'catatan'        => $request->catatan,
            ]
        );

        return redirect()->route('mentor.students.show', $placement->id)
            ->with('success', 'Penilaian evaluasi akhir berhasil disimpan! Mahasiswa kini siap diterbitkan sertifikatnya.');
    }
}
