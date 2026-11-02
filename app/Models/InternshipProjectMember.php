<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InternshipProjectMember extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'is_leader' => 'boolean',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(InternshipProject::class, 'internship_project_id');
    }

    public function placement(): BelongsTo
    {
        return $this->belongsTo(Placement::class, 'placement_id');
    }
}

