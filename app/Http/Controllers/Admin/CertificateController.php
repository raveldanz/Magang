<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\Placement;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CertificateController extends Controller
{
    // Menampilkan daftar mahasiswa yang siap cetak sertifikat (Multi-Tenant Scoped)
    public function index()
    {
        $user = Auth::user();

        // Ambil aplikasi yang sudah accepted, punya placement, nilai lengkap, dan laporan disetujui
        $query = Application::with([
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
            });

        // Multi-Tenant Isolation: Admin instansi hanya melihat sertifikat pada unit instansinya sendiri
        if ($user && $user->agency_profile_id !== null) {
            $query->whereHas('unit', function ($q) use ($user) {
                $q->where('agency_profile_id', $user->agency_profile_id);
            });
        }

        $applications = $query->get();

        return view('admin.certificates.index', compact('applications'));
    }

<<<<<<< HEAD
    // Pratinjau Sertifikat (HTML view)
    public function show($placementId)
    {
        $user = Auth::user();

        $placement = Placement::with([
            'application.user.studentProfile', 
            'application.unit.agencyProfile', 
            'evaluation', 
            'pembimbing'
        ])->findOrFail($placementId);

        // Multi-Tenant Authorization Check
        if ($user && $user->agency_profile_id !== null && optional($placement->application?->unit)->agency_profile_id !== $user->agency_profile_id) {
            abort(403, 'Anda tidak memiliki hak akses ke data sertifikat instansi lain.');
        }

        if (!$placement->evaluation) {
            return redirect()->back()->with('error', 'Penilaian belum lengkap!');
        }

        // Ambil profil instansi murni dari relasi placement/unit mahasiswa
        $agencyProfile = $placement->application?->unit?->agencyProfile 
            ?? $placement->agencyProfile 
            ?? AgencyProfile::first();

        $student = $placement->application->user;
        $profile = $student->studentProfile;
        $eval = $placement->evaluation;
        $unit = $placement->application->unit;
        $pembimbing = $placement->mentor ?? $placement->pembimbing;

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

        return view('admin.certificates.template', $data);
    }

    // Generate & Download PDF Sertifikat
    public function generate($placementId)
    {
        $user = Auth::user();

        $placement = Placement::with([
            'application.user.studentProfile', 
            'application.unit.agencyProfile', 
            'evaluation', 
            'mentor',
            'pembimbing'
        ])->findOrFail($placementId);

        // Multi-Tenant Authorization Check
        if ($user && $user->agency_profile_id !== null && optional($placement->application?->unit)->agency_profile_id !== $user->agency_profile_id) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengunduh sertifikat instansi lain.');
        }

        if (!$placement->evaluation) {
            return redirect()->back()->with('error', 'Penilaian belum lengkap!');
        }

        // Ambil profil instansi murni dari relasi placement/unit mahasiswa
        $agencyProfile = $placement->application?->unit?->agencyProfile 
            ?? $placement->agencyProfile 
            ?? AgencyProfile::first();

        $student = $placement->application->user;
        $profile = $student->studentProfile;
        $eval = $placement->evaluation;
        $unit = $placement->application->unit;
        $pembimbing = $placement->mentor ?? $placement->pembimbing;

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
=======
    // Pratinjau & Cetak E-Sertifikat Lengkap (Format Resmi Mahasiswa & Transkrip 2 Halaman)
    public function show($placementId)
    {
        $data = \App\Http\Controllers\Student\CertificateController::getCertificateData($placementId, Auth::user());
        return view('certificates.internship_certificate', $data);
    }

    // Generate & Cetak E-Sertifikat
    public function generate($placementId)
    {
        $data = \App\Http\Controllers\Student\CertificateController::getCertificateData($placementId, Auth::user());
        return view('certificates.internship_certificate', $data);
>>>>>>> main
    }
}

