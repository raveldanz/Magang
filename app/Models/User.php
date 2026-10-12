<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\AccountStatus;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'role', 'status', 'agency_profile_id', 'university', 'university_id', 'last_notification_read_at', 'phone'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    // HasApiTokens wajib untuk API login Sanctum (createToken / tokens) di Api\AuthController
    use HasApiTokens, HasFactory, Notifiable;

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
            // Status online chat; diperbarui ChatService::touchPresence (bukan lewat mass assignment)
            'last_seen_at' => 'datetime',
            'password' => 'hashed',
            'must_change_password' => 'boolean',
        ];
    }

    /** Password bawaan untuk akun yang dibuat/di-reset oleh admin. */
    public const DEFAULT_PASSWORD = 'password';

    protected static function booted(): void
    {
        // Setiap kali password berubah, tandai apakah masih password bawaan. Berlaku untuk semua
        // jalur (buat akun, reset oleh admin, seeder) tanpa perlu mengubah tiap controller;
        // flag otomatis hilang saat pemilik akun mengganti passwordnya sendiri.
        static::saving(function (User $user) {
            if ($user->isDirty('password') && ! empty($user->getAttributes()['password'] ?? null)) {
                try {
                    $user->must_change_password = Hash::check(self::DEFAULT_PASSWORD, $user->getAttributes()['password']);
                } catch (\Throwable $e) {
                    $user->must_change_password = false;
                }
            }
        });
    }

    /**
     * Super Admin = role 'super_admin' ATAU role 'admin' tanpa agency_profile_id (Admin Sistem).
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin'
            || ($this->role === 'admin' && is_null($this->agency_profile_id));
    }

    public function studentProfile()
    {
        return $this->hasOne(StudentProfile::class);
    }

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

    public function pembimbingPlacements()
    {
        return $this->hasMany(Placement::class, 'pembimbing_id');
    }

    /**
     * Jumlah riwayat magang yang melekat pada akun: pengajuan (mahasiswa) atau penempatan
     * yang pernah dibimbing (mentor / pembimbing / dosen). Akun dengan riwayat wajib dipertahankan
     * sebagai arsip — menghapusnya ikut menghapus (cascade) atau mengosongkan data alumni & sertifikat.
     */
    public function internshipHistoryCount(): int
    {
        return $this->applications()->count()
            + $this->mentorPlacements()->count()
            + $this->pembimbingPlacements()->count()
            + $this->academicPlacements()->count();
    }

    public function hasInternshipHistory(): bool
    {
        return $this->internshipHistoryCount() > 0;
    }

    /**
     * Akun berstatus Nonaktif tidak boleh login. Super Admin / Admin Sistem dikecualikan
     * agar sistem tidak pernah terkunci tanpa administrator.
     */
    public function isInactive(): bool
    {
        return AccountStatus::resolve($this->status) === AccountStatus::INACTIVE && ! $this->isSuperAdmin();
    }
}