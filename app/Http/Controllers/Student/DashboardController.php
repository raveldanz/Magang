<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Placement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        $profile = $user->studentProfile;

        // Ambil pengajuan magang terbaru mahasiswa
        $application = Application::with([
            'unit.agencyProfile', 
            'placement.mentor', 
            'placement.pembimbing',
            'placement.academicAdvisor', 
            'placement.evaluation', 
            'placement.finalreport'
        ])
        ->where('user_id', $user->id)
        ->latest()
        ->first();

        // Cari daftar dosen pembimbing yang sesuai dengan perguruan tinggi mahasiswa
        $univName = $user->university ?? $profile?->universitas;
        $availableDosens = collect();

        if ($univName) {
            $availableDosens = User::whereIn('role', ['dosen', 'academic_advisor'])
                ->where(function ($q) use ($univName) {
                    $q->where('university', $univName)
                      ->orWhere('university', 'like', "%{$univName}%")
                      ->orWhere('university', 'like', '%' . explode(' ', $univName)[0] . '%');
                })
                ->get();
            
            // Fallback jika tidak ada dosen dengan nama univ yang exact sama, ambil semua dosen
            if ($availableDosens->isEmpty()) {
                $availableDosens = User::whereIn('role', ['dosen', 'academic_advisor'])->get();
            }
        } else {
            $availableDosens = User::whereIn('role', ['dosen', 'academic_advisor'])->get();
        }

        return view('dashboard', compact('user', 'profile', 'application', 'availableDosens', 'univName'));
    }

    /**
     * Mahasiswa memilih / menentukan Dosen Pembimbing Lapangan (DPL Kampus)
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

        return redirect()->route('dashboard')->with('success', 'Dosen Pembimbing Kampus (' . $advisor->name . ') berhasil disimpan!');
    }
}
