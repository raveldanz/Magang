<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Placement;
use App\Models\User;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    // Menampilkan semua daftar pengajuan magang masuk
    public function index()
    {
        $applications = Application::with(['user.studentProfile', 'unit', 'documents'])->latest()->get();
        return view('admin.applications.index', compact('applications'));
    }

    // Detail pengajuan magang & dokumen
    public function show($id)
    {
        $application = Application::with(['user.studentProfile', 'unit', 'documents'])->findOrFail($id);
        $pembimbings = User::where('role', 'pembimbing')->get(); // Untuk dropdown penempatan
        return view('admin.applications.show', compact('application', 'pembimbings'));
    }

    // Update status pengajuan (Verifikasi / Seleksi)
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:verified,accepted,rejected',
            'rejection_note' => 'nullable|string',
            'pembimbing_id' => 'nullable|exists:users,id',
        ]);

        $application = Application::findOrFail($id);
        $application->update([
            'status' => $request->status,
            'rejection_note' => $request->status === 'rejected' ? $request->rejection_note : null,
        ]);

        // Jika status disetujui (Accepted), otomatis buatkan record Penempatan (Placement)
        if ($request->status === 'accepted') {
            Placement::updateOrCreate(
                ['application_id' => $application->id],
                ['pembimbing_id' => $request->pembimbing_id]
            );
        }

        return redirect()->back()->with('success', 'Status pengajuan berhasil diperbarui!');
    }
}