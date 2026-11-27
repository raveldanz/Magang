<?php

namespace App\Console\Commands;

use App\Exceptions\SyncAlreadyRunning;
use App\Services\Directory\CollegeDirectoryClient;
use App\Services\Directory\SchoolDirectoryClient;
use App\Services\Institutions\InstitutionSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class SyncExternalInstitutions extends Command
{
    protected $signature = 'institutions:sync-external
        {--kind=school : Jenis direktori (school atau college)}
        {--form=* : Bentuk sekolah yang disinkronkan (default: SMA, SMK, MA, SMLB)}
        {--per-page=1000 : Jumlah data per permintaan ke API}
        {--max-pages=0 : Batas halaman per bentuk (0 = semua), berguna untuk uji coba}';

    protected $description = 'Sinkronisasi sekolah (SMA/SMK/MA/SLB) dari direktori nasional NPSN: tambah yang baru, perbarui yang ada, tidak pernah menghapus';

    /** Waktu sinkron penuh terakhir (disk privat 'local'). */
    public const STAMP_FILE = 'sync/institutions-external-last.txt';

    public static function stampFile(string $kind): string
    {
        return $kind === 'college' ? 'sync/colleges-external-last.txt' : self::STAMP_FILE;
    }

    public static function lastFullSync(string $kind = 'school'): ?Carbon
    {
        $disk = Storage::disk('local');
        if (! $disk->exists(self::stampFile($kind))) {
            return null;
        }

        try {
            return Carbon::parse(trim((string) $disk->get(self::stampFile($kind))));
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Sinkron dianggap jatuh tempo bila belum pernah sinkron penuh atau sudah lewat $days hari.
     * Penjadwal memeriksanya tiap jam, sehingga laptop yang mati pada jadwal mingguan tetap menyusul.
     */
    public static function isDue(int $days = 7, string $kind = 'school'): bool
    {
        $last = self::lastFullSync($kind);

        return $last === null || $last->lte(now()->subDays($days));
    }

    public function handle(SchoolDirectoryClient $client, InstitutionSyncService $sync, CollegeDirectoryClient $colleges): int
    {
        $kind = $this->option('kind');
        if (! in_array($kind, ['school', 'college'], true)) {
            $this->error('Jenis direktori tidak dikenal.');

            return Command::FAILURE;
        }
        if (! ($kind === 'college' ? $colleges : $client)->enabled()) {
            $this->warn('Direktori yang dipilih dinonaktifkan.');

            return Command::SUCCESS;
        }

        try {
            $run = $sync->start(
                'schedule',
                kind: $kind,
                forms: array_map('strtoupper', $this->option('form')),
                perPage: max(50, min(1000, (int) $this->option('per-page'))),
                maxPages: max(0, (int) $this->option('max-pages')),
            );
        } catch (SyncAlreadyRunning $e) {
            $this->warn($e->getMessage().' Dilewati.');

            return Command::SUCCESS;
        }

        try {
            $step = $sync->firstStep($run);
            while ($step !== null) {
                $current = $step;
                $step = $sync->processStep($run, $current['step'], $current['page']);
                $this->line("{$current['step']}: halaman {$current['page']}".($run->last_page ? "/{$run->last_page}" : ''));
            }
        } catch (\Throwable $e) {
            $sync->fail($run, 'Sinkron berhenti karena galat: '.mb_substr($e->getMessage(), 0, 200));

            throw $e;
        }

        $run->refresh();
        $this->info("Selesai: {$run->created_count} baru, {$run->updated_count} dilengkapi, {$run->skipped_count} dilewati.");
        if ($run->status === 'failed') {
            $this->error((string) $run->message);

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
