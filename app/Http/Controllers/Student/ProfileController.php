<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ApplicationDocument;
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

        // Dropdown memakai pencarian async (institutions.search); halaman hanya memuat pilihan saat ini
        // (termasuk kampus milik mahasiswa yang masih menunggu verifikasi).
        $universities = University::query()
            ->where('id', $currentUniversityId ?: 0)
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'code', 'acronym', 'slug', 'is_verified', 'institution_type'])
            // Keterangan kecil di daftar pilihan agar sekolah dan kampus mudah dibedakan
            ->each(fn (University $u) => $u->setAttribute('meta', $u->institutionTypeLabel()));

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

            // 0. Beberapa institusi bernama persis sama: jangan dipasangkan sembarang
            $sameName = $resolver->sameNamed($typed);
            if ($sameName->count() > 1) {
                if ($request->boolean('confirm_new_university')) {
                    $university = University::create([
                        'name' => University::normalizeName($typed), 'is_verified' => false,
                        'status' => 'active', 'institution_type' => $this->educationLevel($request),
                    ]);
                    $this->recordSelfRegistration($request, $university);

                    return $university;
                }
                session()->flash('university_suggestions', $sameName->take(30)->map(fn (University $u) => [
                    'id' => $u->id,
                    'name' => implode(' · ', array_filter([$u->name, $u->cityLabel()])),
                    'acronym' => $u->acronym ?? $u->code,
                ])->values()->all());

                throw ValidationException::withMessages([
                    'custom_university_name' => "Ada beberapa sekolah/kampus bernama \"{$typed}\". Pilih yang sesuai dari saran di bawah.",
                ]);
            }

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

            $university = University::findOrCreateUnverified($typed, $this->educationLevel($request));

            if ($university->wasRecentlyCreated) {
                $this->recordSelfRegistration($request, $university);
            }

            return $university;
        }

        $university = University::findOrFail((int) $request->input('university_id'));

        // Jenjang yang dipilih harus cocok dengan jenis institusi (sekolah vs perguruan tinggi)
        if ($university->isSchool() !== ($this->educationLevel($request) === University::TYPE_SCHOOL)) {
            throw ValidationException::withMessages([
                'university_id' => $university->isSchool()
                    ? "\"{$university->name}\" terdaftar sebagai sekolah. Ubah jenjang menjadi Sekolah, atau pilih kampus/sekolah Anda."
                    : "\"{$university->name}\" terdaftar sebagai perguruan tinggi. Ubah jenjang menjadi Perguruan Tinggi, atau pilih sekolah Anda.",
            ]);
        }

        return $university;
    }

    private function recordSelfRegistration(Request $request, University $university): void
    {
        AuditLog::record('UNIVERSITY_SELF_REGISTER', 'University', $university->id, [
            'name' => $university->name,
            'registered_by' => $request->user()?->email,
        ]);
    }

    /** Jenjang peserta dari form profil: perguruan_tinggi (bawaan) atau sekolah. */
    protected function educationLevel(Request $request): string
    {
        return $request->input('education_level') === University::TYPE_SCHOOL
            ? University::TYPE_SCHOOL
            : University::TYPE_COLLEGE;
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $isSchool = $this->educationLevel($request) === University::TYPE_SCHOOL;

        // Normalisasi hanya teks yang diketik peserta. Nilai pilihan dan pengenal dipertahankan.
        foreach (['name', 'faculty', 'fakultas', 'jurusan', 'major', 'alamat', 'address', 'emergency_contact_name'] as $field) {
            $value = $request->input($field);
            if (is_string($value)) {
                $request->merge([$field => mb_strtoupper($value)]);
            }
        }

        $request->validate([
            'education_level' => 'nullable|in:perguruan_tinggi,sekolah',
            'name' => 'required|string|max:255',
            'nim' => ['required', 'string', 'max:30', 'regex:/^[A-Za-z0-9][A-Za-z0-9.\-\/ ]*$/'],
            'university_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    if ($value === 'other') {
                        return;
                    }
                    if (! ctype_digit((string) $value) || ! University::whereKey((int) $value)->exists()) {
                        $fail('Kampus/sekolah yang dipilih tidak valid.');
                    }
                },
            ],
            'custom_university_name' => 'nullable|required_if:university_id,other|string|min:3|max:255',
            'faculty' => 'nullable|string|max:255',
            'jurusan' => 'required|string|max:255',
            // Mahasiswa: semester 1-16; siswa SMK/SMA: kelas 10-13 (disimpan di kolom yang sama)
            'semester' => $isSchool ? 'nullable|integer|between:10,13' : 'nullable|integer|between:1,16',
            'phone' => ['required', 'string', 'max:20', 'regex:/^\+?[0-9][0-9 \-]{7,18}$/'],
            'alamat' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:30',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'cv' => 'nullable|mimes:pdf|max:2048',
            'id_card' => 'nullable|mimes:pdf,jpg,jpeg,png|max:2048',
        ], [
            'university_id.required' => $isSchool ? 'Silakan pilih sekolah Anda.' : 'Silakan pilih kampus/sekolah Anda.',
            'nim.required' => $isSchool ? 'NIS wajib diisi.' : 'NIM wajib diisi.',
            'nim.regex' => ($isSchool ? 'NIS' : 'NIM').' hanya boleh berisi huruf, angka, titik, garis miring, atau tanda hubung.',
            'phone.regex' => 'Nomor telepon harus berupa 8 sampai 19 angka, boleh diawali tanda +.',
            'semester.integer' => $isSchool ? 'Kelas harus berupa angka (10, 11, 12, atau 13).' : 'Semester harus berupa angka.',
            'semester.between' => $isSchool ? 'Kelas harus antara 10 sampai 13.' : 'Semester harus antara 1 sampai 16.',
            'custom_university_name.required_if' => 'Nama kampus/sekolah wajib diisi jika memilih "Lainnya".',
            'cv.mimes' => 'File CV harus berformat PDF.',
            'cv.max' => 'Ukuran file CV maksimal 2MB.',
            'id_card.mimes' => 'File KTP / KTM / Kartu Pelajar harus berformat PDF, JPG, JPEG, atau PNG.',
            'id_card.max' => 'Ukuran file KTP / KTM / Kartu Pelajar maksimal 2MB.',
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

        // Ambil profil saat ini untuk cek berkas lama
        $currentProfile = $user->studentProfile;
        $photoPath = $currentProfile?->photo;
        $cvPath = $currentProfile?->cv_path;
        $idCardPath = $currentProfile?->id_card_path;

        $storage = app(PrivateDocumentStorage::class);
        // Berkas lama baru dihapus di akhir, setelah dipastikan tidak ada data lain yang masih merujuknya.
        $replacedPaths = [];

        // Upload Foto Profil
        if ($request->hasFile('photo')) {
            $replacedPaths[] = $photoPath;
            $photoPath = $storage->store($request->file('photo'), PrivateDocumentStorage::PROFILE_PHOTO_DIR);
        }

        // Upload Dokumen Personal Mahasiswa
        if ($request->hasFile('cv')) {
            $replacedPaths[] = $cvPath;
            $cvPath = $storage->store($request->file('cv'), 'documents/student-profiles');
        }

        if ($request->hasFile('id_card')) {
            $replacedPaths[] = $idCardPath;
            $idCardPath = $storage->store($request->file('id_card'), 'documents/student-profiles');
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
                'education_level' => $isSchool ? University::TYPE_SCHOOL : University::TYPE_COLLEGE,
                'phone' => $request->phone,
                'alamat' => $address,
                'address' => $address,
                'emergency_contact_name' => $request->emergency_contact_name,
                'emergency_contact_phone' => $request->emergency_contact_phone,
                'photo' => $photoPath,
                'cv_path' => $cvPath,
                'id_card_path' => $idCardPath,
            ]
        );

        // Sinkronkan path berkas personal terbaru ke pengajuan yang sedang berjalan atau menunggu perbaikan
        if ($request->hasFile('cv') || $request->hasFile('id_card')) {
            $syncableApps = Application::where('user_id', $user->id)
                ->whereIn('status', ['draft', 'pending', 'waiting_approval', 'rejected'])
                ->get();

            foreach ($syncableApps as $app) {
                $app->update([
                    'cv_path' => $cvPath,
                    'id_card_path' => $idCardPath,
                ]);

                if ($cvPath) {
                    ApplicationDocument::updateOrCreate(
                        ['application_id' => $app->id, 'document_type' => 'CV'],
                        ['file_path' => $cvPath]
                    );
                }
                if ($idCardPath) {
                    ApplicationDocument::updateOrCreate(
                        ['application_id' => $app->id, 'document_type' => 'KTM / Kartu Identitas'],
                        ['file_path' => $idCardPath]
                    );
                }
            }
        }

        $this->deleteUnreferencedFiles($storage, $replacedPaths);

        $message = $university->is_verified
            ? (session('university_auto_matched')
                ? "Profil berhasil disimpan. Kampus yang Anda ketik ternyata sudah terdaftar sebagai '{$university->name}', jadi kami pilihkan otomatis."
                : 'Profil peserta magang berhasil diperbarui dan disinkronkan!')
            : "Profil berhasil disimpan! {$university->institutionTypeLabel()} '{$university->name}' tercatat sebagai institusi baru dan sedang menunggu verifikasi Admin. Anda tetap dapat melanjutkan pengajuan magang.";

        return redirect()->back()->with('success', $message);
    }

    /**
     * Hapus berkas lama hanya bila tidak ada lagi yang merujuknya. Pengajuan yang sudah
     * diterima/aktif/selesai tetap menunjuk berkas saat dikirim, jadi berkas itu tidak boleh
     * ikut terhapus ketika mahasiswa mengganti dokumen di profil.
     *
     * @param  array<int, string|null>  $paths
     */
    private function deleteUnreferencedFiles(PrivateDocumentStorage $storage, array $paths): void
    {
        foreach (array_unique(array_filter($paths)) as $path) {
            $stillUsed = Application::where('cv_path', $path)
                ->orWhere('transcript_path', $path)
                ->orWhere('id_card_path', $path)
                ->exists()
                || ApplicationDocument::where('file_path', $path)->exists()
                || StudentProfile::where('photo', $path)
                    ->orWhere('cv_path', $path)
                    ->orWhere('transcript_path', $path)
                    ->orWhere('id_card_path', $path)
                    ->exists();

            if (! $stillUsed) {
                $storage->delete($path);
            }
        }
    }
}
