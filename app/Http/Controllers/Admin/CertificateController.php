<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\Placement;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class CertificateController extends Controller
{
    // Menampilkan daftar mahasiswa yang siap cetak sertifikat
    public function index()
    {
        // Ambil aplikasi yang sudah accepted, punya placement, nilai lengkap, dan laporan disetujui
        $applications = Application::with([
            'user.studentProfile', 
            'unit.agencyProfile', 
            'placement.evaluation', 
            'placement.finalreport', 
            'placement.pembimbing'
        ])
            ->where('status', 'accepted')
            ->whereHas('placement', function ($query) {
                $query->whereHas('evaluation')
                      ->whereHas('finalreport', function ($subQuery) {
                          $subQuery->where('status', 'approved');
                      });
            })
            ->get();

        return view('admin.certificates.index', compact('applications'));
    }

    // Generate PDF Sertifikat
    public function generate($placementId)
    {
        $placement = Placement::with([
            'application.user.studentProfile', 
            'application.unit.agencyProfile', 
            'evaluation', 
            'pembimbing'
        ])->findOrFail($placementId);

        if (!$placement->evaluation) {
            return redirect()->back()->with('error', 'Penilaian belum lengkap!');
        }

        // Ambil profil instansi dinamis berdasarkan tempat unit mahasiswa magang
        $agencyProfile = $placement->application?->unit?->agencyProfile 
            ?? $placement->agencyProfile 
            ?? AgencyProfile::first();

        $student = $placement->application->user;
        $profile = $student->studentProfile;
        $eval = $placement->evaluation;
        $unit = $placement->application->unit;
        $pembimbing = $placement->pembimbing;

        // Hitung rata-rata
        $rataRata = round(($eval->nilai_disiplin + $eval->nilai_kinerja + $eval->nilai_laporan) / 3, 2);

        // Grade
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

        // Generate PDF dari view template
        $pdf = Pdf::loadView('admin.certificates.template', $data)->setPaper('a4', 'landscape');
        
        $filename = 'Sertifikat_Magang_' . str_replace(' ', '_', $student->name) . '.pdf';

        return $pdf->download($filename);
    }
}
