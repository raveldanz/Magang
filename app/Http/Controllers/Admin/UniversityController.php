<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\AuditLog;
use App\Models\Placement;
use App\Models\StudentProfile;
use App\Models\SystemFeedback;
use App\Models\University;
use App\Models\User;
use App\Services\UniversityHubService;
use App\Services\UniversityResolver;
use App\Services\Admin\UniversityManagementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class UniversityController extends Controller
{
    /**
     * Master Perguruan Tinggi / Universitas
     */
    public function index(Request $request)
    {
        $query = University::with(['universityAdmin'])
            ->withCount(['users', 'dosens', 'students'])
            ->withExists([
                'users as has_admin_account' => function ($q) {
                    $q->where('role', 'universitas');
                },
            ])
            ->orderBy('is_verified', 'asc') // kampus "Menunggu Verifikasi" tampil paling atas
            ->orderBy('has_admin_account', 'asc')
            ->orderBy('updated_at', 'desc');

        if ($request->input('verification') === 'pending') {
            $query->where('is_verified', false);
        } elseif ($request->input('verification') === 'verified') {
            $query->where('is_verified', true);
        }

        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $like = \DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
            $query->where(function ($q) use ($search, $like) {
                $q->where('name', $like, "%{$search}%")
                    ->orWhere('code', $like, "%{$search}%")
                    ->orWhere('email', $like, "%{$search}%")
                    ->orWhere('pic_name', $like, "%{$search}%");
            });
        }

        $universities = $query->paginate(12)->withQueryString();

        // Count un-provisioned university accounts
        $unregisteredCount = University::doesntHave('universityAdmin')->count();

        // Kampus baru hasil input mandiri mahasiswa yang belum divalidasi admin
        $pendingVerificationCount = University::pendingVerification()->count();

        // Saran "mungkin sama dengan" untuk kampus belum terverifikasi di halaman ini (bantu admin menggabungkan)
        $resolver = app(UniversityResolver::class);
        $mergeSuggestions = [];
        $verifiedOptions = collect();
        if ($universities->getCollection()->contains(fn ($u) => ! ($u->is_verified ?? true))) {
            $verifiedOptions = University::verified()->orderBy('name')->get(['id', 'name', 'acronym', 'code']);
            foreach ($universities as $u) {
                if (! ($u->is_verified ?? true)) {
                    $mergeSuggestions[$u->id] = $resolver->suggest($u->name, 3)->pluck('university.id')->all();
                }
            }
        }

        // Executive Macro Stats
        $totalUniversities = University::count();
        $totalStudents = User::where('role', 'mahasiswa')
            ->where(function ($q) {
                $q->whereNotNull('university_id')
                    ->orWhereNotNull('university');
            })->count();
        $totalDosens = User::whereIn('role', ['dosen', 'academic_advisor'])->count();
        // "Sedang magang di dinas" = status active (selaras dengan metrik Aktif Magang di detail kampus)
        $totalActiveInterns = Application::where('status', 'active')->count();

        $macroStats = [
            'total_universities' => $totalUniversities,
            'total_students' => $totalStudents,
            'total_dosens' => $totalDosens,
            'total_active_interns' => $totalActiveInterns,
        ];

        $user = Auth::user();
        $isSuperAdmin = ($user?->isSuperAdmin() ?? false);

        return view('admin.universities.index', compact('universities', 'unregisteredCount', 'pendingVerificationCount', 'isSuperAdmin', 'macroStats', 'mergeSuggestions', 'verifiedOptions'));
    }

    /**
     * Gabungkan kampus dobel (biasanya input mandiri mahasiswa) ke kampus yang benar.
     * Seluruh akun, profil mahasiswa, tiket, dan kanal chat dipindahkan ke kampus tujuan,
     * lalu entri dobel dihapus.
     */
    public function merge(Request $request, $id, UniversityManagementService $service)
    {
        $this->ensureSuperAdmin('Hanya Super Administrator yang dapat menggabungkan data perguruan tinggi.');
        $source = University::findOrFail($id);

        $request->validate([
            'target_university_id' => 'required|integer|exists:universities,id',
        ], [
            'target_university_id.required' => 'Pilih kampus tujuan penggabungan.',
        ]);

        $target = University::findOrFail((int) $request->target_university_id);
        
        try {
            $moved = $service->mergeUniversities($source, $target);
            return redirect()->route('admin.universities.index')
                ->with('success', "Kampus '{$source->name}' berhasil digabungkan ke '{$target->name}' ({$moved['users']} akun dipindahkan).");
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Validasi kampus yang didaftarkan mandiri oleh mahasiswa (is_verified = false → true).
     */
    public function verify(Request $request, $id, UniversityManagementService $service)
    {
        $this->ensureSuperAdmin('Hanya Super Administrator yang dapat memverifikasi data perguruan tinggi.');
        $univ = University::findOrFail($id);

        if (!$service->verifyUniversity($univ)) {
            return redirect()->back()->with('success', "Perguruan tinggi '{$univ->name}' sudah terverifikasi.");
        }

        return redirect()->back()->with('success', "Perguruan tinggi '{$univ->name}' berhasil diverifikasi. Lengkapi kode kampus & profilnya melalui menu Edit bila diperlukan.");
    }

    public function create()
    {
        $this->ensureSuperAdmin('Hanya Super Administrator yang dapat mengubah data perguruan tinggi, akun kampus, dan dosen pembimbing.');

        return view('admin.universities.create');
    }

    public function store(Request $request)
    {
        $this->ensureSuperAdmin('Hanya Super Administrator yang dapat mengubah data perguruan tinggi, akun kampus, dan dosen pembimbing.');
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:universities,code',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'pic_name' => 'nullable|string|max:255',
            'pic_nip' => 'nullable|string|max:50',
            'pic_position' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
        ]);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $cleanCode = strtolower(preg_replace('/[^A-Za-z0-9]/', '', $request->code ?? 'univ'));
            $filename = $cleanCode.'_'.time().'.'.$file->getClientOriginalExtension();
            $targetDir = public_path('images/logos');
            if (! File::exists($targetDir)) {
                File::makeDirectory($targetDir, 0755, true);
            }
            $file->move($targetDir, $filename);
            $logoPath = 'images/logos/'.$filename;
        }

        $univ = University::create([
            'name' => $request->name,
            'code' => strtoupper(trim($request->code)),
            'email' => $request->email ? strtolower(trim($request->email)) : null,
            'phone' => $request->phone,
            'address' => $request->address,
            'pic_name' => $request->pic_name,
            'pic_nip' => $request->pic_nip,
            'pic_position' => $request->pic_position,
            'logo' => $logoPath,
        ]);

        AuditLog::record('UNIVERSITY_CREATE', 'University', $univ->id, [
            'name' => $univ->name,
            'code' => $univ->code,
        ]);

        return redirect()->route('admin.universities.index')
            ->with('success', "Universitas '{$univ->name}' ({$univ->code}) berhasil didaftarkan!");
    }

    public function edit($id)
    {
        $this->ensureSuperAdmin('Hanya Super Administrator yang dapat mengubah data perguruan tinggi, akun kampus, dan dosen pembimbing.');
        $university = University::findOrFail($id);

        return view('admin.universities.edit', compact('university'));
    }

    public function update(Request $request, $id)
    {
        $this->ensureSuperAdmin('Hanya Super Administrator yang dapat mengubah data perguruan tinggi, akun kampus, dan dosen pembimbing.');
        $univ = University::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:universities,code,'.$univ->id,
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'pic_name' => 'nullable|string|max:255',
            'pic_nip' => 'nullable|string|max:50',
            'pic_position' => 'nullable|string|max:255',
            'evaluation_scheme' => 'nullable|in:dual_evaluation,mentor_only',
            'weight_mentor' => 'nullable|integer|min:0|max:100',
            'weight_lecturer' => 'nullable|integer|min:0|max:100',
            'require_dpl' => 'nullable',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
        ]);

        $evaluationScheme = $request->input('evaluation_scheme', $univ->evaluation_scheme ?? 'dual_evaluation');
        if ($evaluationScheme === 'mentor_only') {
            $weightMentor = 100;
            $weightLecturer = 0;
            $requireDpl = false;
        } else {
            $weightMentor = (int) $request->input('weight_mentor', $univ->weight_mentor ?? 40);
            $weightLecturer = (int) $request->input('weight_lecturer', $univ->weight_lecturer ?? 60);

            if (($weightMentor + $weightLecturer) !== 100) {
                return back()->withInput()->with('error', 'Total bobot penilaian Mentor Dinas ('.$weightMentor.'%) dan DPL Kampus ('.$weightLecturer.'%) harus berjumlah tepat 100%.');
            }

            $requireDpl = $request->boolean('require_dpl', true);
        }

        $data = [
            'name' => $request->name,
            'code' => strtoupper(trim($request->code)),
            'email' => $request->email ? strtolower(trim($request->email)) : null,
            'phone' => $request->phone,
            'address' => $request->address,
            'pic_name' => $request->pic_name,
            'pic_nip' => $request->pic_nip,
            'pic_position' => $request->pic_position,
            'evaluation_scheme' => $evaluationScheme,
            'weight_mentor' => $weightMentor,
            'weight_lecturer' => $weightLecturer,
            'require_dpl' => $requireDpl,
        ];

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $cleanCode = strtolower(preg_replace('/[^A-Za-z0-9]/', '', $request->code ?? 'univ'));
            $filename = $cleanCode.'_'.time().'.'.$file->getClientOriginalExtension();
            $targetDir = public_path('images/logos');
            if (! File::exists($targetDir)) {
                File::makeDirectory($targetDir, 0755, true);
            }
            $file->move($targetDir, $filename);
            $data['logo'] = 'images/logos/'.$filename;
        }

        $univ->update($data);

        AuditLog::record('UNIVERSITY_UPDATE', 'University', $univ->id, [
            'name' => $univ->name,
            'code' => $univ->code,
        ]);

        return redirect()->route('admin.universities.index')
            ->with('success', "Data Universitas '{$univ->name}' berhasil diperbarui!");
    }

    /**
     * Buat Akun PIC / Admin Kampus untuk Universitas tertentu
     */
    public function createAccount(Request $request, $id)
    {
        $this->ensureSuperAdmin('Hanya Super Administrator yang dapat mengubah data perguruan tinggi, akun kampus, dan dosen pembimbing.');
        $univ = University::findOrFail($id);

        $existingAccount = User::where('role', 'universitas')
            ->where('university_id', $univ->id)
            ->first();

        if ($existingAccount) {
            return redirect()->back()->with('error', "Universitas '{$univ->name}' sudah memiliki akun Admin Kampus aktif ({$existingAccount->email}).");
        }

        $cleanCode = strtolower(preg_replace('/[^A-Za-z0-9]/', '', $univ->code ?: 'univ'));
        $defaultEmail = $univ->email ?: ($cleanCode.'@'.$cleanCode.'.ac.id');

        $email = $defaultEmail;
        $counter = 1;
        while (User::where('email', $email)->exists()) {
            $email = $cleanCode.$counter.'@magang.surabaya.go.id';
            $counter++;
        }

        $password = 'password';

        $user = User::create([
            'name' => 'Admin Portal '.$univ->name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'universitas',
            'university_id' => $univ->id,
            'university' => $univ->name,
            'email_verified_at' => now(),
        ]);

        AuditLog::record('UNIVERSITY_ACCOUNT_CREATE', 'User', $user->id, [
            'university_name' => $univ->name,
            'email' => $email,
        ]);

        session()->flash('new_university_credential', [
            'univ_name' => $univ->name,
            'name' => $user->name,
            'user_id' => $user->id,
            'email' => $email,
            'password' => $password,
            'login_url' => url('/login'),
        ]);

        return redirect()->route('admin.universities.index')
            ->with('success', "Akun Admin Portal untuk '{$univ->name}' berhasil dibuat! Email: {$email} | Password: {$password}");
    }

    public function destroy($id)
    {
        $this->ensureSuperAdmin('Hanya Super Administrator yang dapat mengubah data perguruan tinggi, akun kampus, dan dosen pembimbing.');
        $univ = University::withCount(['students', 'dosens'])->findOrFail($id);

        // 1. Proteksi Mahasiswa: Jika ada mahasiswa terdaftar, jangan hapus
        if ($univ->students_count > 0) {
            return redirect()->back()->with('error', "Gagal menghapus: Masih ada {$univ->students_count} mahasiswa terdaftar di {$univ->name}. Pindahkan atau hapus data mahasiswa terlebih dahulu.");
        }

        // 2. Proteksi Dosen dengan Bimbingan Aktif
        $activeDosenCount = $univ->dosens()
            ->whereHas('academicPlacements', function ($q) {
                $q->whereHas('application', function ($aq) {
                    $aq->whereIn('status', ['verified', ...Application::QUOTA_STATUSES]);
                });
            })->count();

        if ($activeDosenCount > 0) {
            return redirect()->back()->with('error', "Gagal menghapus: Terdapat {$activeDosenCount} dosen pembimbing dari {$univ->name} yang sedang membimbing mahasiswa aktif.");
        }

        $name = $univ->name;

        \DB::transaction(function () use ($univ) {
            // Hapus akun admin kampus yang terafiliasi dengan universitas ini
            User::where('university_id', $univ->id)
                ->where('role', 'universitas')
                ->delete();

            // Lepaskan relasi dosen non-aktif jika ada
            User::where('university_id', $univ->id)
                ->whereIn('role', ['dosen', 'academic_advisor'])
                ->update(['university_id' => null, 'university' => null]);

            // Hapus data universitas
            $univ->delete();
        });

        AuditLog::record('UNIVERSITY_DELETE', 'University', $id, ['name' => $name]);

        return redirect()->route('admin.universities.index')
            ->with('success', "Universitas '{$name}' dan akun admin kampusnya berhasil dihapus.");
    }

    /**
     * Pusat Manajemen Terpadu Universitas (Super Admin Hub)
     */
    public function show(Request $request, $id)
    {
        $university = University::with(['universityAdmin'])->findOrFail($id);
        $isSuperAdmin = $this->currentUserIsSuperAdmin();

        // Data dosen, mahasiswa (urut prioritas tindakan) & statistik kampus disusun di service
        [$dosens, $students, $stats, $requireAdvisor] = app(UniversityHubService::class)
            ->build($university, (string) $request->input('student_status', ''), $request->input('student_search'));

        $activeTab = $request->query('tab', 'dosen');

        return view('admin.universities.show', compact(
            'university',
            'dosens',
            'students',
            'stats',
            'isSuperAdmin',
            'activeTab',
            'requireAdvisor'
        ));
    }

    /**
     * Tambah DPL Baru untuk Universitas oleh Super Admin
     */
    public function storeDosen(Request $request, $id)
    {
        $this->ensureSuperAdmin('Hanya Super Administrator yang dapat mengubah data perguruan tinggi, akun kampus, dan dosen pembimbing.');
        $university = University::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'nidn' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:30',
        ], [
            'name.required' => 'Nama lengkap dan gelar dosen wajib diisi.',
            'email.required' => 'Email resmi dosen wajib diisi.',
            'email.unique' => 'Email ini sudah terdaftar di dalam sistem.',
        ]);

        $dosenName = trim($request->name);
        if ($request->filled('nidn') && ! str_contains($dosenName, 'NIDN')) {
            $dosenName .= ' (NIDN: '.trim($request->nidn).')';
        }

        $password = 'password';

        $dosen = User::create([
            'name' => $dosenName,
            'email' => strtolower(trim($request->email)),
            'password' => Hash::make($password),
            'role' => 'dosen',
            'university_id' => $university->id,
            'university' => $university->name,
            'phone' => $request->phone,
            'email_verified_at' => now(),
        ]);

        AuditLog::record('UNIVERSITY_DOSEN_CREATE', 'User', $dosen->id, [
            'university_id' => $university->id,
            'university_name' => $university->name,
            'dosen_name' => $dosen->name,
            'dosen_email' => $dosen->email,
        ]);

        return redirect()->route('admin.universities.show', ['university' => $university->id, 'tab' => 'dosen'])
            ->with('success', "Dosen Pembimbing '{$dosen->name}' berhasil ditambahkan ke {$university->name}! Password default: 'password'");
    }

    /**
     * Reset Password DPL ke default 'password'
     */
    public function resetDosenPassword(Request $request, $univId, $dosenId)
    {
        $this->ensureSuperAdmin('Hanya Super Administrator yang dapat mengubah data perguruan tinggi, akun kampus, dan dosen pembimbing.');
        $university = University::findOrFail($univId);
        $dosen = User::whereIn('role', ['dosen', 'academic_advisor'])
            ->where(function ($q) use ($university) {
                $q->where('university_id', $university->id)
                    ->orWhere('university', $university->name);
            })
            ->findOrFail($dosenId);

        $dosen->update([
            'password' => Hash::make('password'),
        ]);

        AuditLog::record('UNIVERSITY_DOSEN_RESET_PASSWORD', 'User', $dosen->id, [
            'dosen_name' => $dosen->name,
            'university_name' => $university->name,
        ]);

        return redirect()->route('admin.universities.show', ['university' => $university->id, 'tab' => 'dosen'])
            ->with('success', "Password untuk Dosen '{$dosen->name}' ({$dosen->email}) berhasil direset ke default ('password')!");
    }

    /**
     * Hapus DPL Kampus oleh Super Admin
     */
    public function destroyDosen(Request $request, $univId, $dosenId)
    {
        $this->ensureSuperAdmin('Hanya Super Administrator yang dapat mengubah data perguruan tinggi, akun kampus, dan dosen pembimbing.');
        $university = University::findOrFail($univId);
        $dosen = User::whereIn('role', ['dosen', 'academic_advisor'])
            ->where(function ($q) use ($university) {
                $q->where('university_id', $university->id)
                    ->orWhere('university', $university->name);
            })
            ->findOrFail($dosenId);

        // Periksa riwayat bimbingan (aktif maupun alumni): placements.academic_advisor_id ON DELETE SET NULL
        // akan mengosongkan nama DPL pada arsip nilai & sertifikat alumni.
        $historyCount = $dosen->academicPlacements()->count();

        if ($historyCount > 0) {
            return redirect()->route('admin.universities.show', ['university' => $university->id, 'tab' => 'dosen'])
                ->with('error', "Dosen '{$dosen->name}' tidak dapat dihapus karena tercatat membimbing {$historyCount} mahasiswa (termasuk arsip alumni). Ubah status akun menjadi Nonaktif untuk mencabut aksesnya.");
        }

        $name = $dosen->name;
        $dosen->delete();

        AuditLog::record('UNIVERSITY_DOSEN_DELETE', 'User', $dosenId, [
            'dosen_name' => $name,
            'university_name' => $university->name,
        ]);

        return redirect()->route('admin.universities.show', ['university' => $university->id, 'tab' => 'dosen'])
            ->with('success', "Dosen Pembimbing '{$name}' berhasil dihapus dari daftar kampus.");
    }

    /**
     * Penugasan / Plotting DPL untuk Mahasiswa Kampus oleh Super Admin
     */
    public function assignAdvisor(Request $request, $id)
    {
        $this->ensureSuperAdmin('Hanya Super Administrator yang dapat mengubah data perguruan tinggi, akun kampus, dan dosen pembimbing.');
        $university = University::findOrFail($id);

        $request->validate([
            'application_id' => 'required|exists:applications,id',
            'academic_advisor_id' => 'required|exists:users,id',
        ], [
            'application_id.required' => 'Pengajuan mahasiswa wajib dipilih.',
            'academic_advisor_id.required' => 'Dosen Pembimbing Lapangan wajib dipilih.',
        ]);

        $application = Application::with('user.studentProfile')->findOrFail($request->application_id);
        $advisor = User::whereIn('role', ['dosen', 'academic_advisor'])->findOrFail($request->academic_advisor_id);

        $placement = Placement::updateOrCreate(
            ['application_id' => $application->id],
            ['academic_advisor_id' => $advisor->id]
        );

        // Bersihkan duplikasi jika ada
        Placement::where('application_id', $application->id)
            ->where('id', '!=', $placement->id)
            ->delete();

        AuditLog::record('UNIVERSITY_ASSIGN_ADVISOR', 'Placement', $placement->id, [
            'student_name' => $application->user->name,
            'advisor_name' => $advisor->name,
            'university_name' => $university->name,
        ]);

        return redirect()->route('admin.universities.show', ['university' => $university->id, 'tab' => 'mahasiswa'])
            ->with('success', "Dosen Pembimbing Lapangan ({$advisor->name}) berhasil ditugaskan untuk {$application->user->name}!");
    }

    /**
     * Export Rekapitulasi Data Mahasiswa Kampus ke CSV
     */
    public function exportStudents(Request $request, $id)
    {
        $university = University::findOrFail($id);

        $applications = Application::with([
            'user.studentProfile',
            'unit.agencyProfile',
            'placement.mentor',
            'placement.pembimbing',
            'placement.academicAdvisor',
            'placement.evaluation',
        ])->whereHas('user', function ($uq) use ($university) {
            $uq->where('university_id', $university->id)
                ->orWhere('university', $university->name)
                ->orWhereHas('studentProfile', fn ($sp) => $sp->where('university_id', $university->id)->orWhereRaw('LOWER(universitas) = ?', [mb_strtolower($university->name)]));
        })
            ->latest()
            ->get();

        $cleanUnivName = preg_replace('/[^A-Za-z0-9_]/', '_', $university->name);
        $filename = 'Rekap_Mahasiswa_'.$cleanUnivName.'_'.date('Ymd_His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($applications, $university) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

            fputcsv($handle, [
                'No',
                'NIM',
                'Nama Mahasiswa',
                'Program Studi',
                'Instansi Magang',
                'Unit / Bidang Kerja',
                'Dosen Pembimbing',
                'Mentor Dinas',
                'Periode Magang',
                'Status Magang',
                'Nilai Mentor',
                'Nilai Dosen',
                'Nilai Akhir',
            ]);

            $no = 1;
            foreach ($applications as $app) {
                $student = $app->user;
                $profile = $student?->studentProfile;
                $placement = $app->placement;
                $dosen = $placement?->academicAdvisor;
                $mentor = $placement?->mentor ?? $placement?->pembimbing;
                $eval = $placement?->evaluation?->useUniversity($university);

                // Sumber nilai sama dengan tabel & sertifikat: accessor resmi di model Evaluation
                $mentorScore = ($eval && $eval->nilai_pembimbing > 0) ? number_format($eval->nilai_pembimbing, 2) : '-';
                $dosenScore = ($eval && $eval->nilai_dosen_calculated > 0) ? number_format($eval->nilai_dosen_calculated, 2) : '-';
                $finalScore = ($eval && $eval->nilai_akhir > 0) ? number_format($eval->nilai_akhir, 2) : '-';

                $periode = ($app->start_date && $app->end_date)
                    ? date('d/m/Y', strtotime($app->start_date)).' s.d. '.date('d/m/Y', strtotime($app->end_date))
                    : '-';

                fputcsv($handle, [
                    $no++,
                    $profile?->nim ?? '-',
                    $student?->name ?? '-',
                    $profile?->jurusan ?? '-',
                    $app->unit?->agencyProfile?->agency_name ?? '-',
                    $app->unit?->name ?? '-',
                    $dosen?->name ?? 'Belum Ditentukan',
                    $mentor?->name ?? 'Belum Diplot',
                    $periode,
                    ApplicationStatus::tryFrom($app->statusValue())?->label() ?? ucfirst($app->statusValue()),
                    $mentorScore,
                    $dosenScore,
                    $finalScore,
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
