<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\AuditLog;
use App\Models\FinalReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FinalReportController extends Controller
{
    /**
     * Tampilkan Halaman Laporan Akhir Mahasiswa
     */
    public function index()
    {
        $application = Application::with(['placement.finalreport', 'placement.evaluation', 'unit.agencyProfile'])
            ->where('user_id', Auth::id())
            ->latest()
            ->first();

        // Cek apakah mahasiswa sudah punya placement
        if (!$application || !$application->placement) {
            return redirect()->route('dashboard')->with('error', 'Anda belum memiliki penempatan magang aktif.');
        }

        $placement = $application->placement;
        $finalReport = $placement->finalreport;
        $evaluation = $placement->evaluation;

        return view('student.final_report', compact('application', 'placement', 'finalReport', 'evaluation'));
    }

    /**
     * Upload / Unggah Dokumen Laporan Akhir Magang (PDF / DOCX) & Repositori Proyek
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'repository_url' => 'nullable|url|max:255',
            'file_laporan' => 'required|file|mimes:pdf,doc,docx|max:10240', // Maks 10MB
        ], [
            'file_laporan.required' => 'File naskah laporan akhir wajib diunggah.',
            'file_laporan.mimes' => 'Format file harus berupa PDF atau DOCX.',
            'file_laporan.max' => 'Ukuran file maksimal adalah 10 MB.',
            'repository_url.url' => 'Format tautan repositori proyek harus berupa URL valid.',
        ]);

        $user = Auth::user();
        $application = Application::with('placement')->where('user_id', $user->id)->latest()->first();
        
        if (!$application || !$application->placement) {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak: Data penempatan tidak ditemukan.');
        }

        $placementId = $application->placement->id;
        $finalReport = FinalReport::where('placement_id', $placementId)->first();

        // Bersihkan file lama jika ada (kecuali file default/template bawaan)
        if ($finalReport && $finalReport->file_path && Storage::disk('public')->exists($finalReport->file_path)) {
            $baseOld = basename($finalReport->file_path);
            if (!in_array($baseOld, ['default.pdf', 'sample_laporan_akhir.pdf', 'test_report.pdf'])) {
                Storage::disk('public')->delete($finalReport->file_path);
                @unlink(public_path('storage/' . $finalReport->file_path));
            }
        }

        // Format nama file terstruktur: Laporan_Akhir_[NIM]_[Nama_Mahasiswa]_[Timestamp].[ext]
        $file = $request->file('file_laporan');
        $ext = strtolower($file->getClientOriginalExtension());
        $nim = $user->studentProfile?->nim ?? 'NIM';
        $cleanNim = preg_replace('/[^A-Za-z0-9]/', '', $nim) ?: 'NIM';
        $cleanName = Str::slug($user->name, '_') ?: 'Mahasiswa';
        $timestamp = time();
        $customFilename = "Laporan_Akhir_{$cleanNim}_{$cleanName}_{$timestamp}.{$ext}";

        $filePath = $file->storeAs('final_reports', $customFilename, 'public');

        // Pastikan salinan juga tersedia di public/storage/final_reports untuk keandalan di Windows
        try {
            $publicTarget = public_path('storage/' . $filePath);
            $publicDir = dirname($publicTarget);
            if (!file_exists($publicDir)) {
                @mkdir($publicDir, 0755, true);
            }
            @copy(storage_path('app/public/' . $filePath), $publicTarget);
        } catch (\Throwable $e) {
            // Abaikan jika symlink aktif
        }

        $report = FinalReport::updateOrCreate(
            ['placement_id' => $placementId],
            [
                'title' => $request->title ?? 'Laporan Akhir Praktik Kerja Lapangan (PKL) / Magang MBKM',
                'repository_url' => $request->repository_url,
                'file_path' => $filePath,
                'final_report_path' => $filePath,
                'status' => 'pending',
                'feedback' => null
            ]
        );

        AuditLog::record('STUDENT_REPORT_SUBMIT', 'FinalReport', $report->id, [
            'student_name' => $user->name,
            'title' => $report->title,
            'file_path' => $filePath,
        ]);

        return redirect()->route('student.final_report.index')->with('success', 'Laporan akhir magang berhasil diunggah dengan format resmi dan sedang menunggu verifikasi dari DPL serta Pembimbing Dinas.');
    }

    /**
     * Buka / Unduh Berkas Naskah Laporan Akhir dengan Nama Terformat & Otorisasi Ketat
     */
    public function showFile(Request $request, $id)
    {
        $report = FinalReport::with([
            'placement.application.user.studentProfile',
            'placement.application.unit.agencyProfile',
            'placement.mentor',
            'placement.academicAdvisor',
        ])->findOrFail($id);

        $currentUser = Auth::user();
        $placement = $report->placement;
        $application = $placement?->application;
        $student = $application?->user;

        // Validasi Otorisasi Hak Akses Multi-Role
        $isAuthorized = false;
        $isSuperAdmin = ($currentUser->role === 'super_admin' || ($currentUser->role === 'admin' && is_null($currentUser->agency_profile_id)));

        if ($isSuperAdmin) {
            $isAuthorized = true;
        } elseif ($currentUser->role === 'admin') {
            // Admin Dinas instansi yang sama
            if ($currentUser->agency_profile_id && optional($application?->unit)->agency_profile_id === $currentUser->agency_profile_id) {
                $isAuthorized = true;
            }
        } elseif (in_array($currentUser->role, ['mentor', 'pembimbing'])) {
            // Pembimbing lapangan penempatan atau instansi terkait
            if ($placement && ($placement->mentor_id === $currentUser->id || $placement->pembimbing_id === $currentUser->id)) {
                $isAuthorized = true;
            } elseif ($currentUser->agency_profile_id && optional($application?->unit)->agency_profile_id === $currentUser->agency_profile_id) {
                $isAuthorized = true;
            }
        } elseif (in_array($currentUser->role, ['dosen', 'academic_advisor'])) {
            // DPL penempatan atau satu perguruan tinggi
            if ($placement && $placement->academic_advisor_id === $currentUser->id) {
                $isAuthorized = true;
            } elseif ($currentUser->university_id && $student?->university_id === $currentUser->university_id) {
                $isAuthorized = true;
            }
        } elseif (in_array($currentUser->role, ['university', 'admin_univ', 'universitas'])) {
            // Pengelola magang universitas
            if ($currentUser->university_id && $student?->university_id === $currentUser->university_id) {
                $isAuthorized = true;
            }
        } elseif ($currentUser->role === 'mahasiswa') {
            // Mahasiswa pemilik berkas laporan
            if ($application && $application->user_id === $currentUser->id) {
                $isAuthorized = true;
            }
        }

        if (!$isAuthorized) {
            abort(403, 'Anda tidak memiliki hak akses untuk membuka naskah laporan akhir ini.');
        }

        // Resolusi Lokasi Berkas Fisik
        $filePath = $report->file_path ?? $report->final_report_path;
        $realPath = null;

        $candidatePaths = [
            storage_path('app/public/' . $filePath),
            public_path('storage/' . $filePath),
            storage_path('app/public/final_reports/default.pdf'),
            public_path('storage/final_reports/default.pdf'),
        ];

        foreach ($candidatePaths as $candidate) {
            if ($candidate && file_exists($candidate) && is_file($candidate)) {
                $realPath = $candidate;
                break;
            }
        }

        if (!$realPath || !file_exists($realPath)) {
            return redirect()->back()->with('error', 'Berkas naskah laporan akhir belum tersedia atau sedang disiapkan oleh mahasiswa.');
        }

        // Susun Nama Berkas Bersih untuk Browser / Download: Laporan_Akhir_[NIM]_[Nama_Mahasiswa].[ext]
        $studentName = $student?->name ?? 'Mahasiswa';
        $nim = $student?->studentProfile?->nim ?? 'NIM';
        $cleanNim = preg_replace('/[^A-Za-z0-9]/', '', $nim) ?: 'NIM';
        $cleanName = Str::slug($studentName, '_') ?: 'Mahasiswa';
        $ext = pathinfo($realPath, PATHINFO_EXTENSION) ?: 'pdf';
        $downloadFilename = "Laporan_Akhir_{$cleanNim}_{$cleanName}.{$ext}";

        $mimeType = mime_content_type($realPath) ?: 'application/octet-stream';
        $forceDownload = $request->boolean('download') || !in_array(strtolower($ext), ['pdf', 'png', 'jpg', 'jpeg']);
        $disposition = $forceDownload ? 'attachment' : 'inline';

        return response()->file($realPath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => "{$disposition}; filename=\"{$downloadFilename}\"",
        ]);
    }
}
