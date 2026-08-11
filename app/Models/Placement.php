<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Placement extends Model
{
    // Tambahkan baris ini untuk mengizinkan pengisian data otomatis
    protected $guarded = ['id'];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function pembimbing()
    {
        return $this->belongsTo(User::class, 'pembimbing_id');
    }

    public function logbook()
    {
        return $this->hasMany(Logbook::class);
    }

    public function finalreport()
    {
        return $this->hasOne(FinalReport::class);
    }
}
