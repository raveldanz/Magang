<?php

namespace App\Http\Controllers;

use App\Models\SystemNotification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display unified notification center for the active user
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $notifications = NotificationService::getNotificationsForUser($user);

        // Filter category if requested
        $category = $request->query('category', 'all');
        if ($category === 'all') {
            // Exclude audit logs from 'all' tab so only important/actionable notifications are displayed
            $notifications = array_filter($notifications, function ($item) {
                return ($item['category'] ?? '') !== 'audit';
            });
        } elseif ($category === 'urgent') {
            $notifications = array_filter($notifications, function ($item) {
                return !empty($item['is_action_required']) || in_array($item['type'] ?? '', ['urgent', 'warning']);
            });
        } else {
            $notifications = array_filter($notifications, function ($item) use ($category) {
                return ($item['category'] ?? '') === $category;
            });
        }

        $unreadCount = NotificationService::getUnreadCount($user);

        return view('notifications.index', compact('notifications', 'unreadCount', 'category', 'user'));
    }

    /**
     * Mark a database notification as read
     */
    public function markAsRead($id)
    {
        $user = Auth::user();
        if (str_starts_with($id, 'db_')) {
            $dbId = str_replace('db_', '', $id);
            SystemNotification::where('id', $dbId)
                ->where(function ($q) use ($user) {
                    $q->where('user_id', $user->id)->orWhereNull('user_id');
                })
                ->update(['read_at' => now()]);
        }

        return redirect()->back()->with('success', 'Pemberitahuan ditandai sudah dibaca.');
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        $user = Auth::user();
        $user->update(['last_notification_read_at' => now()]);
        SystemNotification::forUser($user)->whereNull('read_at')->update(['read_at' => now()]);

        return redirect()->back()->with('success', 'Seluruh pemberitahuan telah ditandai sudah dibaca.');
    }
}
