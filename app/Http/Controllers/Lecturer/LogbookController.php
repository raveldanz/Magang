<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\Logbook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogbookController extends Controller
{
    /**
     * Tampilkan detail logbook untuk monitoring Dosen Kampus (Read-Only)
     */
    public function show($id)
    {
        $user = Auth::user();

        $logbook = Logbook::with([
            'placement.application.user.studentProfile',
            'placement.application.unit.agencyProfile',
            'placement.mentor',
            'placement.pembimbing',
            'placement.academicAdvisor',
        ])->findOrFail($id);

        $placement = $logbook->placement;
        $student = $placement?->application?->user;

        // Otorisasi: Dosen diizinkan jika mahasiswa satu kampus (university_id sama) ATAU dosen adalah pembimbing yang diplot
        $isSameUniv = ($user->university_id !== null && $student?->university_id === $user->university_id);
        $isAssignedAdvisor = ($placement && ($placement->academic_advisor_id === $user->id || $placement->mentor_id === $user->id));

        // Fallback jika salah satu akun belum memiliki university_id
        if (!$isSameUniv && $user->university && $student) {
            $isSameUniv = (
                $student->university === $user->university || 
                optional($student->studentProfile)->universitas === $user->university
            );
        }

        if (!$isSameUniv && !$isAssignedAdvisor) {
            abort(403, 'Anda tidak memiliki hak akses untuk memonitor logbook mahasiswa ini.');
        }

        return view('admin.logbooks.show', compact('logbook'));
    }
}
