<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AgencyProfile;
use App\Models\Placement;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CertificateController extends Controller
{
    /**
     * Download E-Sertifikat untuk Mahasiswa yang telah menyelesaikan magang (Laporan Disetujui & Nilai Lengkap)
     */
    public function download($placementId)
    {
        $user = Auth::user();

        $placement = Placement::with([
            'application.user.studentProfile',
            'application.unit.agencyProfile',
            'evaluation',
            'finalreport',
            'mentor',
            'pembimbing'
        ])
            ->where('id', $placementId)
            ->whereHas('application', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->firstOrFail();

        // Validasi kelayakan unduh sertifikat
        if (!$placement->evaluation || optional($placement->finalreport)->status !== 'approved') {
            return redirect()->route('dashboard')->with('error', 'E-Sertifikat belum dapat diunduh. Pastikan laporan akhir telah disetujui dan pembimbing telah memberikan evaluasi.');
        }

        // Ambil profil instansi terkait
        $agencyProfile = $placement->application?->unit?->agencyProfile 
            ?? $placement->agencyProfile 
            ?? AgencyProfile::first();

        $student = $placement->application->user;
        $profile = $student->studentProfile;
        $eval = $placement->evaluation;
        $unit = $placement->application->unit;
        $pembimbing = $placement->mentor ?? $placement->pembimbing;

        // Hitung rata-rata & grade
        $rataRata = round(($eval->nilai_disiplin + $eval->nilai_kinerja + $eval->nilai_laporan) / 3, 2);
        $grade = 'C';
        if ($rataRata >= 85) $grade = 'A';
        elseif ($rataRata >= 70) $grade = 'B';

        $data = [
            'placement' => $placement,
            'agencyProfile' => $agencyProfile,
            'name' => strtoupper($student->name),
            'nim' => $profile->nim ?? '-',
            'universitas' => strtoupper($profile->universitas ?? '-'),
            'unit' => strtoupper($unit->name ?? '-'),
            'start_date' => \Carbon\Carbon::parse($placement->application->start_date)->translatedFormat('d F Y'),
            'end_date' => \Carbon\Carbon::parse($placement->application->end_date)->translatedFormat('d F Y'),
            'rataRata' => $rataRata,
            'grade' => $grade,
            'date_issued' => \Carbon\Carbon::now()->translatedFormat('d F Y'),
            'pembimbing' => $pembimbing,
        ];

        $pdf = Pdf::loadView('admin.certificates.template', $data)->setPaper('a4', 'landscape');
        $filename = 'E-Sertifikat_Magang_' . str_replace(' ', '_', $student->name) . '.pdf';

        return $pdf->download($filename);
    }
}
