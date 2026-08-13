<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Placement;
use App\Models\User;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    // Menampilkan semua daftar pengajuan magang masuk (dengan Search, Filter, & Paginasi)
    public function index(Request $request)
    {
        $query = Application::with(['user.studentProfile', 'unit', 'documents', 'placement.evaluation', 'placement.finalreport'])->latest();

        // 1. Pencarian berdasarkan Nama Mahasiswa, NIM, atau Universitas
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('studentProfile', function ($spQuery) use ($search) {
                      $spQuery->where('universitas', 'like', "%{$search}%")
                              ->orWhere('nim', 'like', "%{$search}%");
                  });
            });
        }

        // 2. Filter Berdasarkan Status Pengajuan
        if ($request->filled('status')) {
            $query->where('status', strtolower($request->status));
        }

        // 3. Paginasi 10 Data Per Halaman
        $applications = $query->paginate(10)->withQueryString();

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
        $statusInput = strtolower($request->status);
        $request->merge(['status' => $statusInput]);

        $request->validate([
            'status' => 'required|in:pending,verified,accepted,rejected',
            'rejection_note' => 'nullable|string',
            'pembimbing_id' => 'nullable|exists:users,id',
            'letter_number' => 'nullable|string|max:100',
            'letter_date' => 'nullable|date',
        ]);

        $application = Application::findOrFail($id);
        
        $application->update([
            'status' => $request->status,
            'rejection_note' => $request->status === 'rejected' ? $request->rejection_note : null,
            'letter_number' => $request->status === 'accepted' ? $request->letter_number : null,
            'letter_date' => $request->status === 'accepted' ? $request->letter_date : null,
        ]);

        if ($request->status === 'accepted' && $request->pembimbing_id) {
            Placement::updateOrCreate(
                ['application_id' => $application->id],
                ['pembimbing_id' => $request->pembimbing_id]
            );
        }

        return redirect()->route('admin.applications.index')->with('success', 'Status pengajuan berhasil diperbarui!');
    }

    // Cetak / Pratinjau Surat Balasan Penerimaan untuk Admin
    public function downloadLetter($id)
    {
        $application = Application::with(['user.studentProfile', 'unit', 'placement.pembimbing'])
            ->where('status', 'accepted')
            ->findOrFail($id);

        return view('letters.acceptance', compact('application'));
    }
}