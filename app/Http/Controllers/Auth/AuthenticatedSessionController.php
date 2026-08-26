<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();

<<<<<<< HEAD
        if ($user->role === 'admin') {
            return redirect()->route('admin.applications.index');
=======
        if ($user->role === 'admin' || $user->role === 'super_admin') {
            return redirect()->route('admin.dashboard');
>>>>>>> main
        }

        if ($user->role === 'mentor' || $user->role === 'pembimbing') {
            return redirect()->route('mentor.dashboard');
        }

        if ($user->role === 'dosen' || $user->role === 'academic_advisor') {
            return redirect()->route('lecturer.dashboard');
        }

<<<<<<< HEAD
        if ($user->role === 'mahasiswa') {
            if (!$user->studentProfile) {
                return redirect()->route('student.profile.edit')->with('warning', 'Silakan lengkapi profil Anda terlebih dahulu');
            }
            return redirect()->route('student.application.create');
=======
        if ($user->role === 'universitas') {
            return redirect()->route('university.dashboard');
        }

        if ($user->role === 'mahasiswa') {
            return redirect()->route('dashboard');
>>>>>>> main
        }

        return redirect()->route('dashboard');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
