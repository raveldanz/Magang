<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Placement;
use App\Models\University;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class DashboardController extends Controller
{
    /**
     * Tampilan Dashboard Mahasiswa dengan Data Terintegrasi
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'admin' || $user->role === 'super_admin') {
            return redirect()->route('admin.dashboard');
        }

        if ($user->role === 'mentor' || $user->role === 'pembimbing') {
            return redirect()->route('mentor.dashboard');
        }

        if ($user->role === 'dosen' || $user->role === 'academic_advisor') {
            return redirect()->route('lecturer.dashboard');
        }

        if ($user->role === 'universitas') {
            return redirect()->route('university.dashboard');
        }

        $profile = $user->studentProfile;

        // Hubungkan university_id jika belum terisi pada user
        if (!$user->university_id && ($profile?->universitas || $user->university)) {
            $univNameSearch = $profile->universitas ?? $user->university;
            $matchedUniv = University::where('name', 'like', "%{$univNameSearch}%")
                ->orWhere('code', 'like', "%{$univNameSearch}%")
                ->first();
            if ($matchedUniv) {
                $user->update([
                    'university_id' => $matchedUniv->id,
                    'university' => $matchedUniv->name,
                ]);
            }
        }

        // Ambil pengajuan magang terbaru mahasiswa
        $application = Application::with([
            'unit.agencyProfile', 
            'placement.mentor', 
            'placement.pembimbing',
            'placement.academicAdvisor.university', 
            'placement.evaluation', 
            'placement.finalreport'
        ])
        ->where('user_id', $user->id)
        ->latest()
        ->first();

        $university = $user->university_id ? University::find($user->university_id) : null;
        $univName = $university?->name ?? $user->university ?? $profile?->universitas;

        // Query dosen dari universitas yang sama
        $availableDosens = collect();
        if ($user->university_id) {
            $availableDosens = User::whereIn('role', ['dosen', 'academic_advisor'])
                ->where('university_id', $user->university_id)
                ->orderBy('name')
                ->get();
        } elseif ($univName) {
            $availableDosens = User::whereIn('role', ['dosen', 'academic_advisor'])
                ->where(function ($q) use ($univName) {
                    $q->where('university', 'like', "%{$univName}%");
                })
                ->orderBy('name')
                ->get();
        }

        $allUniversities = University::orderBy('name')->get();

        return view('dashboard', compact(
            'user', 
            'profile', 
            'application', 
            'availableDosens', 
            'university', 
            'univName', 
            'allUniversities'
        ));
    }

    /**
     * Mahasiswa memilih Dosen Pembimbing yang sudah terdaftar
     */
    public function selectAdvisor(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'academic_advisor_id' => 'required|exists:users,id',
        ]);

        $application = Application::where('user_id', $user->id)
            ->where('status', 'accepted')
            ->latest()
            ->firstOrFail();

        $advisor = User::whereIn('role', ['dosen', 'academic_advisor'])->findOrFail($request->academic_advisor_id);

        // Update row placement yang sudah ada (bukan membuat baris baru)
        $placement = Placement::updateOrCreate(
            ['application_id' => $application->id],
            ['academic_advisor_id' => $advisor->id]
        );

        // Hapus placement duplikat jika ada
        Placement::where('application_id', $application->id)
            ->where('id', '!=', $placement->id)
            ->delete();

        return redirect()->route('dashboard')->with('success', 'Dosen Pembimbing Kampus (' . $advisor->name . ') berhasil dipilih!');
    }

    /**
     * Mahasiswa mendaftarkan Dosen Pembimbing Baru dengan Antisipasi Duplikasi & Flash Kredensial
     */
    public function storeNewAdvisor(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'nidn' => 'nullable|string|max:50',
            'university_id' => 'nullable|exists:universities,id',
        ]);

        $application = Application::where('user_id', $user->id)
            ->where('status', 'accepted')
            ->latest()
            ->firstOrFail();

        $targetUnivId = $request->university_id ?? $user->university_id;
        $targetUniv = $targetUnivId ? University::find($targetUnivId) : null;
        $univName = $targetUniv?->name ?? $user->university ?? 'Perguruan Tinggi';

        $cleanEmail = strtolower(trim($request->email));
        $cleanName = trim($request->name);

        // 1. Cek duplikasi berdasarkan email (case-insensitive)
        $existingLecturer = User::whereIn('role', ['dosen', 'academic_advisor'])
            ->whereRaw('LOWER(email) = ?', [$cleanEmail])
            ->first();

        // 2. Jika tidak ditemukan lewat email, cek berdasarkan kesamaan nama pada universitas yang sama
        if (!$existingLecturer && $targetUnivId) {
            $existingLecturer = User::whereIn('role', ['dosen', 'academic_advisor'])
                ->where('university_id', $targetUnivId)
                ->where(function ($q) use ($cleanName) {
                    $q->whereRaw('LOWER(TRIM(name)) = ?', [strtolower($cleanName)])
                      ->orWhereRaw('LOWER(TRIM(name)) LIKE ?', [strtolower($cleanName) . '%']);
                })
                ->first();
        }

        // Kasus 1: Dosen sudah terdaftar di database
        if ($existingLecturer) {
            // Update relasi universitas jika sebelumnya belum terisi
            if ($targetUnivId && !$existingLecturer->university_id) {
                $existingLecturer->update([
                    'university_id' => $targetUnivId,
                    'university' => $univName,
                ]);
            }

            $placement = Placement::updateOrCreate(
                ['application_id' => $application->id],
                ['academic_advisor_id' => $existingLecturer->id]
            );

            Placement::where('application_id', $application->id)
                ->where('id', '!=', $placement->id)
                ->delete();

            return redirect()->route('dashboard')->with('success', "Dosen Pembimbing {$existingLecturer->name} sudah terdaftar sebelumnya di sistem dan berhasil dipilih sebagai Dosen Pembimbing Anda.");
        }

        // Kasus 2: Dosen Baru (Belum Terdaftar)
        $dosenName = $cleanName;
        if ($request->filled('nidn')) {
            $dosenName .= ' (NIDN: ' . trim($request->nidn) . ')';
        }

        $newDosen = User::create([
            'name' => $dosenName,
            'email' => $cleanEmail,
            'password' => Hash::make('password'),
            'role' => 'dosen',
            'university_id' => $targetUnivId,
            'university' => $univName,
            'email_verified_at' => now(),
        ]);

        $placement = Placement::updateOrCreate(
            ['application_id' => $application->id],
            ['academic_advisor_id' => $newDosen->id]
        );

        Placement::where('application_id', $application->id)
            ->where('id', '!=', $placement->id)
            ->delete();

        // Kirimkan session flash detail kredensial untuk ditampilkan di modal informasi mahasiswa
        session()->flash('new_advisor_credential', [
            'name' => $newDosen->name,
            'email' => $newDosen->email,
            'password' => 'password',
            'login_url' => url('/login'),
            'univ_name' => $univName,
        ]);

        return redirect()->route('dashboard')->with('success', "Akun Dosen Pembimbing Baru ({$newDosen->name}) berhasil didaftarkan dan dipilih sebagai DPL!");
    }
}
