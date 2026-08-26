<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinalReport extends Model
{
    protected $guarded=['id'];

    public function placement()
    {
        return $this->belongsTo(Placement::class);
    }
}
