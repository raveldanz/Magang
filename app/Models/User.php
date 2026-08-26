<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

<<<<<<< HEAD
#[Fillable(['name', 'email', 'password', 'role', 'agency_profile_id', 'university'])]
=======
#[Fillable(['name', 'email', 'password', 'role', 'status', 'agency_profile_id', 'university', 'university_id', 'last_notification_read_at'])]
>>>>>>> main
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_notification_read_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function studentProfile()
    {
        return $this->hasOne(StudentProfile::class);
    }
<<<<<<< HEAD

    public function agencyProfile()
    {
        return $this->belongsTo(AgencyProfile::class);
    }
}


=======

    public function agencyProfile()
    {
        return $this->belongsTo(AgencyProfile::class);
    }

    public function university()
    {
        return $this->belongsTo(University::class, 'university_id');
    }

    public function universityRelation()
    {
        return $this->belongsTo(University::class, 'university_id');
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function academicPlacements()
    {
        return $this->hasMany(Placement::class, 'academic_advisor_id');
    }

    public function mentorPlacements()
    {
        return $this->hasMany(Placement::class, 'mentor_id');
    }
}
>>>>>>> main
