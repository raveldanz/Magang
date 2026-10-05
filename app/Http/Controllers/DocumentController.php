<?php

namespace App\Http\Controllers;

use App\Models\ApplicationDocument;
use App\Models\User;
use App\Services\PrivateDocumentStorage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Akses berkas persyaratan pengajuan magang (CV, transkrip, KTM, surat pengantar)
 * melalui route terotorisasi (middleware auth), bukan URL publik /storage/...
 *
 * Berkas baru disimpan di disk privat 'local' (storage/app/private/documents/applications).
 * Berkas lama yang masih berada di disk 'public' tetap bisa dibuka lewat route ini (fallback),
 * sehingga tidak ada data yang perlu dipindahkan atau dihapus.
 *
 * Yang berhak membuka/mengunduh: mahasiswa pemilik berkas, Admin Dinas instansi yang dilamar,
 * Admin Kampus asal mahasiswa, dan Super Admin.
 */
class DocumentController extends Controller
{
    /** Pratinjau inline (PDF/gambar) di tab browser. */
    public function showApplicationDocument($id)
    {
        [$document, $location, $downloadName] = $this->resolveAuthorizedDocument($id);

        $realPath = $location['disk'] ? Storage::disk($location['disk'])->path($location['path']) : $location['path'];
        $ext = strtolower(pathinfo($realPath, PATHINFO_EXTENSION) ?: 'pdf');
        $inline = in_array($ext, ['pdf', 'png', 'jpg', 'jpeg'], true);

        return response()->file($realPath, [
            'Content-Type' => mime_content_type($realPath) ?: 'application/octet-stream',
            'Content-Disposition' => ($inline ? 'inline' : 'attachment')."; filename=\"{$downloadName}\"",
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    /** Unduh berkas sebagai lampiran (Storage::download). */
    public function downloadApplicationDocument($id)
    {
        [$document, $location, $downloadName] = $this->resolveAuthorizedDocument($id);

        if (! $location['disk']) {
            // Salinan lama di public/storage (Windows tanpa symlink)
            return response()->download($location['path'], $downloadName, ['X-Content-Type-Options' => 'nosniff']);
        }

        return Storage::disk($location['disk'])->download($location['path'], $downloadName, [
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    /**
     * @return array{0: ApplicationDocument, 1: array{disk: ?string, path: string}, 2: string}
     */
    private function resolveAuthorizedDocument($id): array
    {
        $document = ApplicationDocument::with([
            'application.user.studentProfile',
            'application.unit',
        ])->findOrFail($id);

        abort_unless(
            $this->canAccessApplicationDocument(Auth::user(), $document),
            403,
            'Anda tidak memiliki hak akses untuk membuka dokumen ini.'
        );

        $location = PrivateDocumentStorage::locate($document->file_path);
        if (! $location && ($legacy = self::resolveStoredFile($document->file_path))) {
            $location = ['disk' => null, 'path' => $legacy];
        }
        abort_unless($location, 404, 'Berkas dokumen tidak ditemukan.');

        $student = $document->application?->user;
        $ext = strtolower(pathinfo($location['path'], PATHINFO_EXTENSION) ?: 'pdf');
        $downloadName = Str::slug($document->document_type ?: 'dokumen', '_').'_'.Str::slug($student?->name ?? 'mahasiswa', '_').'.'.$ext;

        return [$document, $location, $downloadName];
    }

    private function canAccessApplicationDocument(?User $user, ApplicationDocument $document): bool
    {
        if (! $user) {
            return false;
        }

        $application = $document->application;
        $student = $application?->user;

        if ($this->currentUserIsSuperAdmin($user)) {
            return true;
        }

        return match ($user->role) {
            // Mahasiswa pemilik berkas
            'mahasiswa' => $application && (int) $application->user_id === (int) $user->id,
            // Admin Dinas dari instansi yang dilamar
            'admin' => $user->agency_profile_id !== null
                && (int) optional($application?->unit)->agency_profile_id === (int) $user->agency_profile_id,
            // Admin Kampus asal mahasiswa
            'universitas' => $user->university_id !== null && in_array((int) $user->university_id, array_filter([
                (int) $student?->university_id,
                (int) $student?->studentProfile?->university_id,
            ]), true),
            default => false,
        };
    }

    /**
     * Cari lokasi fisik berkas: disk private dulu, lalu lokasi publik lama (kompatibilitas data lama).
     * Dipertahankan untuk pemanggil lain (mis. laporan akhir).
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
            public_path('storage/'.$relativePath),        // salinan lama di public/storage (Windows)
        ] as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}
