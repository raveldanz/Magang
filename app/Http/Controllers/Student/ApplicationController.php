<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApplicationController extends Controller
{
    // Menampilkan halaman form pengajuan magang
    public function create()
{
    $units = Unit::all();
    $user = Auth::user();

    // 1. Cek apakah mahasiswa sudah punya profil
    if (!$user->studentProfile) {
        return redirect()->route('student.profile.edit')
            ->with('error', 'Silakan lengkapi profil Anda terlebih dahulu sebelum mengajukan magang.');
    }

    // 2. Cek apakah ada pengajuan yang SANGAT AKTIF (masih PENDING)
    // Jika masih ada yang diproses, mahasiswa tidak boleh buat pengajuan baru dulu
    $activeApplication = Application::where('user_id', $user->id)
        ->where('status', 'pending')
        ->first();

    // 3. Ambil SELURUH riwayat pengajuan mahasiswa ini (urutkan dari yang terbaru)
    $applicationHistory = Application::with('unit')
        ->where('user_id', $user->id)
        ->latest()
        ->get();

    return view('student.application.create', compact('units', 'activeApplication', 'applicationHistory'));
}

    // Menyimpan data pengajuan magang & upload dokumen
    public function store(Request $request)
    {
        $request->validate([
            'unit_id' => 'required|exists:units,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'surat_pengantar' => 'required|mimes:pdf|max:2048', 
            'cv' => 'required|mimes:pdf|max:2048',
            'transkrip' => 'required|mimes:pdf|max:2048',
        ]);

        // Cek Sisa Kuota Instansi yang Dipilih
        $unit = Unit::findOrFail($request->unit_id);
        if ($unit->remaining_quota <= 0) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['unit_id' => 'Kuota untuk instansi/unit ini sudah penuh. Silakan pilih unit kerja lain.']);
        }

        // 1. Simpan Data Pengajuan
        $application = Application::create([
            'user_id' => Auth::id(),
            'unit_id' => $request->unit_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'status' => 'pending',
        ]);

        // 2. Simpan Dokumen Persyaratan
        $documents = [
            'Surat Pengantar' => $request->file('surat_pengantar'),
            'CV' => $request->file('cv'),
            'Transkrip Nilai' => $request->file('transkrip'),
        ];

        foreach ($documents as $type => $file) {
            $path = $file->store('documents/applications', 'public');
            ApplicationDocument::create([
                'application_id' => $application->id,
                'document_type' => $type,
                'file_path' => $path,
            ]);
        }

        return redirect()->back()->with('success', 'Pengajuan magang dan dokumen berhasil dikirim!');
    }

    // Download / Print Surat Penerimaan Magang untuk Mahasiswa
    public function downloadLetter($id)
    {
        $application = Application::with(['user.studentProfile', 'unit.agencyProfile', 'placement.pembimbing'])
            ->where('user_id', Auth::id())
            ->where('status', 'accepted')
            ->findOrFail($id);

        return view('letters.acceptance', compact('application'));
    }

    // Method show: Ambil data placement yang sudah ada
public function show($id)
{
    $application = Application::with(['user.studentProfile', 'unit', 'documents', 'placement'])->findOrFail($id);
    $pembimbings = User::where('role', 'pembimbing')->get();

    return view('admin.applications.show', compact('application', 'pembimbings'));
}

// Method updateStatus: Simpan atau update pembimbing_id di tabel placements
public function updateStatus(Request $request, $id)
{
    $request->validate([
        'status' => 'required|in:pending,verified,accepted,rejected',
        'rejection_note' => 'nullable|string',
        'pembimbing_id' => 'nullable|exists:users,id',
    ]);

    $application = Application::findOrFail($id);
    $application->update([
        'status' => $request->status,
        'rejection_note' => $request->status === 'rejected' ? $request->rejection_note : null,
    ]);

    // Jika status disetujui (Accepted) ATAU pembimbing_id diisi, update/create Placement
    if ($request->status === 'accepted' || $request->pembimbing_id) {
        Placement::updateOrCreate(
            ['application_id' => $application->id],
            ['pembimbing_id' => $request->pembimbing_id]
        );
    }

    return redirect()->back()->with('success', 'Status dan Pembimbing berhasil diperbarui!');
}

}