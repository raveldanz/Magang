<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Logbook;
use App\Models\StudentProfile;
use App\Models\SystemFeedback;
use App\Models\User;
use App\Services\PrivateDocumentStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Akses terotorisasi untuk berkas pribadi yang disimpan di disk privat:
 * lampiran logbook, foto profil mahasiswa, dan lampiran tiket masukan.
 * Berkas lama yang masih di disk 'public' tetap terlayani (fallback) tanpa perlu dipindahkan.
 */
class PrivateFileController extends Controller
{
    public function logbookAttachment(Request $request, Logbook $logbook)
    {
        $logbook->loadMissing(['placement.application.user.studentProfile', 'placement.application.unit']);

        abort_unless($logbook->isViewableBy($request->user()), 403, 'Anda tidak memiliki hak akses ke lampiran logbook ini.');
        abort_unless($logbook->attachment, 404);

        $student = $logbook->placement?->application?->user;
        $name = 'lampiran_logbook_'.Str::slug($student?->name ?? 'mahasiswa', '_').'_'.$logbook->date;

        return PrivateDocumentStorage::response($logbook->attachment, $name);
    }

    public function studentPhoto(Request $request, int $userId)
    {
        $viewer = $request->user();
        $profile = StudentProfile::where('user_id', $userId)->firstOrFail();

        // Foto mahasiswa: pemilik dan seluruh akun staf (admin, mentor, dosen, admin kampus).
        $allowed = (int) $viewer->id === (int) $userId || $viewer->role !== 'mahasiswa';
        abort_unless($allowed, 403, 'Anda tidak memiliki hak akses ke foto ini.');
        abort_unless($profile->photo, 404);

        return PrivateDocumentStorage::response($profile->photo, 'foto_profil_'.$userId);
    }

    public function feedbackAttachment(Request $request, $id)
    {
        $feedback = SystemFeedback::findOrFail($id);

        abort_unless(FeedbackController::canAccess($request->user(), $feedback), 403, 'Anda tidak memiliki akses ke lampiran tiket ini.');
        abort_unless($feedback->attachment, 404);

        return PrivateDocumentStorage::response($feedback->attachment, 'lampiran_tiket_'.$feedback->id);
    }

    public function studentDocument(Request $request, int $userId, string $type)
    {
        $viewer = $request->user();
        $profile = StudentProfile::with('user')->where('user_id', $userId)->firstOrFail();

        abort_unless($this->canViewStudentDocuments($viewer, $profile), 403, 'Anda tidak memiliki hak akses ke dokumen ini.');

        $typeMap = [
            'cv' => [$profile->cv_path, 'cv_'.Str::slug($profile->user?->name ?? 'mahasiswa', '_')],
            'transkrip' => [$profile->transcript_path, 'transkrip_'.Str::slug($profile->user?->name ?? 'mahasiswa', '_')],
            'transcript' => [$profile->transcript_path, 'transkrip_'.Str::slug($profile->user?->name ?? 'mahasiswa', '_')],
            'id_card' => [$profile->id_card_path, 'ktm_'.Str::slug($profile->user?->name ?? 'mahasiswa', '_')],
            'ktm' => [$profile->id_card_path, 'ktm_'.Str::slug($profile->user?->name ?? 'mahasiswa', '_')],
        ];

        abort_unless(isset($typeMap[$type]), 404, 'Jenis dokumen tidak valid.');
        [$path, $name] = $typeMap[$type];
        abort_unless($path, 404, 'Dokumen belum diunggah.');

        $forceDownload = $request->boolean('download');

        return PrivateDocumentStorage::response($path, $name, $forceDownload);
    }

    /**
     * CV, transkrip, dan KTM hanya untuk pihak yang benar-benar terkait dengan mahasiswa
     * (selaras dengan DocumentController::canAccessApplicationDocument untuk berkas yang sama):
     * pemilik, Super Admin, Admin Dinas/Supervisor/Mentor dari instansi yang dilamar,
     * Admin Kampus asal mahasiswa, dan DPL yang ditugaskan untuk mahasiswa tersebut.
     */
    private function canViewStudentDocuments(?User $viewer, StudentProfile $profile): bool
    {
        if (! $viewer) {
            return false;
        }

        $studentId = (int) $profile->user_id;

        if ((int) $viewer->id === $studentId || $this->currentUserIsSuperAdmin($viewer)) {
            return true;
        }

        $applications = Application::where('user_id', $studentId);

        return match ($viewer->role) {
            'admin', 'supervisor', 'mentor', 'pembimbing' => $viewer->agency_profile_id !== null
                && (clone $applications)->whereHas('unit', fn ($q) => $q->where('agency_profile_id', $viewer->agency_profile_id))->exists(),
            'universitas' => $viewer->university_id !== null && in_array((int) $viewer->university_id, array_filter([
                (int) $profile->user?->university_id,
                (int) $profile->university_id,
            ]), true),
            'dosen', 'academic_advisor' => (clone $applications)
                ->whereHas('placement', fn ($q) => $q->where('academic_advisor_id', $viewer->id))->exists(),
            default => false,
        };
    }
}
