<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\StudentProfile;
use App\Models\University;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        $profile = $user->studentProfile;
        $universities = University::orderBy('name')->get();

        return view('student.profile.edit', compact('user', 'profile', 'universities'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'nim' => 'required|string|max:50',
            'universitas' => 'required|string|max:255',
            'jurusan' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'alamat' => 'nullable|string',
        ]);

        $universityName = trim($request->input('universitas') ?? $request->input('university_name'));

        if ($universityName) {
            $university = University::firstOrCreate(
                ['name' => $universityName],
                ['code' => strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $universityName), 0, 8))]
            );

            $user->update([
                'university_id' => $university->id,
                'university' => $university->name,
            ]);
        }

        StudentProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'nim' => $request->nim,
                'universitas' => $universityName,
                'jurusan' => $request->jurusan,
                'phone' => $request->phone,
                'alamat' => $request->alamat,
            ]
        );

        return redirect()->back()->with('success', 'Profil berhasil diperbarui dan terhubung dengan perguruan tinggi!');
    }
}