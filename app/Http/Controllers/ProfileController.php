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
