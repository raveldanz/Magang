<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Placement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApplicationController extends Controller
{
    // Menampilkan semua daftar pengajuan magang masuk (dengan Multi-Tenant Scoping, Search, Filter, Eager Loading & Paginasi)
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Application::with([
            'user.studentProfile', 
            'unit.agencyProfile', 
            'documents', 
            'placement.evaluation', 
            'placement.finalreport',
            'placement.pembimbing'
        ])->latest();

        // Multi-Tenant Isolation: Admin instansi hanya melihat pengajuan pada unit instansinya sendiri
        if ($user && $user->agency_profile_id !== null) {
            $query->whereHas('unit', function ($q) use ($user) {
                $q->where('agency_profile_id', $user->agency_profile_id);
            });
        }

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
        $user = Auth::user();

        $application = Application::with([
            'user.studentProfile', 
            'unit.agencyProfile', 
            'documents', 
            'placement.pembimbing'
        ])->findOrFail($id);

        // Multi-Tenant Authorization Check
        if ($user && $user->agency_profile_id !== null && optional($application->unit)->agency_profile_id !== $user->agency_profile_id) {
            abort(403, 'Anda tidak memiliki hak akses ke data pengajuan instansi lain.');
        }
        
        $pembimbingQuery = User::whereIn('role', ['mentor', 'pembimbing']);
        if ($user && $user->agency_profile_id !== null) {
            $pembimbingQuery->where(function ($q) use ($user) {
                $q->where('agency_profile_id', $user->agency_profile_id)
                  ->orWhereNull('agency_profile_id');
            });
        }
        $pembimbings = $pembimbingQuery->get(); // Untuk dropdown penempatan pembimbing/mentor

        return view('admin.applications.show', compact('application', 'pembimbings'));
    }

    // Update status pengajuan (Verifikasi / Seleksi)
    public function updateStatus(Request $request, $id)
    {
        $user = Auth::user();

        $statusInput = strtolower($request->status);
        $request->merge(['status' => $statusInput]);

        $request->validate([
            'status' => 'required|in:pending,verified,accepted,rejected',
            'rejection_note' => 'nullable|string',
            'mentor_id' => 'nullable|exists:users,id',
            'pembimbing_id' => 'nullable|exists:users,id',
            'letter_number' => 'nullable|string|max:100',
            'letter_date' => 'nullable|date',
        ]);

        $application = Application::with('unit')->findOrFail($id);

        // Multi-Tenant Authorization Check
        if ($user && $user->agency_profile_id !== null && optional($application->unit)->agency_profile_id !== $user->agency_profile_id) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah pengajuan instansi lain.');
        }
        
        $application->update([
            'status' => $request->status,
            'rejection_note' => $request->status === 'rejected' ? $request->rejection_note : null,
            'letter_number' => $request->status === 'accepted' ? $request->letter_number : null,
            'letter_date' => $request->status === 'accepted' ? $request->letter_date : null,
        ]);

        $assignedMentorId = $request->mentor_id ?? $request->pembimbing_id;

        if ($request->status === 'accepted' && $assignedMentorId) {
            Placement::updateOrCreate(
                ['application_id' => $application->id],
                [
                    'mentor_id' => $assignedMentorId,
                    'pembimbing_id' => $assignedMentorId,
                ]
            );
        }

        return redirect()->route('admin.applications.index')->with('success', 'Status pengajuan berhasil diperbarui!');
    }

    // Cetak / Pratinjau Surat Balasan Penerimaan untuk Admin
    public function downloadLetter($id)
    {
        $user = Auth::user();

        $application = Application::with([
            'user.studentProfile', 
            'unit.agencyProfile', 
            'placement.pembimbing'
        ])
            ->where('status', 'accepted')
            ->findOrFail($id);

        // Multi-Tenant Authorization Check
        if ($user && $user->agency_profile_id !== null && optional($application->unit)->agency_profile_id !== $user->agency_profile_id) {
            abort(403, 'Anda tidak memiliki hak akses ke surat pengajuan instansi lain.');
        }

        return view('letters.acceptance', compact('application'));
    }
}