<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicConsultation extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'consultation_date' => 'date',
    ];

    public function placement()
    {
        return $this->belongsTo(Placement::class);
    }

    public function academicAdvisor()
    {
        return $this->belongsTo(User::class, 'academic_advisor_id');
    }
}
