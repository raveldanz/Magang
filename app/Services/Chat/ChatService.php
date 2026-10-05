<?php

namespace App\Services\Chat;

use App\Models\ChatAttachment;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\ChatParticipant;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Membuka percakapan, membaca pesan, status baca, jumlah belum dibaca, online & sedang mengetik.
 * Pengiriman/penghapusan pesan: ChatMessageService. Grup: ChatGroupService.
 */
class ChatService
{
    public const TYPING_TTL_SECONDS = 6;

    private const MESSAGE_RELATIONS = ['sender', 'attachments', 'replyTo.sender', 'replyTo.attachments'];

    public function __construct(
        private ChatContactDirectory $contacts,
        private ChatPresenter $presenter,
        private ChatNotifier $notifier,
    ) {}

    /**
     * Buka (atau buat) percakapan 1-on-1. Percakapan lama tetap bisa dibuka walau relasinya
     * sudah berakhir; percakapan baru hanya boleh dengan kontak yang diizinkan.
     */
    public function startDirect(User $from, User $to): ChatConversation
    {
        if ((int) $from->id === (int) $to->id) {
            throw ValidationException::withMessages(['user_id' => 'Tidak dapat memulai chat dengan akun sendiri.']);
        }

        $key = ChatConversation::directKeyFor($from->id, $to->id);
        if ($existing = ChatConversation::where('direct_key', $key)->first()) {
            return $existing;
        }

        if (! $this->contacts->canContact($from, $to)) {
            throw new AuthorizationException('Anda tidak dapat memulai chat dengan pengguna ini.');
        }

        try {
            return DB::transaction(function () use ($from, $to, $key) {
                $conversation = ChatConversation::create(['type' => ChatConversation::TYPE_DIRECT, 'direct_key' => $key]);
                foreach ([$from->id, $to->id] as $userId) {
                    $conversation->participants()->create(['user_id' => $userId]);
                }

                return $conversation;
            });
        } catch (UniqueConstraintViolationException) {
            // Dua klik bersamaan: percakapan sudah dibuat oleh request lain
            return ChatConversation::where('direct_key', $key)->firstOrFail();
        }
    }

    public function participantOrFail(ChatConversation $conversation, User $user): ChatParticipant
    {
        $participant = ChatParticipant::where('conversation_id', $conversation->id)->where('user_id', $user->id)->first();

        if (! $participant) {
            throw new AuthorizationException('Anda bukan peserta percakapan ini.');
        }

        return $participant;
    }

    public function conversationsFor(User $user): array
    {
        $mine = ChatParticipant::where('user_id', $user->id)->get()->keyBy('conversation_id');
        if ($mine->isEmpty()) {
            return [];
        }

        $unread = $this->unreadCounts($user);
        $conversations = ChatConversation::whereIn('id', $mine->keys())
            // Chat 1-on-1 baru muncul setelah ada pesan; grup langsung muncul
            ->where(fn ($q) => $q->whereNotNull('last_message_at')->orWhere('type', '!=', ChatConversation::TYPE_DIRECT))
            ->with([
                'agencyProfile',
                'university',
                'lastMessage.sender',
                'lastMessage.attachments',
                'placement.application.user.universityRelation',
                'placement.application.placement.finalreport',
                'placement.application.placement.evaluation',
            ])
            ->withCount('participants')
            ->get();

        $others = ChatParticipant::whereIn('conversation_id', $conversations->where('type', ChatConversation::TYPE_DIRECT)->pluck('id'))
            ->where('user_id', '!=', $user->id)
            ->with([
                'user.agencyProfile',
                'user.universityRelation',
                'user.applications' => fn ($q) => $q->latest('created_at')->with([
                    'placement.finalreport',
                    'placement.evaluation',
                    'placement.logbooks',
                    'user.universityRelation',
                ]),
            ])
            ->get()
            ->keyBy('conversation_id');

        $items = $conversations->map(fn (ChatConversation $c) => $this->presenter->conversationSummary(
            $c, $user, $mine[$c->id], $others[$c->id] ?? null, (int) ($unread[$c->id] ?? 0)
        ))->all();

        // Disematkan di atas, lalu pesan terbaru
        usort($items, fn ($a, $b) => [$b['pinned'], $b['sort_at']] <=> [$a['pinned'], $a['sort_at']]);

        return $items;
    }

    public function conversationDetail(ChatConversation $conversation, User $viewer): array
    {
        $participants = ChatParticipant::where('conversation_id', $conversation->id)
            ->with([
                'user.agencyProfile',
                'user.universityRelation',
                'user.studentProfile',
                'user.applications' => fn ($q) => $q->latest('created_at')->with([
                    'placement.finalreport',
                    'placement.evaluation',
                    'placement.logbooks',
                    'user.universityRelation',
                ]),
            ])
            ->orderBy('id')
            ->get();
        $me = $participants->firstWhere('user_id', $viewer->id) ?? $this->participantOrFail($conversation, $viewer);
        $conversation->loadMissing([
            'agencyProfile',
            'university',
            'lastMessage.sender',
            'lastMessage.attachments',
            'placement.application.user.universityRelation',
            'placement.application.placement.finalreport',
            'placement.application.placement.evaluation',
        ]);
        $unread = (int) ($this->unreadCounts($viewer)[$conversation->id] ?? 0);
        // Satu query untuk semua anggota: siapa yang email/telepon/NIM-nya boleh dilihat
        $visible = $this->contacts->personalDetailsVisibleTo($viewer, $participants->pluck('user_id')->all());

        return $this->presenter->conversationDetail($conversation, $viewer, $me, $participants, $unread, $visible);
    }

    /**
     * Status ringan untuk setiap polling: posisi baca tiap anggota, siapa yang sedang mengetik,
     * status online lawan bicara (1-on-1), dan versi detail grup.
     */
    public function conversationState(ChatConversation $conversation, User $viewer): array
    {
        $participants = ChatParticipant::where('conversation_id', $conversation->id)->get(['user_id', 'last_read_message_id']);

        $presence = null;
        if ($conversation->isDirect()) {
            $otherId = $participants->first(fn ($p) => (int) $p->user_id !== (int) $viewer->id)?->user_id;
            $lastSeen = $otherId ? User::whereKey($otherId)->value('last_seen_at') : null;
            $lastSeen = $lastSeen ? Carbon::parse($lastSeen) : null;
            $presence = ['online' => ChatPresenter::isOnline($lastSeen), 'last_seen_at' => $lastSeen?->toIso8601String()];
        }

        return [
            'read_state' => $participants->mapWithKeys(fn ($p) => [(int) $p->user_id => (int) $p->last_read_message_id])->all(),
            'typing' => $this->typingNames($conversation, $viewer),
            'presence' => $presence,
            'meta_version' => (int) $conversation->meta_version,
        ];
    }

    /**
     * @return array{messages: array, has_more: bool, changed: array, server_time: string}
     */
    public function messagesFor(ChatConversation $conversation, User $viewer, ?int $before = null, ?int $after = null, ?string $since = null): array
    {
        $query = fn () => ChatMessage::where('conversation_id', $conversation->id)->whereNull('parent_id')->with(self::MESSAGE_RELATIONS);

        if ($after !== null) {
            $items = $query()->where('id', '>', $after)->orderBy('id')->limit(100)->get();
            $hasMore = false;
        } else {
            $size = (int) config('chat.page_size', 30);
            $items = $query()->when($before, fn ($q) => $q->where('id', '<', $before))
                ->orderByDesc('id')->limit($size + 1)->get();
            $hasMore = $items->count() > $size;
            $items = $items->take($size)->reverse()->values();
        }

        // Pesan lama yang berubah sejak polling terakhir (mis. dihapus pengirimnya)
        $changed = collect();
        $sinceAt = $this->parseTime($since);
        if ($sinceAt && $after) {
            $changed = $query()->where('id', '<=', $after)->where('updated_at', '>=', $sinceAt)->limit(100)->get();
        }

        return [
            'messages' => $items->map(fn (ChatMessage $m) => $this->presenter->message($m, $viewer))->values()->all(),
            'has_more' => $hasMore,
            'changed' => $changed->map(fn (ChatMessage $m) => $this->presenter->message($m, $viewer))->values()->all(),
            'server_time' => now()->toIso8601String(),
        ];
    }

    /**
     * Mengambil daftar komentar untuk sebuah pesan pengumuman (utas komentar).
     *
     * @return array{parent: array, comments: array}
     */
    public function commentsFor(ChatMessage $parent, User $viewer): array
    {
        $comments = ChatMessage::where('parent_id', $parent->id)
            ->with(self::MESSAGE_RELATIONS)
            ->orderBy('id')
            ->get();

        return [
            'parent' => $this->presenter->message($parent, $viewer),
            'comments' => $comments->map(fn (ChatMessage $c) => $this->presenter->message($c, $viewer))->values()->all(),
        ];
    }

    public function markRead(ChatConversation $conversation, User $user, int $messageId): void
    {
        $participant = $this->participantOrFail($conversation, $user);
        $latestId = (int) ChatMessage::where('conversation_id', $conversation->id)->where('id', '<=', $messageId)->max('id');

        if ($latestId > (int) $participant->last_read_message_id) {
            $participant->forceFill(['last_read_message_id' => $latestId, 'last_read_at' => now()])->save();
        }

        $this->notifier->conversationRead($conversation, $user);
    }

    /**
     * Lampiran yang pernah dibagikan di percakapan (panel info: galeri & dokumen).
     */
    public function mediaFor(ChatConversation $conversation): array
    {
        return ChatAttachment::whereIn('message_id', ChatMessage::select('id')
            ->where('conversation_id', $conversation->id)
            ->whereNull('deleted_at'))
            ->latest('id')
            ->limit(200)
            ->get()
            ->map(fn (ChatAttachment $a) => $this->presenter->attachment($a))
            ->all();
    }

    // ---------------- Sedang mengetik (cache, kedaluwarsa otomatis) ----------------

    public function typing(ChatConversation $conversation, User $user): void
    {
        $this->participantOrFail($conversation, $user);
        $key = $this->typingKey($conversation);
        $entries = $this->freshTyping(Cache::get($key, []));
        $entries[$user->id] = ['at' => now()->timestamp, 'name' => $user->name];
        Cache::put($key, $entries, self::TYPING_TTL_SECONDS * 2);
    }

    public function stopTyping(ChatConversation $conversation, User $user): void
    {
        $key = $this->typingKey($conversation);
        $entries = Cache::get($key, []);
        if (isset($entries[$user->id])) {
            unset($entries[$user->id]);
            Cache::put($key, $entries, self::TYPING_TTL_SECONDS * 2);
        }
    }

    private function typingNames(ChatConversation $conversation, User $viewer): array
    {
        return collect($this->freshTyping(Cache::get($this->typingKey($conversation), [])))
            ->except([$viewer->id])
            ->pluck('name')
            ->values()
            ->all();
    }

    private function freshTyping(array $entries): array
    {
        $now = now()->timestamp;

        return array_filter($entries, fn ($e) => $now - (int) ($e['at'] ?? 0) <= self::TYPING_TTL_SECONDS);
    }

    private function typingKey(ChatConversation $conversation): string
    {
        return 'chat:typing:'.$conversation->id;
    }

    // ---------------- Belum dibaca, badge & toast ----------------

    /**
     * Jumlah pesan belum dibaca per percakapan: [conversation_id => jumlah] (termasuk yang dibisukan).
     */
    public function unreadCounts(User $user): Collection
    {
        return $this->incomingUnread($user)
            ->groupBy('chat_messages.conversation_id')
            ->selectRaw('chat_messages.conversation_id as cid, COUNT(*) as total')
            ->pluck('total', 'cid');
    }

    /**
     * Total untuk badge navbar — percakapan yang dibisukan tidak dihitung.
     */
    public function unreadTotal(User $user): int
    {
        return (int) $this->incomingUnread($user, excludeMuted: true)->count();
    }

    /**
     * Versi aman untuk layout (dipanggil di SETIAP halaman): badge chat tidak boleh membuat
     * seluruh aplikasi error, mis. saat kode terbaru ditarik tapi `php artisan migrate` belum dijalankan.
     */
    public function unreadTotalForNavbar(User $user): int
    {
        try {
            return $this->unreadTotal($user);
        } catch (QueryException $e) {
            report($e);

            return 0;
        }
    }

    /**
     * Data badge navbar & toast. $after = id pesan terakhir yang sudah dilihat browser;
     * null berarti panggilan pertama (hanya menetapkan titik awal, tanpa toast).
     */
    public function summary(User $user, ?int $after): array
    {
        $latestId = (int) $this->incomingUnread($user, excludeMuted: true)->max('chat_messages.id');
        $messages = [];

        if ($after !== null && $latestId > $after) {
            $messages = $this->incomingUnread($user, excludeMuted: true)
                ->where('chat_messages.id', '>', $after)
                ->select('chat_messages.*')
                ->with(['sender', 'attachments', 'conversation:id,type,title'])
                ->orderByDesc('chat_messages.id')
                ->limit(5)
                ->get()
                ->map(function (ChatMessage $m) {
                    $isDirect = $m->conversation?->isDirect() ?? true;

                    return [
                        'id' => $m->id,
                        'conversation_id' => $m->conversation_id,
                        'url' => route('chat.show', $m->conversation_id, false),
                        'title' => $isDirect ? ($m->sender?->name ?? 'Pesan baru') : (string) $m->conversation->title,
                        'sender' => $this->presenter->brief($m->sender),
                        'preview' => ($isDirect ? '' : ChatPresenter::shortName($m->sender?->name).': ').$m->preview(100),
                    ];
                })->all();
        }

        return [
            'unread_total' => $this->unreadTotal($user),
            'latest_id' => $latestId,
            'messages' => $messages,
        ];
    }

    /**
     * Catat "terakhir dilihat" paling sering sekali per menit, tanpa menyentuh users.updated_at.
     */
    public function touchPresence(User $user): void
    {
        if ($user->last_seen_at && $user->last_seen_at->gt(now()->subMinute())) {
            return;
        }

        DB::table('users')->where('id', $user->id)->update(['last_seen_at' => now()]);
        $user->last_seen_at = now();
    }

    private function incomingUnread(User $user, bool $excludeMuted = false): Builder
    {
        return ChatMessage::query()
            ->join('chat_participants as p', function ($join) use ($user) {
                $join->on('p.conversation_id', '=', 'chat_messages.conversation_id')
                    ->where('p.user_id', '=', $user->id);
            })
            ->where('chat_messages.type', ChatMessage::TYPE_TEXT)
            ->whereNull('chat_messages.deleted_at')
            ->where(fn ($q) => $q->whereNull('chat_messages.sender_id')->orWhere('chat_messages.sender_id', '!=', $user->id))
            ->whereRaw('chat_messages.id > COALESCE(p.last_read_message_id, 0)')
            ->when($excludeMuted, fn ($q) => $q->whereNull('p.muted_at'));
    }

    private function parseTime(?string $value): ?Carbon
    {
        if (! $value) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }
}
