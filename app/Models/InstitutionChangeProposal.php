<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Perbedaan dari API yang menunggu keputusan admin; data lokal tidak diubah sebelum diterima. */
class InstitutionChangeProposal extends Model
{
    public const KIND_NAME = 'name';

    public const KIND_ADDRESS = 'address';

    public const KIND_NEW_COLLEGE = 'new_college';

    protected $guarded = ['id'];

    protected $casts = [
        'payload' => 'array',
        'decided_at' => 'datetime',
    ];

    public function university(): BelongsTo
    {
        return $this->belongsTo(University::class);
    }
}
