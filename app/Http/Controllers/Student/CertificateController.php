<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\AuditLog;
use App\Models\Placement;
use App\Models\University;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CertificateController extends Controller
{
    /**
     * Helper untuk mengambil data placement & otorisasi sertifikat
     */
    protected function getCertificateData($id)
    {
        $user = Auth::user();

        // Cari berdasarkan application_id atau placement_id
        $application = Application::with([
            'user.studentProfile.university',
            'unit.agencyProfile',
            'placement.mentor',
            'placement.pembimbing',
            'placement.academicAdvisor',
            'placement.dosen',
            'placement.evaluation',
            'placement.finalreport',
        ])->where(function ($q) use ($id) {
            $q->where('id', $id)
              ->orWhereHas('placement', function ($pq) use ($id) {
                  $pq->where('id', $id);
              });
        })->firstOrFail();

        $placement = $application->placement;

        // Otorisasi: Mahasiswa pemilik, DPL, Mentor, atau Admin
        $isOwner = ($user->id === $application->user_id);
        $isSuperAdmin = ($user->role === 'super_admin' || ($user->role === 'admin' && is_null($user->agency_profile_id)));
        $isAgencyAdmin = ($user->role === 'admin' && $user->agency_profile_id === $application->unit?->agency_profile_id);
        $isAdvisor = ($placement && ($placement->academic_advisor_id === $user->id || $placement->mentor_id === $user->id));

        if (!$isOwner && !$isSuperAdmin && !$isAgencyAdmin && !$isAdvisor) {
            abort(403, 'Akses Ditolak: Anda tidak berhak melihat atau mengunduh sertifikat ini.');
        }

        // Cek apakah mahasiswa memenuhi syarat kelulusan
        $eval = $placement?->evaluation;
        $finalReport = $placement?->finalreport;
        $isCompleted = ($application->status === 'completed') || 
                       ($application->status === 'accepted' && $eval && $eval->nilai_akhir > 0 && optional($finalReport)->status === 'approved');

        if (!$isCompleted && !$isSuperAdmin) {
            abort(403, 'Sertifikat Magang Belum Diterbitkan: Pastikan laporan akhir telah disetujui dan nilai evaluasi (Dinas 40% & DPL 60%) telah lengkap.');
        }

        // Ambil profil instansi
        $agencyProfile = $application->unit?->agencyProfile 
            ?? AgencyProfile::first();

        $student = $application->user;
        $profile = $student->studentProfile;
        $mentor = $placement ? ($placement->mentor ?? $placement->pembimbing) : null;
        $dosen = $placement ? ($placement->academicAdvisor ?? $placement->dosen) : null;

        // Cari Perguruan Tinggi
        $university = $profile?->university ?? University::where('name', $profile?->universitas ?? '')->first();

        // Nomor Registrasi Sertifikat
        $regNumber = "SERT/{$application->id}/PEMKOT-SBY/" . Carbon::now()->format('Y');

        return compact(
            'application',
            'placement',
            'agencyProfile',
            'student',
            'profile',
            'mentor',
            'dosen',
            'university',
            'eval',
            'finalReport',
            'regNumber'
        );
    }

    /**
     * Tampilkan E-Sertifikat Resmi Format Cetak / Preview A4 Landscape
     */
    public function show($id)
    {
        $data = $this->getCertificateData($id);

        AuditLog::record('CERTIFICATE_VIEW', 'Application', $data['application']->id, [
            'student_name' => $data['student']->name,
            'reg_number' => $data['regNumber'],
        ]);

        return view('certificates.internship_certificate', $data);
    }

    /**
     * Unduh Dokumen PDF E-Sertifikat
     */
    public function download($id)
    {
        $data = $this->getCertificateData($id);

        AuditLog::record('CERTIFICATE_DOWNLOAD', 'Application', $data['application']->id, [
            'student_name' => $data['student']->name,
            'reg_number' => $data['regNumber'],
        ]);

        $studentName = str_replace(' ', '_', $data['student']->name);
        $filename = "Sertifikat_Magang_{$studentName}.pdf";

        // Tampilkan print view jika dompdf mengalami kendala aset gambar lokal
        return view('certificates.internship_certificate', $data);
    }
}
