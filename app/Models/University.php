<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class University extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'address',
        'phone',
        'email',
        'pic_name',
        'pic_nip',
        'pic_position',
        'logo',
        'evaluation_scheme',
        'weight_mentor',
        'weight_lecturer',
        'require_dpl',
    ];

    protected $casts = [
        'weight_mentor' => 'integer',
        'weight_lecturer' => 'integer',
        'require_dpl' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function dosens()
    {
        return $this->hasMany(User::class)->whereIn('role', ['dosen', 'academic_advisor']);
    }

    public function students()
    {
        return $this->hasMany(User::class)->where('role', 'mahasiswa');
    }

    public function universityAdmin()
    {
        return $this->hasOne(User::class)->where('role', 'universitas');
    }
}
