<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Placement;
use Illuminate\Support\Facades\Auth;

class CertificateController extends Controller
{
    // Menampilkan daftar mahasiswa yang siap cetak sertifikat (Multi-Tenant Scoped)
    public function index()
    {
        $user = Auth::user();

        // 1. Sinkronisasi otomatis mahasiswa yang sudah ACC laporan & tuntas evaluasi agar statusnya lulus (completed)
        $candidates = Placement::whereHas('application', function ($q) {
            $q->whereIn('status', ['accepted', 'active']);
        })->whereHas('finalreport', function ($subQuery) {
            $subQuery->where('status', 'approved');
        })->whereHas('evaluation')->get();

        foreach ($candidates as $cand) {
            $cand->syncCompletionStatus();
        }

        // 2. Ambil aplikasi yang sudah berstatus completed dan siap cetak sertifikat
        $query = Application::with([
            'user.studentProfile',
            'unit.agencyProfile',
            'placement.evaluation',
            'placement.finalreport',
            'placement.pembimbing',
        ])
            ->where('status', 'completed')
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

        // Certificate Gate: hanya tampilkan yang benar-benar memenuhi seluruh syarat penerbitan
        $applications = $query->get()->filter(fn (Application $app) => $app->isCertificateEligible())->values();

        return view('admin.certificates.index', compact('applications'));
    }

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
    }
}
