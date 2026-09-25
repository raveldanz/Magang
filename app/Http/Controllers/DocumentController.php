<?php

namespace App\Http\Controllers;

use App\Models\ApplicationDocument;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Akses berkas persyaratan pengajuan magang (CV, transkrip, KTM, surat pengantar)
 * melalui route terotorisasi, bukan URL publik /storage/...
 *
 * Berkas baru disimpan di disk 'local' (storage/app/private). Berkas lama yang
 * masih berada di disk 'public' tetap bisa dibuka lewat route ini (fallback),
 * sehingga tidak ada data yang perlu dipindahkan atau dihapus.
 */
class DocumentController extends Controller
{
    public function showApplicationDocument($id)
    {
        $document = ApplicationDocument::with([
            'application.user',
            'application.unit',
        ])->findOrFail($id);

        $application = $document->application;
        $student = $application?->user;
        $currentUser = Auth::user();

        $isAuthorized = false;

        if ($this->currentUserIsSuperAdmin($currentUser)) {
            $isAuthorized = true;
        } elseif (in_array($currentUser->role, ['admin', 'mentor', 'pembimbing'], true)) {
            // Admin Dinas / Mentor hanya untuk pengajuan ke instansinya sendiri
            $isAuthorized = $currentUser->agency_profile_id
                && (int) optional($application?->unit)->agency_profile_id === (int) $currentUser->agency_profile_id;
        } elseif (in_array($currentUser->role, ['universitas', 'dosen', 'academic_advisor'], true)) {
            // Pihak kampus hanya untuk mahasiswa dari perguruan tingginya sendiri
            $isAuthorized = $currentUser->university_id
                && (int) $student?->university_id === (int) $currentUser->university_id;
        } elseif ($currentUser->role === 'mahasiswa') {
            $isAuthorized = $application && (int) $application->user_id === (int) $currentUser->id;
        }

        abort_unless($isAuthorized, 403, 'Anda tidak memiliki hak akses untuk membuka dokumen ini.');

        $realPath = self::resolveStoredFile($document->file_path);
        abort_unless($realPath, 404, 'Berkas dokumen tidak ditemukan.');

        $ext = strtolower(pathinfo($realPath, PATHINFO_EXTENSION) ?: 'pdf');
        $downloadName = Str::slug($document->document_type ?: 'dokumen', '_') . '_' . Str::slug($student?->name ?? 'mahasiswa', '_') . '.' . $ext;
        $inline = in_array($ext, ['pdf', 'png', 'jpg', 'jpeg'], true);

        return response()->file($realPath, [
            'Content-Type' => mime_content_type($realPath) ?: 'application/octet-stream',
            'Content-Disposition' => ($inline ? 'inline' : 'attachment') . "; filename=\"{$downloadName}\"",
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * Cari lokasi fisik berkas: disk private dulu, lalu lokasi publik lama (kompatibilitas data lama).
     */
    public static function resolveStoredFile(?string $relativePath): ?string
    {
        if (empty($relativePath) || str_contains($relativePath, '..')) {
            return null;
        }

        $relativePath = ltrim(str_replace('\\', '/', $relativePath), '/');

        foreach ([
            Storage::disk('local')->path($relativePath),   // lokasi baru (private)
            Storage::disk('public')->path($relativePath),  // lokasi lama (kompatibilitas)
            public_path('storage/' . $relativePath),        // salinan lama di public/storage (Windows)
        ] as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}
