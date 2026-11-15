<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\AuditLog;
use App\Models\InternshipGroup;
use App\Models\Placement;
use App\Models\Unit;
use App\Models\University;
use App\Models\User;
use App\Services\Chat\ChatGroupService;
use App\Services\NotificationService;
use App\Services\PlacementAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ApplicationController extends Controller
{
    /**
     * Menampilkan semua daftar pengajuan magang masuk (dengan Multi-Tenant Scoping, Bypass Super Admin, Filter & Paginasi)
     */
    public function index(Request $request)
    {
        // Sinkronkan seluruh pengajuan magang yang tanggal mulainya telah tiba ke ACTIVE (on-demand batch sync)
        Application::syncStartedApplications();

        $user = Auth::user();
        $isSuperAdmin = $user->isSuperAdmin();
        $agencyId = $isSuperAdmin ? $request->agency_id : $user->agency_profile_id;

        $query = Application::with([
            'user.studentProfile',
            'user.universityRelation',
            'unit.agencyProfile',
            'group.applications.user.studentProfile',
            'group.applications.documents',
            'group.applications.placement.mentor',
            'group.leader.studentProfile',
            'documents',
            'placement.evaluation',
            'placement.finalreport',
            'placement.mentor',
            'placement.pembimbing',
            'placement.academicAdvisor',
        ]);

        // Urutan standar prioritas tindakan (lihat Application::actionPrioritySql): verifikasi → siap lulus →
        // pembimbing belum ada (antrean terlama dulu) → aktif → diterima → selesai → ditolak (terbaru dulu).
        // Sama persis dengan pusat kendali dinas & kampus.
        $query->orderByActionPriority();

        // Tampilkan 1 baris per pengajuan: pengajuan mandiri tampil mandiri, pengajuan kelompok diwakili ketua/baris utama kelompok
        $query->where(function ($q) {
            $q->whereNull('applications.group_id')
                ->orWhereRaw('applications.id = COALESCE(
                    (SELECT a_lead.id FROM applications a_lead WHERE a_lead.group_id = applications.group_id AND a_lead.role_in_group = \'leader\' ORDER BY a_lead.id ASC LIMIT 1),
                    (SELECT MIN(a_min.id) FROM applications a_min WHERE a_min.group_id = applications.group_id)
                )');
        });

        // Isolasi Bidang & Multi-Tenant:
        // Jika user adalah Admin Bidang, otomatis kunci hanya melihat pengajuan pada bidangnya sendiri
        if (! $isSuperAdmin && $user->unit_id) {
            $query->where('unit_id', $user->unit_id);
        } elseif ($agencyId) {
            // Admin dinas keseluruhan (unit_id null) melihat semua pengajuan pada instansinya
            $query->whereHas('unit', function ($q) use ($agencyId) {
                $q->where('agency_profile_id', $agencyId);
            });
        }

        // 1. Pencarian komprehensif berdasarkan Nama Mahasiswa, NIM, Universitas, atau Nama/Kode Kelompok
        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $like = \DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
            $query->where(function ($sq) use ($search, $like) {
                $sq->whereHas('user', function ($q) use ($search, $like) {
                    $q->where('name', $like, "%{$search}%")
                        ->orWhereHas('studentProfile', function ($spQuery) use ($search, $like) {
                            $spQuery->where('universitas', $like, "%{$search}%")
                                ->orWhere('nim', $like, "%{$search}%");
                        });
                })->orWhereHas('group', function ($gq) use ($search, $like) {
                    $gq->where('code', $like, "%{$search}%")
                        ->orWhere('name', $like, "%{$search}%")
                        ->orWhereHas('applications.user', function ($uq) use ($search, $like) {
                            $uq->where('name', $like, "%{$search}%")
                                ->orWhereHas('studentProfile', function ($spQuery) use ($search, $like) {
                                    $spQuery->where('universitas', $like, "%{$search}%")
                                        ->orWhere('nim', $like, "%{$search}%");
                                });
                        });
                });
            });
        }

        // 2. Filter Berdasarkan Status Pengajuan ('action' = semua yang butuh tindakan admin, tingkat 1–3)
        if ($request->filled('status')) {
            $statusVal = strtolower($request->status);
            if ($statusVal === 'action') {
                $query->where(function ($q) {
                    $q->requiringAction()
                        ->orWhereHas('group.applications', function ($gq) {
                            $gq->requiringAction();
                        });
                });
            } else {
                $query->where(function ($q) use ($statusVal) {
                    $q->where('applications.status', $statusVal)
                        ->orWhereHas('group.applications', function ($gq) use ($statusVal) {
                            $gq->where('status', $statusVal);
                        });
                });
            }
        }

        // 3. Filter Berdasarkan Unit / Divisi Kerja (Hanya jika Super Admin atau Admin Dinas Keseluruhan)
        if (! $isSuperAdmin && $user->unit_id) {
            $query->where('unit_id', $user->unit_id);
        } elseif ($request->filled('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        // 4. Filter Berdasarkan Universitas
        $selectedUniversity = null;
        if ($request->filled('university_id')) {
            $univId = $request->university_id;
            $univ = University::find($univId);
            $selectedUniversity = $univ;
            $like = \DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

            $query->whereHas('user', function ($uq) use ($univId, $univ) {
                $uq->where('university_id', $univId);
                if ($univ) {
                    $uq->orWhereRaw('LOWER(university) = ?', [mb_strtolower($univ->name)])
                        ->orWhereHas('studentProfile', fn ($sp) => $sp->where('university_id', $univId)->orWhereRaw('LOWER(universitas) = ?', [mb_strtolower($univ->name)]));
                }
            });
        }

        // 5. Filter Berdasarkan Tipe Pendaftaran (Individu vs Kelompok)
        if ($request->filled('registration_type')) {
            if ($request->registration_type === 'group') {
                $query->whereNotNull('applications.group_id');
            } elseif ($request->registration_type === 'individual') {
                $query->whereNull('applications.group_id');
            }
        }

        // Query Unit untuk Filter Dropdown (Scoped per bidang untuk Admin Bidang, scoped per dinas untuk Admin Dinas, all untuk Superadmin)
        if (! $isSuperAdmin && $user->unit_id) {
            $units = Unit::where('id', $user->unit_id)->get();
            $groupedUnits = null;
        } elseif ($agencyId) {
            $units = Unit::where('agency_profile_id', $agencyId)->get();
            $groupedUnits = null;
        } else {
            $units = Unit::with('agencyProfile')->get();
            $groupedUnits = $units->groupBy(function ($u) {
                return $u->agencyProfile->agency_name ?? 'Pemerintah Kota Surabaya';
            });
        }

        $agencies = AgencyProfile::all();
        $universities = University::orderBy('name')->get();

        // Query Pembimbing Lapangan untuk modal aksi
        $pembimbingQuery = User::whereIn('role', ['mentor', 'pembimbing'])->with('unit');
        if ($agencyId !== null) {
            $pembimbingQuery->where('agency_profile_id', $agencyId);
        }
        if (! $isSuperAdmin && $user->unit_id) {
            $pembimbingQuery->where('unit_id', $user->unit_id);
        }
        $pembimbings = $pembimbingQuery->orderBy('name')->get();

        // Paginasi 10 Data Per Halaman
        $applications = $query->paginate(10)->withQueryString();

        return view('admin.applications.index', compact(
            'applications',
            'units',
            'groupedUnits',
            'agencies',
            'universities',
            'selectedUniversity',
            'pembimbings',
            'isSuperAdmin',
            'agencyId'
        ));
    }

    /**
     * Detail pengajuan magang & dokumen
     */
    public function show($id)
    {
        $user = Auth::user();
        $isSuperAdmin = $user->isSuperAdmin();

        $application = Application::with([
            'user.studentProfile',
            'unit.agencyProfile',
            'group.applications.user.studentProfile',
            'documents',
            'placement.pembimbing',
            'placement.mentor',
            'placement.academicAdvisor',
            'placement.evaluation',
            'placement.finalreport',
            'placement.internshipProjects',
        ])->findOrFail($id);

        // Pemicu Otomatis 3: jika mahasiswa sudah mencapai tanggal mulai (today >= start_date) dan belum berstatus active, sistem langsung memperbarui statusnya secara on-the-fly
        $application->autoActivateIfStarted();

        // Multi-Tenant & Bidang Authorization Check
        if (! $isSuperAdmin && $user->agency_profile_id !== null && optional($application->unit)->agency_profile_id !== $user->agency_profile_id) {
            abort(403, 'Anda tidak memiliki hak akses ke data pengajuan instansi lain.');
        }

        if (! $isSuperAdmin && $user->unit_id && $application->unit_id !== $user->unit_id) {
            abort(403, 'Anda tidak memiliki hak akses ke data pengajuan bidang lain.');
        }

        // Dropdown 'Pilih Pembimbing Lapangan' HANYA memuat user role 'mentor' yang terdaftar di instansi yang bersangkutan
        $targetAgencyId = $application->unit?->agency_profile_id ?? $user?->agency_profile_id;
        $pembimbingQuery = User::whereIn('role', ['mentor', 'pembimbing'])->with('unit');

        if ($targetAgencyId !== null) {
            $pembimbingQuery->where('agency_profile_id', $targetAgencyId);
        }

        if (! $isSuperAdmin && $user->unit_id) {
            $pembimbingQuery->where('unit_id', $user->unit_id);
        }

        $pembimbings = $pembimbingQuery->orderBy('name')->get();

        // Dropdown Dosen Kampus untuk Super Admin Override
        $dosens = User::whereIn('role', ['dosen', 'academic_advisor'])
            ->when($application->user?->university_id, fn ($q) => $q->where('university_id', $application->user->university_id))
            ->orderBy('name')
            ->get();

        return view('admin.applications.show', compact('application', 'pembimbings', 'dosens', 'isSuperAdmin'));
    }

    /**
     * Update status pengajuan (Verifikasi / Seleksi / Quota Lifecycle)
     */
    public function updateStatus(Request $request, $id)
    {
        $user = Auth::user();
        $isSuperAdmin = $user->isSuperAdmin();
        $isSupervisor = $user->role === 'supervisor';

        $statusInput = strtolower($request->status);
        $request->merge(['status' => $statusInput]);

        if ($request->filled('rejection_reason') && ! $request->filled('rejection_note')) {
            $request->merge(['rejection_note' => $request->rejection_reason]);
        }

        $rules = [
            'status' => 'required|in:pending,waiting_approval,accepted,active,completed,rejected,resigned',
            'mentor_id' => 'nullable|exists:users,id',
            'pembimbing_id' => 'nullable|exists:users,id',
            'academic_advisor_id' => 'nullable|exists:users,id',
            'letter_number' => 'nullable|string|max:100',
            'letter_date' => 'nullable|date',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'override_reason' => 'nullable|string',
        ];

        // Validasi ketat: Alasan penolakan wajib diisi jika status diubah menjadi 'rejected'
        if ($statusInput === 'rejected') {
            $rules['rejection_note'] = 'required|string|min:5';
        } else {
            $rules['rejection_note'] = 'nullable|string';
        }

        $messages = [
            'rejection_note.required' => 'Alasan penolakan wajib diisi jika status diubah menjadi ditolak (rejected).',
            'rejection_note.min' => 'Alasan penolakan minimal 5 karakter agar informatif bagi pemohon.',
        ];

        $request->validate($rules, $messages);

        $application = Application::with(['unit', 'placement', 'user'])->findOrFail($id);
        $oldStatus = $application->status instanceof ApplicationStatus ? $application->status->value : strtolower((string) $application->status);
        $newStatus = $request->status;

        // Multi-Tenant & Bidang Authorization Check
        if (! $isSuperAdmin && $user->agency_profile_id !== null && optional($application->unit)->agency_profile_id !== $user->agency_profile_id) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah pengajuan instansi lain.');
        }

        if (! $isSuperAdmin && $user->unit_id && $application->unit_id !== $user->unit_id) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah pengajuan bidang lain.');
        }

        // Penguncian Otorisasi: Saat status berada di WAITING_APPROVAL, status ACCEPTED dan ACTIVE
        // HANYA dapat disahkan oleh Atasan (supervisor) atau super_admin
        if ($oldStatus === 'waiting_approval' && in_array($newStatus, ['accepted', 'active'], true) && ! $isSuperAdmin && ! $isSupervisor) {
            return redirect()->back()
                ->with('error', 'Status pengajuan saat ini sedang menunggu persetujuan Atasan. Persetujuan akhir (ACC) hanya dapat dilakukan oleh Atasan (Tim Kerja Umum dan Kepegawaian) atau Super Admin.')
                ->withInput();
        }

        // Alur Double ACC: Jika Admin Dinas staf biasa menyetujui pengajuan awal (dari pending),
        // otomatis arahkan status ke waiting_approval (Menunggu ACC Atasan)
        if (! $isSuperAdmin && ! $isSupervisor && $newStatus === 'accepted' && in_array($oldStatus, ['pending', 'waiting_approval'], true)) {
            $newStatus = ApplicationStatus::WAITING_APPROVAL->value;
        }

        // Validasi State Ketat: keputusan terima/ajukan hanya dari tahap seleksi (PENDING atau WAITING_APPROVAL)
        $decisionStatuses = ['pending', 'waiting_approval'];
        if (($newStatus === 'accepted' || $newStatus === 'waiting_approval') && ! in_array($oldStatus, [...$decisionStatuses, 'accepted', 'active'], true)) {
            return redirect()->back()
                ->with('error', "Gagal memproses pengajuan: Aksi persetujuan hanya dapat dilakukan pada pengajuan yang berstatus 'pending' atau 'waiting_approval'. Status saat ini: '{$oldStatus}'.")
                ->withInput();
        }

        if ($newStatus === 'rejected' && ! in_array($oldStatus, $decisionStatuses, true)) {
            return redirect()->back()
                ->with('error', "Gagal menolak pengajuan: Aksi penolakan (Reject) hanya dapat dilakukan pada pengajuan yang berstatus 'pending' atau 'waiting_approval'. Status saat ini: '{$oldStatus}'.")
                ->withInput();
        }

        if ($oldStatus === 'rejected' && $newStatus !== 'rejected') {
            return redirect()->back()
                ->with('error', 'Gagal mengubah status: Pengajuan yang telah ditolak tidak dapat diubah statusnya kembali.')
                ->withInput();
        }

        // Mentor & DPL yang dipilih wajib akun resmi: mentor dari instansi penempatan, DPL dari kampus mahasiswa
        try {
            $assignmentService = app(PlacementAssignmentService::class);
            $chosenMentorId = $request->mentor_id ?? $request->pembimbing_id;
            if ($chosenMentorId) {
                $assignmentService->resolveMentor($application, (int) $chosenMentorId);
            }
            if ($request->filled('academic_advisor_id')) {
                $assignmentService->resolveAdvisor($application, (int) $request->academic_advisor_id);
            }
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput()
                ->with('error', collect($e->errors())->flatten()->first());
        }

        $unit = $application->unit;

        // Strict Validation for COMPLETED status
        if ($newStatus === 'completed' && $oldStatus !== 'completed') {
            if (! $application->can_complete) {
                $missing = [];
                if (! $application->has_filled_logbook) {
                    $missing[] = 'Logbook aktivitas magang belum pernah diisi';
                }
                if (! $application->has_approved_report) {
                    $missing[] = 'Laporan Akhir belum disetujui';
                }
                if (! $application->has_complete_evaluation) {
                    $missing[] = 'Penilaian (Mentor & Dosen Pembimbing) belum tuntas diisi';
                }
                $missingStr = implode(' dan ', $missing);

                return redirect()->back()->with('error', "Gagal menyelesaikan magang: {$missingStr}.");
            }
        }

        // Time-Aware Quota Lifecycle Engine: Cek irisan tanggal jika status diterima / diaktifkan
        if (in_array($newStatus, ['accepted', 'active']) && ! in_array($oldStatus, ['accepted', 'active'])) {
            if ($unit) {
                $overlappingInterns = $unit->applications()
                    ->whereIn('status', ['accepted', 'active'])
                    ->where('id', '!=', $application->id)
                    ->where(function ($query) use ($application) {
                        $query->where('start_date', '<=', $application->end_date)
                            ->where('end_date', '>=', $application->start_date);
                    })->count();

                if ($overlappingInterns >= $unit->quota) {
                    return redirect()->back()->with('error', "Gagal menerima/mengaktifkan pengajuan: Kuota unit kerja '{$unit->name}' penuh pada rentang tanggal magang pemohon (Kapasitas: {$unit->quota}, Terisi: {$overlappingInterns} pada rentang tersebut).");
                }
            }
        }

        $year = date('Y');
        $paddedId = str_pad($application->id, 3, '0', STR_PAD_LEFT);
        $groupRef = $application->group_id ? InternshipGroup::find($application->group_id) : null;
        $autoLetterNumber = ($groupRef && $groupRef->letter_number) ? $groupRef->letter_number : "500.12.1/{$paddedId}/436.7.14/{$year}";
        $letterToken = $application->letter_token ?: Str::random(32);
        $assignedMentorId = $request->mentor_id ?? $request->pembimbing_id;
        if (! $assignedMentorId && $groupRef) {
            $siblingPlacementWithMentor = Placement::whereHas('application', function ($q) use ($groupRef) {
                $q->where('group_id', $groupRef->id);
            })->whereNotNull('mentor_id')->first();
            if ($siblingPlacementWithMentor) {
                $assignedMentorId = $siblingPlacementWithMentor->mentor_id;
            }
        }
        $placementEligibleStatuses = ['waiting_approval', 'accepted', 'active', 'completed'];

        // Auto-Elevate: jika status disetujui (accepted) namun tanggal mulai magang sudah tiba (start_date <= today), langsung aktifkan ke ACTIVE
        $today = now()->toDateString();
        $effectiveStartDate = $request->start_date ?: ($application->start_date ?: ($groupRef?->start_date ? $groupRef->start_date->toDateString() : null));
        $effectiveEndDate = $request->end_date ?: ($application->end_date ?: ($groupRef?->end_date ? $groupRef->end_date->toDateString() : null));

        if ($newStatus === ApplicationStatus::ACCEPTED->value
            && $effectiveStartDate
            && $effectiveStartDate <= $today
            && (! $effectiveEndDate || $effectiveEndDate >= $today)) {
            $newStatus = ApplicationStatus::ACTIVE->value;
        }

        DB::transaction(function () use ($application, $newStatus, $oldStatus, $request, $autoLetterNumber, $letterToken, $paddedId, $year, $unit, $assignedMentorId, $isSuperAdmin, $placementEligibleStatuses, $user) {
            $appUpdateData = [
                'status' => $newStatus,
                'rejection_note' => $newStatus === 'rejected' ? ($request->rejection_reason ?? $request->rejection_note) : null,
                'rejection_reason' => $newStatus === 'rejected' ? ($request->rejection_reason ?? $request->rejection_note) : null,
                'letter_number' => in_array($newStatus, $placementEligibleStatuses) ? ($request->letter_number ?: ($application->letter_number ?: $autoLetterNumber)) : null,
                'letter_date' => in_array($newStatus, $placementEligibleStatuses) ? ($request->letter_date ?: ($application->letter_date ?: date('Y-m-d'))) : null,
                'letter_token' => in_array($newStatus, $placementEligibleStatuses) ? $letterToken : $application->letter_token,
            ];

            if ($request->filled('start_date')) {
                $appUpdateData['start_date'] = $request->start_date;
            }
            if ($request->filled('end_date')) {
                $appUpdateData['end_date'] = $request->end_date;
            }

            $application->update($appUpdateData);

            $placementData = [];
            if ($assignedMentorId) {
                $placementData['mentor_id'] = $assignedMentorId;
                $placementData['pembimbing_id'] = $assignedMentorId;
            }

            if ($request->filled('academic_advisor_id')) {
                $placementData['academic_advisor_id'] = $request->academic_advisor_id;
            }

            $existingPlacement = Placement::where('application_id', $application->id)->first();
            $oldMentor = $existingPlacement?->mentor ?? $existingPlacement?->pembimbing;
            $oldAdvisor = $existingPlacement?->academicAdvisor;

            if (in_array($newStatus, $placementEligibleStatuses)) {
                $placementData['certificate_hash'] = $existingPlacement?->certificate_hash ?: Str::random(32);
                $placementData['certificate_number'] = $existingPlacement?->certificate_number ?: "SERT/{$paddedId}/PEMKOT-SBY/{$year}";
            }

            $reassignChanges = [];
            if ($assignedMentorId && (int) ($existingPlacement?->mentor_id ?? 0) !== (int) $assignedMentorId) {
                $newMentorObj = User::find($assignedMentorId);
                $reassignChanges['mentor'] = [
                    'from' => $oldMentor?->name,
                    'to' => $newMentorObj?->name ?? (string) $assignedMentorId,
                ];
            }

            if ($request->filled('academic_advisor_id')) {
                $newAdvisorId = (int) $request->academic_advisor_id;
                if ((int) ($existingPlacement?->academic_advisor_id ?? 0) !== $newAdvisorId) {
                    $newAdvisorObj = User::find($newAdvisorId);
                    $reassignChanges['academic_advisor'] = [
                        'from' => $oldAdvisor?->name,
                        'to' => $newAdvisorObj?->name ?? (string) $newAdvisorId,
                    ];
                }
            }

            // Anti-Reset: gunakan update() pada instance yang sudah ada agar ID, logbook, absensi, dan laporan akhir tetap utuh
            if ($existingPlacement) {
                if (! empty($placementData)) {
                    $existingPlacement->update($placementData);
                }
            } elseif (! empty($placementData) || in_array($newStatus, $placementEligibleStatuses)) {
                $existingPlacement = Placement::create(array_merge(
                    ['application_id' => $application->id],
                    $placementData
                ));
            }

            // Sinkronkan seluruh anggota kelompok jika ini adalah pengajuan kelompok
            if ($application->group_id) {
                $group = InternshipGroup::find($application->group_id);
                if ($group) {
                    $defaultSync = $newStatus !== ApplicationStatus::RESIGNED->value;
                    $shouldSyncSiblings = $request->boolean('sync_group', $defaultSync);

                    if ($shouldSyncSiblings) {
                        $siblingQuery = Application::where('group_id', $group->id)
                            ->where('id', '!=', $application->id);

                        // Jika keputusan kelompok adalah menyetujui/melanjutkan (bukan menolak seluruh kelompok),
                        // jangan timpa anggota yang sedang ditolak berkasnya (rejected/resigned), dan jangan
                        // men-downgrade anggota yang statusnya sudah lebih maju (mis. sudah accepted/active/completed).
                        if ($newStatus === ApplicationStatus::WAITING_APPROVAL->value) {
                            $siblingQuery->whereIn('status', ['pending', 'waiting_approval']);
                        } elseif ($newStatus === ApplicationStatus::ACCEPTED->value || $newStatus === ApplicationStatus::ACTIVE->value) {
                            $siblingQuery->whereIn('status', ['pending', 'waiting_approval', 'accepted']);
                        } elseif ($newStatus !== 'rejected') {
                            $siblingQuery->whereNotIn('status', ['rejected', 'resigned', 'completed']);
                        } else {
                            $siblingQuery->whereNotIn('status', ['active', 'completed', 'resigned']);
                        }

                        $siblingApps = $siblingQuery->get();

                        foreach ($siblingApps as $sibling) {
                            $siblingUpdateData = $appUpdateData;
                            $siblingUpdateData['letter_token'] = $sibling->letter_token ?: Str::random(32);
                            $sibling->update($siblingUpdateData);

                            if ($assignedMentorId || in_array($newStatus, $placementEligibleStatuses)) {
                                $sibPlacement = Placement::where('application_id', $sibling->id)->first();
                                $sibPaddedId = str_pad($sibling->id, 3, '0', STR_PAD_LEFT);
                                $sibPlacementData = $placementData;
                                if (in_array($newStatus, $placementEligibleStatuses)) {
                                    $sibPlacementData['certificate_hash'] = $sibPlacement?->certificate_hash ?: Str::random(32);
                                    $sibPlacementData['certificate_number'] = $sibPlacement?->certificate_number ?: "SERT/{$sibPaddedId}/PEMKOT-SBY/{$year}";
                                }
                                if ($sibPlacement) {
                                    $sibPlacement->update($sibPlacementData);
                                } else {
                                    $sibPlacementData['application_id'] = $sibling->id;
                                    Placement::create($sibPlacementData);
                                }
                            }
                        }

                        // Jika admin memperbarui mentor untuk seluruh kelompok, sinkronkan juga mentor ke anggota yang sudah accepted/active
                        if ($assignedMentorId && $newStatus !== 'rejected') {
                            $alreadyApprovedSiblings = Application::where('group_id', $group->id)
                                ->where('id', '!=', $application->id)
                                ->whereIn('status', ['accepted', 'active'])
                                ->get();
                            foreach ($alreadyApprovedSiblings as $approvedSibling) {
                                Placement::where('application_id', $approvedSibling->id)->update([
                                    'mentor_id' => $assignedMentorId,
                                    'pembimbing_id' => $assignedMentorId,
                                ]);
                            }
                        }
                    }

                    $this->syncAggregateGroupStatus(
                        $group,
                        $newStatus,
                        $appUpdateData['rejection_reason'] ?? null,
                        $appUpdateData['letter_number'] ?? null,
                        $appUpdateData['letter_date'] ?? null,
                        $letterToken
                    );
                }
            }

            // Catat audit log khusus mutasi/pergantian pembimbing jika ada perubahan
            if (! empty($reassignChanges) && $existingPlacement) {
                AuditLog::record('PLACEMENT_SUPERVISOR_REASSIGN', 'Placement', $existingPlacement->id, [
                    'student_name' => $application->user?->name,
                    'application_status' => $newStatus,
                    'changes' => $reassignChanges,
                    'actor_role' => $user->role,
                ]);
            }

            // Sinkronkan grup bimbingan chat jika mentor ditetapkan/berganti
            if ($assignedMentorId && (! $oldMentor || (int) $oldMentor->id !== (int) $assignedMentorId)) {
                try {
                    $chatGroups = app(ChatGroupService::class);
                    if ($oldMentor) {
                        $chatGroups->syncMentorGroup($oldMentor);
                    }
                    if ($newMentor = User::find($assignedMentorId)) {
                        $chatGroups->syncMentorGroup($newMentor);
                    }
                } catch (\Throwable $e) {
                    report($e);
                }
            }

            // Sinkronkan grup bimbingan chat jika Dosen Pembimbing ditetapkan/berganti
            if ($request->filled('academic_advisor_id')) {
                $newAdvisorId = (int) $request->academic_advisor_id;
                if (! $oldAdvisor || (int) $oldAdvisor->id !== $newAdvisorId) {
                    try {
                        $chatGroups = app(ChatGroupService::class);
                        if ($oldAdvisor) {
                            $chatGroups->syncDplGroup($oldAdvisor);
                        }
                        if ($newAdvisor = User::find($newAdvisorId)) {
                            $chatGroups->syncDplGroup($newAdvisor);
                        }
                    } catch (\Throwable $e) {
                        report($e);
                    }
                }
            }

            // Catat Audit Trail
            AuditLog::record('APPLICATION_STATUS_UPDATE', 'Application', $application->id, [
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'student_name' => $application->user?->name,
                'unit_name' => $unit?->name,
                'assigned_mentor_id' => $assignedMentorId,
                'override_reason' => $request->override_reason,
                'is_super_admin' => $isSuperAdmin,
            ]);

            // Kirim notifikasi jika pengajuan diteruskan ke Atasan (Double ACC)
            if ($newStatus === ApplicationStatus::WAITING_APPROVAL->value) {
                NotificationService::notifySupervisorForApplication($application);
            }
        });

        // Sinkronkan status kelompok jika pengajuan memiliki grup
        if ($application->group_id) {
            $group = InternshipGroup::with('applications')->find($application->group_id);
            if ($group) {
                $allStatuses = $group->applications->map(fn ($a) => $a->statusValue());
                $nonRejectedStatuses = $allStatuses->reject(fn ($s) => in_array($s, ['rejected', 'resigned'], true))->unique();
                if ($nonRejectedStatuses->isEmpty()) {
                    $group->update(['status' => $allStatuses->contains('rejected') ? 'rejected' : ($allStatuses->first() ?: 'rejected')]);
                } elseif ($nonRejectedStatuses->count() === 1) {
                    $group->update(['status' => $nonRejectedStatuses->first()]);
                } elseif ($nonRejectedStatuses->contains('active')) {
                    $group->update(['status' => 'active']);
                } elseif ($nonRejectedStatuses->contains('accepted')) {
                    $group->update(['status' => 'accepted']);
                } elseif ($nonRejectedStatuses->contains('waiting_approval')) {
                    $group->update(['status' => 'waiting_approval']);
                } elseif ($nonRejectedStatuses->contains('pending')) {
                    $group->update(['status' => 'pending']);
                }
            }
        }

        if ($request->filled('return_to')) {
            return redirect()->to($request->input('return_to'))->with('success', 'Status pengajuan & penempatan berhasil diperbarui!');
        }

        return redirect()->route('admin.applications.index')->with('success', 'Status pengajuan & penempatan berhasil diperbarui!');
    }

    /**
     * Update status kelompok magang secara serentak (Aksi Langsung Sekelompok)
     */
    public function updateGroupStatus(Request $request, $id)
    {
        $user = Auth::user();
        $isSuperAdmin = $user->isSuperAdmin();
        $group = InternshipGroup::with(['applications.user', 'unit'])->findOrFail($id);

        if (! $isSuperAdmin && $user->agency_profile_id !== null && optional($group->unit)->agency_profile_id !== $user->agency_profile_id) {
            abort(403, 'Anda tidak memiliki hak akses ke data pengajuan instansi lain.');
        }

        if (! $isSuperAdmin && $user->unit_id && $group->unit_id !== $user->unit_id) {
            abort(403, 'Anda tidak memiliki hak akses ke data pengajuan bidang lain.');
        }

        $request->validate([
            'status' => 'required|in:pending,waiting_approval,accepted,active,completed,rejected',
            'rejection_note' => 'nullable|string',
            'mentor_id' => 'nullable|exists:users,id',
        ]);

        if ($request->status === 'rejected' && empty($request->rejection_note)) {
            return redirect()->back()->with('error', 'Alasan penolakan wajib diisi jika menolak pengajuan kelompok.');
        }

        $newStatus = $request->status;
        if ($newStatus === 'accepted' && ! $isSuperAdmin && ! ($user->role === 'supervisor')) {
            $newStatus = ApplicationStatus::WAITING_APPROVAL->value;
        }

        // Auto-Elevate Kelompok: jika status disetujui (accepted) namun tanggal mulai magang kelompok sudah tiba (start_date <= today), langsung aktifkan ke ACTIVE
        $today = now()->toDateString();
        $groupStartDate = $group->start_date ? $group->start_date->toDateString() : null;
        $groupEndDate = $group->end_date ? $group->end_date->toDateString() : null;
        if ($newStatus === ApplicationStatus::ACCEPTED->value && $groupStartDate && $groupStartDate <= $today && (! $groupEndDate || $groupEndDate >= $today)) {
            $newStatus = ApplicationStatus::ACTIVE->value;
        }

        $targetApplications = $group->applications;
        if ($newStatus === ApplicationStatus::WAITING_APPROVAL->value) {
            $targetApplications = $group->applications->filter(fn ($a) => in_array($a->statusValue(), ['pending', 'waiting_approval'], true));
            if ($targetApplications->isEmpty() && ! $request->filled('mentor_id')) {
                return redirect()->back()->with('error', 'Tidak ada anggota berstatus menunggu verifikasi yang dapat diajukan.');
            }
        } elseif (in_array($newStatus, [ApplicationStatus::ACCEPTED->value, ApplicationStatus::ACTIVE->value], true)) {
            $targetApplications = $group->applications->filter(fn ($a) => in_array($a->statusValue(), ['pending', 'waiting_approval', 'accepted'], true));
            if ($targetApplications->isEmpty() && ! $request->filled('mentor_id')) {
                return redirect()->back()->with('error', 'Seluruh anggota dalam kelompok ini berstatus ditolak/mengundurkan diri. Tidak ada anggota aktif yang dapat disetujui.');
            }
        } elseif ($newStatus !== 'rejected') {
            $targetApplications = $group->applications->reject(fn ($a) => in_array($a->statusValue(), ['rejected', 'resigned'], true));
            if ($targetApplications->isEmpty()) {
                return redirect()->back()->with('error', 'Seluruh anggota dalam kelompok ini berstatus ditolak/mengundurkan diri. Tidak ada anggota aktif yang dapat disetujui.');
            }
        } else {
            $targetApplications = $group->applications->reject(fn ($a) => in_array($a->statusValue(), ['active', 'completed', 'resigned'], true));
        }

        // Sama seperti jalur perorangan & massal: mentor harus akun mentor resmi dari instansi penempatan
        // setiap anggota, karena penempatan anggota yang sudah disetujui ikut diperbarui di bawah.
        if ($request->filled('mentor_id') && $newStatus !== 'rejected') {
            $assignmentService = app(PlacementAssignmentService::class);
            foreach ($group->applications as $memberApplication) {
                $assignmentService->resolveMentor($memberApplication, (int) $request->mentor_id);
            }
        }

        $year = date('Y');
        $letterDate = date('Y-m-d');
        $letterToken = $group->letter_token ?: Str::random(32);

        DB::transaction(function () use ($group, $targetApplications, $newStatus, $request, $year, $letterDate, $letterToken) {
            foreach ($targetApplications as $app) {
                $paddedId = str_pad($app->id, 3, '0', STR_PAD_LEFT);
                $autoLetterNumber = $group->letter_number ?: "500.12.1/{$paddedId}/436.7.14/{$year}";

                $app->update([
                    'status' => $newStatus,
                    'rejection_note' => $newStatus === 'rejected' ? $request->rejection_note : null,
                    'rejection_reason' => $newStatus === 'rejected' ? $request->rejection_note : null,
                    'letter_number' => in_array($newStatus, ['waiting_approval', 'accepted', 'active']) ? ($app->letter_number ?: $autoLetterNumber) : null,
                    'letter_date' => in_array($newStatus, ['waiting_approval', 'accepted', 'active']) ? ($app->letter_date ?: $letterDate) : null,
                    'letter_token' => in_array($newStatus, ['waiting_approval', 'accepted', 'active']) ? ($app->letter_token ?: Str::random(32)) : $app->letter_token,
                ]);

                if (in_array($newStatus, ['waiting_approval', 'accepted', 'active'])) {
                    $existingPlacement = Placement::where('application_id', $app->id)->first();
                    $placementData = [
                        'certificate_hash' => $existingPlacement?->certificate_hash ?: Str::random(32),
                        'certificate_number' => $existingPlacement?->certificate_number ?: "SERT/{$paddedId}/PEMKOT-SBY/{$year}",
                    ];
                    if ($request->filled('mentor_id')) {
                        $placementData['mentor_id'] = $request->mentor_id;
                        $placementData['pembimbing_id'] = $request->mentor_id;
                    }
                    if ($existingPlacement) {
                        $existingPlacement->update($placementData);
                    } else {
                        Placement::create(array_merge(['application_id' => $app->id], $placementData));
                    }
                }

                AuditLog::record('GROUP_APPLICATION_STATUS_UPDATE', 'Application', $app->id, [
                    'group_id' => $group->id,
                    'group_name' => $group->name,
                    'new_status' => $newStatus,
                    'student_name' => $app->user?->name,
                ]);

                if ($newStatus === ApplicationStatus::WAITING_APPROVAL->value) {
                    NotificationService::notifySupervisorForApplication($app);
                }
            }

            if ($request->filled('mentor_id') && $newStatus !== 'rejected') {
                $alreadyApproved = Application::where('group_id', $group->id)
                    ->whereIn('status', ['accepted', 'active'])
                    ->pluck('id');
                if ($alreadyApproved->isNotEmpty()) {
                    Placement::whereIn('application_id', $alreadyApproved)->update([
                        'mentor_id' => $request->mentor_id,
                        'pembimbing_id' => $request->mentor_id,
                    ]);
                }
            }

            $this->syncAggregateGroupStatus(
                $group,
                $newStatus,
                $newStatus === 'rejected' ? $request->rejection_note : null,
                $group->letter_number,
                in_array($newStatus, ['waiting_approval', 'accepted', 'active']) ? ($group->letter_date ?: $letterDate) : null,
                in_array($newStatus, ['waiting_approval', 'accepted', 'active']) ? $letterToken : $group->letter_token
            );
        });

        return redirect()->back()->with('success', 'Status kelompok dan seluruh anggotanya berhasil diperbarui!');
    }

    /**
     * Update status pengajuan masal (Bulk Approve / Reject)
     */
    public function bulkUpdateStatus(Request $request)
    {
        $user = Auth::user();
        $isSuperAdmin = $user->isSuperAdmin();

        $request->validate([
            'application_ids' => 'required|array',
            'application_ids.*' => 'exists:applications,id',
            'bulk_action' => 'required|in:accepted,rejected',
            'bulk_rejection_note' => 'required_if:bulk_action,rejected',
            'bulk_mentor_id' => 'nullable|exists:users,id',
        ], [
            'application_ids.required' => 'Pilih minimal satu pengajuan untuk diproses.',
            'bulk_rejection_note.required_if' => 'Alasan penolakan wajib diisi jika menolak pengajuan secara massal.',
        ]);

        $action = $request->bulk_action;
        $applicationIds = array_values(array_unique((array) $request->application_ids));
        $successCount = 0;
        $failCount = 0;
        $year = date('Y');
        $processedGroupIds = [];

        $applications = Application::with(['unit', 'placement', 'user', 'group.applications'])->whereIn('id', $applicationIds)->get();

        foreach ($applications as $application) {
            // Jika grup sudah diproses dalam batch yang sama, lewati tanpa menghitung gagal
            if ($application->group_id && in_array($application->group_id, $processedGroupIds, true)) {
                continue;
            }

            // Multi-Tenant Check
            if (! $isSuperAdmin && $user->agency_profile_id !== null && optional($application->unit)->agency_profile_id !== $user->agency_profile_id) {
                $failCount++;

                continue;
            }

            // Bidang Isolation Check: Admin bidang hanya boleh memproses pengajuan bidangnya
            if (! $isSuperAdmin && $user->unit_id && $application->unit_id !== $user->unit_id) {
                $failCount++;

                continue;
            }

            $oldStatus = $application->status instanceof ApplicationStatus ? $application->status->value : strtolower((string) $application->status);

            // Jika baris utama kelompok (ketua) sudah diproses/ditolak, namun masih ada anggota kelompok yang pending/waiting_approval, arahkan ke anggota tersebut
            if (! in_array($oldStatus, ['pending', 'waiting_approval'], true) && $application->group) {
                $pendingMember = $application->group->applications
                    ->first(fn ($m) => in_array($m->statusValue(), ['pending', 'waiting_approval'], true));
                if ($pendingMember) {
                    $application = Application::with(['unit', 'placement', 'user', 'group.applications'])->find($pendingMember->id) ?? $application;
                    $oldStatus = $application->statusValue();
                }
            }

            // Hanya bisa terima/tolak jika status pending atau waiting_approval
            if (! in_array($oldStatus, ['pending', 'waiting_approval'], true)) {
                $failCount++;

                continue;
            }

            $unit = $application->unit;

            // Kuota Check jika accepted
            if ($action === 'accepted') {
                if ($unit) {
                    $groupSize = $application->group
                        ? max(1, $application->group->applications()->whereIn('status', ['pending', 'waiting_approval'])->count())
                        : 1;
                    $overlappingInterns = $unit->applications()
                        ->whereIn('status', ['accepted', 'active'])
                        ->where('id', '!=', $application->id)
                        ->when($application->start_date && $application->end_date, function ($query) use ($application) {
                            $query->where('start_date', '<=', $application->end_date)
                                ->where('end_date', '>=', $application->start_date);
                        })->count();

                    if (($overlappingInterns + $groupSize) > $unit->quota) {
                        $failCount++;

                        continue;
                    }
                }
            }

            $paddedId = str_pad($application->id, 3, '0', STR_PAD_LEFT);
            $autoLetterNumber = "500.12.1/{$paddedId}/436.7.14/{$year}";
            $letterToken = $application->letter_token ?: Str::random(32);
            $assignedMentorId = $request->bulk_mentor_id;

            // Mentor massal hanya untuk pengajuan di instansi yang sama dengan akun mentor tsb.
            if ($assignedMentorId) {
                try {
                    app(PlacementAssignmentService::class)->resolveMentor($application, (int) $assignedMentorId);
                } catch (ValidationException $e) {
                    $failCount++;

                    continue;
                }
            }

            // Alur Double ACC Massal: Admin Dinas staf biasa → waiting_approval → Atasan ACC akhir
            // Selaraskan dengan logika single-update di updateStatus()
            $effectiveAction = $action;
            if ($action === 'accepted' && ! $isSuperAdmin && ! ($user->role === 'supervisor')) {
                $effectiveAction = ApplicationStatus::WAITING_APPROVAL->value;
            }

            $today = now()->toDateString();
            if ($effectiveAction === ApplicationStatus::ACCEPTED->value
                && $application->start_date
                && $application->start_date <= $today
                && (! $application->end_date || $application->end_date >= $today)) {
                $effectiveAction = ApplicationStatus::ACTIVE->value;
            }

            DB::transaction(function () use ($application, $effectiveAction, $oldStatus, $request, $autoLetterNumber, $letterToken, $paddedId, $year, $assignedMentorId, $isSuperAdmin) {
                $application->update([
                    'status' => $effectiveAction,
                    'rejection_note' => $effectiveAction === 'rejected' ? $request->bulk_rejection_note : null,
                    'rejection_reason' => $effectiveAction === 'rejected' ? $request->bulk_rejection_note : null,
                    'letter_number' => in_array($effectiveAction, ['waiting_approval', 'accepted', 'active']) ? ($application->letter_number ?: $autoLetterNumber) : null,
                    'letter_date' => in_array($effectiveAction, ['waiting_approval', 'accepted', 'active']) ? ($application->letter_date ?: date('Y-m-d')) : null,
                    'letter_token' => in_array($effectiveAction, ['waiting_approval', 'accepted', 'active']) ? $letterToken : $application->letter_token,
                ]);

                if (in_array($effectiveAction, ['waiting_approval', 'accepted', 'active']) || $assignedMentorId) {
                    $placementData = [];
                    if ($assignedMentorId) {
                        $placementData['mentor_id'] = $assignedMentorId;
                        $placementData['pembimbing_id'] = $assignedMentorId;
                    }
                    if (in_array($effectiveAction, ['waiting_approval', 'accepted', 'active'])) {
                        $existingPlacement = Placement::where('application_id', $application->id)->first();
                        $placementData['certificate_hash'] = $existingPlacement?->certificate_hash ?: Str::random(32);
                        $placementData['certificate_number'] = $existingPlacement?->certificate_number ?: "SERT/{$paddedId}/PEMKOT-SBY/{$year}";
                    }

                    if (! empty($placementData)) {
                        $existingPlacement = Placement::where('application_id', $application->id)->first();
                        if ($existingPlacement) {
                            $existingPlacement->update($placementData);
                        } else {
                            Placement::create(array_merge(['application_id' => $application->id], $placementData));
                        }
                    }
                }

                // Sinkronkan seluruh anggota kelompok jika pengajuan kelompok diproses massal
                if ($application->group_id) {
                    $group = InternshipGroup::find($application->group_id);
                    if ($group) {
                        $siblingQuery = Application::where('group_id', $group->id)
                            ->where('id', '!=', $application->id);
                        if ($effectiveAction === ApplicationStatus::WAITING_APPROVAL->value) {
                            $siblingQuery->whereIn('status', ['pending', 'waiting_approval']);
                        } elseif (in_array($effectiveAction, [ApplicationStatus::ACCEPTED->value, ApplicationStatus::ACTIVE->value], true)) {
                            $siblingQuery->whereIn('status', ['pending', 'waiting_approval', 'accepted']);
                        } elseif ($effectiveAction !== 'rejected') {
                            $siblingQuery->whereNotIn('status', ['rejected', 'resigned', 'completed']);
                        } else {
                            $siblingQuery->whereNotIn('status', ['active', 'completed', 'resigned']);
                        }
                        $siblingApps = $siblingQuery->get();

                        foreach ($siblingApps as $sibling) {
                            $sibling->update([
                                'status' => $effectiveAction,
                                'rejection_note' => $effectiveAction === 'rejected' ? $request->bulk_rejection_note : null,
                                'rejection_reason' => $effectiveAction === 'rejected' ? $request->bulk_rejection_note : null,
                                'letter_number' => in_array($effectiveAction, ['waiting_approval', 'accepted', 'active']) ? ($sibling->letter_number ?: ($group->letter_number ?: $autoLetterNumber)) : null,
                                'letter_date' => in_array($effectiveAction, ['waiting_approval', 'accepted', 'active']) ? ($sibling->letter_date ?: date('Y-m-d')) : null,
                                'letter_token' => in_array($effectiveAction, ['waiting_approval', 'accepted', 'active']) ? ($sibling->letter_token ?: Str::random(32)) : $sibling->letter_token,
                            ]);

                            if (in_array($effectiveAction, ['waiting_approval', 'accepted', 'active']) || $assignedMentorId) {
                                $sibPlacement = Placement::where('application_id', $sibling->id)->first();
                                $sibPaddedId = str_pad($sibling->id, 3, '0', STR_PAD_LEFT);
                                $sibPlacementData = $placementData ?? [];
                                if (in_array($effectiveAction, ['waiting_approval', 'accepted', 'active'])) {
                                    $sibPlacementData['certificate_hash'] = $sibPlacement?->certificate_hash ?: Str::random(32);
                                    $sibPlacementData['certificate_number'] = $sibPlacement?->certificate_number ?: "SERT/{$sibPaddedId}/PEMKOT-SBY/{$year}";
                                }
                                if ($sibPlacement) {
                                    $sibPlacement->update($sibPlacementData);
                                } else {
                                    Placement::create(array_merge(['application_id' => $sibling->id], $sibPlacementData));
                                }
                            }
                        }

                        $this->syncAggregateGroupStatus(
                            $group,
                            $effectiveAction,
                            $effectiveAction === 'rejected' ? $request->bulk_rejection_note : null,
                            in_array($effectiveAction, ['waiting_approval', 'accepted', 'active']) ? ($group->letter_number ?: $autoLetterNumber) : null,
                            in_array($effectiveAction, ['waiting_approval', 'accepted', 'active']) ? ($group->letter_date ?: date('Y-m-d')) : null,
                            $group->letter_token ?: $letterToken
                        );
                    }
                }

                AuditLog::record('APPLICATION_STATUS_UPDATE_BULK', 'Application', $application->id, [
                    'old_status' => $oldStatus,
                    'new_status' => $effectiveAction,
                    'is_super_admin' => $isSuperAdmin,
                ]);

                // Kirim notifikasi ke Atasan jika status diteruskan ke waiting_approval (Double ACC)
                if ($effectiveAction === ApplicationStatus::WAITING_APPROVAL->value) {
                    NotificationService::notifySupervisorForApplication($application);
                }
            });

            if ($application->group_id) {
                $processedGroupIds[] = $application->group_id;
            }

            $successCount++;
        }

        if ($successCount > 0 && $failCount === 0) {
            return redirect()->route('admin.applications.index')->with('success', "Berhasil memperbarui $successCount pengajuan secara massal.");
        } elseif ($successCount > 0 && $failCount > 0) {
            return redirect()->route('admin.applications.index')->with('success', "Berhasil memperbarui $successCount pengajuan. Namun $failCount pengajuan gagal (mungkin kuota penuh atau status tidak valid).");
        } else {
            return redirect()->route('admin.applications.index')->with('error', 'Gagal memperbarui status secara massal. Pastikan kuota tersedia dan pengajuan berstatus pending.');
        }
    }

    /**
     * Sinkronisasi status agregat InternshipGroup berdasarkan status seluruh anggota.
     * Menjaga agar kelompok yang sebagian anggotanya sudah accepted/active tidak ter-downgrade
     * ke waiting_approval saat anggota susulan yang baru memperbaiki berkas diverifikasi.
     */
    private function syncAggregateGroupStatus(
        InternshipGroup $group,
        ?string $fallbackStatus = null,
        ?string $rejectionReason = null,
        ?string $letterNumber = null,
        ?string $letterDate = null,
        ?string $letterToken = null
    ): void {
        $allStatuses = Application::where('group_id', $group->id)
            ->pluck('status')
            ->map(fn ($s) => $s instanceof ApplicationStatus ? $s->value : strtolower((string) $s));

        $nonRejectedStatuses = $allStatuses->reject(fn ($s) => in_array($s, ['rejected', 'resigned'], true))->unique();

        if ($nonRejectedStatuses->isEmpty()) {
            $aggregateStatus = $allStatuses->contains('rejected')
                ? 'rejected'
                : ($allStatuses->first() ?: ($fallbackStatus ?: 'rejected'));
        } elseif ($nonRejectedStatuses->count() === 1) {
            $aggregateStatus = $nonRejectedStatuses->first();
        } elseif ($nonRejectedStatuses->contains('active')) {
            $aggregateStatus = 'active';
        } elseif ($nonRejectedStatuses->contains('accepted')) {
            $aggregateStatus = 'accepted';
        } elseif ($nonRejectedStatuses->contains('waiting_approval')) {
            $aggregateStatus = 'waiting_approval';
        } elseif ($nonRejectedStatuses->contains('pending')) {
            $aggregateStatus = 'pending';
        } elseif ($nonRejectedStatuses->contains('completed')) {
            $aggregateStatus = 'completed';
        } else {
            $aggregateStatus = $fallbackStatus ?: ($nonRejectedStatuses->first() ?: $group->status);
        }

        $groupUpdate = [
            'status' => $aggregateStatus,
            'rejection_reason' => $aggregateStatus === 'rejected' ? ($rejectionReason ?? $group->rejection_reason) : null,
        ];

        if ($letterNumber || $group->letter_number) {
            $groupUpdate['letter_number'] = $group->letter_number ?: $letterNumber;
        }
        if ($letterDate || $group->letter_date) {
            $groupUpdate['letter_date'] = $group->letter_date ?: $letterDate;
        }
        if ($letterToken || $group->letter_token) {
            $groupUpdate['letter_token'] = $group->letter_token ?: $letterToken;
        }

        $group->update($groupUpdate);
    }

    /**
     * Cetak / Pratinjau Surat Balasan Penerimaan untuk Admin
     */
    public function downloadLetter($id)
    {
        $user = Auth::user();
        $isSuperAdmin = $user->isSuperAdmin();

        $application = Application::with([
            'user.studentProfile',
            'unit.agencyProfile',
            'placement.pembimbing',
            'placement.mentor',
        ])
            ->whereIn('status', ['accepted', 'active', 'completed'])
            ->findOrFail($id);

        // Multi-Tenant Authorization Check
        if (! $isSuperAdmin && $user->agency_profile_id !== null && optional($application->unit)->agency_profile_id !== $user->agency_profile_id) {
            abort(403, 'Anda tidak memiliki hak akses ke surat pengajuan instansi lain.');
        }

        if (! $isSuperAdmin && $user->unit_id && $application->unit_id !== $user->unit_id) {
            abort(403, 'Anda tidak memiliki hak akses ke surat pengajuan bidang lain.');
        }

        return view('letters.acceptance', compact('application'));
    }

    /**
     * Pemicu manual sinkronisasi status magang (ACCEPTED -> ACTIVE jika tanggal mulai magang tiba)
     */
    public function manualSyncStatus(Request $request)
    {
        $activated = Application::syncStartedApplications();

        return back()->with('success', "Sinkronisasi status selesai. Sebanyak {$activated} pengajuan magang yang tanggal mulainya telah tiba berhasil diperbarui ke status ACTIVE.");
    }
}
