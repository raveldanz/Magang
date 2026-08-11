<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\StudentProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function edit()
    {
        $profile = Auth::user()->studentProfile;
        return view('student.profile.edit', compact('profile'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'nim' => 'required|string|max:50',
            'universitas' => 'required|string|max:255',
            'jurusan' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'alamat' => 'nullable|string',
        ]);

        StudentProfile::updateOrCreate(
            ['user_id' => Auth::id()],
            $request->only(['nim', 'universitas', 'jurusan', 'phone', 'alamat'])
        );

        return redirect()->back()->with('success', 'Profil berhasil diperbarui!');
    }
}