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
}
