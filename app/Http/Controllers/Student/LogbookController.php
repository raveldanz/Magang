<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Logbook;
use App\Models\Placement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogbookController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        // 1. Ambil pengajuan mahasiswa yang disetujui (akomodasi huruf kecil 'accepted' maupun kapital 'ACCEPTED')
        $application = Application::where('user_id', $userId)
            ->whereIn('status', ['accepted', 'ACCEPTED'])
            ->latest()
            ->first();

        $placement = null;
        $logbooks = collect();

        if ($application) {
            // 2. Ambil data penempatan (placement)
            $placement = Placement::where('application_id', $application->id)
                ->with('pembimbing')
                ->first();

            if ($placement) {
                // 3. Ambil riwayat logbook jika placement sudah ada
                $logbooks = Logbook::where('placement_id', $placement->id)
                    ->orderBy('date', 'desc')
                    ->get();
            }
        }

        // 4. Hitung statistik logbook untuk tampilan kartu/card di view
        $stats = [
            'total'    => $logbooks->count(),
            'approved' => $logbooks->where('status', 'approved')->count(),
            'pending'  => $logbooks->where('status', 'pending')->count(),
            'rejected' => $logbooks->where('status', 'rejected')->count(),
        ];

        // Tampilkan view tanpa me-redirect paksa agar alert peringatan/info tetap dapat terbaca
        return view('student.logbook.index', compact('application', 'placement', 'logbooks', 'stats'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date'       => 'required|date',
            'activity'   => 'required|string|min:10',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $userId = Auth::id();

        // Cari application yang disetujui
        $application = Application::where('user_id', $userId)
            ->whereIn('status', ['accepted', 'ACCEPTED'])
            ->latest()
            ->firstOrFail();

        // Ambil placement
        $placement = Placement::where('application_id', $application->id)->firstOrFail();

        $filePath = null;
        if ($request->hasFile('attachment')) {
            $filePath = $request->file('attachment')->store('documents/logbooks', 'public');
        }

        Logbook::create([
            'placement_id' => $placement->id,
            'date'         => $request->date,
            'activity'     => $request->activity,
            'attachment'   => $filePath,
            'status'       => 'pending',
        ]);

        return redirect()->route('student.logbook.index')->with('success', 'Logbook kegiatan berhasil disimpan!');
    }

    // 1. Menampilkan Halaman Form Edit
public function edit($id)
{
    $logbook = Logbook::findOrFail($id);

    // Cek Keamanan: Pastikan logbook milik mahasiswa yang sedang login
    // Dan hanya status PENDING / REJECTED yang bisa diedit
    if (strtolower($logbook->status) === 'approved') {
        return redirect()->route('student.logbook.index')
            ->with('error', 'Logbook yang sudah disetujui tidak dapat diubah.');
    }

    return view('student.logbook.edit', compact('logbook'));
}

// 2. Memproses Update Data Logbook
public function update(Request $request, $id)
{
    $request->validate([
        'date'       => 'required|date',
        'activity'   => 'required|string|min:10',
        'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
    ]);

    $logbook = Logbook::findOrFail($id);

    // Keamanan: Tolak jika sudah approved
    if (strtolower($logbook->status) === 'approved') {
        return redirect()->route('student.logbook.index')
            ->with('error', 'Logbook yang sudah disetujui tidak dapat diubah.');
    }

    // Jika ada file baru yang diunggah
    if ($request->hasFile('attachment')) {
        // Hapus file lama jika ada
        if ($logbook->attachment && \Illuminate\Support\Facades\Storage::disk('public')->exists($logbook->attachment)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($logbook->attachment);
        }

        $logbook->attachment = $request->file('attachment')->store('documents/logbooks', 'public');
    }

    // Update data utama
    $logbook->date = $request->date;
    $logbook->activity = $request->activity;
    
    // Jika logbook sebelumnya rejected, kembalikan ke pending agar direview ulang
    if (strtolower($logbook->status) === 'rejected') {
        $logbook->status = 'pending';
    }

    $logbook->save();

    // REDIRECT KEMBALI KE HALAMAN UTAMA LOGBOOK + KIRIM PESAN SUCCESS
    return redirect()->route('student.logbook.index')
        ->with('success', 'Logbook kegiatan berhasil diperbarui!');
    }
    // Tambahkan fungsi create ini di dalam LogbookController.php
public function create()
{
    return view('student.logbook.create');
}


}
