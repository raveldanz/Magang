<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\AuditLog;
use App\Models\InternshipGroup;
use App\Models\Unit;
use App\Models\User;
use App\Services\PrivateDocumentStorage;
use App\Support\UploadRules;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ApplicationController extends Controller
{
    // Menampilkan halaman form pengajuan magang
    public function create()
    {
        $units = Unit::with('agencyProfile')
            ->withCount(['applications as occupied_count_db' => function ($query) {
                $query->occupyingQuota();
            }])->get();
        $groupedUnits = $units->groupBy(function ($unit) {
            return $unit->agencyProfile->agency_name ?? 'Pemerintah Kota Surabaya';
        });

        $user = Auth::user();

        // 1. Cek apakah mahasiswa sudah punya profil
        if (! $user->studentProfile) {
            return redirect()->route('student.profile.edit')
                ->with('error', 'Silakan lengkapi profil Anda terlebih dahulu sebelum mengajukan magang.');
        }

        // 2. Cek apakah ada pengajuan yang sedang berjalan atau aktif (pending, waiting_approval, accepted, active, completed)
        $activeApplication = Application::with(['unit.agencyProfile', 'placement', 'group.leader', 'group.applications.user.studentProfile'])
            ->where('user_id', $user->id)
            ->whereIn('status', ['pending', 'waiting_approval', 'accepted', 'active', 'completed'])
            ->latest()
            ->first();

        if ($activeApplication) {
            $activeApplication->autoActivateIfStarted();
            $activeApplication->refresh();
        }

        // 2b. Cek apakah mahasiswa sedang tergabung dalam kelompok berstatus draft
        $draftGroup = InternshipGroup::with(['leader.studentProfile', 'unit.agencyProfile', 'applications.user.studentProfile'])
            ->where('status', 'draft')
            ->whereHas('applications', fn ($q) => $q->where('user_id', $user->id))
            ->first();

        // 3. Ambil seluruh riwayat pengajuan mahasiswa
        $applicationHistory = Application::with(['unit.agencyProfile', 'group.leader', 'group.applications.user.studentProfile'])
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        // 3b. Cek apakah pengajuan terakhir mahasiswa adalah anggota kelompok yang ditolak (perlu perbaikan berkas untuk kembali ke kelompoknya)
        $latestApp = $applicationHistory->first();
        $rejectedGroupApplication = (! $activeApplication && ! $draftGroup && $latestApp && $latestApp->statusValue() === 'rejected' && $latestApp->group_id && $latestApp->group)
            ? $latestApp
            : null;

        $agencies = AgencyProfile::orderBy('agency_name')->get();
        $defaultAgency = $agencies->first();
        $maxGroupMembers = 100;

        return view('student.application.create', compact('units', 'groupedUnits', 'agencies', 'activeApplication', 'draftGroup', 'applicationHistory', 'maxGroupMembers', 'rejectedGroupApplication'));
    }

    // Menyimpan data pengajuan magang & upload dokumen
    public function store(Request $request)
    {
        $user = Auth::user();

        // 1. Cek kelengkapan profil mahasiswa
        if (! $user->studentProfile) {
            return redirect()->route('student.profile.edit')
                ->with('error', 'Silakan lengkapi profil Anda terlebih dahulu sebelum mengajukan magang.');
        }

        // 2. Cegah pengajuan ganda jika sudah ada pengajuan aktif / diterima / selesai
        $existingActive = Application::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'waiting_approval', 'accepted', 'active', 'completed'])
            ->first();

        if ($existingActive) {
            $existingStatusVal = $existingActive->status instanceof \BackedEnum ? $existingActive->status->value : (string) $existingActive->status;
            $msg = match ($existingStatusVal) {
                'pending', 'waiting_approval' => 'Anda masih memiliki berkas pengajuan magang yang sedang diproses. Mohon tunggu proses verifikasi admin dinas.',
                'accepted', 'active' => 'Akses ditolak: Anda sudah memiliki penempatan magang aktif yang sedang berjalan.',
                'completed' => 'Anda telah menyelesaikan program magang pada instansi sebelumnya.',
                default => 'Anda sudah memiliki pengajuan magang aktif.'
            };

            return redirect()->route('student.application.create')->with('error', $msg);
        }

        $hasProfileDocs = $user->studentProfile->hasCompletePersonalDocuments();
        $hasUploadedAll = $request->hasFile('cv') && $request->hasFile('transkrip') && $request->hasFile('id_card');

        if (! $hasProfileDocs && ! $hasUploadedAll) {
            return redirect()->route('student.profile.edit')
                ->with('error', 'Silakan lengkapi berkas personal Anda yaitu CV, Transkrip Nilai, dan KTM pada menu Profil Saya terlebih dahulu.');
        }

        $request->validate([
            'unit_id' => 'required|exists:units,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => ['required', 'date', 'after_or_equal:start_date', $this->maxInternshipEndRule($request)],
            'surat_pengantar' => 'required|'.UploadRules::APPLICATION_DOCUMENT,
            'cv' => 'nullable|'.UploadRules::APPLICATION_DOCUMENT,
            'transkrip' => 'nullable|'.UploadRules::APPLICATION_DOCUMENT,
            'id_card' => 'nullable|'.UploadRules::APPLICATION_ID_CARD,
        ], [
            'start_date.required' => 'Tanggal mulai magang wajib diisi.',
            'start_date.date' => 'Tanggal mulai magang tidak valid.',
            'start_date.after_or_equal' => 'Tanggal mulai magang tidak boleh sebelum hari ini.',
            'end_date.required' => 'Tanggal selesai magang wajib diisi.',
            'end_date.after_or_equal' => 'Tanggal selesai magang harus setelah atau sama dengan tanggal mulai.',
            'surat_pengantar.required' => 'Surat Pengantar dari kampus/sekolah wajib diunggah.',
            'surat_pengantar.mimes' => 'File Surat Pengantar harus berformat PDF.',
            'surat_pengantar.max' => 'Ukuran file Surat Pengantar maksimal 2MB.',
            'cv.mimes' => 'File CV harus berformat PDF.',
            'cv.max' => 'Ukuran file CV maksimal 2MB.',
            'transkrip.mimes' => 'File Transkrip Nilai harus berformat PDF.',
            'transkrip.max' => 'Ukuran file Transkrip Nilai maksimal 2MB.',
            'id_card.mimes' => 'File KTM atau Kartu Identitas harus berformat PDF, JPG, JPEG, atau PNG.',
            'id_card.max' => 'Ukuran file KTM atau Kartu Identitas maksimal 2MB.',
        ]);

        // Cek Sisa Kuota Instansi yang Dipilih
        $unit = Unit::findOrFail($request->unit_id);
        if ($unit->remaining_quota <= 0) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['unit_id' => 'Kuota untuk Bidang ini sudah penuh. Silakan pilih Bidang Dispusip lain.']);
        }

        $storage = app(PrivateDocumentStorage::class);
        $proposalPath = $storage->storeApplicationDocument($request->file('surat_pengantar'));
        $newlyUploadedPaths = [$proposalPath];

        if ($request->hasFile('cv')) {
            $cvPath = $storage->storeApplicationDocument($request->file('cv'));
            $newlyUploadedPaths[] = $cvPath;
        } else {
            $cvPath = $user->studentProfile->cv_path;
        }

        if ($request->hasFile('transkrip')) {
            $transcriptPath = $storage->storeApplicationDocument($request->file('transkrip'));
            $newlyUploadedPaths[] = $transcriptPath;
        } else {
            $transcriptPath = $user->studentProfile->transcript_path;
        }

        if ($request->hasFile('id_card')) {
            $idCardPath = $storage->storeApplicationDocument($request->file('id_card'));
            $newlyUploadedPaths[] = $idCardPath;
        } else {
            $idCardPath = $user->studentProfile->id_card_path;
        }

        // Pastikan seluruh berkas prasyarat wajib telah terpenuhi sebelum membuat pengajuan
        if (! $cvPath || ! $transcriptPath || ! $idCardPath) {
            $missingDocs = [];
            if (! $cvPath) {
                $missingDocs[] = 'CV';
            }
            if (! $transcriptPath) {
                $missingDocs[] = 'Transkrip Nilai';
            }
            if (! $idCardPath) {
                $missingDocs[] = 'KTM / Kartu Identitas';
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Pengajuan magang belum dapat dikirim karena berkas prasyarat ('.implode(', ', $missingDocs).') belum lengkap. Silakan lengkapi berkas pada menu Profil Saya terlebih dahulu.');
        }

        // Simpan Data Pengajuan dalam transaksi + kunci baris user, supaya klik ganda /
        // dua tab yang submit bersamaan tidak menghasilkan 2 pengajuan aktif sekaligus.
        $application = DB::transaction(function () use ($request, $user, $proposalPath, $cvPath, $transcriptPath, $idCardPath) {
            User::whereKey($user->id)->lockForUpdate()->first();

            $alreadyActive = Application::where('user_id', $user->id)
                ->whereIn('status', ['pending', 'waiting_approval', 'accepted', 'active', 'completed'])
                ->exists();
            if ($alreadyActive) {
                return null;
            }

            if ($request->hasFile('cv') || $request->hasFile('transkrip') || $request->hasFile('id_card')) {
                $user->studentProfile->update(array_filter([
                    'cv_path' => $cvPath,
                    'transcript_path' => $transcriptPath,
                    'id_card_path' => $idCardPath,
                ]));
            }

            return $this->createApplicationRecords($request, $proposalPath, $cvPath, $transcriptPath, $idCardPath);
        });

        if (! $application) {
            // Pengajuan ganda dibatalkan: hapus hanya berkas baru yang diunggah pada request ini (jangan hapus berkas profil!)
            $storage->discard($newlyUploadedPaths);

            return redirect()->route('student.application.create')
                ->with('error', 'Anda masih memiliki berkas pengajuan magang yang sedang diproses. Mohon tunggu proses verifikasi admin dinas.');
        }

        return redirect()->back()->with('success', 'Pengajuan magang dan dokumen persyaratan berhasil dikirim!');
    }

    /**
     * Buat record pengajuan + dokumen persyaratan (dipanggil di dalam transaksi).
     */
    /**
     * Periode magang paling lama satu tahun sejak tanggal mulai, supaya salah ketik tahun
     * (misalnya 2062) tidak lolos menjadi pengajuan.
     */
    private function maxInternshipEndRule(Request $request): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) use ($request) {
            try {
                $start = Carbon::parse((string) $request->input('start_date'));
                $end = Carbon::parse((string) $value);
            } catch (InvalidFormatException) {
                return;
            }

            if ($end->gt($start->copy()->addYear())) {
                $fail('Periode magang paling lama 1 tahun sejak tanggal mulai.');
            }
        };
    }

    private function createApplicationRecords(Request $request, ?string $proposalPath, ?string $cvPath, ?string $transcriptPath, ?string $idCardPath): Application
    {
        $application = Application::create([
            'user_id' => Auth::id(),
            'unit_id' => $request->unit_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'proposal_letter_path' => $proposalPath,
            'cv_path' => $cvPath,
            'transcript_path' => $transcriptPath,
            'id_card_path' => $idCardPath,
            'status' => 'pending',
            'letter_token' => Str::random(32),
        ]);

        // Simpan Dokumen Persyaratan ke tabel application_documents
        $documents = [
            'Surat Pengantar' => $proposalPath,
            'CV' => $cvPath,
            'Transkrip Nilai' => $transcriptPath,
        ];
        if ($idCardPath) {
            $documents['KTM / Kartu Identitas'] = $idCardPath;
        }

        foreach ($documents as $type => $path) {
            if ($path) {
                ApplicationDocument::create([
                    'application_id' => $application->id,
                    'document_type' => $type,
                    'file_path' => $path,
                ]);
            }
        }

        return $application;
    }

    // Download / Print Surat Penerimaan Magang untuk Mahasiswa
    public function downloadLetter($id)
    {
        $application = Application::with(['user.studentProfile', 'unit.agencyProfile', 'placement.pembimbing', 'group.applications.user.studentProfile'])
            ->where('user_id', Auth::id())
            ->whereIn('status', ['accepted', 'active', 'completed'])
            ->findOrFail($id);

        return view('letters.acceptance', compact('application'));
    }

    // Membuat kelompok baru (Ketua)
    public function createGroup(Request $request)
    {
        $user = Auth::user();

        if (! $user->studentProfile) {
            return redirect()->route('student.profile.edit')
                ->with('error', 'Silakan lengkapi profil Anda terlebih dahulu sebelum membuat kelompok.');
        }

        if (! $user->studentProfile->hasCompletePersonalDocuments()) {
            return redirect()->route('student.profile.edit')
                ->with('error', 'Silakan lengkapi berkas personal Anda yaitu CV, Transkrip Nilai, dan KTM pada menu Profil Saya terlebih dahulu sebelum membuat kelompok.');
        }

        $hasActive = Application::where('user_id', $user->id)
            ->whereIn('status', ['draft', 'pending', 'waiting_approval', 'accepted', 'active', 'completed'])
            ->exists();
        if ($hasActive) {
            return redirect()->back()->with('error', 'Anda sudah memiliki pengajuan magang aktif atau sedang berada dalam kelompok.');
        }

        $request->validate([
            'unit_id' => 'required|exists:units,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => ['required', 'date', 'after_or_equal:start_date', $this->maxInternshipEndRule($request)],
            'name' => 'nullable|string|max:100',
        ], [
            'start_date.after_or_equal' => 'Tanggal mulai magang tidak boleh sebelum hari ini.',
            'end_date.after_or_equal' => 'Tanggal selesai magang harus setelah atau sama dengan tanggal mulai.',
        ]);

        $unit = Unit::findOrFail($request->unit_id);
        if ($unit->remaining_quota <= 0) {
            return redirect()->back()->with('error', 'Kuota untuk Bidang ini sudah penuh.');
        }

        DB::transaction(function () use ($user, $request) {
            $group = InternshipGroup::create([
                'code' => InternshipGroup::generateUniqueCode(),
                'name' => $request->name ?: ('Kelompok '.$user->name),
                'leader_user_id' => $user->id,
                'unit_id' => $request->unit_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status' => 'draft',
                'letter_token' => Str::random(32),
            ]);

            Application::create([
                'user_id' => $user->id,
                'unit_id' => $request->unit_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'group_id' => $group->id,
                'role_in_group' => 'leader',
                'status' => 'draft',
                'cv_path' => $user->studentProfile->cv_path,
                'transcript_path' => $user->studentProfile->transcript_path,
                'id_card_path' => $user->studentProfile->id_card_path,
                'letter_token' => Str::random(32),
            ]);
        });

        return redirect()->route('student.application.create')
            ->with('success', 'Kelompok magang berhasil dibuat! Bagikan Kode Gabung kepada rekan se-kampus Anda.');
    }

    // Gabung kelompok menggunakan Kode Undangan (Anggota)
    public function joinGroup(Request $request)
    {
        $user = Auth::user();

        if (! $user->studentProfile) {
            return redirect()->route('student.profile.edit')
                ->with('error', 'Silakan lengkapi profil Anda terlebih dahulu sebelum bergabung ke kelompok.');
        }

        if (! $user->studentProfile->hasCompletePersonalDocuments()) {
            return redirect()->route('student.profile.edit')
                ->with('error', 'Silakan lengkapi berkas personal Anda yaitu CV, Transkrip Nilai, dan KTM pada menu Profil Saya terlebih dahulu sebelum bergabung ke kelompok.');
        }

        $hasActive = Application::where('user_id', $user->id)
            ->whereIn('status', ['draft', 'pending', 'waiting_approval', 'accepted', 'active', 'completed'])
            ->exists();
        if ($hasActive) {
            return redirect()->back()->with('error', 'Anda sudah memiliki pengajuan magang aktif atau sedang berada dalam kelompok.');
        }

        $request->validate([
            'group_code' => 'required|string|max:20',
        ]);

        $code = strtoupper(trim($request->group_code));
        $group = InternshipGroup::with(['leader.studentProfile', 'unit'])->where('code', $code)->where('status', 'draft')->first();

        if (! $group) {
            $existingGroup = InternshipGroup::with(['leader.studentProfile', 'unit'])->where('code', $code)->first();
            if ($existingGroup) {
                $rejectedMemberApp = Application::where('group_id', $existingGroup->id)
                    ->where('user_id', $user->id)
                    ->where('status', 'rejected')
                    ->latest()
                    ->first();
                if ($rejectedMemberApp) {
                    return $this->resubmitToExistingGroup($request, $rejectedMemberApp, $existingGroup);
                }
            }

            return redirect()->back()->with('error', 'Kode kelompok tidak valid atau pendaftaran kelompok tersebut sudah dikirim/ditutup.');
        }

        // Validasi kesamaan kampus
        $leaderUnivId = $group->leader?->university_id;
        $userUnivId = $user->university_id;
        if ($leaderUnivId && $userUnivId && $leaderUnivId !== $userUnivId) {
            return redirect()->back()->with('error', 'Anda tidak dapat bergabung karena kelompok ini dikhususkan untuk peserta magang dari '.($group->leader->university ?? 'kampus lain').'.');
        }

        // Validasi batas maksimal aman anggota kelompok standar 100 orang
        $maxGroupMembers = 100;
        $currentCount = $group->applications()->count();
        if ($currentCount >= $maxGroupMembers) {
            return redirect()->back()->with('error', "Jumlah anggota kelompok sudah mencapai batas maksimal sistem yaitu {$maxGroupMembers} orang.");
        }

        // Validasi sisa kuota bidang
        if ($currentCount >= $group->unit->remaining_quota) {
            return redirect()->back()->with('error', 'Sisa kuota Bidang Dispusip tidak mencukupi untuk menambah anggota baru.');
        }

        Application::create([
            'user_id' => $user->id,
            'unit_id' => $group->unit_id,
            'start_date' => $group->start_date,
            'end_date' => $group->end_date,
            'group_id' => $group->id,
            'role_in_group' => 'member',
            'status' => 'draft',
            'cv_path' => $user->studentProfile->cv_path,
            'transcript_path' => $user->studentProfile->transcript_path,
            'id_card_path' => $user->studentProfile->id_card_path,
            'letter_token' => Str::random(32),
        ]);

        return redirect()->route('student.application.create')
            ->with('success', "Anda berhasil bergabung ke kelompok '{$group->name}'!");
    }

    // Anggota keluar dari kelompok (saat masih draft atau saat pengajuan kelompoknya berstatus ditolak/perlu perbaikan)
    public function leaveGroup(Request $request)
    {
        $user = Auth::user();
        $application = Application::where('user_id', $user->id)
            ->where('status', 'draft')
            ->where('role_in_group', 'member')
            ->first();

        if ($application) {
            $application->delete();

            return redirect()->route('student.application.create')
                ->with('success', 'Anda telah keluar dari kelompok magang.');
        }

        $rejectedGroupApp = Application::with('group')
            ->where('user_id', $user->id)
            ->where('status', 'rejected')
            ->whereNotNull('group_id')
            ->latest()
            ->first();

        if ($rejectedGroupApp) {
            DB::transaction(function () use ($rejectedGroupApp, $user) {
                $group = $rejectedGroupApp->group;
                if ($group && (int) $group->leader_user_id === (int) $user->id) {
                    $nextLeaderApp = Application::where('group_id', $group->id)
                        ->where('user_id', '!=', $user->id)
                        ->orderByRaw("CASE WHEN status IN ('accepted', 'active', 'waiting_approval', 'pending') THEN 0 ELSE 1 END")
                        ->first();

                    if ($nextLeaderApp) {
                        $group->update(['leader_user_id' => $nextLeaderApp->user_id]);
                        $nextLeaderApp->update(['role_in_group' => 'leader']);
                    }
                }

                $rejectedGroupApp->update([
                    'group_id' => null,
                    'role_in_group' => null,
                ]);
            });

            return redirect()->route('student.application.create')
                ->with('success', 'Anda telah keluar dari kelompok magang. Silakan pilih jalur pendaftaran baru di bawah ini.');
        }

        return redirect()->back()->with('error', 'Anda tidak terdaftar sebagai anggota kelompok yang dapat ditinggalkan.');
    }

    // Ketua mengeluarkan anggota dari kelompok (sebelum disubmit)
    public function removeMember(Request $request)
    {
        $user = Auth::user();
        $request->validate(['application_id' => 'required|exists:applications,id']);

        $memberApp = Application::with('group')->findOrFail($request->application_id);
        if ($memberApp->group?->leader_user_id !== $user->id || $memberApp->role_in_group !== 'member' || $memberApp->status !== 'draft') {
            return redirect()->back()->with('error', 'Anda tidak memiliki hak untuk mengeluarkan anggota ini.');
        }

        $memberApp->delete();

        return redirect()->route('student.application.create')
            ->with('success', 'Anggota berhasil dikeluarkan dari kelompok.');
    }

    // Ketua membatalkan kelompok draft
    public function cancelGroup(Request $request)
    {
        $user = Auth::user();
        $group = InternshipGroup::where('leader_user_id', $user->id)
            ->where('status', 'draft')
            ->first();

        if (! $group) {
            return redirect()->back()->with('error', 'Kelompok tidak ditemukan.');
        }

        DB::transaction(function () use ($group) {
            Application::where('group_id', $group->id)->delete();
            $group->delete();
        });

        return redirect()->route('student.application.create')
            ->with('success', 'Kelompok magang telah dibatalkan.');
    }

    // Ketua mengirim pengajuan kelompok ke Admin
    public function submitGroup(Request $request)
    {
        $user = Auth::user();
        $group = InternshipGroup::with(['applications.user', 'unit'])->where('leader_user_id', $user->id)
            ->where('status', 'draft')
            ->first();

        if (! $group) {
            return redirect()->back()->with('error', 'Kelompok tidak ditemukan atau sudah diajukan.');
        }

        if ($group->applications->count() < 2) {
            return redirect()->back()->with('error', 'Kelompok magang minimal memiliki 2 anggota (Ketua + 1 Anggota). Jika mendaftar sendiri, gunakan formulir Pendaftaran Individu.');
        }

        $request->validate([
            'surat_pengantar' => 'required|'.UploadRules::APPLICATION_DOCUMENT,
        ], [
            'surat_pengantar.required' => 'Surat Pengantar / Proposal magang kelompok wajib diunggah.',
            'surat_pengantar.mimes' => 'File Surat Pengantar harus berformat PDF.',
            'surat_pengantar.max' => 'Ukuran file Surat Pengantar maksimal 2MB (2048 KB).',
        ]);

        if ($group->unit->remaining_quota < $group->applications->count()) {
            return redirect()->back()->with('error', 'Sisa kuota Bidang Dispusip tidak mencukupi untuk jumlah anggota kelompok ini.');
        }

        $storage = app(PrivateDocumentStorage::class);
        $proposalPath = $storage->storeApplicationDocument($request->file('surat_pengantar'));

        DB::transaction(function () use ($group, $proposalPath) {
            $group->update([
                'status' => 'pending',
                'proposal_letter_path' => $proposalPath,
            ]);

            foreach ($group->applications as $app) {
                $app->update([
                    'status' => 'pending',
                    'proposal_letter_path' => $proposalPath,
                ]);

                ApplicationDocument::firstOrCreate([
                    'application_id' => $app->id,
                    'document_type' => 'Surat Pengantar',
                ], ['file_path' => $proposalPath]);

                if ($app->cv_path) {
                    ApplicationDocument::firstOrCreate([
                        'application_id' => $app->id,
                        'document_type' => 'CV',
                    ], ['file_path' => $app->cv_path]);
                }
                if ($app->transcript_path) {
                    ApplicationDocument::firstOrCreate([
                        'application_id' => $app->id,
                        'document_type' => 'Transkrip Nilai',
                    ], ['file_path' => $app->transcript_path]);
                }
                if ($app->id_card_path) {
                    ApplicationDocument::firstOrCreate([
                        'application_id' => $app->id,
                        'document_type' => 'KTM / Kartu Identitas',
                    ], ['file_path' => $app->id_card_path]);
                }
            }
        });

        return redirect()->route('student.application.create')
            ->with('success', 'Pengajuan kelompok magang berhasil dikirim ke Admin Dinas untuk diverifikasi!');
    }

    /**
     * Mahasiswa anggota kelompok yang berstatus rejected memperbaiki berkas & mengajukan ulang kembali ke kelompoknya.
     */
    public function resubmitGroupMember(Request $request)
    {
        $user = Auth::user();

        if (! $user->studentProfile) {
            return redirect()->route('student.profile.edit')
                ->with('error', 'Silakan lengkapi profil Anda terlebih dahulu.');
        }

        $hasActive = Application::where('user_id', $user->id)
            ->whereIn('status', ['draft', 'pending', 'waiting_approval', 'accepted', 'active', 'completed'])
            ->exists();
        if ($hasActive) {
            return redirect()->route('student.application.create')
                ->with('error', 'Anda sudah memiliki pengajuan magang aktif yang sedang diproses.');
        }

        $application = Application::with(['group', 'unit'])
            ->where('user_id', $user->id)
            ->whereNotNull('group_id')
            ->where('status', 'rejected')
            ->latest()
            ->first();

        if (! $application || ! $application->group) {
            return redirect()->route('student.application.create')
                ->with('error', 'Data pengajuan kelompok yang perlu diperbaiki tidak ditemukan.');
        }

        $request->validate([
            'cv' => 'nullable|'.UploadRules::APPLICATION_DOCUMENT,
            'transkrip' => 'nullable|'.UploadRules::APPLICATION_DOCUMENT,
            'id_card' => 'nullable|'.UploadRules::APPLICATION_ID_CARD,
            'surat_pengantar' => 'nullable|'.UploadRules::APPLICATION_DOCUMENT,
        ], [
            'cv.mimes' => 'File CV harus berformat PDF.',
            'cv.max' => 'Ukuran file CV maksimal 2MB (2048 KB).',
            'transkrip.mimes' => 'File Transkrip Nilai harus berformat PDF.',
            'transkrip.max' => 'Ukuran file Transkrip Nilai maksimal 2MB (2048 KB).',
            'id_card.mimes' => 'File KTM atau Kartu Identitas harus berformat PDF, JPG, JPEG, atau PNG.',
            'id_card.max' => 'Ukuran file KTM atau Kartu Identitas maksimal 2MB (2048 KB).',
            'surat_pengantar.mimes' => 'File Surat Pengantar harus berformat PDF.',
            'surat_pengantar.max' => 'Ukuran file Surat Pengantar maksimal 2MB (2048 KB).',
        ]);

        return $this->resubmitToExistingGroup($request, $application, $application->group);
    }

    /**
     * Proses inti sinkronisasi berkas perbaikan & pengembalian status anggota ke dalam kelompok yang sama.
     */
    protected function resubmitToExistingGroup(Request $request, Application $application, InternshipGroup $group)
    {
        $user = Auth::user();
        $profile = $user->studentProfile;
        $storage = app(PrivateDocumentStorage::class);

        $cvPath = $profile->cv_path;
        $transcriptPath = $profile->transcript_path;
        $idCardPath = $profile->id_card_path;

        if ($request->hasFile('cv')) {
            $cvPath = $storage->store($request->file('cv'), 'documents/student-profiles');
        }
        if ($request->hasFile('transkrip')) {
            $transcriptPath = $storage->store($request->file('transkrip'), 'documents/student-profiles');
        }
        if ($request->hasFile('id_card')) {
            $idCardPath = $storage->store($request->file('id_card'), 'documents/student-profiles');
        }

        if ($request->hasFile('cv') || $request->hasFile('transkrip') || $request->hasFile('id_card')) {
            $profile->update([
                'cv_path' => $cvPath,
                'transcript_path' => $transcriptPath,
                'id_card_path' => $idCardPath,
            ]);
        }

        if (empty($cvPath) || empty($transcriptPath) || empty($idCardPath)) {
            return redirect()->back()->with('error', 'Berkas personal (CV, Transkrip Nilai, dan KTM) wajib lengkap sebelum mengajukan ulang ke kelompok.');
        }

        $newProposalPath = null;
        if ($request->hasFile('surat_pengantar')) {
            $newProposalPath = $storage->storeApplicationDocument($request->file('surat_pengantar'));
        }

        $finalProposalPath = $newProposalPath ?: ($application->proposal_letter_path ?: $group->proposal_letter_path);

        DB::transaction(function () use ($application, $group, $user, $cvPath, $transcriptPath, $idCardPath, $newProposalPath, $finalProposalPath, $request) {
            $application->update([
                'status' => 'pending',
                'rejection_note' => null,
                'rejection_reason' => null,
                'cv_path' => $cvPath,
                'transcript_path' => $transcriptPath,
                'id_card_path' => $idCardPath,
                'proposal_letter_path' => $finalProposalPath,
            ]);

            if ($finalProposalPath) {
                ApplicationDocument::updateOrCreate(
                    ['application_id' => $application->id, 'document_type' => 'Surat Pengantar'],
                    ['file_path' => $finalProposalPath]
                );
            }

            ApplicationDocument::updateOrCreate(
                ['application_id' => $application->id, 'document_type' => 'CV'],
                ['file_path' => $cvPath]
            );
            ApplicationDocument::updateOrCreate(
                ['application_id' => $application->id, 'document_type' => 'Transkrip Nilai'],
                ['file_path' => $transcriptPath]
            );
            ApplicationDocument::updateOrCreate(
                ['application_id' => $application->id, 'document_type' => 'KTM / Kartu Identitas'],
                ['file_path' => $idCardPath]
            );

            // Jika ketua mengunggah surat pengantar baru, perbarui juga surat pengantar kelompok
            if ($newProposalPath && ($application->role_in_group === 'leader' || (int) $group->leader_user_id === (int) $user->id)) {
                $group->update(['proposal_letter_path' => $newProposalPath]);
            }

            // Jika seluruh kelompok sebelumnya ditolak dan Ketua memilih mengajukan ulang seluruh kelompok bersama-sama
            if ($request->boolean('resubmit_entire_group') && ($application->role_in_group === 'leader' || (int) $group->leader_user_id === (int) $user->id)) {
                $rejectedSiblings = Application::where('group_id', $group->id)
                    ->where('id', '!=', $application->id)
                    ->where('status', 'rejected')
                    ->get();

                foreach ($rejectedSiblings as $sibling) {
                    $sibProfile = $sibling->user?->studentProfile;
                    $sibCv = $sibProfile?->cv_path ?: $sibling->cv_path;
                    $sibTranscript = $sibProfile?->transcript_path ?: $sibling->transcript_path;
                    $sibIdCard = $sibProfile?->id_card_path ?: $sibling->id_card_path;
                    $sibProposal = $newProposalPath ?: ($sibling->proposal_letter_path ?: $finalProposalPath);

                    $sibling->update([
                        'status' => 'pending',
                        'rejection_note' => null,
                        'rejection_reason' => null,
                        'cv_path' => $sibCv,
                        'transcript_path' => $sibTranscript,
                        'id_card_path' => $sibIdCard,
                        'proposal_letter_path' => $sibProposal,
                    ]);

                    if ($sibProposal) {
                        ApplicationDocument::updateOrCreate(
                            ['application_id' => $sibling->id, 'document_type' => 'Surat Pengantar'],
                            ['file_path' => $sibProposal]
                        );
                    }
                    if ($sibCv) {
                        ApplicationDocument::updateOrCreate(
                            ['application_id' => $sibling->id, 'document_type' => 'CV'],
                            ['file_path' => $sibCv]
                        );
                    }
                    if ($sibTranscript) {
                        ApplicationDocument::updateOrCreate(
                            ['application_id' => $sibling->id, 'document_type' => 'Transkrip Nilai'],
                            ['file_path' => $sibTranscript]
                        );
                    }
                    if ($sibIdCard) {
                        ApplicationDocument::updateOrCreate(
                            ['application_id' => $sibling->id, 'document_type' => 'KTM / Kartu Identitas'],
                            ['file_path' => $sibIdCard]
                        );
                    }
                }
            }

            // Jika status kelompok sebelumnya rejected atau draft, kembalikan ke pending
            if (in_array($group->status, ['rejected', 'draft'], true)) {
                $group->update([
                    'status' => 'pending',
                    'rejection_reason' => null,
                ]);
            }

            AuditLog::record('GROUP_MEMBER_RESUBMIT', 'Application', $application->id, [
                'group_id' => $group->id,
                'group_name' => $group->name,
                'student_name' => $user->name,
                'resubmit_entire_group' => $request->boolean('resubmit_entire_group'),
            ]);
        });

        return redirect()->route('student.application.create')
            ->with('success', "Perbaikan berkas berhasil dikirim! Anda telah kembali terdaftar aktif pada kelompok '{$group->name}' dan sedang menunggu verifikasi Admin Dinas.");
    }
}
