<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgencyProfile extends Model
{
    protected $guarded = ['id'];

    public function units()
    {
        return $this->hasMany(Unit::class, 'agency_profile_id');
    }
}
