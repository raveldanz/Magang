<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Logbook;
use App\Models\Placement;
<<<<<<< HEAD
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
=======
use App\Models\University;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
>>>>>>> main

class LogbookController extends Controller
{
    public function index()
    {
<<<<<<< HEAD
        $userId = Auth::id();

        // 1. Ambil pengajuan mahasiswa yang disetujui (akomodasi huruf kecil 'accepted' maupun kapital 'ACCEPTED')
        $application = Application::where('user_id', $userId)
            ->whereIn('status', ['accepted', 'ACCEPTED'])
=======
        $user = Auth::user();
        $userId = $user->id;

        // 1. Ambil pengajuan terakhir mahasiswa
        $application = Application::where('user_id', $userId)
>>>>>>> main
            ->latest()
            ->first();

        $placement = null;
        $logbooks = collect();

        if ($application) {
            // 2. Ambil data penempatan (placement)
            $placement = Placement::where('application_id', $application->id)
<<<<<<< HEAD
                ->with('pembimbing')
=======
                ->with(['pembimbing', 'mentor', 'academicAdvisor'])
>>>>>>> main
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

<<<<<<< HEAD
        // Tampilkan view tanpa me-redirect paksa agar alert peringatan/info tetap dapat terbaca
        return view('student.logbook.index', compact('application', 'placement', 'logbooks', 'stats'));
=======
        $requiresDpl = $this->isDplRequiredForStudent($user);

        return view('student.logbook.index', compact('application', 'placement', 'logbooks', 'stats', 'requiresDpl'));
    }

    public function create()
    {
        $user = Auth::user();
        $application = Application::where('user_id', $user->id)->latest()->first();
        $placement = $application ? Placement::where('application_id', $application->id)->first() : null;
        $requiresDpl = $this->isDplRequiredForStudent($user);

        if (!$application || !$application->is_active_internship || !$placement || ($requiresDpl && empty($placement->academic_advisor_id))) {
            return redirect()->route('student.logbook.index')
                ->with('warning', 'Pengisian logbook hanya dapat dilakukan saat masa magang aktif dan Dosen Pembimbing Lapangan (DPL) telah terdaftar.');
        }

        return view('student.logbook.create', compact('application'));
>>>>>>> main
    }

    public function store(Request $request)
    {
<<<<<<< HEAD
=======
        $user = Auth::user();
        $application = Application::where('user_id', $user->id)->latest()->first();
        $placement = $application ? Placement::where('application_id', $application->id)->first() : null;
        $requiresDpl = $this->isDplRequiredForStudent($user);

        if (!$application || !$application->is_active_internship || !$placement || ($requiresDpl && empty($placement->academic_advisor_id))) {
            return redirect()->route('student.logbook.index')
                ->with('warning', 'Pengisian logbook hanya dapat dilakukan saat masa magang aktif dan Dosen Pembimbing Lapangan (DPL) telah terdaftar.');
        }

>>>>>>> main
        $request->validate([
            'date'       => 'required|date',
            'activity'   => 'required|string|min:10',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

<<<<<<< HEAD
        $userId = Auth::id();

        // Cari application yang disetujui
        $application = Application::where('user_id', $userId)
            ->whereIn('status', ['accepted', 'ACCEPTED'])
            ->latest()
            ->firstOrFail();

        // Ambil placement
        $placement = Placement::where('application_id', $application->id)->firstOrFail();

=======
>>>>>>> main
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
<<<<<<< HEAD
=======
            'lecturer_status' => $requiresDpl ? 'pending' : 'approved',
            'lecturer_feedback' => $requiresDpl ? null : 'Dilewati (Kebijakan Penilaian 100% Instansi Dinas)',
            'lecturer_verified_at' => $requiresDpl ? null : now(),
>>>>>>> main
        ]);

        return redirect()->route('student.logbook.index')->with('success', 'Logbook kegiatan berhasil disimpan!');
    }

<<<<<<< HEAD
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


=======
    /**
     * Periksa apakah kampus mahasiswa mewajibkan DPL
     */
    protected function isDplRequiredForStudent($user): bool
    {
        $univ = null;
        if ($user->university_id) {
            $univ = University::find($user->university_id);
        } elseif ($user->university || $user->studentProfile?->universitas) {
            $name = $user->university ?? $user->studentProfile?->universitas;
            $univ = University::where('name', 'like', "%{$name}%")->orWhere('code', 'like', "%{$name}%")->first();
        }

        if ($univ) {
            if ($univ->evaluation_scheme === 'mentor_only') {
                return false;
            }
            return (bool) ($univ->require_dpl ?? true);
        }

        return true;
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
>>>>>>> main
}
