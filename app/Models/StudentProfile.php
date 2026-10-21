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

    /**
     * URL foto profil lewat route terotorisasi (foto disimpan di disk privat).
     */
    public function getPhotoUrlAttribute(): ?string
    {
        return ! empty($this->attributes['photo'] ?? null) ? route('student.photo', $this->user_id) : null;
    }

    public function getCvUrlAttribute(): ?string
    {
        return ! empty($this->cv_path) ? route('student.document', ['userId' => $this->user_id, 'type' => 'cv']) : null;
    }

    public function getTranscriptUrlAttribute(): ?string
    {
        return ! empty($this->transcript_path) ? route('student.document', ['userId' => $this->user_id, 'type' => 'transkrip']) : null;
    }

    public function getIdCardUrlAttribute(): ?string
    {
        return ! empty($this->id_card_path) ? route('student.document', ['userId' => $this->user_id, 'type' => 'id_card']) : null;
    }

    public function hasCompletePersonalDocuments(): bool
    {
        return ! empty($this->cv_path) && ! empty($this->transcript_path) && ! empty($this->id_card_path);
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
