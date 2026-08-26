<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\FinalReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FinalReportController extends Controller
{
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
        ]);

        $application = Application::with('placement')->where('user_id', Auth::id())->latest()->first();
        
        if (!$application || !$application->placement) {
            return redirect()->route('dashboard')->with('error', 'Akses ditolak.');
        }

        $placementId = $application->placement->id;

        // Cek jika laporan sudah ada, hapus file lama (opsional/bisa disesuaikan)
        $finalReport = FinalReport::where('placement_id', $placementId)->first();
        
        if ($finalReport && $finalReport->file_path && Storage::disk('public')->exists($finalReport->file_path)) {
            Storage::disk('public')->delete($finalReport->file_path);
        }

        $filePath = $request->file('file_laporan')->store('final_reports', 'public');

        FinalReport::updateOrCreate(
            ['placement_id' => $placementId],
            [
                'file_path' => $filePath,
                'status' => 'pending', // kembali ke pending setelah upload ulang
                'feedback' => null
            ]
        );

        return redirect()->route('student.final_report.index')->with('success', 'Laporan akhir berhasil diunggah dan menunggu verifikasi.');
    }
}
