<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Application;
<<<<<<< HEAD
=======
use App\Models\AuditLog;
>>>>>>> main
use App\Models\FinalReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FinalReportController extends Controller
{
<<<<<<< HEAD
    // Tampilkan Halaman Laporan Akhir
    public function index()
    {
        $application = Application::with('placement.finalreport')->where('user_id', Auth::id())->latest()->first();

        // Cek apakah mahasiswa sudah punya placement
        if (!$application || !$application->placement) {
            return redirect()->route('dashboard')->with('error', 'Anda belum diplot ke pembimbing atau belum memulai magang.');
        }

        $placement = $application->placement;

        return view('student.final_report', compact('placement'));
    }

    // Upload Laporan
    public function store(Request $request)
    {
        $request->validate([
            'file_laporan' => 'required|mimes:pdf,doc,docx|max:5120', // Maks 5MB
=======
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
     * Upload / Unggah Dokumen Laporan Akhir Magang (PDF) & Repositori Proyek
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
>>>>>>> main
        ]);

        $application = Application::with('placement')->where('user_id', Auth::id())->latest()->first();
        
        if (!$application || !$application->placement) {
<<<<<<< HEAD
            return redirect()->route('dashboard')->with('error', 'Akses ditolak.');
=======
            return redirect()->route('dashboard')->with('error', 'Akses ditolak: Data penempatan tidak ditemukan.');
>>>>>>> main
        }

        $placementId = $application->placement->id;

<<<<<<< HEAD
        // Cek jika laporan sudah ada, hapus file lama (opsional/bisa disesuaikan)
=======
        // Cek jika laporan sudah ada, hapus file lama
>>>>>>> main
        $finalReport = FinalReport::where('placement_id', $placementId)->first();
        
        if ($finalReport && $finalReport->file_path && Storage::disk('public')->exists($finalReport->file_path)) {
            Storage::disk('public')->delete($finalReport->file_path);
        }

        $filePath = $request->file('file_laporan')->store('final_reports', 'public');

<<<<<<< HEAD
        FinalReport::updateOrCreate(
            ['placement_id' => $placementId],
            [
                'file_path' => $filePath,
                'status' => 'pending', // kembali ke pending setelah upload ulang
=======
        $report = FinalReport::updateOrCreate(
            ['placement_id' => $placementId],
            [
                'title' => $request->title ?? 'Laporan Akhir Praktik Kerja Lapangan (PKL) / Magang MBKM',
                'repository_url' => $request->repository_url,
                'file_path' => $filePath,
                'final_report_path' => $filePath,
                'status' => 'pending',
>>>>>>> main
                'feedback' => null
            ]
        );

<<<<<<< HEAD
        return redirect()->route('student.final_report.index')->with('success', 'Laporan akhir berhasil diunggah dan menunggu verifikasi.');
=======
        AuditLog::record('STUDENT_REPORT_SUBMIT', 'FinalReport', $report->id, [
            'student_name' => Auth::user()->name,
            'title' => $report->title,
            'file_path' => $filePath,
        ]);

        return redirect()->route('student.final_report.index')->with('success', 'Laporan akhir magang berhasil diunggah dan sedang menunggu verifikasi dari DPL serta Pembimbing Dinas.');
>>>>>>> main
    }
}
