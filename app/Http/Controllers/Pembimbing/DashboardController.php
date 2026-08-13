<?php

namespace App\Http\Controllers\Pembimbing;

use App\Http\Controllers\Controller;
use App\Models\Logbook;
use App\Models\Placement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    // Halaman Utam / Dashboard Pembimbing (Daftar Mahasiswa Bimbingan)
    public function index()
    {
        $pembimbingId = Auth::id();

        // Ambil data placement mahasiswa yang diplot ke pembimbing ini
        $placements = Placement::with(['application.user.studentProfile', 'application.unit', 'logbooks'])
            ->where('pembimbing_id', $pembimbingId)
            ->get();

        return view('pembimbing.dashboard', compact('placements'));
    }

    // Detail Logbook & Aktivitas Mahasiswa Tertentu
    public function showStudent($placementId)
    {
        $placement = Placement::with(['application.user.studentProfile', 'application.unit', 'logbooks'])
            ->where('pembimbing_id', Auth::id())
            ->findOrFail($placementId);

        return view('pembimbing.student-detail', compact('placement'));
    }

    // Process Approval / Reject Logbook
    public function updateLogbookStatus(Request $request, $logbookId)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'feedback' => 'nullable|string',
        ]);

        $logbook = Logbook::findOrFail($logbookId);
        
        $logbook->update([
            'status' => $request->status,
            'feedback' => $request->feedback,
        ]);

        return redirect()->back()->with('success', 'Status logbook berhasil diperbarui!');
    }

    // Process Approval / Reject Final Report
    public function updateFinalReportStatus(Request $request, $reportId)
    {
        $request->validate([
            'status' => 'required|in:approved,revision',
            'feedback' => 'nullable|string',
        ]);

        $report = \App\Models\FinalReport::findOrFail($reportId);
        
        $report->update([
            'status' => $request->status,
            'feedback' => $request->feedback,
        ]);

        return redirect()->back()->with('success', 'Status laporan akhir berhasil diperbarui!');
    }
}