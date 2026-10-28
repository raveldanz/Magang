<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectCommentLike extends Model
{
    protected $guarded = ['id'];

    public function comment()
    {
        return $this->belongsTo(ProjectComment::class, 'project_comment_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
