<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\InternshipGroup;
use App\Models\Unit;
use App\Models\User;
use App\Services\PrivateDocumentStorage;
use App\Support\UploadRules;
use Illuminate\Http\Request;
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

        // 2b. Cek apakah mahasiswa sedang tergabung dalam kelompok berstatus draft
        $draftGroup = InternshipGroup::with(['leader.studentProfile', 'unit.agencyProfile', 'applications.user.studentProfile'])
            ->where('status', 'draft')
            ->whereHas('applications', fn ($q) => $q->where('user_id', $user->id))
            ->first();

        // 3. Ambil seluruh riwayat pengajuan mahasiswa
        $applicationHistory = Application::with(['unit.agencyProfile', 'group'])
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $agencies = AgencyProfile::orderBy('agency_name')->get();

        return view('student.application.create', compact('units', 'groupedUnits', 'agencies', 'activeApplication', 'draftGroup', 'applicationHistory'));
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
                'completed' => 'Anda telah menyelesaikan program magang MBKM pada instansi sebelumnya.',
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
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'surat_pengantar' => 'required|'.UploadRules::APPLICATION_DOCUMENT,
            'cv' => 'nullable|'.UploadRules::APPLICATION_DOCUMENT,
            'transkrip' => 'nullable|'.UploadRules::APPLICATION_DOCUMENT,
            'id_card' => 'nullable|'.UploadRules::APPLICATION_ID_CARD,
        ], [
            'start_date.required' => 'Tanggal mulai magang wajib diisi.',
            'start_date.date' => 'Tanggal mulai magang tidak valid.',
            'end_date.required' => 'Tanggal selesai magang wajib diisi.',
            'end_date.after_or_equal' => 'Tanggal selesai magang harus setelah atau sama dengan tanggal mulai.',
            'surat_pengantar.required' => 'Surat Pengantar dari perguruan tinggi wajib diunggah.',
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

        $cvPath = $request->hasFile('cv')
            ? $storage->storeApplicationDocument($request->file('cv'))
            : $user->studentProfile->cv_path;

        $transcriptPath = $request->hasFile('transkrip')
            ? $storage->storeApplicationDocument($request->file('transkrip'))
            : $user->studentProfile->transcript_path;

        $idCardPath = $request->hasFile('id_card')
            ? $storage->storeApplicationDocument($request->file('id_card'))
            : $user->studentProfile->id_card_path;

        if ($request->hasFile('cv') || $request->hasFile('transkrip') || $request->hasFile('id_card')) {
            $user->studentProfile->update(array_filter([
                'cv_path' => $cvPath,
                'transcript_path' => $transcriptPath,
                'id_card_path' => $idCardPath,
            ]));
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

            return $this->createApplicationRecords($request, $proposalPath, $cvPath, $transcriptPath, $idCardPath);
        });

        if (! $application) {
            // Pengajuan ganda dibatalkan: jangan tinggalkan berkas yatim di disk privat
            $storage->discard([$proposalPath, $cvPath, $transcriptPath, $idCardPath]);

            return redirect()->route('student.application.create')
                ->with('error', 'Anda masih memiliki berkas pengajuan magang yang sedang diproses. Mohon tunggu proses verifikasi admin dinas.');
        }

        return redirect()->back()->with('success', 'Pengajuan magang dan dokumen persyaratan berhasil dikirim!');
    }

    /**
     * Buat record pengajuan + dokumen persyaratan (dipanggil di dalam transaksi).
     */
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
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'name' => 'nullable|string|max:100',
        ]);

        $unit = Unit::findOrFail($request->unit_id);
        if ($unit->remaining_quota <= 0) {
            return redirect()->back()->with('error', 'Kuota untuk Bidang ini sudah penuh.');
        }

        DB::transaction(function () use ($user, $request) {
            $group = InternshipGroup::create([
                'code' => InternshipGroup::generateUniqueCode(),
                'name' => $request->name ?: ('Kelompok ' . $user->name),
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
            return redirect()->back()->with('error', 'Kode kelompok tidak valid atau pendaftaran kelompok tersebut sudah dikirim/ditutup.');
        }

        // Validasi kesamaan kampus
        $leaderUnivId = $group->leader?->university_id;
        $userUnivId = $user->university_id;
        if ($leaderUnivId && $userUnivId && $leaderUnivId !== $userUnivId) {
            return redirect()->back()->with('error', 'Anda tidak dapat bergabung karena kelompok ini dikhususkan untuk mahasiswa dari ' . ($group->leader->university ?? 'kampus lain') . '.');
        }

        // Validasi batas maksimal 5 anggota
        $currentCount = $group->applications()->count();
        if ($currentCount >= 5) {
            return redirect()->back()->with('error', 'Jumlah anggota kelompok sudah mencapai batas maksimal (5 orang).');
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

    // Anggota keluar dari kelompok (sebelum disubmit)
    public function leaveGroup(Request $request)
    {
        $user = Auth::user();
        $application = Application::where('user_id', $user->id)
            ->where('status', 'draft')
            ->where('role_in_group', 'member')
            ->first();

        if (! $application) {
            return redirect()->back()->with('error', 'Anda tidak terdaftar sebagai anggota kelompok draft.');
        }

        $application->delete();

        return redirect()->route('student.application.create')
            ->with('success', 'Anda telah keluar dari kelompok magang.');
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
            'surat_pengantar' => 'required|' . UploadRules::APPLICATION_DOCUMENT,
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
}
