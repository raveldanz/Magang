<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();

        return view('profile.edit', [
            'user' => $user,
            'hasInternshipHistory' => $user?->hasInternshipHistory() ?? false,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Nomor telepon/WhatsApp staf untuk Info Kontak chat.
     * Mahasiswa memakai nomor di Profil Mahasiswa (student_profiles.phone).
     */
    public function updatePhone(Request $request): RedirectResponse
    {
        if ($request->user()->role === 'mahasiswa') {
            return Redirect::route('student.profile.edit')->with('warning', 'Nomor telepon mahasiswa diubah melalui halaman Profil Saya.');
        }

        $data = $request->validate([
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+()\-\s]{8,30}$/'],
        ], [
            'phone.regex' => 'Nomor telepon hanya boleh berisi angka, spasi, tanda +, -, atau kurung (8–30 karakter).',
            'phone.max' => 'Nomor telepon maksimal :max karakter.',
        ]);

        $request->user()->forceFill(['phone' => trim((string) ($data['phone'] ?? '')) ?: null])->save();

        return Redirect::route('profile.edit')->with('success', 'Nomor kontak berhasil disimpan.');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Blokir jika yang mencoba menghapus akun adalah Admin
        if (in_array($user->role, ['admin', 'super_admin'])) {
            return back()->with('error', 'Akun Administrator tidak dapat dihapus secara mandiri demi keamanan sistem.');
        }

        if ($user->hasInternshipHistory()) {
            return back()->with('error', 'Akun ini menyimpan riwayat magang yang wajib diarsipkan sehingga tidak dapat dihapus.');
        }

    $request->validateWithBag('userDeletion', [
        'password' => ['required', 'current_password'],
    ]);

    Auth::logout();

    $user->delete();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/')->with('success', 'Akun Anda berhasil dihapus.');
}
}
