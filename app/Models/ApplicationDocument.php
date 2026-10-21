<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationDocument extends Model
{
    protected $guarded = ['id'];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function getNameAttribute($value)
    {
        return $value ?: ($this->attributes['document_type'] ?? 'Dokumen Pengajuan');
    }
}
