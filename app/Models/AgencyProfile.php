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

    public function users()
    {
        return $this->hasMany(User::class, 'agency_profile_id');
    }

    public function agencyAdmin()
    {
        return $this->hasOne(User::class, 'agency_profile_id')->where('role', 'admin');
    }

    public function admins()
    {
        return $this->hasMany(User::class, 'agency_profile_id')->where('role', 'admin');
    }
}

