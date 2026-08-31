<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentProfile extends Model
{
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function university()
    {
        return $this->belongsTo(University::class);
    }

    // Accessor & Mutator Helper untuk fleksibilitas nama atribut
    public function getFacultyAttribute($value)
    {
        return $value ?: $this->attributes['fakultas'] ?? null;
    }

    public function getFakultasAttribute($value)
    {
        return $value ?: $this->attributes['faculty'] ?? null;
    }

    public function getMajorAttribute($value)
    {
        return $value ?: $this->attributes['jurusan'] ?? null;
    }

    public function getJurusanAttribute($value)
    {
        return $value ?: $this->attributes['major'] ?? null;
    }

    public function getAddressAttribute($value)
    {
        return $value ?: $this->attributes['alamat'] ?? null;
    }

    public function getAlamatAttribute($value)
    {
        return $value ?: $this->attributes['address'] ?? null;
    }
}