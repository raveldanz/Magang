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

    public function user(?User $user): array
    {
        if (!$user) {
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
        if (!$user) {
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
        $stageBadge = $this->stageBadge($c, $other?->user);

        return [
            'id' => $c->id,
            'type' => $c->type,
            'url' => route('chat.show', $c->id, false),
            'title' => $contact ? $contact['name'] : ($c->title ?: 'Grup'),
            'subtitle' => $contact
                ? implode(' · ', array_filter([$contact['role_label'], $contact['org']]))
                : ($c->type === ChatConversation::TYPE_PLACEMENT ? 'Grup Bimbingan' : ((int) $c->participants_count) . ' anggota'),
            'avatar' => $contact
                ? ['initials' => $contact['initials'], 'color' => $contact['color'], 'group' => false]
                : ['initials' => $this->initials((string) $c->title), 'color' => $c->type === ChatConversation::TYPE_PLACEMENT ? self::PLACEMENT_COLOR : self::GROUP_COLOR, 'group' => true],
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
                'sender_name' => ($c->isGroup() && !$lastIsMine && !$last->isSystem() && !$last->isDeleted())
                    ? self::shortName($last->sender?->name) : null,
                'created_at' => $last->created_at?->toIso8601String(),
            ] : null,
            'sort_at' => ($c->last_message_at ?? $c->created_at)?->toIso8601String(),
        ];
    }

    /**
     * Menghitung status tahapan alur magang & urgensi percakapan secara deterministik (Smart Priority Engine).
     */
    public function stageBadge(ChatConversation $c, ?User $otherUser): ?array
    {
        // 1. Grup Bimbingan Penempatan
        if ($c->type === ChatConversation::TYPE_PLACEMENT) {
            $app = $c->placement?->application;
            if ($app) {
                return $this->applicationStageBadge($app, 'student');
            }

            return [
                'code' => 'placement',
                'label' => 'Bimbingan',
                'theme' => 'info',
                'is_urgent' => false,
                'role_group' => 'student',
                'priority_rank' => 6,
                'hint' => 'Grup bimbingan penempatan magang',
            ];
        }

        // 2. Grup Koordinasi Umum
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

        // 3. Chat 1-on-1
        if (!$otherUser) {
            return null;
        }

        // Jika lawan bicara adalah Mahasiswa: evaluasi status tahapan pengajuan / magangnya
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

        // Jika lawan bicara adalah Staf Kedinasan / Pembimbing / DPL / Kampus
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
                'hint' => 'Mentor atau DPL belum ditetapkan',
            ];
        }

        // Cek sisa masa magang bila sedang aktif
        if ($status === 'active') {
            if (!empty($app->end_date)) {
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
     *                                      (ChatContactDirectory::personalDetailsVisibleTo)
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

        return $summary + [
            'description' => $c->description,
            'members' => $members,
            'member_count' => count($members),
            'can_send' => !($other && $other->user?->isInactive()),
            'can_manage' => $c->type === ChatConversation::TYPE_GROUP && $me->isAdmin(),
            'can_leave' => $c->type === ChatConversation::TYPE_GROUP,
            'is_admin' => $me->isAdmin(),
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
            $digits = '62' . substr($digits, 1);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '62' . $digits;
        }

        return str_starts_with($digits, '62') && strlen($digits) >= 10 && strlen($digits) <= 15
            ? 'https://wa.me/' . $digits
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
        if (!$reply) {
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
            if (!str_contains($token, '.') && preg_match('/^\pL/u', $token)) {
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
            'dosen', 'academic_advisor' => 'Dosen Pembimbing (DPL)',
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
            return number_format($bytes / 1048576, 1, ',', '.') . ' MB';
        }

        return max(1, (int) round($bytes / 1024)) . ' KB';
    }
}
