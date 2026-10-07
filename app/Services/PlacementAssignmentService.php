<?php

namespace App\Services;

use App\Models\Application;
use App\Models\AuditLog;
use App\Models\Placement;
use App\Models\User;
use App\Services\Chat\ChatGroupService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Penugasan susulan Mentor Dinas & Dosen Pembimbing Lapangan (DPL) pada penempatan yang sudah
 * berjalan (ACCEPTED / ACTIVE) tanpa mengulang verifikasi pengajuan.
 *
 * Hanya kolom penugasan pada `placements` yang diubah — histori logbook (status & feedback
 * yang sudah divalidasi), evaluasi, laporan akhir, dan sertifikat tetap utuh.
 */
class PlacementAssignmentService
{
    /** Status pengajuan yang boleh menerima penugasan susulan. */
    public const ASSIGNABLE_STATUSES = ['accepted', 'active'];

    /**
     * @return array<string, array{from: ?string, to: ?string}> daftar perubahan (kosong bila tidak ada)
     *
     * @throws ValidationException
     */
    public function assign(Application $application, User $actor, ?int $mentorId, ?int $advisorId): array
    {
        $application->loadMissing(['unit', 'user', 'placement.mentor', 'placement.pembimbing', 'placement.academicAdvisor']);

        $status = $application->statusValue();
        if (! in_array($status, self::ASSIGNABLE_STATUSES, true)) {
            throw ValidationException::withMessages([
                'assignment' => 'Penugasan pembimbing susulan hanya dapat dilakukan pada penempatan berstatus Diterima atau Aktif.',
            ]);
        }

        if (! $mentorId && ! $advisorId) {
            throw ValidationException::withMessages([
                'assignment' => 'Pilih minimal satu pembimbing (Mentor Dinas atau DPL) yang akan ditetapkan.',
            ]);
        }

        $mentor = $mentorId ? $this->resolveMentor($application, $mentorId) : null;
        $advisor = $advisorId ? $this->resolveAdvisor($application, $advisorId) : null;

        $placement = $application->placement;
        $previousMentor = $placement?->mentor ?? $placement?->pembimbing;
        $previousAdvisor = $placement?->academicAdvisor;

        $data = [];
        $changes = [];

        if ($mentor && (int) ($placement?->mentor_id ?? 0) !== (int) $mentor->id) {
            $data['mentor_id'] = $mentor->id;
            $data['pembimbing_id'] = $mentor->id; // sinkron kolom legacy
            $changes['mentor'] = ['from' => $previousMentor?->name, 'to' => $mentor->name];
        }

        if ($advisor && (int) ($placement?->academic_advisor_id ?? 0) !== (int) $advisor->id) {
            $data['academic_advisor_id'] = $advisor->id;
            $changes['academic_advisor'] = ['from' => $previousAdvisor?->name, 'to' => $advisor->name];
        }

        if (empty($data)) {
            return [];
        }

        DB::transaction(function () use ($application, $data, $changes, $actor, $status) {
            $placement = Placement::updateOrCreate(['application_id' => $application->id], $data);

            AuditLog::record('PLACEMENT_SUPERVISOR_REASSIGN', 'Placement', $placement->id, [
                'student_name' => $application->user?->name,
                'application_status' => $status,
                'changes' => $changes,
                'actor_role' => $actor->role,
            ]);
        });

        $application->unsetRelation('placement');

        // Grup bimbingan pembimbing lama ikut disinkronkan agar mahasiswa keluar dari grup lamanya.
        $this->resyncPreviousGroups(
            isset($data['mentor_id']) ? $previousMentor : null,
            isset($data['academic_advisor_id']) ? $previousAdvisor : null,
        );

        return $changes;
    }

    public function resolveMentor(Application $application, int $mentorId): User
    {
        $mentor = User::whereIn('role', ['mentor', 'pembimbing'])->find($mentorId);
        $agencyId = $application->unit?->agency_profile_id;

        if (! $mentor || ($agencyId !== null && (int) $mentor->agency_profile_id !== (int) $agencyId)) {
            throw ValidationException::withMessages([
                'mentor_id' => 'Mentor yang dipilih harus akun mentor resmi dari instansi penempatan mahasiswa.',
            ]);
        }

        return $mentor;
    }

    public function resolveAdvisor(Application $application, int $advisorId): User
    {
        $advisor = User::whereIn('role', ['dosen', 'academic_advisor'])->find($advisorId);
        $student = $application->user;
        $studentUnivId = app(UniversityResolver::class)->forUser($student)?->id;

        if (! $advisor || ($studentUnivId && (int) $advisor->university_id !== (int) $studentUnivId)) {
            throw ValidationException::withMessages([
                'academic_advisor_id' => 'Dosen Pembimbing yang dipilih harus berasal dari perguruan tinggi mahasiswa.',
            ]);
        }

        return $advisor;
    }

    protected function resyncPreviousGroups(?User $oldMentor, ?User $oldAdvisor): void
    {
        if (! $oldMentor && ! $oldAdvisor) {
            return;
        }

        try {
            $groups = app(ChatGroupService::class);
            if ($oldMentor) {
                $groups->syncMentorGroup($oldMentor);
            }
            if ($oldAdvisor) {
                $groups->syncDplGroup($oldAdvisor);
            }
        } catch (\Throwable $e) {
            report($e); // kegagalan chat tidak boleh menggagalkan penugasan
        }
    }
}
