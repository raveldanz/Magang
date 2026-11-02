<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectComment extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'is_guest' => 'boolean',
    ];

    public function project()
    {
        return $this->belongsTo(InternshipProject::class, 'internship_project_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function likes()
    {
        return $this->hasMany(ProjectCommentLike::class, 'project_comment_id');
    }

    /**
     * Cek apakah komentar sudah di-like oleh user atau tamu tertentu
     */
    public function isLikedBy(?User $user, ?string $guestId = null): bool
    {
        if ($user) {
            return $this->likes()->where('user_id', $user->id)->exists();
        }

        if ($guestId) {
            return $this->likes()->where('guest_identifier', $guestId)->exists();
        }

        return false;
    }
}
