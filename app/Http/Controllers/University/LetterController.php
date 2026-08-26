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

        // Cari profil universitas
        $university = $user->university_id 
            ? University::find($user->university_id) 
            : ($student->university_id ? University::find($student->university_id) : University::where('name', $user->university)->orWhere('code', $user->university)->first());

        // Otorisasi: Pastikan mahasiswa berasal dari universitas yang sama
        $isSameUniv = false;

        if ($user->university_id && $student->university_id && (int)$user->university_id === (int)$student->university_id) {
            $isSameUniv = true;
        }

        if (!$isSameUniv && $university) {
            $studentUniv = strtolower(trim($student->university ?? ''));
            $studentProfileUniv = strtolower(trim(optional($student->studentProfile)->universitas ?? ''));
            $targetUnivName = strtolower(trim($university->name ?? ''));
            $targetUnivCode = strtolower(trim($university->code ?? ''));

            if (
                ($studentUniv && ($studentUniv === $targetUnivName || $studentUniv === $targetUnivCode)) ||
                ($studentProfileUniv && ($studentProfileUniv === $targetUnivName || $studentProfileUniv === $targetUnivCode)) ||
                ($targetUnivName && (str_contains($studentUniv, $targetUnivName) || str_contains($targetUnivName, $studentUniv))) ||
                ($targetUnivCode && (str_contains($studentUniv, $targetUnivCode) || str_contains($targetUnivCode, $studentUniv)))
            ) {
                $isSameUniv = true;
            }
        }

        if (!$isSameUniv && $user->university) {
            $userUniv = strtolower(trim($user->university));
            $studentUniv = strtolower(trim($student->university ?? ''));
            $studentProfileUniv = strtolower(trim(optional($student->studentProfile)->universitas ?? ''));

            if ($studentUniv === $userUniv || $studentProfileUniv === $userUniv || str_contains($studentUniv, $userUniv) || str_contains($userUniv, $studentUniv)) {
                $isSameUniv = true;
            }
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
