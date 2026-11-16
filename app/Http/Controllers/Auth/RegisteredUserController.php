<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\University;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'register_as' => ['nullable', 'in:student,advisor'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'university' => ['required_if:register_as,advisor', 'nullable', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'university.required_if' => 'Nama Perguruan Tinggi atau Sekolah asal wajib diisi untuk akun Pembimbing.',
        ]);

        $isAdvisor = $request->input('register_as') === 'advisor';
        $role = $isAdvisor ? 'dosen' : 'mahasiswa';

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $role,
        ];

        // Jika mendaftar sebagai Dosen / Guru Pembimbing, simpan asal Kampus / Sekolahnya
        if ($isAdvisor && $request->filled('university')) {
            $univName = trim($request->university);

            if (Schema::hasColumn('users', 'university')) {
                $userData['university'] = $univName;
            }

            if (class_exists(University::class)) {
                $univRecord = University::firstOrCreate(['name' => $univName]);
                if (Schema::hasColumn('users', 'university_id')) {
                    $userData['university_id'] = $univRecord->id;
                }
            }
        }

        $user = User::create($userData);

        event(new Registered($user));

        Auth::login($user);

        // Jika Dosen / Guru Pembimbing, arahkan langsung ke Dashboard Pembimbing
        if ($isAdvisor) {
            return redirect()->route('dashboard')->with('success', 'Akun Pembimbing berhasil dibuat! Selamat datang di Portal Magang Dispusip.');
        }

        // Jika Mahasiswa / Siswa Peserta Magang, arahkan ke pengisian biodata profil
        return redirect()->route('student.profile.edit')->with('success', 'Akun berhasil dibuat! Silakan lengkapi profil Anda.');
    }
}