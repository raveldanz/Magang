<?php

namespace App\Console\Commands;

use App\Services\Directory\SchoolDirectoryClient;
use App\Services\MasterUniversityService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SyncExternalInstitutions extends Command
{
    protected $signature = 'institutions:sync-external
        {--form=* : Bentuk sekolah yang disinkronkan (default: SMA, SMK, MA, SMLB)}
        {--per-page=1000 : Jumlah data per permintaan ke API}
        {--max-pages=0 : Batas halaman per bentuk (0 = semua), berguna untuk uji coba}';

    protected $description = 'Sinkronisasi sekolah (SMA/SMK/MA/SLB) dari direktori nasional NPSN: tambah yang baru, perbarui yang ada, tidak pernah menghapus';

    /** Waktu sinkron penuh terakhir (disk privat 'local'). */
    public const STAMP_FILE = 'sync/institutions-external-last.txt';

    public static function lastFullSync(): ?Carbon
    {
        $disk = Storage::disk('local');
        if (! $disk->exists(self::STAMP_FILE)) {
            return null;
        }

        try {
            return Carbon::parse(trim((string) $disk->get(self::STAMP_FILE)));
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Sinkron dianggap jatuh tempo bila belum pernah sinkron penuh atau sudah lewat $days hari.
     * Penjadwal memeriksanya tiap jam, sehingga laptop yang mati pada jadwal mingguan tetap menyusul.
     */
    public static function isDue(int $days = 7): bool
    {
        $last = self::lastFullSync();

        return $last === null || $last->lte(now()->subDays($days));
    }

    public function handle(SchoolDirectoryClient $client, MasterUniversityService $service): int
    {
        if (! $client->enabled()) {
            $this->warn('Direktori sekolah dinonaktifkan (SCHOOL_DIRECTORY_ENABLED / SCHOOL_DIRECTORY_URL).');

            return Command::SUCCESS;
        }

        $forms = array_map('strtoupper', $this->option('form') ?: SchoolDirectoryClient::FORMS);
        $perPage = max(50, min(1000, (int) $this->option('per-page')));
        $maxPages = max(0, (int) $this->option('max-pages'));
        $totals = ['created' => 0, 'updated' => 0, 'skipped' => 0];
        $failed = false;

        foreach ($forms as $form) {
            $page = 1;
            do {
                $result = $client->page($form, $page, $perPage);
                if ($result === null) {
                    // API gagal: hentikan bentuk ini, data lokal tetap utuh dan percobaan berikutnya melanjutkan.
                    $this->error("Gagal mengambil {$form} halaman {$page}; dilewati.");
                    $failed = true;
                    break;
                }

                DB::transaction(function () use ($result, $service, &$totals) {
                    foreach ($result['rows'] as $row) {
                        if ($row['npsn'] === '' || $row['name'] === '') {
                            $totals['skipped']++;

                            continue;
                        }
                        $totals[$service->upsertDirectorySchool($row)['created'] ? 'created' : 'updated']++;
                    }
                });

                $lastPage = (int) ceil($result['total'] / $perPage);
                $this->line("{$form}: halaman {$page}/{$lastPage} ({$result['total']} sekolah)");
                $page++;
            } while ($page <= $lastPage && ($maxPages === 0 || $page <= $maxPages));
        }

        // Hanya sinkron penuh yang dicatat: uji coba --max-pages/--form tidak boleh menunda sinkron mingguan.
        $isFullRun = $maxPages === 0 && array_diff(SchoolDirectoryClient::FORMS, $forms) === [];
        if (! $failed && $isFullRun) {
            $stamp = now()->toIso8601String();
            Cache::forever('institutions:last-external-sync', $stamp);
            // Cache dikosongkan setiap demo dimulai (optimize:clear), jadi penanda utama disimpan di berkas.
            Storage::disk('local')->put(self::STAMP_FILE, $stamp);
        }

        $this->info("Selesai: {$totals['created']} baru, {$totals['updated']} diperbarui, {$totals['skipped']} dilewati.");

        return $failed ? Command::FAILURE : Command::SUCCESS;
    }
}
