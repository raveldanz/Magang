<?php

namespace App\Console\Commands;

use App\Models\ApplicationDocument;
use App\Models\Logbook;
use App\Models\StudentProfile;
use App\Models\SystemFeedback;
use App\Services\PrivateDocumentStorage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Memindahkan berkas pribadi LAMA dari disk 'public' (bisa diakses lewat URL /storage/...) ke disk
 * privat 'local'. Path relatif tidak berubah, jadi kolom database tidak perlu diubah.
 *
 *   php artisan app:move-private-files --dry-run   # lihat dulu yang akan dipindah
 *   php artisan app:move-private-files             # jalankan
 */
class MovePrivateFiles extends Command
{
    protected $signature = 'app:move-private-files {--dry-run : Hanya tampilkan berkas yang akan dipindah}';

    protected $description = 'Pindahkan lampiran logbook, foto profil, lampiran tiket & dokumen pengajuan lama dari disk public ke disk privat';

    public function handle(): int
    {
        $paths = collect()
            ->merge(Logbook::whereNotNull('attachment')->pluck('attachment'))
            ->merge(StudentProfile::whereNotNull('photo')->pluck('photo'))
            ->merge(SystemFeedback::whereNotNull('attachment')->pluck('attachment'))
            ->merge(ApplicationDocument::whereNotNull('file_path')->pluck('file_path'))
            ->filter(fn ($p) => is_string($p) && $p !== '' && ! str_contains($p, '..'))
            ->unique()
            ->values();

        $public = Storage::disk('public');
        $private = Storage::disk(PrivateDocumentStorage::DISK);
        $dry = (bool) $this->option('dry-run');
        $moved = 0;
        $skipped = 0;

        foreach ($paths as $path) {
            if (! $public->exists($path)) {
                continue;
            }
            if ($private->exists($path)) {
                $skipped++;
                if (! $dry) {
                    $public->delete($path); // salinan privat sudah ada → hapus salinan publik
                }

                continue;
            }

            $this->line(($dry ? '[dry-run] ' : '')."pindah: {$path}");
            if (! $dry) {
                $private->writeStream($path, $public->readStream($path));
                $public->delete($path);
                // Salinan lama di public/storage (Windows tanpa symlink)
                $copy = public_path('storage/'.$path);
                if (is_file($copy)) {
                    @unlink($copy);
                }
            }
            $moved++;
        }

        $this->info(($dry ? 'Akan dipindah' : 'Dipindah').": {$moved} berkas, sudah privat: {$skipped}.");

        return self::SUCCESS;
    }
}
