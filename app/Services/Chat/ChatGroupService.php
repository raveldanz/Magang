<?php

namespace App\Services\Chat;

use App\Models\Application;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\ChatParticipant;
use App\Models\Placement;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

/**
 * Grup buatan staf dan Grup Bimbingan otomatis per penempatan.
 */
class ChatGroupService
{
    public function __construct(
        private ChatContactDirectory $contacts,
        private ChatService $chat,
        private ChatNotifier $notifier,
    ) {}

    // ---------------- Grup buatan staf ----------------

    public function create(User $creator, string $title, ?string $description, array $memberIds): ChatConversation
    {
        if (! $this->contacts->canCreateGroups($creator)) {
            throw new AuthorizationException('Mahasiswa tidak dapat membuat grup. Minta mentor, dosen, atau admin untuk membuatkan grup.');
        }

        $members = $this->resolveContacts($creator, $memberIds);
        if ($members->isEmpty()) {
            throw ValidationException::withMessages(['member_ids' => 'Pilih minimal satu anggota grup.']);
        }
        $this->assertCapacity($members->count() + 1);

        return DB::transaction(function () use ($creator, $title, $description, $members) {
            $conversation = ChatConversation::create([
                'type' => ChatConversation::TYPE_GROUP,
                'title' => $title,
                'description' => $description,
                'created_by' => $creator->id,
            ]);

            $conversation->participants()->create(['user_id' => $creator->id, 'role' => ChatParticipant::ROLE_ADMIN]);
            foreach ($members as $member) {
                $conversation->participants()->create(['user_id' => $member->id, 'role' => ChatParticipant::ROLE_MEMBER]);
            }

            $this->system($conversation, "{$creator->name} membuat grup \"{$title}\" dan menambahkan {$this->names($members)}.", 'group_created', $creator);

            return $conversation;
        });
    }

    public function update(ChatConversation $conversation, User $actor, string $title, ?string $description): ChatConversation
    {
        $this->assertManager($conversation, $actor);

        return DB::transaction(function () use ($conversation, $actor, $title, $description) {
            $renamed = $conversation->title !== $title;
            $conversation->forceFill(['title' => $title, 'description' => $description])->save();
            $conversation->increment('meta_version');

            if ($renamed) {
                $this->system($conversation, "{$actor->name} mengubah nama grup menjadi \"{$title}\".", 'group_renamed', $actor);
            }

            return $conversation->fresh();
        });
    }

    public function addMembers(ChatConversation $conversation, User $actor, array $userIds): Collection
    {
        $this->assertManager($conversation, $actor);

        $existing = ChatParticipant::where('conversation_id', $conversation->id)->pluck('user_id')->map(fn ($id) => (int) $id);
        $newIds = collect($userIds)->map(fn ($id) => (int) $id)->diff($existing)->values()->all();
        $members = $this->resolveContacts($actor, $newIds);
        if ($members->isEmpty()) {
            throw ValidationException::withMessages(['user_ids' => 'Pilih minimal satu anggota baru.']);
        }
        $this->assertCapacity($existing->count() + $members->count());

        DB::transaction(function () use ($conversation, $actor, $members) {
            foreach ($members as $member) {
                // Riwayat tetap bisa dibaca anggota baru, tetapi tidak dihitung sebagai belum dibaca
                $conversation->participants()->create([
                    'user_id' => $member->id,
                    'role' => ChatParticipant::ROLE_MEMBER,
                    'last_read_message_id' => $conversation->last_message_id,
                ]);
            }
            $conversation->increment('meta_version');
            $this->system($conversation, "{$actor->name} menambahkan {$this->names($members)}.", 'members_added', $actor);
        });

        return $members;
    }

    public function removeMember(ChatConversation $conversation, User $actor, User $target): void
    {
        $this->assertManager($conversation, $actor);

        if ((int) $target->id === (int) $actor->id) {
            throw ValidationException::withMessages(['user_id' => 'Gunakan tombol "Keluar dari Grup" untuk keluar.']);
        }

        DB::transaction(function () use ($conversation, $actor, $target) {
            $deleted = ChatParticipant::where('conversation_id', $conversation->id)->where('user_id', $target->id)->delete();
            if (! $deleted) {
                throw ValidationException::withMessages(['user_id' => 'Pengguna ini bukan anggota grup.']);
            }
            $conversation->increment('meta_version');
            $this->system($conversation, "{$actor->name} mengeluarkan {$target->name} dari grup.", 'member_removed', $actor);
        });

        $this->notifier->forget($conversation, [$target->id]);
    }

    public function leave(ChatConversation $conversation, User $user): void
    {
        $participant = $this->chat->participantOrFail($conversation, $user);

        if ($conversation->isGuidanceGroup() || $conversation->type !== ChatConversation::TYPE_GROUP) {
            throw ValidationException::withMessages(['conversation' => ($conversation->isGuidanceGroup() || $conversation->type === ChatConversation::TYPE_PLACEMENT)
                ? 'Keanggotaan Grup Bimbingan diatur otomatis sesuai data penempatan dan bimbingan Anda. Gunakan "Bisukan" bila tidak ingin menerima notifikasi.'
                : 'Chat 1-on-1 tidak dapat ditinggalkan.']);
        }

        DB::transaction(function () use ($conversation, $user, $participant) {
            $participant->delete();
            $this->system($conversation, "{$user->name} keluar dari grup.", 'member_left', $user);

            // Grup tidak boleh tanpa admin: anggota paling lama otomatis menjadi admin
            $remaining = ChatParticipant::where('conversation_id', $conversation->id)->orderBy('id')->get();
            if ($remaining->isNotEmpty() && ! $remaining->contains(fn (ChatParticipant $p) => $p->isAdmin())) {
                $next = $remaining->first();
                $next->forceFill(['role' => ChatParticipant::ROLE_ADMIN])->save();
                $this->system($conversation, ($next->user?->name ?? 'Seorang anggota').' sekarang menjadi admin grup.', 'admin_promoted');
            }
            $conversation->increment('meta_version');
        });

        $this->notifier->forget($conversation, [$user->id]);
    }

    /**
     * Pengaturan pribadi per percakapan: bisukan & sematkan.
     */
    public function updateSettings(ChatConversation $conversation, User $user, ?bool $muted, ?bool $pinned): ChatParticipant
    {
        $participant = $this->chat->participantOrFail($conversation, $user);

        if ($muted !== null) {
            $participant->muted_at = $muted ? now() : null;
        }
        if ($pinned !== null) {
            $participant->pinned_at = $pinned ? now() : null;
        }
        $participant->save();

        return $participant;
    }

    // ---------------- Grup Bimbingan otomatis (Per Mentor & Per DPL) ----------------

    /**
     * Sinkronkan Grup Bimbingan Mentor (1 Mentor menaungi semua mahasiswa bimbingan yang di-plotting).
     */
    public function syncMentorGroup(User $mentor): ?ChatConversation
    {
        $activeStatuses = ['accepted', 'active', 'completed'];
        $students = User::query()
            ->where(fn ($q) => $q->whereNull('status')->orWhere('status', '!=', 'inactive'))
            ->whereHas('applications', function ($appQ) use ($mentor, $activeStatuses) {
                $appQ->whereIn('status', $activeStatuses)
                    ->whereNotIn('status', ['resigned', 'rejected'])
                    ->whereHas('placement', function ($plcQ) use ($mentor) {
                        $plcQ->where('mentor_id', $mentor->id)
                            ->orWhere('pembimbing_id', $mentor->id);
                    });
            })
            ->orderBy('name')
            ->get();

        $conversation = ChatConversation::where('scope_type', ChatConversation::SCOPE_MENTOR_GUIDANCE)
            ->where('created_by', $mentor->id)
            ->first();

        if ($students->isEmpty() && ! $conversation) {
            return null;
        }

        $created = false;
        if (! $conversation) {
            $conversation = ChatConversation::create([
                'type' => ChatConversation::TYPE_GROUP,
                'scope_type' => ChatConversation::SCOPE_MENTOR_GUIDANCE,
                'created_by' => $mentor->id,
                'agency_profile_id' => $mentor->agency_profile_id,
                'title' => "Bimbingan Mentor {$mentor->name}",
                'description' => "Grup Koordinasi Bimbingan Magang Mahasiswa bersama Mentor {$mentor->name}",
            ]);
            $created = true;
        }

        // Mentor selalu sebagai Admin
        ChatParticipant::firstOrCreate(
            ['conversation_id' => $conversation->id, 'user_id' => $mentor->id],
            ['role' => ChatParticipant::ROLE_ADMIN]
        );

        $expectedMemberIds = array_merge([(int) $mentor->id], $students->pluck('id')->map(fn ($id) => (int) $id)->all());
        $currentMemberIds = ChatParticipant::where('conversation_id', $conversation->id)->pluck('user_id')->map(fn ($id) => (int) $id)->all();

        $added = array_values(array_diff($expectedMemberIds, $currentMemberIds));
        $removed = array_values(array_diff($currentMemberIds, $expectedMemberIds));

        // Mentor tidak boleh terhapus dari grup bimbingannya sendiri
        $removed = array_values(array_diff($removed, [(int) $mentor->id]));

        if ($created) {
            $this->system($conversation, "Grup Bimbingan Mentor {$mentor->name} dibuat otomatis untuk koordinasi mahasiswa bimbingan.", 'guidance_group_created');
        }

        $usersById = User::whereIn('id', array_merge($added, $removed))->get()->keyBy('id');

        foreach ($added as $userId) {
            if ($userId === (int) $mentor->id) {
                continue;
            }
            $conversation->participants()->create([
                'user_id' => $userId,
                'role' => ChatParticipant::ROLE_MEMBER,
                'last_read_message_id' => $conversation->last_message_id,
            ]);
            $addedUser = $usersById[$userId] ?? null;
            if ($addedUser) {
                $this->system($conversation, "{$addedUser->name} bergabung ke grup bimbingan mentor.", 'member_added');
            }
        }

        foreach ($removed as $userId) {
            ChatParticipant::where('conversation_id', $conversation->id)->where('user_id', $userId)->delete();
            $removedUser = $usersById[$userId] ?? null;
            if ($removedUser) {
                $isResigned = Application::where('user_id', $userId)->where('status', 'resigned')->exists();
                $msg = $isResigned
                    ? "{$removedUser->name} dikeluarkan dari grup bimbingan karena telah mengundurkan diri (resigned)."
                    : "{$removedUser->name} tidak lagi berada dalam bimbingan mentor ini.";
                $this->system($conversation, $msg, 'member_removed');
            }
        }

        if (! empty($added) || ! empty($removed)) {
            $conversation->increment('meta_version');
            if (! empty($removed)) {
                $this->notifier->forget($conversation, $removed);
            }
        }

        return $conversation;
    }

    /**
     * Sinkronkan Grup Bimbingan DPL (1 DPL menaungi semua mahasiswa bimbingan kampusnya).
     */
    public function syncDplGroup(User $dpl): ?ChatConversation
    {
        $activeStatuses = ['accepted', 'active', 'completed'];
        $students = User::query()
            ->where(fn ($q) => $q->whereNull('status')->orWhere('status', '!=', 'inactive'))
            ->whereHas('applications', function ($appQ) use ($dpl, $activeStatuses) {
                $appQ->whereIn('status', $activeStatuses)
                    ->whereNotIn('status', ['resigned', 'rejected'])
                    ->whereHas('placement', function ($plcQ) use ($dpl) {
                        $plcQ->where('academic_advisor_id', $dpl->id);
                    });
            })
            ->orderBy('name')
            ->get();

        $conversation = ChatConversation::where('scope_type', ChatConversation::SCOPE_DPL_GUIDANCE)
            ->where('created_by', $dpl->id)
            ->first();

        if ($students->isEmpty() && ! $conversation) {
            return null;
        }

        $created = false;
        if (! $conversation) {
            $conversation = ChatConversation::create([
                'type' => ChatConversation::TYPE_GROUP,
                'scope_type' => ChatConversation::SCOPE_DPL_GUIDANCE,
                'created_by' => $dpl->id,
                'university_id' => $dpl->university_id,
                'title' => "Bimbingan Dosen {$dpl->name}",
                'description' => "Grup Koordinasi Bimbingan Magang Mahasiswa bersama Dosen {$dpl->name}",
            ]);
            $created = true;
        }

        // DPL selalu sebagai Admin
        ChatParticipant::firstOrCreate(
            ['conversation_id' => $conversation->id, 'user_id' => $dpl->id],
            ['role' => ChatParticipant::ROLE_ADMIN]
        );

        $expectedMemberIds = array_merge([(int) $dpl->id], $students->pluck('id')->map(fn ($id) => (int) $id)->all());
        $currentMemberIds = ChatParticipant::where('conversation_id', $conversation->id)->pluck('user_id')->map(fn ($id) => (int) $id)->all();

        $added = array_values(array_diff($expectedMemberIds, $currentMemberIds));
        $removed = array_values(array_diff($currentMemberIds, $expectedMemberIds));

        // DPL tidak boleh terhapus dari grup bimbingannya sendiri
        $removed = array_values(array_diff($removed, [(int) $dpl->id]));

        if ($created) {
            $this->system($conversation, "Grup Bimbingan Dosen {$dpl->name} dibuat otomatis untuk koordinasi mahasiswa bimbingan.", 'guidance_group_created');
        }

        $usersById = User::whereIn('id', array_merge($added, $removed))->get()->keyBy('id');

        foreach ($added as $userId) {
            if ($userId === (int) $dpl->id) {
                continue;
            }
            $conversation->participants()->create([
                'user_id' => $userId,
                'role' => ChatParticipant::ROLE_MEMBER,
                'last_read_message_id' => $conversation->last_message_id,
            ]);
            $addedUser = $usersById[$userId] ?? null;
            if ($addedUser) {
                $this->system($conversation, "{$addedUser->name} bergabung ke grup bimbingan dosen.", 'member_added');
            }
        }

        foreach ($removed as $userId) {
            ChatParticipant::where('conversation_id', $conversation->id)->where('user_id', $userId)->delete();
            $removedUser = $usersById[$userId] ?? null;
            if ($removedUser) {
                $isResigned = Application::where('user_id', $userId)->where('status', 'resigned')->exists();
                $msg = $isResigned
                    ? "{$removedUser->name} dikeluarkan dari grup bimbingan karena telah mengundurkan diri (resigned)."
                    : "{$removedUser->name} tidak lagi berada dalam bimbingan dosen ini.";
                $this->system($conversation, $msg, 'member_removed');
            }
        }

        if (! empty($added) || ! empty($removed)) {
            $conversation->increment('meta_version');
            if (! empty($removed)) {
                $this->notifier->forget($conversation, $removed);
            }
        }

        return $conversation;
    }

    /**
     * Memastikan mahasiswa hanya berada di grup mentor dan DPL aktifnya, serta dikeluarkan
     * jika mahasiswa mutasi pembimbing atau statusnya resigned/keluar.
     */
    public function syncStudentSupervisionGroups(User $student): void
    {
        $activePlacement = Placement::whereHas('application', function ($q) use ($student) {
            $q->where('user_id', $student->id)
                ->whereIn('status', ['accepted', 'active', 'completed'])
                ->whereNotIn('status', ['resigned', 'rejected']);
        })->latest()->first();

        $currentMentorId = $activePlacement ? ($activePlacement->mentor_id ?: $activePlacement->pembimbing_id) : null;
        $currentDplId = $activePlacement?->academic_advisor_id;

        // Ambil grup bimbingan yang saat ini diikuti oleh mahasiswa
        $currentGuidanceConversations = ChatConversation::query()
            ->whereIn('scope_type', [ChatConversation::SCOPE_MENTOR_GUIDANCE, ChatConversation::SCOPE_DPL_GUIDANCE])
            ->whereHas('participants', fn ($q) => $q->where('user_id', $student->id))
            ->get();

        foreach ($currentGuidanceConversations as $conv) {
            $isCurrent = false;
            if ($conv->isMentorGuidance() && $currentMentorId && (int) $conv->created_by === (int) $currentMentorId) {
                $isCurrent = true;
            } elseif ($conv->isDplGuidance() && $currentDplId && (int) $conv->created_by === (int) $currentDplId) {
                $isCurrent = true;
            }

            if (! $isCurrent) {
                ChatParticipant::where('conversation_id', $conv->id)->where('user_id', $student->id)->delete();
                $conv->increment('meta_version');
                $this->notifier->forget($conv, [$student->id]);

                $isResigned = Application::where('user_id', $student->id)->where('status', 'resigned')->exists();
                $roleWord = $conv->isMentorGuidance() ? 'mentor' : ($conv->isDplGuidance() ? 'dosen' : 'grup');
                $msg = $isResigned
                    ? "{$student->name} dikeluarkan dari grup bimbingan karena telah mengundurkan diri (resigned)."
                    : "{$student->name} tidak lagi berada dalam bimbingan {$roleWord} ini.";
                $this->system($conv, $msg, 'member_removed');
            }
        }

        if ($currentMentorId) {
            $mentor = User::find($currentMentorId);
            if ($mentor) {
                $this->syncMentorGroup($mentor);
            }
        }

        if ($currentDplId) {
            $dpl = User::find($currentDplId);
            if ($dpl) {
                $this->syncDplGroup($dpl);
            }
        }
    }

    /**
     * Memastikan grup bimbingan siap bagi pengguna saat mengakses antarmuka chat.
     */
    public function ensurePlacementGroupsFor(User $user): void
    {
        if (! Schema::hasTable('chat_conversations')) {
            return;
        }

        if ($user->role === 'mahasiswa') {
            $this->syncStudentSupervisionGroups($user);

            return;
        }

        if (in_array($user->role, ['mentor', 'pembimbing'], true)) {
            $this->syncMentorGroup($user);

            return;
        }

        if (in_array($user->role, ['dosen', 'academic_advisor'], true)) {
            $this->syncDplGroup($user);

            return;
        }

        // Untuk role staf atau admin: sinkronkan seluruh mentor dan DPL yang aktif
        $mentorIds = Placement::whereNotNull('mentor_id')->pluck('mentor_id')
            ->merge(Placement::whereNotNull('pembimbing_id')->pluck('pembimbing_id'))
            ->unique()->filter();
        foreach (User::whereIn('id', $mentorIds)->get() as $m) {
            $this->syncMentorGroup($m);
        }

        $dplIds = Placement::whereNotNull('academic_advisor_id')->pluck('academic_advisor_id')->unique()->filter();
        foreach (User::whereIn('id', $dplIds)->get() as $d) {
            $this->syncDplGroup($d);
        }
    }

    /**
     * Memperbarui grup bimbingan mentor dan DPL terkait sebuah penempatan.
     */
    public function syncPlacementGroup(Placement $placement): ?ChatConversation
    {
        $placement->loadMissing(['application.user', 'mentor', 'pembimbing', 'academicAdvisor']);
        $mentor = $placement->mentor ?? $placement->pembimbing;
        $dpl = $placement->academicAdvisor;
        $student = $placement->application?->user;

        $conv = null;
        if ($mentor) {
            $conv = $this->syncMentorGroup($mentor);
        }
        if ($dpl) {
            $dplConv = $this->syncDplGroup($dpl);
            $conv = $conv ?? $dplConv;
        }
        if ($student) {
            $this->syncStudentSupervisionGroups($student);
        }

        return $conv;
    }

    public function system(ChatConversation $conversation, string $text, string $event, ?User $actor = null): ChatMessage
    {
        $message = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => null,
            'type' => ChatMessage::TYPE_SYSTEM,
            'body' => $text,
            'meta' => ['event' => $event, 'actor_id' => $actor?->id],
        ]);
        $conversation->forceFill(['last_message_id' => $message->id, 'last_message_at' => $message->created_at])->save();

        return $message;
    }

    private function assertManager(ChatConversation $conversation, User $actor): void
    {
        $participant = $this->chat->participantOrFail($conversation, $actor);

        if ($conversation->type !== ChatConversation::TYPE_GROUP || ! $participant->isAdmin() || $conversation->isGuidanceGroup()) {
            throw new AuthorizationException(($conversation->isGuidanceGroup() || $conversation->type === ChatConversation::TYPE_PLACEMENT)
                ? 'Anggota Grup Bimbingan dikelola secara otomatis oleh sistem sesuai plotting bimbingan.'
                : 'Hanya admin grup yang dapat mengubah grup ini.');
        }
    }

    private function assertCapacity(int $total): void
    {
        $max = (int) config('chat.group_max_members', 100);
        if ($total > $max) {
            throw ValidationException::withMessages(['member_ids' => "Satu grup maksimal berisi {$max} anggota."]);
        }
    }

    /**
     * Anggota baru hanya boleh dari kontak pelaku (aturan ChatContactDirectory).
     */
    private function resolveContacts(User $actor, array $ids): Collection
    {
        $ids = collect($ids)->map(fn ($id) => (int) $id)->filter()->unique()->reject(fn ($id) => $id === (int) $actor->id)->values();
        if ($ids->isEmpty()) {
            return collect();
        }

        $allowed = $this->contacts->contactsQuery($actor)->whereIn('id', $ids)->orderBy('name')->get();
        if ($allowed->count() !== $ids->count()) {
            throw ValidationException::withMessages(['member_ids' => 'Sebagian pengguna yang dipilih bukan kontak Anda sehingga tidak dapat ditambahkan.']);
        }

        return $allowed;
    }

    private function names(Collection $users): string
    {
        $names = $users->pluck('name');

        return $names->count() > 3
            ? $names->take(3)->implode(', ').' dan '.($names->count() - 3).' lainnya'
            : $names->implode(', ');
    }
}
