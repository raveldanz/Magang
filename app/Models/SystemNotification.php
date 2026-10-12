<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemNotification extends Model
{
    use HasFactory;

    protected $table = 'system_notifications';

    protected $fillable = [
        'user_id',
        'target_role',
        'type',
        'category',
        'icon',
        'title',
        'message',
        'action_url',
        'action_label',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeForUser($query, $user)
    {
        return $query->where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
              ->orWhere(function ($subQ) use ($user) {
                  $subQ->whereNull('user_id')
                       ->where(function ($roleQ) use ($user) {
                           $roleQ->whereNull('target_role')
                                 ->orWhere('target_role', $user->role);
                       });
              });
        });
    }

    public static function send($title, $message, $userId = null, $targetRole = null, $actionUrl = null, $actionLabel = null, $type = 'info', $category = 'system', $icon = '🔔')
    {
        return self::create([
            'user_id' => $userId,
            'target_role' => $targetRole,
            'title' => $title,
            'message' => $message,
            'action_url' => $actionUrl,
            'action_label' => $actionLabel,
            'type' => $type,
            'category' => $category,
            'icon' => $icon,
        ]);
    }
}
