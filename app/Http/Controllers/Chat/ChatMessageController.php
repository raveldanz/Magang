<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Http\Requests\Chat\ReportChatMessageRequest;
use App\Http\Requests\Chat\SendChatMessageRequest;
use App\Models\ChatAttachment;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\SystemFeedback;
use App\Services\Chat\ChatAttachmentStorage;
use App\Services\Chat\ChatMessageService;
use App\Services\Chat\ChatPresenter;
use App\Services\Chat\ChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChatMessageController extends Controller
{
    public function __construct(
        private ChatService $chat,
        private ChatMessageService $messages,
        private ChatPresenter $presenter,
        private ChatAttachmentStorage $storage,
    ) {}

    public function index(Request $request, ChatConversation $conversation)
    {
        $user = $request->user();
        $this->chat->participantOrFail($conversation, $user);
        if (! $this->impersonating($request)) {
            $this->chat->touchPresence($user);
        }

        $result = $this->chat->messagesFor(
            $conversation,
            $user,
            $request->filled('before') ? (int) $request->query('before') : null,
            $request->filled('after') ? (int) $request->query('after') : null,
            $request->query('since'),
        );

        return response()->json($result + [
            'state' => $this->chat->conversationState($conversation, $user),
            'conversation' => $request->boolean('detail') ? $this->chat->conversationDetail($conversation, $user) : null,
        ]);
    }

    public function store(SendChatMessageRequest $request, ChatConversation $conversation)
    {
        $message = $this->messages->send(
            $conversation,
            $request->user(),
            $request->input('body'),
            $request->attachments(),
            $request->filled('reply_to_id') ? (int) $request->input('reply_to_id') : null,
            $request->boolean('voice'),
        );

        return response()->json(['message' => $this->presenter->message($message, $request->user())], 201);
    }

    public function comments(Request $request, ChatMessage $message)
    {
        $user = $request->user();
        $this->chat->participantOrFail($message->conversation, $user);

        return response()->json($this->chat->commentsFor($message, $user));
    }

    public function storeComment(Request $request, ChatMessage $message)
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $comment = $this->messages->sendComment($message, $request->user(), $data['body']);

        return response()->json([
            'comment' => $this->presenter->message($comment, $request->user()),
            'comments_count' => (int) $message->fresh()->comments_count,
        ], 201);
    }

    public function destroy(Request $request, ChatMessage $message)
    {
        $message = $this->messages->delete($message, $request->user());

        return response()->json(['message' => $this->presenter->message($message, $request->user())]);
    }

    public function report(ReportChatMessageRequest $request, ChatMessage $message)
    {
        $this->messages->report($message, $request->user(), $request->input('reason'), $request->input('note'));

        return response()->json(['ok' => true, 'message' => 'Laporan terkirim. Tim Super Admin akan meninjau pesan ini.']);
    }

    public function read(Request $request, ChatConversation $conversation)
    {
        $data = $request->validate(['message_id' => ['required', 'integer', 'min:1']]);

        // Membuka chat saat menyamar tidak boleh menandai pesan milik pengguna itu sebagai dibaca
        if (! $this->impersonating($request)) {
            $this->chat->markRead($conversation, $request->user(), (int) $data['message_id']);
        }

        return response()->json(['ok' => true]);
    }

    public function typing(Request $request, ChatConversation $conversation)
    {
        if (! $this->impersonating($request)) {
            $this->chat->typing($conversation, $request->user());
        }

        return response()->json(['ok' => true]);
    }

    public function attachment(Request $request, ChatAttachment $attachment)
    {
        $message = $attachment->message;
        abort_if(! $message || $message->isDeleted(), 404, 'Lampiran tidak ditemukan.');

        // Peserta percakapan, atau Super Admin yang meninjau tiket laporan pesan ini
        $reviewingReport = $request->user()->isSuperAdmin()
            && SystemFeedback::where('chat_message_id', $message->id)->exists();
        if (! $reviewingReport) {
            $this->chat->participantOrFail($message->conversation, $request->user());
        }

        $path = $this->storage->absolutePath($attachment);
        abort_unless($path, 404, 'Lampiran tidak ditemukan.');

        $name = $attachment->name ?: 'lampiran';
        $inline = $attachment->isPreviewable() && ! $request->boolean('download');

        $response = response()->file($path, [
            'Content-Type' => $attachment->mime ?: 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, max-age=86400',
        ]);
        $response->setContentDisposition($inline ? 'inline' : 'attachment', $name, Str::ascii($name) ?: 'lampiran');

        return $response;
    }

    private function impersonating(Request $request): bool
    {
        return $request->hasSession() && $request->session()->has('impersonator_id');
    }
}
