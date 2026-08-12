<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Placement extends Model
{

   use HasFactory;
    protected $fillable = [
        'application_id',
        'pembimbing_id',
        'status',
        ];

    public function application()
    {return $this->belongsTo(Application::class);
    }

    // Relasi ke User (Pembimbing)
    public function pembimbing()
    {
        return $this->belongsTo(User::class, 'pembimbing_id');
    }

    // Tambahkan relasi Logbooks melalui Application
    public function logbooks()
    {
        return $this->hasMany(Logbook::class, 'placement_id');
    }
    public function finalreport()
    {
        return $this->hasOne(FinalReport::class);
    }
}
