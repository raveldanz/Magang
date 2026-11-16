<?php

namespace App\Services;

use App\Enums\AccountStatus;
use App\Models\AuditLog;
use App\Models\SystemNotification;
use App\Models\University;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Persetujuan akun Pembimbing (dosen/guru) yang mendaftar sendiri lewat halaman Daftar Akun.
 *
 * Akun baru berstatus Menunggu Persetujuan: boleh login dan melihat dashboard pembimbing, tetapi
 * belum bisa dipilih mahasiswa atau ditugaskan membimbing. Yang berhak menyetujui:
 *  - Admin Kampus/Sekolah (role universitas) dari institusi yang sama, atau
 *  - Super Admin (terutama untuk sekolah yang belum punya akun admin).
 * Tanpa persetujuan ini mahasiswa bisa membuat akun pembimbing palsu lalu menilai dirinya sendiri.
 */
class AdvisorApprovalService
{
    public function __construct(private UniversityResolver $resolver) {}

    /** Pembimbing yang menunggu persetujuan. */
    public function pendingQuery(): Builder
    {
        return User::query()
            ->whereIn('role', User::ADVISOR_ROLES)
            ->where('status', AccountStatus::PENDING->value);
    }

    /** Pembimbing menunggu persetujuan dari institusi Admin Kampus/Sekolah ini. */
    public function pendingForInstitutionAdmin(User $admin): Builder
    {
        return $this->pendingQuery()->where('university_id', $admin->university_id ?? 0);
    }

    /**
     * Pembimbing menunggu persetujuan yang institusinya belum punya Admin Kampus/Sekolah aktif,
     * sehingga hanya Super Admin yang bisa menyetujuinya.
     */
    public function pendingWithoutInstitutionAdmin(): Builder
    {
        return $this->pendingQuery()->whereNotExists(function ($q) {
            $q->selectRaw('1')
                ->from('users as institution_admins')
                ->whereColumn('institution_admins.university_id', 'users.university_id')
                ->where('institution_admins.role', 'universitas')
                ->where(fn ($s) => $s->whereNull('institution_admins.status')
                    ->orWhere('institution_admins.status', '!=', AccountStatus::INACTIVE->value));
        });
    }

    public function canApprove(User $actor, User $advisor): bool
    {
        if (! $advisor->isAdvisor()) {
            return false;
        }

        if ($actor->isSuperAdmin()) {
            return true;
        }

        if ($actor->role !== 'universitas' || ! $actor->university_id) {
            return false;
        }

        $university = University::find($actor->university_id);

        return $university !== null && $this->resolver->belongsTo($advisor, $university);
    }

    /**
     * Setujui akun Pembimbing: status menjadi Aktif, dicatat di log audit, dan pembimbing diberi tahu.
     */
    public function approve(User $actor, User $advisor): void
    {
        if (! $advisor->isPendingApproval()) {
            return;
        }

        DB::transaction(function () use ($actor, $advisor) {
            $advisor->update(['status' => AccountStatus::ACTIVE->value]);

            AuditLog::record('ADVISOR_ACCOUNT_APPROVE', 'User', $advisor->id, [
                'advisor_name' => $advisor->name,
                'advisor_email' => $advisor->email,
                'institution' => $advisor->university,
                'approved_by' => $actor->name,
            ]);

            SystemNotification::send(
                'Akun Pembimbing Anda Sudah Disetujui',
                'Akun Anda sudah disetujui. Peserta magang dari institusi Anda kini dapat memilih Anda sebagai pembimbing.',
                $advisor->id,
                null,
                route('lecturer.dashboard', [], false),
                'Buka Dashboard',
                'success',
                'account',
                ''
            );
        });
    }
}
