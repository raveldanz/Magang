<?php

namespace App\Services\Chat;

use App\Enums\AccountStatus;
use App\Models\Application;
use App\Models\Placement;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Aturan siapa boleh memulai chat dengan siapa (berlaku dua arah):
 *  - Super Admin ↔ semua pengguna (helpdesk).
 *  - Rekan satu dinas (admin & mentor) dan rekan satu kampus (admin kampus & dosen).
 *  - Mahasiswa ↔ admin kampus & dosen kampusnya, admin dinas tempat ia mengajukan,
 *    mentor & DPL penempatannya.
 *  - Admin dinas ↔ DPL mahasiswa di dinasnya, admin kampus asal pelamar.
 *  - Mentor ↔ DPL mahasiswa bimbingannya; DPL ↔ admin dinas dari mahasiswa bimbingannya.
 * Akun nonaktif tidak pernah muncul sebagai kontak.
 */
class ChatContactDirectory
{
    public const AGENCY_STAFF_ROLES = ['admin', 'mentor', 'pembimbing'];

    public const MENTOR_ROLES = ['mentor', 'pembimbing'];

    public const LECTURER_ROLES = ['dosen', 'academic_advisor'];

    public const CAMPUS_STAFF_ROLES = ['universitas', 'dosen', 'academic_advisor'];

    /**
     * @param  bool  $strict  true = hanya hubungan kerja langsung (dipakai untuk data pribadi):
     *                        dosen ↔ mahasiswa sekampus hanya bila dosen tersebut DPL-nya.
     */
    public function contactsQuery(User $user, bool $strict = false): Builder
    {
        $query = User::query()
            ->whereKeyNot($user->id)
            ->where(fn (Builder $q) => $q->whereNull('status')->orWhere('status', '!=', AccountStatus::INACTIVE->value));

        if ($user->isSuperAdmin()) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($user, $strict) {
            $this->whereSuperAdmin($q);
            $this->applyRoleContacts($q, $user, $strict);
        });
    }

    public function canContact(User $from, User $to): bool
    {
        return $this->contactsQuery($from)->whereKey($to->id)->exists();
    }

    /**
     * Dari $userIds, siapa saja yang email, nomor telepon, dan data studinya boleh dilihat $viewer.
     * Lebih ketat daripada izin chat: Super Admin melihat semua; selain itu hanya pihak yang punya
     * hubungan magang langsung (mentor/DPL penempatan, admin dinas tujuan, admin kampus, rekan
     * satu instansi). Mahasiswa sekampus yang bukan bimbingan tidak melihat data pribadi dosen,
     * dan sebaliknya.
     *
     * @return int[]
     */
    public function personalDetailsVisibleTo(User $viewer, array $userIds): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $userIds))));
        if (! $ids) {
            return [];
        }
        if ($viewer->isSuperAdmin()) {
            return $ids;
        }

        $visible = $this->contactsQuery($viewer, strict: true)->whereIn('id', $ids)->pluck('id')->map(fn ($id) => (int) $id)->all();
        if (in_array((int) $viewer->id, $ids, true)) {
            $visible[] = (int) $viewer->id;
        }

        return $visible;
    }

    /**
     * Grup hanya dibuat oleh staf (Super Admin, admin dinas, mentor, DPL, admin kampus), bukan mahasiswa.
     */
    public function canCreateGroups(User $user): bool
    {
        return $user->role !== 'mahasiswa';
    }

    private function applyRoleContacts(Builder $q, User $user, bool $strict = false): void
    {
        $role = $user->role;

        if ($role === 'mahasiswa') {
            // Mode ketat: dari pihak kampus hanya admin kampus; DPL ikut lewat data penempatan di bawah
            $this->orCampusStaff($q, $user->university_id, $strict ? ['universitas'] : self::CAMPUS_STAFF_ROLES);
            $applicationIds = Application::select('id')->where('user_id', $user->id);
            $q->orWhere(fn (Builder $w) => $w->where('role', 'admin')->whereIn('agency_profile_id',
                Unit::select('agency_profile_id')->whereIn('id', Application::select('unit_id')->where('user_id', $user->id))));
            $this->orPlacementColumns($q, ['mentor_id', 'pembimbing_id', 'academic_advisor_id'],
                fn ($p) => $p->whereIn('application_id', $applicationIds));

            return;
        }

        if ($role === 'admin') {
            $agencyId = $user->agency_profile_id;
            $this->orAgencyStaff($q, $agencyId);
            $agencyApplications = Application::select('id')->whereIn('unit_id', Unit::select('id')->where('agency_profile_id', $agencyId));
            $applicantIds = Application::select('user_id')->whereIn('unit_id', Unit::select('id')->where('agency_profile_id', $agencyId));
            $q->orWhere(fn (Builder $w) => $w->where('role', 'mahasiswa')->whereIn('id', $applicantIds));
            $this->orPlacementColumns($q, ['academic_advisor_id'], fn ($p) => $p->whereIn('application_id', $agencyApplications));
            $q->orWhere(fn (Builder $w) => $w->where('role', 'universitas')->whereIn('university_id',
                User::select('university_id')->whereNotNull('university_id')->whereIn('id', $applicantIds)));

            return;
        }

        if (in_array($role, self::MENTOR_ROLES, true)) {
            $this->orAgencyStaff($q, $user->agency_profile_id);
            $mine = fn ($p) => $p->where(fn ($w) => $w->where('mentor_id', $user->id)->orWhere('pembimbing_id', $user->id));
            $q->orWhereIn('id', Application::select('user_id')->whereIn('id', $mine(Placement::select('application_id'))));
            $this->orPlacementColumns($q, ['academic_advisor_id'], $mine);

            return;
        }

        if (in_array($role, self::LECTURER_ROLES, true)) {
            $this->orCampusStaff($q, $user->university_id);
            // Mode ketat: mahasiswa sekampus hanya yang menjadi bimbingan (lewat data penempatan di bawah)
            if (! $strict) {
                $this->orCampusStudents($q, $user->university_id);
            }
            $advised = Placement::select('application_id')->where('academic_advisor_id', $user->id);
            $q->orWhereIn('id', Application::select('user_id')->whereIn('id', $advised));
            $this->orPlacementColumns($q, ['mentor_id', 'pembimbing_id'], fn ($p) => $p->where('academic_advisor_id', $user->id));
            $q->orWhere(fn (Builder $w) => $w->where('role', 'admin')->whereIn('agency_profile_id',
                Unit::select('agency_profile_id')->whereIn('id', Application::select('unit_id')->whereIn('id', $advised))));

            return;
        }

        if ($role === 'universitas') {
            $this->orCampusStaff($q, $user->university_id);
            $this->orCampusStudents($q, $user->university_id);
            if ($user->university_id) {
                $campusStudents = User::select('id')->where('role', 'mahasiswa')->where('university_id', $user->university_id);
                $q->orWhere(fn (Builder $w) => $w->where('role', 'admin')->whereIn('agency_profile_id',
                    Unit::select('agency_profile_id')->whereIn('id', Application::select('unit_id')->whereIn('user_id', $campusStudents))));
            }
        }
    }

    private function whereSuperAdmin(Builder $q): void
    {
        $q->where(fn (Builder $w) => $w->where('role', 'super_admin')
            ->orWhere(fn (Builder $a) => $a->where('role', 'admin')->whereNull('agency_profile_id')));
    }

    private function orAgencyStaff(Builder $q, ?int $agencyId): void
    {
        // Guard wajib: where('agency_profile_id', null) akan mencocokkan semua akun tanpa dinas
        if ($agencyId) {
            $q->orWhere(fn (Builder $w) => $w->where('agency_profile_id', $agencyId)->whereIn('role', self::AGENCY_STAFF_ROLES));
        }
    }

    private function orCampusStaff(Builder $q, ?int $universityId, array $roles = self::CAMPUS_STAFF_ROLES): void
    {
        if ($universityId) {
            $q->orWhere(fn (Builder $w) => $w->where('university_id', $universityId)->whereIn('role', $roles));
        }
    }

    private function orCampusStudents(Builder $q, ?int $universityId): void
    {
        if ($universityId) {
            $q->orWhere(fn (Builder $w) => $w->where('university_id', $universityId)->where('role', 'mahasiswa'));
        }
    }

    /**
     * Pengguna yang tercatat pada kolom pembimbing penempatan (mentor/pembimbing/DPL).
     */
    private function orPlacementColumns(Builder $q, array $columns, callable $scope): void
    {
        foreach ($columns as $column) {
            $q->orWhereIn('id', $scope(Placement::select($column)->whereNotNull($column)));
        }
    }
}
