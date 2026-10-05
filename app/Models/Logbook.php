<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Logbook extends Model
{
    protected $guarded = ['id'];

    public function placement()
    {
        return $this->belongsTo(Placement::class);
    }

    /**
     * URL lampiran lewat route terotorisasi (berkas disimpan di disk privat, bukan /storage publik).
     */
    public function getAttachmentUrlAttribute(): ?string
    {
        return $this->attachment ? route('logbooks.attachment', $this->id) : null;
    }

    /**
     * Siapa yang boleh melihat logbook & lampirannya:
     * mahasiswa pemilik, verifikator instansi (mentor / fallback Admin Dinas / Kepala Unit),
     * Admin Dinas instansi terkait, DPL, dosen & Admin Kampus dari kampus mahasiswa, Super Admin.
     */
    public function isViewableBy(?User $user): bool
    {
        $placement = $this->placement;
        $application = $placement?->application;
        if (! $user || ! $placement || ! $application) {
            return false;
        }

        if ($user->isSuperAdmin() || (int) $application->user_id === (int) $user->id) {
            return true;
        }

        if ($placement->canFieldReviewLogbook($user) || $placement->isAssignedFieldMentor($user)) {
            return true;
        }

        $agencyId = $placement->agencyProfileId();
        if ($user->role === 'admin' && $user->agency_profile_id !== null && (int) $user->agency_profile_id === $agencyId) {
            return true;
        }

        if (in_array($user->role, ['dosen', 'academic_advisor', 'universitas'], true)) {
            if ((int) $placement->academic_advisor_id === (int) $user->id) {
                return true;
            }
            $student = $application->user;
            $studentUnivId = $student?->university_id ?: $student?->studentProfile?->university_id;

            return $user->university_id !== null && $studentUnivId && (int) $studentUnivId === (int) $user->university_id;
        }

        return false;
    }
}
