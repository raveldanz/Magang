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

    /**
     * Tautan notifikasi disimpan lengkap dengan alamat pembuatnya (mis. http://127.0.0.1:8000/...).
     * Penerima yang membuka aplikasi dari perangkat lain (HP lewat alamat IP WiFi) akan diarahkan
     * ke alamat yang tidak bisa dijangkau. Karena semua tautan notifikasi menuju halaman aplikasi
     * ini sendiri, kembalikan sebagai jalur relatif agar selalu mengikuti alamat yang sedang dipakai.
     */
    public function getActionUrlAttribute($value)
    {
        if (! is_string($value) || ! preg_match('#^https?://[^/]+#i', $value)) {
            return $value;
        }

        $parts = parse_url($value);
        if ($parts === false) {
            return $value;
        }

        return ($parts['path'] ?? '/')
            .(isset($parts['query']) ? '?'.$parts['query'] : '')
            .(isset($parts['fragment']) ? '#'.$parts['fragment'] : '');
    }

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
