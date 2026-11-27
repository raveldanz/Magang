<?php

namespace App\Services\Institutions;

use App\Console\Commands\SyncExternalInstitutions;
use App\Exceptions\SyncAlreadyRunning;
use App\Models\InstitutionSyncRun;
use App\Models\University;
use App\Services\Directory\CollegeDirectoryClient;
use App\Services\Directory\SchoolDirectoryClient;
use App\Services\MasterUniversityService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Sinkron direktori institusi yang berjalan per langkah (satu halaman API = satu langkah), dipakai bersama
 * oleh command terjadwal (dijalankan berurutan) dan tombol admin (rantai job antrean).
 * Sinkron hanya menambah dan melengkapi kolom kosong; tidak pernah menghapus institusi.
 */
class InstitutionSyncService
{
    /** Langkah terakhir sinkron penuh: usul kampus baru dari direktori perguruan tinggi. */
    public const COLLEGE_STEP = 'KAMPUS';

    public function __construct(
        private SchoolDirectoryClient $schools,
        private MasterUniversityService $master,
        private CollegeDirectoryClient $colleges,
        private ChangeProposalService $proposals,
    ) {}

    /**
     * @param  array<int, string>  $forms
     *
     * @throws SyncAlreadyRunning
     */
    public function start(string $trigger, ?int $userId = null, array $forms = [], int $perPage = 1000, int $maxPages = 0, string $kind = 'school'): InstitutionSyncRun
    {
        // Pemeriksaan dan pembuatan dikunci agar dua klik (atau klik dan jadwal) tidak lolos bersamaan.
        return Cache::lock('institution-sync:start:'.$kind, 10)->block(5, function () use ($trigger, $userId, $forms, $perPage, $maxPages, $kind) {
            foreach (InstitutionSyncRun::where('kind', $kind)->whereIn('status', InstitutionSyncRun::ACTIVE_STATUSES)->get() as $active) {
                if (! $active->isStalled()) {
                    throw new SyncAlreadyRunning;
                }
                $this->fail($active, 'Proses terhenti dan digantikan proses baru.');
            }

            return InstitutionSyncRun::create([
                'trigger' => $trigger,
                'kind' => $kind,
                'status' => 'queued',
                'started_by' => $userId,
                'forms' => array_values($forms ?: SchoolDirectoryClient::FORMS),
                'per_page' => $perPage,
                'max_pages' => $maxPages,
            ]);
        });
    }

    /** @return array{step: string, page: int} */
    public function firstStep(InstitutionSyncRun $run): array
    {
        return ['step' => $run->kind === 'college' ? self::COLLEGE_STEP : $run->forms[0], 'page' => 1];
    }

    /**
     * Proses satu halaman. Mengembalikan langkah berikutnya, atau null bila proses selesai/tidak aktif.
     *
     * @return array{step: string, page: int}|null
     */
    public function processStep(InstitutionSyncRun $run, string $step, int $page): ?array
    {
        $run->refresh();
        if (! $run->isActive()) {
            return null;
        }
        if (! InstitutionSyncRun::whereKey($run->id)->whereIn('status', InstitutionSyncRun::ACTIVE_STATUSES)
            ->update(['status' => 'running', 'current_step' => $step, 'current_page' => $page])) {
            return null;
        }

        $next = $step === self::COLLEGE_STEP
            ? $this->processColleges($run)
            : $this->processSchoolPage($run, $step, $page);
        if ($next === null) {
            $this->finish($run);
        }

        return $next;
    }

    public function fail(InstitutionSyncRun $run, string $message): void
    {
        InstitutionSyncRun::whereKey($run->id)->whereIn('status', InstitutionSyncRun::ACTIVE_STATUSES)->update(['status' => 'failed', 'message' => $message, 'finished_at' => now()]);
    }

    /** @return array{step: string, page: int}|null */
    private function processSchoolPage(InstitutionSyncRun $run, string $form, int $page): ?array
    {
        $result = $this->schools->page($form, $page, $run->per_page);
        if ($result === null || ($result['total'] > 0 && empty($result['rows']))) {
            // API gagal: lewati bentuk ini, data lokal tetap utuh dan sinkron berikutnya mencoba lagi.
            $run->update(['failed_steps' => [...($run->failed_steps ?? []), $form], 'last_page' => null]);

            return $this->stepAfter($run, $form);
        }

        DB::transaction(function () use ($run, $result) {
            $created = $updated = $skipped = $proposed = 0;
            $seen = [];
            foreach ($result['rows'] as $row) {
                if ($row['npsn'] === '' || $row['name'] === '') {
                    $skipped++;

                    continue;
                }
                $outcome = $this->master->upsertDirectorySchool($row);
                if (! $outcome['university']) {
                    $skipped++;

                    continue;
                }
                if ($outcome['matched_by'] === 'npsn') {
                    $proposed += $this->proposals->forSchool($outcome['university'], $row);
                }
                $created += (int) $outcome['created'];
                $updated += (int) $outcome['filled'];
                $seen[] = $outcome['university']->id;
            }

            if ($seen !== []) {
                University::whereIn('id', $seen)->toBase()->update(['directory_synced_at' => now()]);
            }

            $run->update([
                'created_count' => $run->created_count + $created,
                'updated_count' => $run->updated_count + $updated,
                'skipped_count' => $run->skipped_count + $skipped,
                'proposed_count' => $run->proposed_count + $proposed,
                'source_total' => $run->source_total + count($seen),
                'last_page' => max(1, (int) ceil($result['total'] / $run->per_page)),
            ]);
        });

        $more = $page < $run->last_page && ($run->max_pages === 0 || $page < $run->max_pages);

        return $more ? ['step' => $form, 'page' => $page + 1] : $this->stepAfter($run, $form);
    }

    /** @return array{step: string, page: int}|null */
    private function stepAfter(InstitutionSyncRun $run, string $step): ?array
    {
        $index = array_search($step, $run->forms, true);
        $next = $index === false ? null : ($run->forms[$index + 1] ?? null);

        if ($next !== null) {
            return ['step' => $next, 'page' => 1];
        }

        return null;
    }

    private function processColleges(InstitutionSyncRun $run): null
    {
        $rows = $this->colleges->all();
        if (empty($rows)) {
            $run->update(['failed_steps' => [...($run->failed_steps ?? []), self::COLLEGE_STEP]]);

            return null;
        }

        $run->update(['source_total' => count($rows), 'proposed_count' => $run->proposed_count + $this->proposals->forColleges($rows), 'last_page' => 1]);

        return null;
    }

    /** Sinkron penuh: semua bentuk sekolah tanpa batas halaman. */
    private function isFullRun(InstitutionSyncRun $run): bool
    {
        return $run->max_pages === 0 && array_diff(SchoolDirectoryClient::FORMS, $run->forms) === [];
    }

    private function finish(InstitutionSyncRun $run): void
    {
        $message = match (true) {
            ! empty($run->failed_steps) => 'Gagal mengambil: '.implode(', ', $run->failed_steps).'. Data yang sudah ada tetap aman.',
            (int) $run->source_total === 0 => 'Direktori sekolah tidak mengembalikan data.',
            default => null,
        };

        if (! InstitutionSyncRun::whereKey($run->id)->whereIn('status', InstitutionSyncRun::ACTIVE_STATUSES)
            ->update(['status' => $message === null ? 'success' : 'failed', 'message' => $message, 'finished_at' => now()])) {
            return;
        }
        $run->refresh();

        // Hanya sinkron penuh yang berhasil dicatat: uji coba sebagian tidak boleh menunda sinkron mingguan.
        if ($message === null && ($run->kind === 'college' || $this->isFullRun($run))) {
            $stamp = now()->toIso8601String();
            Cache::forever($run->kind === 'college' ? 'institutions:last-college-sync' : 'institutions:last-external-sync', $stamp);
            // Cache dikosongkan setiap demo dimulai (optimize:clear), jadi penanda utama disimpan di berkas.
            Storage::disk('local')->put(SyncExternalInstitutions::stampFile($run->kind), $stamp);
        }
    }
}
