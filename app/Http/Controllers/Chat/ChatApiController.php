<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Services\Chat\ChatContactDirectory;
use App\Services\Chat\ChatPresenter;
use App\Services\Chat\ChatService;
use Illuminate\Http\Request;

class ChatApiController extends Controller
{
    public function __construct(
        private ChatService $chat,
        private ChatContactDirectory $contacts,
        private ChatPresenter $presenter,
    ) {}

    public function conversations(Request $request)
    {
        return response()->json(['conversations' => $this->chat->conversationsFor($request->user())]);
    }

    public function show(Request $request, ChatConversation $conversation)
    {
        $this->chat->participantOrFail($conversation, $request->user());

        return response()->json([
            'conversation' => $this->chat->conversationDetail($conversation, $request->user()),
            'state' => $this->chat->conversationState($conversation, $request->user()),
        ]);
    }

    public function media(Request $request, ChatConversation $conversation)
    {
        $this->chat->participantOrFail($conversation, $request->user());

        return response()->json(['media' => $this->chat->mediaFor($conversation)]);
    }

    public function contacts(Request $request)
    {
        $search = trim((string) $request->query('q', ''));

        $users = $this->contacts->contactsQuery($request->user())
            ->when($search !== '', function ($q) use ($search) {
                $like = '%' . mb_strtolower($search) . '%';
                $q->where(fn ($w) => $w->whereRaw('LOWER(name) LIKE ?', [$like])->orWhereRaw('LOWER(email) LIKE ?', [$like]));
            })
            ->with(['agencyProfile', 'universityRelation'])
            ->orderBy('name')
            ->limit(80)
            ->get();

        return response()->json(['contacts' => $users->map(fn ($u) => $this->presenter->user($u))->values()]);
    }

    public function summary(Request $request)
    {
        $user = $request->user();
        if (!$request->session()->has('impersonator_id')) {
            $this->chat->touchPresence($user);
        }

        $after = $request->filled('after') ? (int) $request->query('after') : null;

        return response()->json($this->chat->summary($user, $after));
    }
}
