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

