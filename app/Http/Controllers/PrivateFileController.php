<?php

namespace App\Http\Controllers;

use App\Models\Logbook;
use App\Models\StudentProfile;
use App\Models\SystemFeedback;
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
}
