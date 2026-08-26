<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AgencyProfile;
<<<<<<< HEAD
use App\Models\Placement;
use Barryvdh\DomPDF\Facade\Pdf;
=======
use App\Models\Application;
use App\Models\AuditLog;
use App\Models\Placement;
use App\Models\University;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
>>>>>>> main
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CertificateController extends Controller
{
    /**
<<<<<<< HEAD
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
=======
     * Helper untuk mengambil data placement & otorisasi sertifikat
     */
    public static function getCertificateData($id, $user = null)
    {
        if (!$user) {
            $user = Auth::user();
        }

        $application = null;
        $placement = null;

        // 1. Jika user adalah Mahasiswa:
        if ($user && $user->role === 'mahasiswa') {
            // Coba cari placement milik mahasiswa yang sesuai
            $placement = Placement::with([
                'application.user.studentProfile',
                'application.unit.agencyProfile',
                'mentor',
                'pembimbing',
                'academicAdvisor',
                'dosen',
                'evaluation',
                'finalreport',
            ])->where('id', $id)
              ->whereHas('application', function ($aq) use ($user) {
                  $aq->where('user_id', $user->id);
              })->first();

            if ($placement) {
                $application = $placement->application;
            } else {
                // Coba cari berdasarkan application_id milik mahasiswa ini
                $application = Application::with([
                    'user.studentProfile',
                    'unit.agencyProfile',
                    'placement.mentor',
                    'placement.pembimbing',
                    'placement.academicAdvisor',
                    'placement.dosen',
                    'placement.evaluation',
                    'placement.finalreport',
                ])->where('user_id', $user->id)
                  ->where(function ($q) use ($id) {
                      $q->where('id', $id)
                        ->orWhereHas('placement', function ($pq) use ($id) {
                            $pq->where('id', $id);
                        });
                  })->first();

                // Fallback: Ambil aplikasi terbaru milik mahasiswa ini jika ID tidak cocok (misal placement ID berubah)
                if (!$application) {
                    $application = Application::with([
                        'user.studentProfile',
                        'unit.agencyProfile',
                        'placement.mentor',
                        'placement.pembimbing',
                        'placement.academicAdvisor',
                        'placement.dosen',
                        'placement.evaluation',
                        'placement.finalreport',
                    ])->where('user_id', $user->id)->latest()->first();
                }

                if ($application) {
                    $placement = $application->placement;
                }
            }
        } else {
            // 2. Untuk Admin Dinas, Super Admin, Dosen, atau Mentor:
            // Coba cari berdasarkan placement_id
            $placement = Placement::with([
                'application.user.studentProfile',
                'application.unit.agencyProfile',
                'mentor',
                'pembimbing',
                'academicAdvisor',
                'dosen',
                'evaluation',
                'finalreport',
            ])->find($id);

            if ($placement && $placement->application) {
                $application = $placement->application;
            } else {
                // Coba cari berdasarkan application_id
                $application = Application::with([
                    'user.studentProfile',
                    'unit.agencyProfile',
                    'placement.mentor',
                    'placement.pembimbing',
                    'placement.academicAdvisor',
                    'placement.dosen',
                    'placement.evaluation',
                    'placement.finalreport',
                ])->find($id);

                if ($application) {
                    $placement = $application->placement;
                }
            }
        }

        if (!$application) {
            abort(404, 'Data pengajuan magang / sertifikat tidak ditemukan.');
        }

        // Otorisasi: Mahasiswa pemilik, DPL, Mentor, Admin Dinas, Super Admin, atau Universitas
        $isOwner = ($user && $user->id === $application->user_id);
        $isSuperAdmin = ($user && ($user->role === 'super_admin' || ($user->role === 'admin' && is_null($user->agency_profile_id))));
        $isAgencyAdmin = ($user && $user->role === 'admin' && ($user->agency_profile_id === null || $user->agency_profile_id === $application->unit?->agency_profile_id));
        $isAdvisor = ($placement && ($placement->academic_advisor_id === $user?->id || $placement->mentor_id === $user?->id || $placement->pembimbing_id === $user?->id));
        $isUnivAdmin = ($user && $user->role === 'universitas' && ($user->university_id === $application->user?->university_id || $user->university_id === optional($application->user?->studentProfile)->university_id));

        if (!$isOwner && !$isSuperAdmin && !$isAgencyAdmin && !$isAdvisor && !$isUnivAdmin) {
            abort(403, 'Akses Ditolak: Anda tidak berhak melihat atau mengunduh sertifikat ini.');
        }

        // Cek evaluasi kelulusan
        $eval = $placement?->evaluation;
        $finalReport = $placement?->finalreport;

        // Ambil profil instansi
        $agencyProfile = $application->unit?->agencyProfile 
            ?? AgencyProfile::first();

        $student = $application->user;
        $profile = $student?->studentProfile;
        $mentor = $placement ? ($placement->mentor ?? $placement->pembimbing) : null;
        $dosen = $placement ? ($placement->academicAdvisor ?? $placement->dosen) : null;

        // Cari Perguruan Tinggi secara aman
        $university = null;
        if ($profile?->university_id) {
            $university = University::find($profile->university_id);
        }
        if (!$university && $student?->university_id) {
            $university = University::find($student->university_id);
        }
        if (!$university && $profile?->universitas) {
            $university = University::where('name', 'like', '%' . $profile->universitas . '%')->first();
        }

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
        $data = self::getCertificateData($id, Auth::user());

        AuditLog::record('CERTIFICATE_VIEW', 'Application', $data['application']->id, [
            'student_name' => $data['student']->name,
            'reg_number' => $data['regNumber'],
        ]);

        return view('certificates.internship_certificate', $data);
    }

    /**
     * Unduh Dokumen PDF / Cetak E-Sertifikat
     */
    public function download($id)
    {
        $data = self::getCertificateData($id, Auth::user());

        AuditLog::record('CERTIFICATE_DOWNLOAD', 'Application', $data['application']->id, [
            'student_name' => $data['student']->name,
            'reg_number' => $data['regNumber'],
        ]);

        return view('certificates.internship_certificate', $data);
>>>>>>> main
    }
}
