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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use NotificationChannels\WebPush\HasPushSubscriptions;

#[Fillable(['name', 'email', 'password', 'role', 'status', 'agency_profile_id', 'unit_id', 'university', 'university_id', 'last_notification_read_at', 'phone'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    // HasApiTokens wajib untuk API login Sanctum (createToken / tokens) di Api\AuthController
    use HasApiTokens, HasFactory, HasPushSubscriptions, Notifiable;

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

    /** Password bawaan lama (akun demo/seed). Akun baru dari staf memakai issueTemporaryPassword(). */
    public const DEFAULT_PASSWORD = 'password';

    /** Penanda sementara (bukan kolom DB) untuk issueTemporaryPassword(). */
    public bool $issuingTemporaryPassword = false;

    protected static function booted(): void
    {
        // Setiap kali password berubah, tandai apakah masih password bawaan. Berlaku untuk semua
        // jalur (buat akun, reset oleh admin, seeder) tanpa perlu mengubah tiap controller;
        // flag otomatis hilang saat pemilik akun mengganti passwordnya sendiri.
        // Password sementara dari issueTemporaryPassword selalu wajib diganti (penanda tidak dihitung ulang).
        static::saving(function (User $user) {
            // Password berganti → cookie "Ingat saya" lama tidak berlaku lagi
            if ($user->exists && $user->isDirty('password') && ! $user->isDirty('remember_token')) {
                $user->remember_token = Str::random(60);
            }

            if ($user->issuingTemporaryPassword) {
                $user->must_change_password = true;
                $user->issuingTemporaryPassword = false;

                return;
            }
            if ($user->isDirty('password') && ! empty($user->getAttributes()['password'] ?? null)) {
                try {
                    $user->must_change_password = Hash::check(self::DEFAULT_PASSWORD, $user->getAttributes()['password']);
                } catch (\Throwable $e) {
                    $user->must_change_password = false;
                }
            }
        });

        // Password berganti (reset sendiri, ganti di profil, atau reset oleh staf) → semua token API
        // lama dicabut dan sesi web lain milik akun ini diputus. Sesi yang sedang dipakai pemilik
        // akun untuk mengganti passwordnya sendiri tetap dipertahankan.
        static::updated(function (User $user) {
            if (! $user->wasChanged('password')) {
                return;
            }

            $user->tokens()->delete();

            if (config('session.driver') === 'database') {
                $keepSessionId = (auth()->id() === $user->id && request()->hasSession())
                    ? request()->session()->getId()
                    : null;

                DB::connection(config('session.connection'))
                    ->table(config('session.table', 'sessions'))
                    ->where('user_id', $user->id)
                    ->when($keepSessionId, fn ($q) => $q->where('id', '!=', $keepSessionId))
                    ->delete();
            }
        });
    }

    /** Password acak untuk akun yang dibuat/di-reset staf; hanya ditampilkan sekali kepada staf tersebut. */
    public static function generateTemporaryPassword(): string
    {
        return 'Mg-'.Str::password(12, letters: true, numbers: true, symbols: false);
    }

    /**
     * Ganti password akun dengan password sementara acak dan wajibkan pemiliknya menggantinya
     * saat login berikutnya. Mengembalikan password polos untuk ditampilkan sekali.
     */
    public function issueTemporaryPassword(): string
    {
        $plain = self::generateTemporaryPassword();
        $this->issuingTemporaryPassword = true;
        $this->forceFill(['password' => Hash::make($plain)])->save();

        return $plain;
    }

    /**
     * Super Admin = role 'super_admin' ATAU role 'admin' tanpa agency_profile_id (Admin Sistem).
     */
    public function isSuperAdmin(): bool
    {
        return in_array($this->role, ['super_admin', 'superadmin'])
            || ($this->role === 'admin' && is_null($this->agency_profile_id));
    }

    /**
     * Supervisor = Tim Kerja Umum dan Kepegawaian Dispusip.
     */
    public function isSupervisor(): bool
    {
        return $this->role === 'supervisor';
    }

    /**
     * Peran lapangan/eksekutif yang antarmukanya menampilkan status berbahasa Indonesia terstandar
     * demi kemudahan baca pengguna senior, tanpa mengubah kode status bahasa Inggris di sistem.
     */
    public static function isLocalizedStatusUiRole(?string $role): bool
    {
        return in_array($role, ['mentor', 'pembimbing', 'supervisor'], true);
    }

    public function usesLocalizedStatusUi(): bool
    {
        return self::isLocalizedStatusUiRole($this->role);
    }

    /**
     * Memeriksa apakah user adalah Admin Bidang spesifik.
     */
    public function isBidangAdmin(): bool
    {
        return $this->role === 'admin' && ! is_null($this->unit_id);
    }

    /**
     * Memeriksa apakah user memiliki akses dinas keseluruhan (Super Admin atau Admin Dinas tanpa unit khusus).
     */
    public function isAgencyWideAdmin(): bool
    {
        return $this->isSuperAdmin() || ($this->role === 'admin' && is_null($this->unit_id));
    }

    /**
     * Standarisasi label role pengguna untuk seluruh antarmuka sistem.
     */
    public function roleLabel(): string
    {
        if ($this->isSuperAdmin()) {
            return 'Super Admin';
        }

        if ($this->role === 'admin') {
            if ($this->unit_id) {
                $unit = $this->relationLoaded('unit') ? $this->unit : Unit::find($this->unit_id);

                return $unit ? 'Admin '.$unit->name : 'Admin Bidang';
            }

            return 'Admin Dispusip';
        }

        return match ($this->role) {
            'super_admin' => 'Super Admin',
            'supervisor' => 'Supervisor',
            // Role 'pembimbing' adalah alias Mentor Lapangan (lihat CheckRole), bukan pembimbing kampus/sekolah.
            'mentor', 'pembimbing' => 'Mentor Lapangan',
            'lecturer', 'dosen', 'academic_advisor' => 'Pembimbing',
            'university', 'universitas' => 'Admin Kampus/Sekolah',
            'student', 'mahasiswa' => 'Peserta Magang',
            default => ucwords(str_replace('_', ' ', (string) $this->role)),
        };
    }

    public function getRoleLabelAttribute(): string
    {
        return $this->roleLabel();
    }

    public function studentProfile()
    {
        return $this->hasOne(StudentProfile::class);
    }

    public function agencyProfile()
    {
        return $this->belongsTo(AgencyProfile::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
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

    /** Role Pembimbing kampus/sekolah (dosen atau guru), termasuk alias lama. */
    public const ADVISOR_ROLES = ['dosen', 'academic_advisor'];

    public function isAdvisor(): bool
    {
        return in_array($this->role, self::ADVISOR_ROLES, true);
    }

    /**
     * Pembimbing yang mendaftar sendiri dan belum disetujui Admin Kampus/Sekolah atau Super Admin.
     * Boleh login, tetapi belum boleh dipilih mahasiswa atau ditugaskan membimbing.
     */
    public function isPendingApproval(): bool
    {
        return AccountStatus::resolve($this->status) === AccountStatus::PENDING;
    }

    /**
     * Pembimbing yang boleh dipilih / ditugaskan: semua akun pembimbing kecuali yang masih
     * Menunggu Persetujuan (status kosong pada data lama dianggap Aktif).
     */
    public function scopeAssignableAdvisors($query)
    {
        return $query->whereIn('role', self::ADVISOR_ROLES)
            ->where(fn ($q) => $q->whereNull('status')->orWhere('status', '!=', AccountStatus::PENDING->value));
    }
}
