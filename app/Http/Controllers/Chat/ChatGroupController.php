<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chat\ChatGroupRequest;
use App\Models\ChatConversation;
use App\Models\User;
use App\Services\Chat\ChatGroupService;
use App\Services\Chat\ChatService;
use Illuminate\Http\Request;

class ChatGroupController extends Controller
{
    public function __construct(private ChatGroupService $groups, private ChatService $chat) {}

    public function store(ChatGroupRequest $request)
    {
        $conversation = $this->groups->create(
            $request->user(),
            trim($request->input('title')),
            $request->input('description'),
            $request->input('member_ids', []),
        );

        return response()->json(['conversation' => $this->chat->conversationDetail($conversation, $request->user())], 201);
    }

    public function update(ChatGroupRequest $request, ChatConversation $conversation)
    {
        $conversation = $this->groups->update($conversation, $request->user(), trim($request->input('title')), $request->input('description'));

        return response()->json(['conversation' => $this->chat->conversationDetail($conversation, $request->user())]);
    }

    public function addMembers(Request $request, ChatConversation $conversation)
    {
        $data = $request->validate(['user_ids' => ['required', 'array', 'min:1'], 'user_ids.*' => ['integer']]);
        $this->groups->addMembers($conversation, $request->user(), $data['user_ids']);

        return response()->json(['conversation' => $this->chat->conversationDetail($conversation->fresh(), $request->user())]);
    }

    public function removeMember(Request $request, ChatConversation $conversation, User $user)
    {
        $this->groups->removeMember($conversation, $request->user(), $user);

        return response()->json(['conversation' => $this->chat->conversationDetail($conversation->fresh(), $request->user())]);
    }

    public function leave(Request $request, ChatConversation $conversation)
    {
        $this->groups->leave($conversation, $request->user());

        return response()->json(['ok' => true]);
    }

    public function settings(Request $request, ChatConversation $conversation)
    {
        $data = $request->validate(['muted' => ['nullable', 'boolean'], 'pinned' => ['nullable', 'boolean']]);
        $participant = $this->groups->updateSettings(
            $conversation,
            $request->user(),
            array_key_exists('muted', $data) ? (bool) $data['muted'] : null,
            array_key_exists('pinned', $data) ? (bool) $data['pinned'] : null,
        );

        return response()->json(['muted' => $participant->muted_at !== null, 'pinned' => $participant->pinned_at !== null]);
    }
}
