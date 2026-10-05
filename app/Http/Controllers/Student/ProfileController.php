<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\StudentProfile;
use App\Models\University;
use App\Services\PrivateDocumentStorage;
use App\Services\UniversityResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        $profile = $user->studentProfile ?? new StudentProfile;
        $currentUniversityId = $profile->university_id ?? $user->university_id;

        // Dropdown: kampus terverifikasi + kampus milik mahasiswa ini sendiri (meski masih menunggu verifikasi)
        $universities = University::query()
            ->where(function ($q) use ($currentUniversityId) {
                $q->where('is_verified', true);
                if ($currentUniversityId) {
                    $q->orWhere('id', $currentUniversityId);
                }
            })
            // Entri penampung "Perguruan Tinggi Lainnya" tidak ditampilkan: mahasiswa memakai opsi kampus baru
            ->where(fn ($q) => $q->whereNull('code')->orWhere('code', '!=', 'LAINNYA'))
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'acronym', 'slug', 'is_verified']);

        return view('student.profile.edit', compact('user', 'profile', 'universities', 'currentUniversityId'));
    }

    /**
     * Resolve kampus dari dropdown: ID kampus terdaftar, atau 'other' → buat kampus baru
     * berstatus "Menunggu Verifikasi" (is_verified = false) agar pendaftaran tidak buntu.
     */
    protected function resolveUniversity(Request $request): University
    {
        if ($request->input('university_id') === 'other') {
            $typed = (string) $request->input('custom_university_name');
            $resolver = app(UniversityResolver::class);

            // 1. Ternyata sama persis dengan nama/akronim/kode kampus terdaftar → pakai yang ada
            $exact = $resolver->findExact($typed);
            if ($exact) {
                session()->flash('university_auto_matched', $exact->name);

                return $exact;
            }

            // 2. Sangat mirip dengan kampus terverifikasi → minta konfirmasi dulu (cegah entri dobel)
            if (! $request->boolean('confirm_new_university')) {
                $similar = $resolver->suggest($typed, 3)
                    ->filter(fn ($row) => $row['score'] >= UniversityResolver::STRONG_MATCH_THRESHOLD);
                if ($similar->isNotEmpty()) {
                    session()->flash('university_suggestions', $similar->map(fn ($row) => [
                        'id' => $row['university']->id,
                        'name' => $row['university']->name,
                        'acronym' => $row['university']->acronym ?? $row['university']->code,
                    ])->values()->all());

                    throw ValidationException::withMessages([
                        'custom_university_name' => "Kampus yang mirip dengan \"{$typed}\" sudah terdaftar. Pilih kampus yang sesuai dari saran di bawah, atau centang konfirmasi bila kampus Anda memang berbeda.",
                    ]);
                }
            }

            $university = University::findOrCreateUnverified($typed);

            if ($university->wasRecentlyCreated) {
                AuditLog::record('UNIVERSITY_SELF_REGISTER', 'University', $university->id, [
                    'name' => $university->name,
                    'registered_by' => $request->user()?->email,
                ]);
            }

            return $university;
        }

        return University::findOrFail((int) $request->input('university_id'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        // 2. Tambahkan validasi untuk photo
        $request->validate([
            'name' => 'required|string|max:255',
            'nim' => 'required|string|max:50',
            'university_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    if ($value === 'other') {
                        return;
                    }
                    if (! ctype_digit((string) $value) || ! University::whereKey((int) $value)->exists()) {
                        $fail('Perguruan tinggi yang dipilih tidak valid.');
                    }
                },
            ],
            'custom_university_name' => 'nullable|required_if:university_id,other|string|min:3|max:255',
            'faculty' => 'nullable|string|max:255',
            'jurusan' => 'required|string|max:255',
            'semester' => 'nullable|string|max:20',
            'phone' => 'required|string|max:20',
            'alamat' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:30',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'university_id.required' => 'Silakan pilih perguruan tinggi Anda.',
            'custom_university_name.required_if' => 'Nama perguruan tinggi wajib diisi jika memilih "Perguruan Tinggi Lainnya".',
        ]);

        $university = DB::transaction(fn () => $this->resolveUniversity($request));
        $universityName = $university->name;
        $targetUnivId = $university->id;

        $user->update([
            'name' => $request->name,
            'university_id' => $university->id,
            'university' => $university->name,
        ]);

        $faculty = $request->input('faculty') ?? $request->input('fakultas');
        $major = $request->input('jurusan') ?? $request->input('major');
        $address = $request->input('alamat') ?? $request->input('address');

        // Ambil profil saat ini untuk cek foto lama
        $currentProfile = $user->studentProfile;
        $photoPath = $currentProfile?->photo;

        // 3. Logika Upload Foto Profil & Hapus Foto Lama jika diganti
        if ($request->hasFile('photo')) {
            // Foto disimpan di disk privat; ditampilkan lewat route student.photo (terotorisasi)
            $storage = app(PrivateDocumentStorage::class);
            $storage->delete($photoPath);
            $photoPath = $storage->store($request->file('photo'), PrivateDocumentStorage::PROFILE_PHOTO_DIR);
        }

        StudentProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'nim' => $request->nim,
                'universitas' => $universityName,
                'university_id' => $targetUnivId,
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

        $message = $university->is_verified
            ? (session('university_auto_matched')
                ? "Profil berhasil disimpan. Kampus yang Anda ketik ternyata sudah terdaftar sebagai '{$university->name}', jadi kami pilihkan otomatis."
                : 'Profil mahasiswa berhasil diperbarui dan disinkronkan!')
            : "Profil berhasil disimpan! Perguruan tinggi '{$university->name}' tercatat sebagai kampus baru dan sedang menunggu verifikasi Admin. Anda tetap dapat melanjutkan pengajuan magang.";

        return redirect()->back()->with('success', $message);
    }
}
