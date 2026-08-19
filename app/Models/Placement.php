<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Placement extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'mentor_id',
        'pembimbing_id',
        'status',
    ];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    // Relasi ke User (Mentor)
    public function mentor()
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }

    // Relasi ke User (Pembimbing / Mentor)
    public function pembimbing()
    {
        return $this->belongsTo(User::class, 'pembimbing_id');
    }

    // Tambahkan relasi Logbooks
    public function logbooks()
    {
        return $this->hasMany(Logbook::class, 'placement_id');
    }

    public function finalreport()
    {
        return $this->hasOne(FinalReport::class);
    }

    public function evaluation()
    {
        return $this->hasOne(Evaluation::class);
    }

    // Accessor untuk mendapatkan AgencyProfile dari unit penempatan
    public function getAgencyProfileAttribute()
    {
        return $this->application?->unit?->agencyProfile ?? AgencyProfile::first();
    }
}
