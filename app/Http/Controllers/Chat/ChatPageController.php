<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\Placement;
use App\Models\User;
use App\Services\Chat\ChatAttachmentStorage;
use App\Services\Chat\ChatChannelService;
use App\Services\Chat\ChatContactDirectory;
use App\Services\Chat\ChatGroupService;
use App\Services\Chat\ChatPresenter;
use App\Services\Chat\ChatService;
use Illuminate\Http\Request;

class ChatPageController extends Controller
{
    public function __construct(
        private ChatService $chat,
        private ChatGroupService $groups,
        private ChatChannelService $channels,
        private ChatPresenter $presenter,
        private ChatContactDirectory $contacts,
    ) {}

    public function index(Request $request)
    {
        $this->channels->ensureChannelsForUser($request->user());
        $this->groups->ensurePlacementGroupsFor($request->user());

        return view('chat.index', ['chatConfig' => $this->config($request, null)]);
    }

    public function show(Request $request, ChatConversation $conversation)
    {
        $this->chat->participantOrFail($conversation, $request->user());
        $this->channels->ensureChannelsForUser($request->user());
        $this->groups->ensurePlacementGroupsFor($request->user());

        return view('chat.index', ['chatConfig' => $this->config($request, $conversation->id)]);
    }

    /**
     * Mulai chat 1-on-1 (dipakai modal "Chat Baru" via fetch, atau tombol "Chat" di halaman lain).
     */
    public function start(Request $request)
    {
        $data = $request->validate(['user_id' => ['required', 'integer', 'exists:users,id']]);
        $conversation = $this->chat->startDirect($request->user(), User::findOrFail($data['user_id']));

        if ($request->expectsJson()) {
            return response()->json(['conversation' => $this->chat->conversationDetail($conversation, $request->user())]);
        }

        return redirect()->route('chat.show', $conversation->id);
    }

    /**
     * Buka Grup Bimbingan sebuah penempatan (tombol "Grup Bimbingan").
     */
    public function placement(Request $request, Placement $placement)
    {
        $user = $request->user();
        $placement->loadMissing(['application.user', 'mentor', 'pembimbing', 'academicAdvisor']);

        $mentor = $placement->mentor ?? $placement->pembimbing;
        $dpl = $placement->academicAdvisor;
        $student = $placement->application?->user;

        $isMember = ($mentor && (int) $user->id === (int) $mentor->id)
            || ($dpl && (int) $user->id === (int) $dpl->id)
            || ($student && (int) $user->id === (int) $student->id)
            || $user->isSuperAdmin();

        abort_unless($isMember, 403, 'Anda tidak memiliki akses ke grup bimbingan ini.');

        $conversation = null;

        if ($mentor && (int) $user->id === (int) $mentor->id) {
            $conversation = $this->groups->syncMentorGroup($mentor);
        } elseif ($dpl && (int) $user->id === (int) $dpl->id) {
            $conversation = $this->groups->syncDplGroup($dpl);
        } elseif ($student && (int) $user->id === (int) $student->id) {
            if ($request->query('type') === 'dpl' && $dpl) {
                $conversation = $this->groups->syncDplGroup($dpl);
            } elseif ($mentor) {
                $conversation = $this->groups->syncMentorGroup($mentor);
            } elseif ($dpl) {
                $conversation = $this->groups->syncDplGroup($dpl);
            }
        } elseif ($user->isSuperAdmin()) {
            $conversation = ($mentor ? $this->groups->syncMentorGroup($mentor) : null)
                ?? ($dpl ? $this->groups->syncDplGroup($dpl) : null);
        }

        abort_unless($conversation, 404, 'Grup Bimbingan belum dibuat.');
        $this->chat->participantOrFail($conversation, $request->user());

        return redirect()->route('chat.show', $conversation->id);
    }

    private function config(Request $request, ?int $conversationId): array
    {
        $id = ['conversation' => '__ID__'];

        return [
            'me' => $this->presenter->user($request->user()),
            'initialConversationId' => $conversationId,
            'readOnly' => $request->session()->has('impersonator_id'),
            'canCreateGroups' => $this->contacts->canCreateGroups($request->user()),
            'isSuperAdmin' => $request->user()->isSuperAdmin(),
            'maxUploadKb' => ChatAttachmentStorage::maxUploadKb(),
            'maxTotalKb' => ChatAttachmentStorage::maxTotalKb(),
            'maxFiles' => (int) config('chat.max_files_per_message', 5),
            'allowedExtensions' => ChatAttachmentStorage::allowedExtensions(),
            'maxBodyLength' => (int) config('chat.max_body_length', 5000),
            'voiceMaxSeconds' => (int) config('chat.voice_note_max_seconds', 300),
            'groupMaxMembers' => (int) config('chat.group_max_members', 100),
            'reportReasons' => config('chat.report_reasons'),
            'poll' => config('chat.poll'),
            // URL relatif; "__ID__" diganti di browser
            'urls' => [
                'index' => route('chat.index', [], false),
                'show' => route('chat.show', $id, false),
                'start' => route('chat.start', [], false),
                'conversations' => route('chat.api.conversations', [], false),
                'contacts' => route('chat.api.contacts', [], false),
                'groups' => route('chat.api.groups.store', [], false),
                'detail' => route('chat.api.conversations.show', $id, false),
                'update' => route('chat.api.groups.update', $id, false),
                'members' => route('chat.api.groups.members.store', $id, false),
                'removeMember' => route('chat.api.groups.members.destroy', ['conversation' => '__ID__', 'user' => '__USER__'], false),
                'leave' => route('chat.api.groups.leave', $id, false),
                'settings' => route('chat.api.conversations.settings', $id, false),
                'media' => route('chat.api.conversations.media', $id, false),
                'messages' => route('chat.api.messages.index', $id, false),
                'send' => route('chat.api.messages.store', $id, false),
                'read' => route('chat.api.messages.read', $id, false),
                'typing' => route('chat.api.messages.typing', $id, false),
                'deleteMessage' => route('chat.api.messages.destroy', ['message' => '__ID__'], false),
                'report' => route('chat.api.messages.report', ['message' => '__ID__'], false),
                'comments' => route('chat.api.messages.comments.index', ['message' => '__ID__'], false),
                'sendComment' => route('chat.api.messages.comments.store', ['message' => '__ID__'], false),
            ],
        ];
    }
}
