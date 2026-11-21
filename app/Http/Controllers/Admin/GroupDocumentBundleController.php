<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\InternshipGroup;
use App\Services\PrivateDocumentStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Gabungan CV / KTP-KTM seluruh anggota kelompok menjadi satu PDF.
 *
 * Penggabungan dilakukan di peramban (pdf-lib dibundel Vite, resources/js/group-documents.js) agar server demo
 * tidak membutuhkan Node.js. Server hanya memeriksa hak akses dan kelengkapan berkas, mengirim daftar berkas
 * (manifest), lalu menyajikan tiap berkas lewat file() dengan pemeriksaan hak akses yang sama.
 */
class GroupDocumentBundleController extends Controller
{
    private const TYPES = [
        'cv' => ['column' => 'cv_path', 'label' => 'CV', 'mimes' => ['application/pdf']],
        'identitas' => ['column' => 'id_card_path', 'label' => 'KTP-KTM', 'mimes' => ['application/pdf', 'image/jpeg', 'image/png']],
    ];

    public function __invoke(Request $request, InternshipGroup $group, string $type): JsonResponse|RedirectResponse
    {
        $this->authorizeGroup($request, $group);
        $config = self::TYPES[$type];

        $members = $group->applications()->with('user.studentProfile')->orderBy('id')->get();
        abort_if($members->isEmpty(), 404);

        $files = [];
        foreach ($members as $member) {
            abort_if($member->unit_id !== $group->unit_id, 403);
            $resolved = $this->resolveMemberFile($member, $config);
            if (is_string($resolved)) {
                return $this->fail($request, $resolved);
            }

            $files[] = [
                'name' => $member->user?->name ?? 'Peserta Magang',
                'type' => $resolved['mime'],
                'url' => route('admin.groups.documents.file', [$group, $type, $member]),
            ];
        }

        if (! $request->expectsJson()) {
            return back()->with('error', 'PDF gabungan dibuat di peramban. Muat ulang halaman lalu klik tautan unduh sekali lagi.');
        }

        return response()->json([
            'filename' => $config['label'].'-Kelompok-'.$group->id.'.pdf',
            'label' => $config['label'],
            'files' => $files,
        ], 200, ['Cache-Control' => 'private, no-store']);
    }

    /** Menyajikan satu berkas anggota untuk digabungkan di peramban. */
    public function file(Request $request, InternshipGroup $group, string $type, Application $application): BinaryFileResponse
    {
        $this->authorizeGroup($request, $group);
        abort_unless((int) $application->group_id === (int) $group->id, 404);
        abort_if($application->unit_id !== $group->unit_id, 403);

        $resolved = $this->resolveMemberFile($application->loadMissing('user.studentProfile'), self::TYPES[$type]);
        abort_if(is_string($resolved), 404, is_string($resolved) ? $resolved : '');

        return response()->file($resolved['path'], [
            'Content-Type' => $resolved['mime'],
            'Content-Disposition' => 'inline',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function authorizeGroup(Request $request, InternshipGroup $group): void
    {
        $admin = $request->user();
        if ($admin->isSuperAdmin()) {
            return;
        }

        abort_unless($admin->agency_profile_id !== null && (int) $group->unit?->agency_profile_id === (int) $admin->agency_profile_id, 403);
        // Supervisor (Kepala Dinas) berwenang atas seluruh bidang di instansinya; admin bidang hanya bidangnya sendiri.
        abort_if(! $admin->isSupervisor() && $admin->unit_id && (int) $group->unit_id !== (int) $admin->unit_id, 403);
    }

    /**
     * @param  array{column: string, label: string, mimes: list<string>}  $config
     * @return array{path: string, mime: string}|string Pesan kesalahan bila berkas tidak tersedia/tidak didukung.
     */
    private function resolveMemberFile(Application $member, array $config): array|string
    {
        $column = $config['column'];
        $name = $member->user?->name ?? 'Peserta Magang';
        $location = PrivateDocumentStorage::locate($member->$column ?: $member->user?->studentProfile?->$column);
        if (! $location) {
            return "Berkas {$config['label']} milik {$name} belum tersedia.";
        }

        $path = Storage::disk($location['disk'])->path($location['path']);
        $mime = is_file($path) ? mime_content_type($path) : false;
        if (! in_array($mime, $config['mimes'], true)) {
            return "Format berkas {$config['label']} milik {$name} tidak didukung.";
        }

        return ['path' => $path, 'mime' => $mime];
    }

    private function fail(Request $request, string $message): JsonResponse|RedirectResponse
    {
        return $request->expectsJson()
            ? response()->json(['message' => $message], 422)
            : back()->with('error', $message);
    }
}
