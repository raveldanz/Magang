<?php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Services\Chat\ChatMessageService;
use Illuminate\Http\Request;

/**
 * Moderasi Super Admin dari halaman tiket "Laporan Pesan Chat" (admin.feedbacks.show).
 */
class ChatModerationController extends Controller
{
    public function __construct(private ChatMessageService $messages) {}

    public function destroy(Request $request, ChatMessage $message)
    {
        abort_unless($request->user()->isSuperAdmin(), 403, 'Hanya Super Admin yang dapat memoderasi pesan chat.');

        $this->messages->delete($message, $request->user(), moderation: true);

        return back()->with('success', 'Pesan chat yang dilaporkan telah dihapus untuk semua peserta percakapan.');
    }
}
