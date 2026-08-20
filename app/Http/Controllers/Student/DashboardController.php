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

        // Redirect jika role bukan mahasiswa
        if ($user->role === 'admin') {
            return redirect()->route('admin.applications.index');
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

        $placement = Placement::firstOrCreate(
            ['application_id' => $application->id]
        );

        $placement->update([
            'academic_advisor_id' => $advisor->id,
        ]);

        return redirect()->route('dashboard')->with('success', 'Dosen Pembimbing Kampus (' . $advisor->name . ') berhasil dipilih!');
    }

    /**
     * Mahasiswa mendaftarkan Dosen Pembimbing Baru (Nama, Email, NIDN)
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

        // Format nama dengan NIDN jika diinputkan
        $dosenName = $request->name;
        if ($request->filled('nidn')) {
            $dosenName .= ' (NIDN: ' . $request->nidn . ')';
        }

        // Cari atau buat akun dosen baru
        $dosen = User::where('email', strtolower($request->email))->first();

        if (!$dosen) {
            $dosen = User::create([
                'name' => $dosenName,
                'email' => strtolower($request->email),
                'password' => Hash::make('password'),
                'role' => 'dosen',
                'university_id' => $targetUnivId,
                'university' => $univName,
            ]);
        } else {
            // Update univ jika sebelumnya belum terikat
            if ($targetUnivId && !$dosen->university_id) {
                $dosen->update([
                    'university_id' => $targetUnivId,
                    'university' => $univName,
                ]);
            }
        }

        $placement = Placement::firstOrCreate(
            ['application_id' => $application->id]
        );

        $placement->update([
            'academic_advisor_id' => $dosen->id,
        ]);

        return redirect()->route('dashboard')->with('success', 'Dosen Pembimbing Baru (' . $dosen->name . ') berhasil didaftarkan dan dipilih sebagai DPL!');
    }
}
