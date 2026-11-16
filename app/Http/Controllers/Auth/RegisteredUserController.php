<?php

namespace App\Http\Controllers\Auth;

use App\Enums\AccountStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\University;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
     * Daftar akun mandiri sebagai Peserta Magang atau Pembimbing (dosen/guru).
     *
     * Pembimbing tidak perlu didaftarkan admin, tetapi akunnya berstatus Menunggu Persetujuan sampai
     * Admin Kampus/Sekolah atau Super Admin menyetujuinya (AdvisorApprovalService), agar tidak ada
     * akun pembimbing palsu yang dipilih mahasiswa untuk menilai dirinya sendiri.
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        $isAdvisor = $request->registersAdvisor();

        $user = DB::transaction(function () use ($request, $isAdvisor) {
            $data = [
                'name' => $request->validated('name'),
                'email' => $request->validated('email'),
                'password' => Hash::make($request->validated('password')),
                'role' => $isAdvisor ? 'dosen' : 'mahasiswa',
            ];

            if ($isAdvisor) {
                // Nama/akronim yang dikenal (mis. "unesa") dipakai ulang; nama baru masuk daftar verifikasi Super Admin.
                $institution = University::findOrCreateUnverified($request->validated('university'));
                $data['university_id'] = $institution->id;
                $data['university'] = $institution->name;
                $data['status'] = AccountStatus::PENDING->value;
            }

            return User::create($data);
        });

        event(new Registered($user));

        Auth::login($user);
        $request->session()->regenerate();

        if ($isAdvisor) {
            return redirect()->route('dashboard')->with('success', 'Akun Pembimbing berhasil dibuat. Akun Anda sedang menunggu persetujuan Admin Kampus/Sekolah. Setelah disetujui, peserta magang dapat memilih Anda sebagai pembimbing.');
        }

        return redirect()->route('student.profile.edit')->with('success', 'Akun berhasil dibuat! Silakan lengkapi profil Anda.');
    }
}
