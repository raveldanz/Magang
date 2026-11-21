<?php

namespace App\Console\Commands;

use App\Services\Directory\SchoolDirectoryClient;
use App\Services\MasterUniversityService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SyncExternalInstitutions extends Command
{
    protected $signature = 'institutions:sync-external
        {--form=* : Bentuk sekolah yang disinkronkan (default: SMA, SMK, MA, SMLB)}
        {--per-page=1000 : Jumlah data per permintaan ke API}
        {--max-pages=0 : Batas halaman per bentuk (0 = semua), berguna untuk uji coba}';

    protected $description = 'Sinkronisasi sekolah (SMA/SMK/MA/SLB) dari direktori nasional NPSN: tambah yang baru, perbarui yang ada, tidak pernah menghapus';

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

        if (! $failed) {
            Cache::forever('institutions:last-external-sync', now()->toIso8601String());
        }

        $this->info("Selesai: {$totals['created']} baru, {$totals['updated']} diperbarui, {$totals['skipped']} dilewati.");

        return $failed ? Command::FAILURE : Command::SUCCESS;
    }
}
