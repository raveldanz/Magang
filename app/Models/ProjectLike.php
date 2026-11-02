<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectLike extends Model
{
    protected $guarded = ['id'];

    public function project()
    {
        return $this->belongsTo(InternshipProject::class, 'internship_project_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
