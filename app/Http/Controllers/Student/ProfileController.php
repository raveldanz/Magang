<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\StudentProfile;
use App\Models\University;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage; 
class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        $profile = $user->studentProfile ?? new StudentProfile();
        $universities = University::orderBy('name')->get();

        return view('student.profile.edit', compact('user', 'profile', 'universities'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        // 2. Tambahkan validasi untuk photo
        $request->validate([
            'name' => 'required|string|max:255',
            'nim' => 'required|string|max:50',
            'universitas' => 'required|string|max:255',
            'faculty' => 'nullable|string|max:255',
            'jurusan' => 'required|string|max:255',
            'semester' => 'nullable|string|max:20',
            'phone' => 'required|string|max:20',
            'alamat' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:30',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $universityName = trim($request->input('universitas') ?? $request->input('university_name'));
        $targetUnivId = null;

        if ($universityName) {
            $university = University::firstOrCreate(
                ['name' => $universityName],
                ['code' => strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $universityName), 0, 8))]
            );
            $targetUnivId = $university->id;

            $user->update([
                'name' => $request->name,
                'university_id' => $university->id,
                'university' => $university->name,
            ]);
        }

        $faculty = $request->input('faculty') ?? $request->input('fakultas');
        $major = $request->input('jurusan') ?? $request->input('major');
        $address = $request->input('alamat') ?? $request->input('address');

        // Ambil profil saat ini untuk cek foto lama
        $currentProfile = $user->studentProfile;
        $photoPath = $currentProfile?->photo;

        // 3. Logika Upload Foto Profil & Hapus Foto Lama jika diganti
        if ($request->hasFile('photo')) {
            if ($photoPath && Storage::disk('public')->exists($photoPath)) {
                Storage::disk('public')->delete($photoPath);
            }
            $photoPath = $request->file('photo')->store('profile-photos', 'public');
        }

        StudentProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'name' => $request->name,
                'nim' => $request->nim,
                'universitas' => $universityName,
                'university_id' => $targetUnivId ?? $user->university_id,
                'faculty' => $faculty,
                'fakultas' => $faculty,
                'jurusan' => $major,
                'major' => $major,
                'semester' => $request->semester,
                'phone' => $request->phone,
                'alamat' => $address,
                'address' => $address,
                'emergency_contact_name' => $request->emergency_contact_name,
                'emergency_contact_phone' => $request->emergency_contact_phone,
                'photo' => $photoPath, 
            ]
        );

        return redirect()->back()->with('success', 'Profil mahasiswa berhasil diperbarui dan disinkronkan!');
    }
}