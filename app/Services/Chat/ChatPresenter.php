<?php

namespace App\Services\Chat;

use App\Models\Application;
use App\Models\ChatAttachment;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\ChatParticipant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Mengubah model chat menjadi array primitif untuk JSON / Alpine.
 * Jangan pernah mengirim model Eloquent utuh ke front-end (LRN-020).
 */
class ChatPresenter
{
    private const AVATAR_COLORS = ['#2563eb', '#0891b2', '#059669', '#7c3aed', '#db2777', '#ea580c', '#4f46e5', '#0d9488'];

    private const GROUP_COLOR = '#4f46e5';

    private const PLACEMENT_COLOR = '#059669';

    public function __construct(
        private ?ChatChannelService $channels = null,
    ) {
        $this->channels ??= app(ChatChannelService::class);
    }

    public function user(?User $user): array
    {
        if (! $user) {
            return [
                'id' => null, 'name' => 'Pengguna dihapus', 'initials' => '?', 'color' => '#94a3b8',
                'role_label' => '', 'role_group' => 'other', 'org' => '', 'inactive' => true,
                'online' => false, 'last_seen_at' => null,
            ];
        }

        return $this->brief($user) + [
            'role_label' => self::roleLabel($user),
            'role_group' => self::roleGroup($user),
            'org' => $this->organization($user),
            'inactive' => $user->isInactive(),
            'online' => self::isOnline($user->last_seen_at),
            'last_seen_at' => $user->last_seen_at?->toIso8601String(),
        ];
    }

    /**
     * Identitas ringkas pengirim pesan (tanpa relasi dinas/kampus agar tidak N+1).
     */
    public function brief(?User $user): array
    {
        if (! $user) {
            return ['id' => null, 'name' => 'Pengguna dihapus', 'initials' => '?', 'color' => '#94a3b8'];
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'initials' => $this->initials($user->name),
            'color' => self::AVATAR_COLORS[$user->id % count(self::AVATAR_COLORS)],
        ];
    }

    /**
     * Item daftar percakapan. $other = lawan bicara (khusus 1-on-1).
     */
    public function conversationSummary(ChatConversation $c, User $viewer, ChatParticipant $me, ?ChatParticipant $other, int $unread): array
    {
        $contact = $c->isDirect() ? $this->user($other?->user) : null;
        $last = $c->lastMessage;
        $lastIsMine = $last && (int) $last->sender_id === (int) $viewer->id;
        $stageBadge = $this->stageBadge($c, $viewer, $other?->user, $unread);

        if ($c->isChannel()) {
            $isGov = $c->scope_type === ChatConversation::SCOPE_GOVERNMENT;

            // Prioritaskan nama langsung dari entitas lembaga resmi agar ringkas dan tidak terpotong
            $title = $c->agencyProfile?->agency_name
                ?? $c->university?->name
                ?? ($c->agency_profile_id === null && $isGov ? 'Pemerintah Kota Surabaya' : (string) $c->title);

            if (str_starts_with(mb_strtolower($title), 'saluran pengumuman ')) {
                $title = trim(mb_substr($title, 19));
            }

            $scopeBadge = $isGov ? ($c->agency_profile_id ? 'Dinas' : 'Pemkot') : 'Kampus';

            return [
                'id' => $c->id,
                'type' => $c->type,
                'scope_type' => $c->scope_type,
                'scope_badge' => $scopeBadge,
                'url' => route('chat.show', $c->id, false),
                'title' => $title,
                'subtitle' => 'Saluran Pengumuman Resmi',
                'avatar' => [
                    'initials' => $this->initials($title),
                    'color' => $isGov ? ($c->agency_profile_id ? '#1d4ed8' : '#0284c7') : '#059669',
                    'group' => false,
                    'is_channel' => true,
                    'squircle' => true,
                    'logo_url' => $this->channels->channelLogoUrl($c),
                ],
                'contact' => null,
                'unread' => $unread,
                'muted' => $me->muted_at !== null,
                'pinned' => $me->pinned_at !== null,
                'stage_badge' => $stageBadge,
                'last_message' => $last ? [
                    'preview' => $last->preview(),
                    'is_mine' => $lastIsMine,
                    'system' => $last->isSystem(),
                    'sender_name' => (! $lastIsMine && ! $last->isSystem() && ! $last->isDeleted())
                        ? self::shortName($last->sender?->name) : null,
                    'created_at' => $last->created_at?->toIso8601String(),
                ] : null,
                'sort_at' => ($c->last_message_at ?? $c->created_at)?->toIso8601String(),
            ];
        }

        return [
            'id' => $c->id,
            'type' => $c->type,
            'scope_type' => $c->scope_type,
            'scope_badge' => null,
            'url' => route('chat.show', $c->id, false),
            'title' => $contact ? $contact['name'] : ($c->title ?: 'Grup'),
            'subtitle' => $contact
                ? implode(' · ', array_filter([$contact['role_label'], $contact['org']]))
                : ($c->isMentorGuidance()
                    ? 'Bimbingan Mentor · '.max(0, ((int) $c->participants_count) - 1).' mahasiswa'
                    : ($c->isDplGuidance()
                        ? 'Bimbingan Dosen · '.max(0, ((int) $c->participants_count) - 1).' mahasiswa'
                        : ($c->type === ChatConversation::TYPE_PLACEMENT ? 'Grup Bimbingan' : ((int) $c->participants_count).' anggota'))),
            'avatar' => $contact
                ? ['initials' => $contact['initials'], 'color' => $contact['color'], 'group' => false, 'is_channel' => false, 'squircle' => false, 'logo_url' => null]
                : ['initials' => $this->initials((string) $c->title), 'color' => ($c->isGuidanceGroup() || $c->type === ChatConversation::TYPE_PLACEMENT) ? self::PLACEMENT_COLOR : self::GROUP_COLOR, 'group' => true, 'is_channel' => false, 'squircle' => false, 'logo_url' => null],
            'contact' => $contact,
            'unread' => $unread,
            'muted' => $me->muted_at !== null,
            'pinned' => $me->pinned_at !== null,
            'stage_badge' => $stageBadge,
            'last_message' => $last ? [
                'preview' => $last->preview(),
                'is_mine' => $lastIsMine,
                'system' => $last->isSystem(),
                // Di grup, pratinjau diawali nama pengirim: "Budi: ..."
                'sender_name' => ($c->isGroup() && ! $lastIsMine && ! $last->isSystem() && ! $last->isDeleted())
                    ? self::shortName($last->sender?->name) : null,
                'created_at' => $last->created_at?->toIso8601String(),
            ] : null,
            'sort_at' => ($c->last_message_at ?? $c->created_at)?->toIso8601String(),
        ];
    }

    /**
     * Menghitung status tahapan alur magang & urgensi percakapan secara deterministik berbasis peran pengguna (Role-Based Smart Priority Engine).
     */
    public function stageBadge(ChatConversation $c, ?User $viewer = null, ?User $otherUser = null, int $unread = 0): ?array
    {
        // Fallback backward-compatibility jika pemanggil lama hanya mengirim ($c, $otherUser)
        if ($viewer !== null && $otherUser === null && func_num_args() === 2) {
            $otherUser = $viewer;
            $viewer = auth()->user();
        }
        $viewer = $viewer ?? auth()->user();

        // 0. Saluran Pengumuman Resmi (tidak memerlukan stage badge alur kerja magang)
        if ($c->isChannel()) {
            return null;
        }

        if (! $viewer) {
            return $this->genericStageBadge($c, $otherUser, $unread);
        }

        return match ($viewer->role) {
            'dosen', 'academic_advisor' => $this->dosenStageBadge($c, $viewer, $otherUser, $unread),
            'admin' => $this->adminDinasStageBadge($c, $viewer, $otherUser, $unread),
            'mentor', 'pembimbing' => $this->mentorStageBadge($c, $viewer, $otherUser, $unread),
            'universitas' => $this->universityStageBadge($c, $viewer, $otherUser, $unread),
            'mahasiswa' => $this->studentStageBadge($c, $viewer, $otherUser, $unread),
            'super_admin', 'admin_pusat' => $this->superAdminStageBadge($c, $viewer, $otherUser, $unread),
            default => $this->genericStageBadge($c, $otherUser, $unread),
        };
    }

    /**
     * Prioritas Dosen: Review Laporan Akhir (Pending/ACC/Revisi), Nilai Belum Keluar, Logbook Pending, Diskusi Bimbingan.
     */
    private function dosenStageBadge(ChatConversation $c, User $viewer, ?User $otherUser, int $unread): ?array
    {
        if ($c->isDplGuidance()) {
            if ($unread > 0) {
                return [
                    'code' => 'urgent',
                    'label' => 'Diskusi Grup',
                    'theme' => 'info',
                    'is_urgent' => true,
                    'role_group' => 'group',
                    'priority_rank' => 3,
                    'hint' => "Ada {$unread} pesan belum dibaca di grup bimbingan dosen",
                ];
            }

            return [
                'code' => 'guidance',
                'label' => 'Bimbingan Dosen',
                'theme' => 'info',
                'is_urgent' => false,
                'role_group' => 'group',
                'priority_rank' => 6,
                'hint' => 'Grup koordinasi bimbingan mahasiswa',
            ];
        }

        if ($c->isGroup()) {
            return [
                'code' => 'group',
                'label' => 'Grup Koordinasi',
                'theme' => 'neutral',
                'is_urgent' => $unread > 0,
                'role_group' => 'group',
                'priority_rank' => $unread > 0 ? 4 : 9,
                'hint' => 'Grup koordinasi internal',
            ];
        }

        if (! $otherUser) {
            return null;
        }

        if ($otherUser->role === 'mahasiswa') {
            $app = $otherUser->relationLoaded('applications')
                ? $otherUser->applications->first()
                : $otherUser->applications()->latest('created_at')->first();
            $placement = $app?->placement;

            $isMyStudent = $placement && (int) $placement->academic_advisor_id === (int) $viewer->id;

            if ($isMyStudent) {
                $finalReport = $placement->finalreport;
                $eval = $placement->evaluation;
                $logs = $placement->relationLoaded('logbooks')
                    ? $placement->logbooks
                    : $placement->logbooks()->get();

                // 1. Laporan Akhir
                if ($finalReport) {
                    $repStatus = strtolower((string) $finalReport->status);
                    if ($repStatus === 'revision') {
                        return [
                            'code' => 'urgent',
                            'label' => 'Revisi Laporan',
                            'theme' => 'warning',
                            'is_urgent' => true,
                            'role_group' => 'student',
                            'priority_rank' => 1,
                            'hint' => 'Mahasiswa sedang dalam proses revisi laporan akhir bimbingan',
                        ];
                    }

                    if (in_array($repStatus, ['pending', 'submitted'], true) || ($finalReport->file_path && $repStatus !== 'approved')) {
                        return [
                            'code' => 'urgent',
                            'label' => 'Perlu Review Laporan',
                            'theme' => 'urgent',
                            'is_urgent' => true,
                            'role_group' => 'student',
                            'priority_rank' => 1,
                            'hint' => 'Laporan akhir mahasiswa menunggu peninjauan/ACC dosen',
                        ];
                    }
                }

                // 2. Nilai Dosen Belum Keluar
                $hasDosenScore = $eval && ($eval->nilai_dosen_calculated > 0 || ($eval->nilai_dosen ?? 0) > 0 || ($eval->nilai_akademik ?? 0) > 0);
                $isEndingOrDone = in_array($app->statusValue(), ['completed', 'active'], true);
                if (! $hasDosenScore && $isEndingOrDone) {
                    $daysLeft = $this->daysRemaining($app);
                    if ($app->statusValue() === 'completed' || ($daysLeft !== null && $daysLeft <= 14)) {
                        return [
                            'code' => 'urgent',
                            'label' => 'Belum Dinilai',
                            'theme' => 'urgent',
                            'is_urgent' => true,
                            'role_group' => 'student',
                            'priority_rank' => 2,
                            'hint' => 'Nilai evaluasi akademik dosen belum diisi',
                        ];
                    }
                }

                // 3. Logbook Pending Validasi Dosen
                $pendingDosenLogs = $logs->where('lecturer_status', 'pending')->count();
                if ($pendingDosenLogs > 0) {
                    return [
                        'code' => 'urgent',
                        'label' => 'Logbook Pending',
                        'theme' => 'warning',
                        'is_urgent' => true,
                        'role_group' => 'student',
                        'priority_rank' => 3,
                        'hint' => "Ada {$pendingDosenLogs} logbook mahasiswa menunggu verifikasi dosen",
                    ];
                }

                // 4. Pesan belum dibaca dari mahasiswa bimbingan
                if ($unread > 0) {
                    return [
                        'code' => 'urgent',
                        'label' => 'Pesan Bimbingan',
                        'theme' => 'info',
                        'is_urgent' => true,
                        'role_group' => 'student',
                        'priority_rank' => 4,
                        'hint' => 'Pesan belum dibaca dari mahasiswa bimbingan',
                    ];
                }

                return [
                    'code' => 'active_student',
                    'label' => 'Mahasiswa Bimbingan',
                    'theme' => 'neutral',
                    'is_urgent' => false,
                    'role_group' => 'student',
                    'priority_rank' => 6,
                    'hint' => 'Mahasiswa bimbingan aktif',
                ];
            }

            return [
                'code' => 'student',
                'label' => 'Mahasiswa Kampus',
                'theme' => 'neutral',
                'is_urgent' => false,
                'role_group' => 'student',
                'priority_rank' => 8,
                'hint' => 'Mahasiswa dari kampus yang sama',
            ];
        }

        if ($unread > 0) {
            return [
                'code' => 'urgent',
                'label' => 'Pesan '.self::roleLabel($otherUser),
                'theme' => 'info',
                'is_urgent' => true,
                'role_group' => self::roleGroup($otherUser),
                'priority_rank' => 5,
                'hint' => 'Pesan belum dibaca dari '.self::roleLabel($otherUser),
            ];
        }

        return [
            'code' => 'staff',
            'label' => self::roleLabel($otherUser),
            'theme' => 'neutral',
            'is_urgent' => false,
            'role_group' => self::roleGroup($otherUser),
            'priority_rank' => 10,
            'hint' => self::roleLabel($otherUser),
        ];
    }

    /**
     * Prioritas Admin Dinas: Koordinasi Super Admin (Kota), Admin Kampus, Laporan Mentor Dinas. Mahasiswa TIDAK masuk prioritas.
     */
    private function adminDinasStageBadge(ChatConversation $c, User $viewer, ?User $otherUser, int $unread): ?array
    {
        // Mahasiswa TIDAK PERNAH masuk prioritas Admin Dinas (bukan tugas bimbingan)
        if ($otherUser && $otherUser->role === 'mahasiswa') {
            return [
                'code' => 'student',
                'label' => 'Mahasiswa',
                'theme' => 'neutral',
                'is_urgent' => false,
                'role_group' => 'student',
                'priority_rank' => 9,
                'hint' => 'Kontak mahasiswa (bimbingan ditangani mentor lapangan)',
            ];
        }

        if ($c->isGuidanceGroup()) {
            return [
                'code' => 'guidance',
                'label' => $c->isMentorGuidance() ? 'Bimbingan Mentor' : 'Bimbingan Dosen',
                'theme' => 'neutral',
                'is_urgent' => false,
                'role_group' => 'group',
                'priority_rank' => 8,
                'hint' => 'Grup koordinasi bimbingan teknis',
            ];
        }

        if ($c->isGroup()) {
            return [
                'code' => 'group',
                'label' => 'Grup Koordinasi',
                'theme' => 'neutral',
                'is_urgent' => $unread > 0,
                'role_group' => 'group',
                'priority_rank' => $unread > 0 ? 3 : 8,
                'hint' => 'Grup koordinasi internal',
            ];
        }

        if (! $otherUser) {
            return null;
        }

        // 1. Super Admin / Administrator Utama (Instruksi Kota)
        if (in_array($otherUser->role, ['super_admin', 'admin_pusat'], true)) {
            if ($unread > 0) {
                return [
                    'code' => 'urgent',
                    'label' => 'Instruksi Kota',
                    'theme' => 'urgent',
                    'is_urgent' => true,
                    'role_group' => 'super_admin',
                    'priority_rank' => 1,
                    'hint' => 'Instruksi/koordinasi penting dari Pemerintah Kota Surabaya',
                ];
            }

            return [
                'code' => 'super_admin',
                'label' => 'Pemerintah Kota',
                'theme' => 'neutral',
                'is_urgent' => false,
                'role_group' => 'super_admin',
                'priority_rank' => 5,
                'hint' => 'Administrator Utama Kota Surabaya',
            ];
        }

        // 2. Admin Kampus / Universitas (Koordinasi Perguruan Tinggi / Surat / Kerjasama)
        if ($otherUser->role === 'universitas') {
            if ($unread > 0) {
                return [
                    'code' => 'urgent',
                    'label' => 'Urusan Kampus',
                    'theme' => 'urgent',
                    'is_urgent' => true,
                    'role_group' => 'university',
                    'priority_rank' => 2,
                    'hint' => 'Pesan koordinasi dari Admin Perguruan Tinggi mitra',
                ];
            }

            return [
                'code' => 'university',
                'label' => 'Admin Kampus',
                'theme' => 'neutral',
                'is_urgent' => false,
                'role_group' => 'university',
                'priority_rank' => 6,
                'hint' => 'Admin Perguruan Tinggi',
            ];
        }

        // 3. Mentor Lapangan Dinas (Laporan Kendala Lapangan)
        if (in_array($otherUser->role, ['mentor', 'pembimbing'], true)) {
            if ($unread > 0) {
                return [
                    'code' => 'urgent',
                    'label' => 'Laporan Mentor',
                    'theme' => 'warning',
                    'is_urgent' => true,
                    'role_group' => 'mentor',
                    'priority_rank' => 3,
                    'hint' => 'Pesan dari Mentor Lapangan dinas terkait mahasiswa/unit',
                ];
            }

            return [
                'code' => 'mentor',
                'label' => 'Mentor Lapangan',
                'theme' => 'neutral',
                'is_urgent' => false,
                'role_group' => 'mentor',
                'priority_rank' => 7,
                'hint' => 'Mentor Lapangan Dinas',
            ];
        }

        if ($unread > 0) {
            return [
                'code' => 'urgent',
                'label' => 'Pesan Baru',
                'theme' => 'info',
                'is_urgent' => true,
                'role_group' => self::roleGroup($otherUser),
                'priority_rank' => 4,
                'hint' => 'Pesan belum dibaca',
            ];
        }

        return [
            'code' => 'staff',
            'label' => self::roleLabel($otherUser),
            'theme' => 'neutral',
            'is_urgent' => false,
            'role_group' => self::roleGroup($otherUser),
            'priority_rank' => 10,
            'hint' => self::roleLabel($otherUser),
        ];
    }

    /**
     * Prioritas Mentor: Logbook Pending, Nilai Evaluasi Mentor Belum Diisi, Pesan Mahasiswa Bimbingan.
     */
    private function mentorStageBadge(ChatConversation $c, User $viewer, ?User $otherUser, int $unread): ?array
    {
        if ($c->isMentorGuidance()) {
            if ($unread > 0) {
                return [
                    'code' => 'urgent',
                    'label' => 'Diskusi Bimbingan',
                    'theme' => 'info',
                    'is_urgent' => true,
                    'role_group' => 'group',
                    'priority_rank' => 2,
                    'hint' => "Ada {$unread} pesan belum dibaca di grup bimbingan mentor",
                ];
            }

            return [
                'code' => 'guidance',
                'label' => 'Bimbingan Mentor',
                'theme' => 'info',
                'is_urgent' => false,
                'role_group' => 'group',
                'priority_rank' => 6,
                'hint' => 'Grup koordinasi bimbingan lapangan',
            ];
        }

        if ($c->isGroup()) {
            return [
                'code' => 'group',
                'label' => 'Grup Koordinasi',
                'theme' => 'neutral',
                'is_urgent' => $unread > 0,
                'role_group' => 'group',
                'priority_rank' => $unread > 0 ? 4 : 9,
                'hint' => 'Grup koordinasi internal',
            ];
        }

        if (! $otherUser) {
            return null;
        }

        if ($otherUser->role === 'mahasiswa') {
            $app = $otherUser->relationLoaded('applications')
                ? $otherUser->applications->first()
                : $otherUser->applications()->latest('created_at')->first();
            $placement = $app?->placement;

            $isMyMentee = $placement && in_array((int) $viewer->id, array_filter([(int) $placement->mentor_id, (int) $placement->pembimbing_id]), true);

            if ($isMyMentee) {
                $eval = $placement->evaluation;
                $logs = $placement->relationLoaded('logbooks')
                    ? $placement->logbooks
                    : $placement->logbooks()->get();

                // 1. Logbook Pending
                $pendingLogs = $logs->where('status', 'pending')->count();
                if ($pendingLogs > 0) {
                    return [
                        'code' => 'urgent',
                        'label' => 'Logbook Pending',
                        'theme' => 'urgent',
                        'is_urgent' => true,
                        'role_group' => 'student',
                        'priority_rank' => 1,
                        'hint' => "Ada {$pendingLogs} logbook mahasiswa menunggu verifikasi mentor",
                    ];
                }

                // 2. Evaluasi Mentor Belum Diisi
                $hasMentorScore = $eval && (float) $eval->nilai_pembimbing > 0;
                $isEndingOrDone = in_array($app->statusValue(), ['completed', 'active'], true);
                if (! $hasMentorScore && $isEndingOrDone) {
                    $daysLeft = $this->daysRemaining($app);
                    if ($app->statusValue() === 'completed' || ($daysLeft !== null && $daysLeft <= 14)) {
                        return [
                            'code' => 'urgent',
                            'label' => 'Belum Dinilai',
                            'theme' => 'urgent',
                            'is_urgent' => true,
                            'role_group' => 'student',
                            'priority_rank' => 2,
                            'hint' => 'Nilai evaluasi kinerja lapangan belum diisi oleh mentor',
                        ];
                    }
                }

                // 3. Pesan belum dibaca
                if ($unread > 0) {
                    return [
                        'code' => 'urgent',
                        'label' => 'Pesan Mahasiswa',
                        'theme' => 'info',
                        'is_urgent' => true,
                        'role_group' => 'student',
                        'priority_rank' => 3,
                        'hint' => 'Pesan belum dibaca dari mahasiswa bimbingan',
                    ];
                }

                return [
                    'code' => 'active_mentee',
                    'label' => 'Bimbingan Lapangan',
                    'theme' => 'neutral',
                    'is_urgent' => false,
                    'role_group' => 'student',
                    'priority_rank' => 6,
                    'hint' => 'Mahasiswa bimbingan aktif',
                ];
            }

            return [
                'code' => 'student',
                'label' => 'Mahasiswa',
                'theme' => 'neutral',
                'is_urgent' => false,
                'role_group' => 'student',
                'priority_rank' => 8,
                'hint' => 'Mahasiswa magang',
            ];
        }

        if ($unread > 0) {
            return [
                'code' => 'urgent',
                'label' => 'Pesan '.self::roleLabel($otherUser),
                'theme' => 'info',
                'is_urgent' => true,
                'role_group' => self::roleGroup($otherUser),
                'priority_rank' => 4,
                'hint' => 'Pesan belum dibaca dari '.self::roleLabel($otherUser),
            ];
        }

        return [
            'code' => 'staff',
            'label' => self::roleLabel($otherUser),
            'theme' => 'neutral',
            'is_urgent' => false,
            'role_group' => self::roleGroup($otherUser),
            'priority_rank' => 10,
            'hint' => self::roleLabel($otherUser),
        ];
    }

    /**
     * Prioritas Admin Kampus: Mahasiswa Belum Ada Dosen Pembimbing, Koordinasi Dinas, Pesan Masuk.
     */
    private function universityStageBadge(ChatConversation $c, User $viewer, ?User $otherUser, int $unread): ?array
    {
        if ($c->isGroup()) {
            return [
                'code' => 'group',
                'label' => 'Grup Koordinasi',
                'theme' => 'neutral',
                'is_urgent' => $unread > 0,
                'role_group' => 'group',
                'priority_rank' => $unread > 0 ? 3 : 8,
                'hint' => 'Grup koordinasi internal',
            ];
        }

        if (! $otherUser) {
            return null;
        }

        if ($otherUser->role === 'mahasiswa') {
            $app = $otherUser->relationLoaded('applications')
                ? $otherUser->applications->first()
                : $otherUser->applications()->latest('created_at')->first();
            $placement = $app?->placement;

            if ($app && in_array($app->statusValue(), ['accepted', 'active'], true) && empty($placement?->academic_advisor_id)) {
                return [
                    'code' => 'urgent',
                    'label' => 'Perlu Dosen',
                    'theme' => 'urgent',
                    'is_urgent' => true,
                    'role_group' => 'student',
                    'priority_rank' => 1,
                    'hint' => 'Mahasiswa telah diterima dinas namun belum di-plot Dosen Pembimbing',
                ];
            }

            if ($unread > 0) {
                return [
                    'code' => 'urgent',
                    'label' => 'Pesan Mahasiswa',
                    'theme' => 'info',
                    'is_urgent' => true,
                    'role_group' => 'student',
                    'priority_rank' => 4,
                    'hint' => 'Pesan dari mahasiswa kampus',
                ];
            }

            return [
                'code' => 'student',
                'label' => 'Mahasiswa Kampus',
                'theme' => 'neutral',
                'is_urgent' => false,
                'role_group' => 'student',
                'priority_rank' => 7,
                'hint' => 'Mahasiswa dari perguruan tinggi',
            ];
        }

        if ($otherUser->role === 'admin') {
            if ($unread > 0) {
                return [
                    'code' => 'urgent',
                    'label' => 'Koordinasi Dinas',
                    'theme' => 'urgent',
                    'is_urgent' => true,
                    'role_group' => 'agency',
                    'priority_rank' => 2,
                    'hint' => 'Pesan koordinasi penerimaan dari Admin Dinas',
                ];
            }

            return [
                'code' => 'agency',
                'label' => 'Admin Dinas',
                'theme' => 'neutral',
                'is_urgent' => false,
                'role_group' => 'agency',
                'priority_rank' => 6,
                'hint' => 'Admin Dinas mitra magang',
            ];
        }

        if (in_array($otherUser->role, ['dosen', 'academic_advisor'], true)) {
            if ($unread > 0) {
                return [
                    'code' => 'urgent',
                    'label' => 'Pesan Dosen',
                    'theme' => 'info',
                    'is_urgent' => true,
                    'role_group' => 'dosen',
                    'priority_rank' => 3,
                    'hint' => 'Pesan dari Dosen Pembimbing',
                ];
            }

            return [
                'code' => 'dosen',
                'label' => 'Dosen Pembimbing',
                'theme' => 'neutral',
                'is_urgent' => false,
                'role_group' => 'dosen',
                'priority_rank' => 7,
                'hint' => 'Dosen Perguruan Tinggi',
            ];
        }

        if ($unread > 0) {
            return [
                'code' => 'urgent',
                'label' => 'Pesan Baru',
                'theme' => 'info',
                'is_urgent' => true,
                'role_group' => self::roleGroup($otherUser),
                'priority_rank' => 4,
                'hint' => 'Pesan belum dibaca',
            ];
        }

        return [
            'code' => 'staff',
            'label' => self::roleLabel($otherUser),
            'theme' => 'neutral',
            'is_urgent' => false,
            'role_group' => self::roleGroup($otherUser),
            'priority_rank' => 10,
            'hint' => self::roleLabel($otherUser),
        ];
    }

    /**
     * Prioritas Mahasiswa: Revisi Laporan Akhir, Pesan Dosen/Mentor, Bimbingan Aktif.
     */
    private function studentStageBadge(ChatConversation $c, User $viewer, ?User $otherUser, int $unread): ?array
    {
        if ($c->isGuidanceGroup()) {
            if ($unread > 0) {
                return [
                    'code' => 'urgent',
                    'label' => 'Pesan Bimbingan',
                    'theme' => 'urgent',
                    'is_urgent' => true,
                    'role_group' => 'group',
                    'priority_rank' => 2,
                    'hint' => 'Ada instruksi baru di grup bimbingan',
                ];
            }

            return [
                'code' => 'guidance',
                'label' => $c->isMentorGuidance() ? 'Bimbingan Mentor' : 'Bimbingan Dosen',
                'theme' => 'info',
                'is_urgent' => false,
                'role_group' => 'group',
                'priority_rank' => 6,
                'hint' => 'Grup koordinasi bimbingan',
            ];
        }

        if ($c->isGroup()) {
            return [
                'code' => 'group',
                'label' => 'Grup Koordinasi',
                'theme' => 'neutral',
                'is_urgent' => $unread > 0,
                'role_group' => 'group',
                'priority_rank' => $unread > 0 ? 3 : 8,
                'hint' => 'Grup koordinasi internal',
            ];
        }

        if (! $otherUser) {
            return null;
        }

        $app = $viewer->relationLoaded('applications')
            ? $viewer->applications->first()
            : $viewer->applications()->latest('created_at')->first();
        $placement = $app?->placement;
        $finalReport = $placement?->finalreport;

        if (in_array($otherUser->role, ['dosen', 'academic_advisor'], true)) {
            if ($finalReport && strtolower((string) $finalReport->status) === 'revision') {
                return [
                    'code' => 'urgent',
                    'label' => 'Perlu Revisi',
                    'theme' => 'urgent',
                    'is_urgent' => true,
                    'role_group' => 'dosen',
                    'priority_rank' => 1,
                    'hint' => 'Laporan akhir Anda diminta revisi oleh Dosen Pembimbing',
                ];
            }

            if ($unread > 0) {
                return [
                    'code' => 'urgent',
                    'label' => 'Pesan Dosen',
                    'theme' => 'urgent',
                    'is_urgent' => true,
                    'role_group' => 'dosen',
                    'priority_rank' => 2,
                    'hint' => 'Pesan belum dibaca dari Dosen Pembimbing',
                ];
            }

            return [
                'code' => 'dosen',
                'label' => 'Dosen Pembimbing',
                'theme' => 'neutral',
                'is_urgent' => false,
                'role_group' => 'dosen',
                'priority_rank' => 6,
                'hint' => 'Dosen Pembimbing Kampus',
            ];
        }

        if (in_array($otherUser->role, ['mentor', 'pembimbing'], true)) {
            if ($unread > 0) {
                return [
                    'code' => 'urgent',
                    'label' => 'Pesan Mentor',
                    'theme' => 'urgent',
                    'is_urgent' => true,
                    'role_group' => 'mentor',
                    'priority_rank' => 2,
                    'hint' => 'Pesan belum dibaca dari Mentor Lapangan',
                ];
            }

            return [
                'code' => 'mentor',
                'label' => 'Mentor Lapangan',
                'theme' => 'neutral',
                'is_urgent' => false,
                'role_group' => 'mentor',
                'priority_rank' => 6,
                'hint' => 'Mentor Lapangan Dinas',
            ];
        }

        if ($unread > 0) {
            return [
                'code' => 'urgent',
                'label' => 'Pesan Baru',
                'theme' => 'info',
                'is_urgent' => true,
                'role_group' => self::roleGroup($otherUser),
                'priority_rank' => 4,
                'hint' => 'Pesan belum dibaca',
            ];
        }

        return [
            'code' => 'contact',
            'label' => self::roleLabel($otherUser),
            'theme' => 'neutral',
            'is_urgent' => false,
            'role_group' => self::roleGroup($otherUser),
            'priority_rank' => 10,
            'hint' => self::roleLabel($otherUser),
        ];
    }

    /**
     * Prioritas Super Admin: Pesan dari Admin Dinas, Admin Kampus, dan Staf.
     */
    private function superAdminStageBadge(ChatConversation $c, User $viewer, ?User $otherUser, int $unread): ?array
    {
        if ($c->isGroup()) {
            return [
                'code' => 'group',
                'label' => 'Grup Koordinasi',
                'theme' => 'neutral',
                'is_urgent' => $unread > 0,
                'role_group' => 'group',
                'priority_rank' => $unread > 0 ? 3 : 8,
                'hint' => 'Grup koordinasi internal',
            ];
        }

        if (! $otherUser) {
            return null;
        }

        if ($unread > 0) {
            $label = match ($otherUser->role) {
                'admin' => 'Admin Dinas',
                'universitas' => 'Admin Kampus',
                'dosen', 'academic_advisor' => 'Dosen',
                'mentor', 'pembimbing' => 'Mentor',
                default => 'Pesan Baru',
            };

            return [
                'code' => 'urgent',
                'label' => $label,
                'theme' => 'urgent',
                'is_urgent' => true,
                'role_group' => self::roleGroup($otherUser),
                'priority_rank' => $otherUser->role === 'admin' ? 1 : 2,
                'hint' => "Pesan belum dibaca dari {$label}",
            ];
        }

        return [
            'code' => 'contact',
            'label' => self::roleLabel($otherUser),
            'theme' => 'neutral',
            'is_urgent' => false,
            'role_group' => self::roleGroup($otherUser),
            'priority_rank' => 10,
            'hint' => self::roleLabel($otherUser),
        ];
    }

    /**
     * Fallback badge generic (kompatibilitas backward jika viewer tidak diketahui).
     */
    private function genericStageBadge(ChatConversation $c, ?User $otherUser, int $unread = 0): ?array
    {
        if ($c->isGuidanceGroup() || $c->type === ChatConversation::TYPE_PLACEMENT) {
            if ($c->type === ChatConversation::TYPE_PLACEMENT) {
                $app = $c->placement?->application;
                if ($app) {
                    return $this->applicationStageBadge($app, 'student');
                }
            }

            return [
                'code' => 'guidance',
                'label' => 'Bimbingan',
                'theme' => 'info',
                'is_urgent' => $unread > 0,
                'role_group' => 'student',
                'priority_rank' => $unread > 0 ? 2 : 6,
                'hint' => 'Grup koordinasi bimbingan magang mahasiswa',
            ];
        }

        if ($c->isGroup()) {
            return [
                'code' => 'group',
                'label' => 'Grup Koordinasi',
                'theme' => 'neutral',
                'is_urgent' => false,
                'role_group' => 'group',
                'priority_rank' => 9,
                'hint' => 'Grup koordinasi internal',
            ];
        }

        if (! $otherUser) {
            return null;
        }

        if ($otherUser->role === 'mahasiswa') {
            $app = $otherUser->relationLoaded('applications')
                ? $otherUser->applications->first()
                : $otherUser->applications()->latest('created_at')->first();

            if ($app) {
                return $this->applicationStageBadge($app, 'student');
            }

            return [
                'code' => 'new_student',
                'label' => 'Mahasiswa',
                'theme' => 'neutral',
                'is_urgent' => false,
                'role_group' => 'student',
                'priority_rank' => 7,
                'hint' => 'Akun mahasiswa terdaftar',
            ];
        }

        $roleGroup = self::roleGroup($otherUser);
        $roleLabel = self::roleLabel($otherUser);

        return [
            'code' => 'staff',
            'label' => $roleLabel,
            'theme' => 'neutral',
            'is_urgent' => false,
            'role_group' => $roleGroup,
            'priority_rank' => 10,
            'hint' => $roleLabel,
        ];
    }

    public function daysRemaining(?Application $app): ?int
    {
        if (! $app || empty($app->end_date)) {
            return null;
        }
        $today = Carbon::now()->startOfDay();
        $endDate = Carbon::parse($app->end_date)->startOfDay();

        return (int) $today->diffInDays($endDate, false);
    }

    /**
     * Menghasilkan badge status deterministik dari model Application.
     */
    public function applicationStageBadge(Application $app, string $roleGroup = 'student'): array
    {
        $status = $app->statusValue();
        $priority = $app->actionPriority();

        // Tingkat 1: Tindakan seleksi / verifikasi masuk (Pending / Verified)
        if ($priority === 1) {
            return [
                'code' => 'urgent',
                'label' => $status === 'verified' ? 'Lolos Berkas' : 'Seleksi Masuk',
                'theme' => 'urgent',
                'is_urgent' => true,
                'role_group' => $roleGroup,
                'priority_rank' => 1,
                'hint' => 'Menunggu verifikasi berkas / seleksi admin',
            ];
        }

        // Tingkat 2: Siap diluluskan (laporan disetujui & nilai lengkap)
        if ($priority === 2) {
            return [
                'code' => 'urgent',
                'label' => 'Siap Lulus',
                'theme' => 'urgent',
                'is_urgent' => true,
                'role_group' => $roleGroup,
                'priority_rank' => 2,
                'hint' => 'Laporan & evaluasi lengkap, siap diterbitkan sertifikat',
            ];
        }

        // Tingkat 3: Pembimbing belum lengkap
        if ($priority === 3) {
            return [
                'code' => 'urgent',
                'label' => 'Perlu Pembimbing',
                'theme' => 'urgent',
                'is_urgent' => true,
                'role_group' => $roleGroup,
                'priority_rank' => 3,
                'hint' => 'Mentor atau Dosen belum ditetapkan',
            ];
        }

        // Cek sisa masa magang bila sedang aktif
        if ($status === 'active') {
            if (! empty($app->end_date)) {
                $today = Carbon::now()->startOfDay();
                $endDate = Carbon::parse($app->end_date)->startOfDay();
                $days = (int) $today->diffInDays($endDate, false);

                if ($days < 0) {
                    return [
                        'code' => 'approaching_end',
                        'label' => 'Masa Berakhir',
                        'theme' => 'urgent',
                        'is_urgent' => true,
                        'role_group' => $roleGroup,
                        'priority_rank' => 3,
                        'hint' => 'Masa magang telah melampaui tanggal selesai',
                    ];
                }

                if ($days <= 7) {
                    return [
                        'code' => 'approaching_end',
                        'label' => $days === 0 ? 'Hari Terakhir' : "H-{$days} Selesai",
                        'theme' => 'warning',
                        'is_urgent' => true,
                        'role_group' => $roleGroup,
                        'priority_rank' => 4,
                        'hint' => 'Masa magang segera berakhir dalam 7 hari',
                    ];
                }
            }

            return [
                'code' => 'active',
                'label' => 'Aktif Magang',
                'theme' => 'success',
                'is_urgent' => false,
                'role_group' => $roleGroup,
                'priority_rank' => 5,
                'hint' => 'Sedang aktif menjalani magang',
            ];
        }

        if ($status === 'accepted') {
            return [
                'code' => 'accepted',
                'label' => 'Diterima',
                'theme' => 'info',
                'is_urgent' => false,
                'role_group' => $roleGroup,
                'priority_rank' => 6,
                'hint' => 'Diterima, menunggu waktu mulai magang',
            ];
        }

        if ($status === 'completed') {
            return [
                'code' => 'completed',
                'label' => 'Alumni Magang',
                'theme' => 'neutral',
                'is_urgent' => false,
                'role_group' => $roleGroup,
                'priority_rank' => 7,
                'hint' => 'Telah menyelesaikan masa magang',
            ];
        }

        if (in_array($status, ['rejected', 'resigned'], true)) {
            return [
                'code' => $status,
                'label' => $status === 'rejected' ? 'Ditolak' : 'Mengundurkan Diri',
                'theme' => 'neutral',
                'is_urgent' => false,
                'role_group' => $roleGroup,
                'priority_rank' => 8,
                'hint' => 'Status pengajuan tidak aktif',
            ];
        }

        return [
            'code' => 'other',
            'label' => strtoupper($status),
            'theme' => 'neutral',
            'is_urgent' => false,
            'role_group' => $roleGroup,
            'priority_rank' => 9,
            'hint' => 'Pengajuan magang',
        ];
    }

    /**
     * Detail percakapan aktif (kepala chat + panel info). $participants sudah memuat user.
     *
     * @param  int[]  $personalVisibleIds  pengguna yang data pribadinya boleh dilihat $viewer
     *                                     (ChatContactDirectory::personalDetailsVisibleTo)
     */
    public function conversationDetail(ChatConversation $c, User $viewer, ChatParticipant $me, Collection $participants, int $unread, array $personalVisibleIds = []): array
    {
        $other = $c->isDirect() ? $participants->first(fn (ChatParticipant $p) => (int) $p->user_id !== (int) $viewer->id) : null;
        $c->participants_count = $participants->count();
        $details = fn (?User $u) => $u ? $this->contactDetails($u, in_array((int) $u->id, $personalVisibleIds, true)) : null;

        $members = $participants
            ->map(fn (ChatParticipant $p) => $this->user($p->user) + [
                'is_admin' => $p->isAdmin(),
                'is_me' => (int) $p->user_id === (int) $viewer->id,
                'details' => $details($p->user),
            ])
            ->sortBy(fn ($m) => [$m['is_me'] ? 0 : 1, $m['is_admin'] ? 0 : 1, mb_strtolower($m['name'])])
            ->values()
            ->all();

        $summary = $this->conversationSummary($c, $viewer, $me, $other, $unread);
        if ($summary['contact'] && $other?->user) {
            $summary['contact']['details'] = $details($other->user);
        }

        $canPublish = $c->isChannel() ? $this->channels->canPublishAnnouncement($c, $viewer) : true;
        $canSend = $c->isChannel() ? $canPublish : ! ($other && $other->user?->isInactive());

        return $summary + [
            'description' => $c->description,
            'members' => $members,
            'member_count' => count($members),
            'can_send' => $canSend,
            'can_publish' => $canPublish,
            'can_manage' => $c->isChannel() ? $this->channels->canManageChannel($c, $viewer) : ($c->type === ChatConversation::TYPE_GROUP && $me->isAdmin()),
            'can_leave' => $c->isChannel() ? false : ($c->type === ChatConversation::TYPE_GROUP),
            'is_admin' => $c->isChannel() ? $this->channels->canManageChannel($c, $viewer) : $me->isAdmin(),
            'is_channel' => $c->isChannel(),
            'scope_type' => $c->scope_type,
            'scope_badge' => $c->scope_type === ChatConversation::SCOPE_GOVERNMENT ? ($c->agency_profile_id ? 'Dinas' : 'Pemkot') : ($c->scope_type === ChatConversation::SCOPE_UNIVERSITY ? 'Kampus' : null),
            'meta_version' => (int) $c->meta_version,
            'placement_url' => $c->placement_id ? $this->placementUrl($viewer, $c) : null,
        ];
    }

    /**
     * Info Kontak sesuai data masing-masing role:
     * - Mahasiswa: email akun, HP dari Profil Mahasiswa, NIM, program studi, fakultas, semester.
     * - Staf (mentor, DPL, admin dinas/kampus, Super Admin): email akun & nomor di Pengaturan Akun.
     * Data pribadi hanya diisi bila $personalVisible; kontak resmi instansi selalu ditampilkan.
     */
    public function contactDetails(User $user, bool $personalVisible): array
    {
        $profile = $user->role === 'mahasiswa' ? $user->studentProfile : null;
        $phone = trim((string) ($profile?->phone ?: $user->phone)) ?: null;

        $fields = [];
        if ($profile) {
            $fields = array_values(array_filter([
                ['label' => 'NIM', 'value' => $profile->nim],
                ['label' => 'Program Studi', 'value' => $profile->jurusan],
                ['label' => 'Fakultas', 'value' => $profile->fakultas],
                ['label' => 'Semester', 'value' => $profile->semester],
            ], fn ($field) => filled($field['value'])));
        }

        return [
            'visible' => $personalVisible,
            'email' => $personalVisible ? $user->email : null,
            'phone' => $personalVisible ? $phone : null,
            'whatsapp_url' => $personalVisible ? self::whatsappUrl($phone) : null,
            'fields' => $personalVisible ? $fields : [],
            'institution' => $this->institutionContact($user),
        ];
    }

    /**
     * Tautan wa.me dari nomor Indonesia (0812… / +62812… / 812…); null bila format tidak dikenali.
     */
    public static function whatsappUrl(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);
        if ($digits === '') {
            return null;
        }
        if (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '62'.$digits;
        }

        return str_starts_with($digits, '62') && strlen($digits) >= 10 && strlen($digits) <= 15
            ? 'https://wa.me/'.$digits
            : null;
    }

    /**
     * Kontak resmi dinas/kampus (data publik instansi, bukan data pribadi).
     */
    private function institutionContact(User $user): ?array
    {
        if ($user->isSuperAdmin()) {
            return null;
        }

        if (in_array($user->role, ChatContactDirectory::AGENCY_STAFF_ROLES, true)) {
            $agency = $user->agencyProfile;
            $contact = $agency ? [
                'label' => 'Kontak Resmi Dinas',
                'name' => $agency->agency_name,
                'phone' => $agency->phone,
                'email' => $agency->email,
                'address' => $agency->address,
            ] : null;
        } else {
            $university = $user->universityRelation;
            $contact = $university ? [
                'label' => 'Kontak Resmi Kampus',
                'name' => $university->name,
                'phone' => $university->phone,
                'email' => $university->email,
                'address' => $university->address,
            ] : null;
        }

        return $contact && (filled($contact['phone']) || filled($contact['email']) || filled($contact['address'])) ? $contact : null;
    }

    public function message(ChatMessage $m, User $viewer): array
    {
        $deleted = $m->isDeleted();

        return [
            'id' => $m->id,
            'conversation_id' => $m->conversation_id,
            'parent_id' => $m->parent_id,
            'comments_count' => (int) $m->comments_count,
            'type' => $m->type,
            'is_mine' => $m->sender_id !== null && (int) $m->sender_id === (int) $viewer->id,
            'sender' => $m->isSystem() ? null : $this->brief($m->sender),
            'body' => $deleted ? null : $m->body,
            'deleted' => $deleted,
            'reply_to' => $m->reply_to_id ? $this->replyPreview($m->replyTo) : null,
            'attachments' => $deleted ? [] : $m->attachments->map(fn (ChatAttachment $a) => $this->attachment($a))->values()->all(),
            'created_at' => $m->created_at?->toIso8601String(),
        ];
    }

    public function attachment(ChatAttachment $a): array
    {
        return [
            'id' => $a->id,
            'message_id' => $a->message_id,
            'kind' => $a->kind,
            'name' => $a->name,
            'mime' => $a->mime,
            'size_label' => $this->formatSize((int) $a->size),
            'url' => route('chat.attachment', $a->id, false),
            'download_url' => route('chat.attachment', ['attachment' => $a->id, 'download' => 1], false),
            'created_at' => $a->created_at?->toIso8601String(),
        ];
    }

    private function replyPreview(?ChatMessage $reply): array
    {
        if (! $reply) {
            return ['id' => null, 'sender_name' => '', 'preview' => 'Pesan tidak tersedia', 'deleted' => true];
        }

        return [
            'id' => $reply->id,
            'sender_name' => $reply->sender?->name ?? 'Sistem',
            'preview' => $reply->preview(100),
            'deleted' => $reply->isDeleted(),
        ];
    }

    /**
     * Nama panggilan tanpa gelar: "Ir. Siti Aminah, M.Kom" → "Siti", "Dr. Erina Nur Azizah" → "Erina".
     */
    public static function shortName(?string $name): string
    {
        $tokens = array_values(array_filter(preg_split('/[\s,]+/u', trim((string) $name)) ?: []));
        foreach ($tokens as $token) {
            if (! str_contains($token, '.') && preg_match('/^\pL/u', $token)) {
                return $token;
            }
        }

        return $tokens[0] ?? '';
    }

    public static function isOnline($lastSeenAt): bool
    {
        return $lastSeenAt !== null && $lastSeenAt->gte(now()->subSeconds((int) config('chat.online_window_seconds', 120)));
    }

    public static function roleLabel(User $user): string
    {
        if ($user->isSuperAdmin()) {
            return 'Super Admin';
        }

        return match ($user->role) {
            'admin' => 'Admin Dinas',
            'mentor', 'pembimbing' => 'Mentor Lapangan',
            'dosen', 'academic_advisor' => 'Dosen Pembimbing',
            'universitas' => 'Admin Kampus',
            'mahasiswa' => 'Mahasiswa',
            default => ucwords(str_replace('_', ' ', (string) $user->role)),
        };
    }

    /**
     * Kelompok untuk daftar kontak "Chat Baru" (urutan tampil mengikuti ROLE_GROUPS di chat/app.js).
     */
    public static function roleGroup(User $user): string
    {
        if ($user->isSuperAdmin()) {
            return 'super_admin';
        }

        return match ($user->role) {
            'admin' => 'agency',
            'mentor', 'pembimbing' => 'mentor',
            'dosen', 'academic_advisor' => 'lecturer',
            'universitas' => 'campus',
            'mahasiswa' => 'student',
            default => 'other',
        };
    }

    /**
     * Tautan ke halaman detail mahasiswa sesuai role yang membuka Grup Bimbingan.
     */
    private function placementUrl(User $viewer, ChatConversation $c): ?string
    {
        return match (true) {
            in_array($viewer->role, ChatContactDirectory::MENTOR_ROLES, true) => route('mentor.students.show', $c->placement_id, false),
            in_array($viewer->role, ChatContactDirectory::LECTURER_ROLES, true) => route('lecturer.students.show', $c->placement_id, false),
            $viewer->role === 'mahasiswa' => route('student.logbook.index', [], false),
            default => null,
        };
    }

    private function organization(User $user): string
    {
        if ($user->isSuperAdmin()) {
            return 'Pemerintah Kota Surabaya';
        }

        if (in_array($user->role, ChatContactDirectory::AGENCY_STAFF_ROLES, true)) {
            return (string) ($user->agencyProfile?->agency_name ?? '');
        }

        // Kolom string lama `users.university` menutupi relasi university(), jadi pakai universityRelation
        return (string) ($user->universityRelation?->name ?? '');
    }

    private function initials(string $name): string
    {
        // Abaikan kata penghubung/simbol, mis. "Bimbingan · Andi" → "BA"
        $words = array_filter(preg_split('/[\s·\-]+/u', trim($name)) ?: [], fn ($w) => preg_match('/^\pL/u', $w));
        $letters = array_map(fn ($w) => mb_substr($w, 0, 1), array_slice(array_values($words), 0, 2));

        return mb_strtoupper(implode('', $letters)) ?: '?';
    }

    private function formatSize(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1, ',', '.').' MB';
        }

        return max(1, (int) round($bytes / 1024)).' KB';
    }
}
