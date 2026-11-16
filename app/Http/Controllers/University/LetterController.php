<?php

namespace App\Http\Controllers\University;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\University;
use App\Services\UniversityResolver;
use Illuminate\Support\Facades\Auth;

class LetterController extends Controller
{
    /**
     * Generate Surat Tugas / Pengantar Magang Resmi Berformat Cetak A4 / PDF
     */
    public function generateLetter($applicationId)
    {
        $user = Auth::user();

        $application = Application::with([
            'user.studentProfile',
            'unit.agencyProfile',
            'placement.academicAdvisor',
            'placement.mentor',
            'placement.pembimbing',
        ])->findOrFail($applicationId);

        $student = $application->user;
        $profile = $student->studentProfile;
        $unit = $application->unit;
        $agency = $unit?->agencyProfile;
        $placement = $application->placement;
        $dosen = $placement?->academicAdvisor;
        $mentor = $placement?->mentor ?? $placement?->pembimbing;

        // Kampus milik admin yang membuka (bukan kampus si mahasiswa — itu selalu cocok)
        $university = $user->university_id
            ? University::find($user->university_id)
            : app(UniversityResolver::class)->findExact($user->university);

        // Otorisasi: mahasiswa harus berasal dari kampus yang sama (university_id / nama persis)
        $isSameUniv = app(UniversityResolver::class)->belongsTo($student, $university);

        if (! $isSameUniv) {
            abort(403, 'Anda tidak memiliki hak akses untuk mencetak surat tugas peserta magang kampus lain.');
        }

        return view('university.letters.internship_letter', compact(
            'application',
            'student',
            'profile',
            'unit',
            'agency',
            'placement',
            'dosen',
            'mentor',
            'university',
            'user'
        ));
    }
}
