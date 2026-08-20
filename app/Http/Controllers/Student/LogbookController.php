<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Logbook;
use App\Models\Placement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LogbookController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        // 1. Ambil pengajuan terakhir mahasiswa
        $application = Application::where('user_id', $userId)
            ->latest()
            ->first();

        $placement = null;
        $logbooks = collect();

        if ($application) {
            // 2. Ambil data penempatan (placement)
            $placement = Placement::where('application_id', $application->id)
                ->with(['pembimbing', 'mentor', 'academicAdvisor'])
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

        return view('student.logbook.index', compact('application', 'placement', 'logbooks', 'stats'));
    }

    public function create()
    {
        $application = Application::where('user_id', Auth::id())->latest()->first();

        if (!$application || !$application->is_active_internship) {
            return redirect()->route('student.logbook.index')
                ->with('warning', 'Pengisian logbook hanya dapat dilakukan saat masa magang aktif dan DPL telah terdaftar.');
        }

        return view('student.logbook.create', compact('application'));
    }

    public function store(Request $request)
    {
        $application = Application::where('user_id', Auth::id())->latest()->first();

        if (!$application || !$application->is_active_internship) {
            return redirect()->route('student.logbook.index')
                ->with('warning', 'Pengisian logbook hanya dapat dilakukan saat masa magang aktif dan DPL telah terdaftar.');
        }

        $request->validate([
            'date'       => 'required|date',
            'activity'   => 'required|string|min:10',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

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
            'lecturer_status' => 'pending',
        ]);

        return redirect()->route('student.logbook.index')->with('success', 'Logbook kegiatan berhasil disimpan!');
    }

    public function edit($id)
    {
        $application = Application::where('user_id', Auth::id())->latest()->first();

        if (!$application || !$application->is_active_internship) {
            return redirect()->route('student.logbook.index')
                ->with('warning', 'Pengisian logbook hanya dapat dilakukan saat masa magang aktif dan DPL telah terdaftar.');
        }

        $logbook = Logbook::findOrFail($id);

        // Pastikan logbook milik penempatan user yang sedang login
        if ($logbook->placement?->application?->user_id !== Auth::id()) {
            abort(403, 'Akses tidak diizinkan.');
        }

        // Keamanan: Hanya status PENDING / REJECTED yang bisa diedit
        if (strtolower($logbook->status) === 'approved') {
            return redirect()->route('student.logbook.index')
                ->with('error', 'Logbook yang sudah disetujui tidak dapat diubah.');
        }

        return view('student.logbook.edit', compact('logbook', 'application'));
    }

    public function update(Request $request, $id)
    {
        $application = Application::where('user_id', Auth::id())->latest()->first();

        if (!$application || !$application->is_active_internship) {
            return redirect()->route('student.logbook.index')
                ->with('warning', 'Pengisian logbook hanya dapat dilakukan saat masa magang aktif dan DPL telah terdaftar.');
        }

        $request->validate([
            'date'       => 'required|date',
            'activity'   => 'required|string|min:10',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $logbook = Logbook::findOrFail($id);

        if ($logbook->placement?->application?->user_id !== Auth::id()) {
            abort(403, 'Akses tidak diizinkan.');
        }

        if (strtolower($logbook->status) === 'approved') {
            return redirect()->route('student.logbook.index')
                ->with('error', 'Logbook yang sudah disetujui tidak dapat diubah.');
        }

        if ($request->hasFile('attachment')) {
            if ($logbook->attachment && Storage::disk('public')->exists($logbook->attachment)) {
                Storage::disk('public')->delete($logbook->attachment);
            }
            $logbook->attachment = $request->file('attachment')->store('documents/logbooks', 'public');
        }

        $logbook->date = $request->date;
        $logbook->activity = $request->activity;
        
        if (strtolower($logbook->status) === 'rejected') {
            $logbook->status = 'pending';
        }
        if (strtolower($logbook->lecturer_status ?? '') === 'rejected') {
            $logbook->lecturer_status = 'pending';
        }

        $logbook->save();

        return redirect()->route('student.logbook.index')
            ->with('success', 'Logbook kegiatan berhasil diperbarui!');
    }
}
