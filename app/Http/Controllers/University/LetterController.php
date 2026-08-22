<?php

namespace App\Http\Controllers\University;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\University;
use Illuminate\Http\Request;
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

        // Cari profil universitas mahasiswa
        $university = $student->university_id 
            ? University::find($student->university_id) 
            : University::where('name', $student->university)->orWhere('code', $student->university)->first();

        if (!$university && $user->university_id) {
            $university = University::find($user->university_id);
        }

        // Otorisasi: Pastikan mahasiswa berasal dari universitas yang sama
        $isSameUniv = ($user->university_id !== null && $student->university_id === $user->university_id);
        if (!$isSameUniv && $university) {
            $isSameUniv = ($student->university === $university->name || optional($student->studentProfile)->universitas === $university->name);
        }

        if (!$isSameUniv) {
            abort(403, 'Anda tidak memiliki hak akses untuk mencetak surat tugas mahasiswa kampus lain.');
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
