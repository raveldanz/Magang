<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Evaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'placement_id',
        'nilai_disiplin',
        'nilai_kinerja',
        'nilai_laporan',
        'nilai_akademik',
        'catatan',
        'catatan_dosen',
    ];

    public function placement()
    {
        return $this->belongsTo(Placement::class);
    }
}
