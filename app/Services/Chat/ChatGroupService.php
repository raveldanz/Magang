<?php

namespace App\Services\Chat;

use App\Models\Application;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\ChatParticipant;
use App\Models\Placement;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\UniqueConstraintViolationException;
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
        if (!$this->contacts->canCreateGroups($creator)) {
            throw new AuthorizationException('Mahasiswa tidak dapat membuat grup. Minta mentor, DPL, atau admin untuk membuatkan grup.');
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
            if (!$deleted) {
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

        if ($conversation->type !== ChatConversation::TYPE_GROUP) {
            throw ValidationException::withMessages(['conversation' => $conversation->type === ChatConversation::TYPE_PLACEMENT
                ? 'Keanggotaan Grup Bimbingan diatur otomatis sesuai penempatan. Gunakan "Bisukan" bila tidak ingin menerima notifikasi.'
                : 'Chat 1-on-1 tidak dapat ditinggalkan.']);
        }

        DB::transaction(function () use ($conversation, $user, $participant) {
            $participant->delete();
            $this->system($conversation, "{$user->name} keluar dari grup.", 'member_left', $user);

            // Grup tidak boleh tanpa admin: anggota paling lama otomatis menjadi admin
            $remaining = ChatParticipant::where('conversation_id', $conversation->id)->orderBy('id')->get();
            if ($remaining->isNotEmpty() && !$remaining->contains(fn (ChatParticipant $p) => $p->isAdmin())) {
                $next = $remaining->first();
                $next->forceFill(['role' => ChatParticipant::ROLE_ADMIN])->save();
                $this->system($conversation, ($next->user?->name ?? 'Seorang anggota') . ' sekarang menjadi admin grup.', 'admin_promoted');
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

    // ---------------- Grup Bimbingan otomatis ----------------

    /**
     * Samakan anggota Grup Bimbingan dengan data penempatan: mahasiswa + mentor/pembimbing + DPL.
     * Dibuat saat minimal ada satu pembimbing; anggota ikut berganti bila pembimbing diganti.
     */
    public function syncPlacementGroup(Placement $placement): ?ChatConversation
    {
        $placement->loadMissing(['application.user', 'application.unit.agencyProfile']);
        $student = $placement->application?->user;
        if (!$student) {
            return null;
        }

        $roles = [(int) $student->id => 'Mahasiswa'];
        foreach (['mentor_id' => 'Mentor Lapangan', 'pembimbing_id' => 'Mentor Lapangan', 'academic_advisor_id' => 'Dosen Pembimbing (DPL)'] as $column => $label) {
            if ($placement->{$column} && !isset($roles[(int) $placement->{$column}])) {
                $roles[(int) $placement->{$column}] = $label;
            }
        }

        $conversation = ChatConversation::where('placement_id', $placement->id)->first();
        if (!$conversation && count($roles) < 2) {
            return null;
        }

        try {
            return DB::transaction(fn () => $this->applyPlacementMembers($placement, $conversation, $student, $roles));
        } catch (UniqueConstraintViolationException) {
            // Dua proses menyinkronkan penempatan yang sama bersamaan
            return ChatConversation::where('placement_id', $placement->id)->first();
        }
    }

    /**
     * Buat Grup Bimbingan yang belum ada untuk penempatan milik pengguna (data lama sebelum fitur chat).
     */
    public function ensurePlacementGroupsFor(User $user): void
    {
        if (!Schema::hasTable('chat_conversations')) {
            return;
        }

        $placements = Placement::query()
            ->whereNotIn('id', ChatConversation::select('placement_id')->whereNotNull('placement_id'))
            ->where(fn ($q) => $q->where('mentor_id', $user->id)
                ->orWhere('pembimbing_id', $user->id)
                ->orWhere('academic_advisor_id', $user->id)
                ->orWhereIn('application_id', Application::select('id')->where('user_id', $user->id)))
            ->limit(100)
            ->get();

        foreach ($placements as $placement) {
            try {
                $this->syncPlacementGroup($placement);
            } catch (\Throwable $e) {
                report($e);
            }
        }
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

    private function applyPlacementMembers(Placement $placement, ?ChatConversation $conversation, User $student, array $roles): ChatConversation
    {
        $created = false;
        if (!$conversation) {
            $unit = $placement->application?->unit;
            $conversation = ChatConversation::create([
                'type' => ChatConversation::TYPE_PLACEMENT,
                'placement_id' => $placement->id,
                'title' => "Bimbingan · {$student->name}",
                'description' => implode(' · ', array_filter([$unit?->name, $unit?->agencyProfile?->agency_name])) ?: null,
            ]);
            $created = true;
        }

        $current = ChatParticipant::where('conversation_id', $conversation->id)->pluck('user_id')->map(fn ($id) => (int) $id)->all();
        $added = array_values(array_diff(array_keys($roles), $current));
        $removed = array_values(array_diff($current, array_keys($roles)));
        if (!$added && !$removed) {
            return $conversation;
        }

        $users = User::whereIn('id', array_merge($added, $removed))->get()->keyBy('id');
        foreach ($added as $userId) {
            $conversation->participants()->create([
                'user_id' => $userId,
                'role' => ChatParticipant::ROLE_MEMBER,
                'last_read_message_id' => $conversation->last_message_id,
            ]);
        }
        ChatParticipant::where('conversation_id', $conversation->id)->whereIn('user_id', $removed)->delete();

        if ($created) {
            $this->system($conversation, "Grup Bimbingan untuk {$student->name} dibuat otomatis. Gunakan grup ini untuk koordinasi mahasiswa, mentor lapangan, dan DPL.", 'placement_group_created');
        }
        foreach ($added as $userId) {
            if ($userId !== (int) $student->id) {
                $this->system($conversation, ($users[$userId]->name ?? 'Anggota baru') . " ditambahkan sebagai {$roles[$userId]}.", 'member_added');
            }
        }
        foreach ($removed as $userId) {
            $this->system($conversation, ($users[$userId]->name ?? 'Seorang anggota') . ' tidak lagi menjadi pembimbing di grup ini.', 'member_removed');
        }

        $conversation->increment('meta_version');
        $this->notifier->forget($conversation, $removed);

        return $conversation;
    }

    private function assertManager(ChatConversation $conversation, User $actor): void
    {
        $participant = $this->chat->participantOrFail($conversation, $actor);

        if ($conversation->type !== ChatConversation::TYPE_GROUP || !$participant->isAdmin()) {
            throw new AuthorizationException($conversation->type === ChatConversation::TYPE_PLACEMENT
                ? 'Anggota Grup Bimbingan diatur otomatis sesuai data penempatan.'
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
            ? $names->take(3)->implode(', ') . ' dan ' . ($names->count() - 3) . ' lainnya'
            : $names->implode(', ');
    }
}
