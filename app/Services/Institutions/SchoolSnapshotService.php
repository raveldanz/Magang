<?php

namespace App\Services\Institutions;

use App\Models\University;
use App\Services\MasterUniversityService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Data awal sekolah dari direktori nasional, dibawa bersama proyek agar pemasangan baru tidak menunggu API.
 * Hanya memuat NPSN, nama, dan alamat sekolah yang berasal dari sinkron direktori.
 */
class SchoolSnapshotService
{
    public static function defaultPath(): string
    {
        return database_path('data/schools_snapshot.json.gz');
    }

    /** @return int jumlah sekolah yang diekspor */
    public function export(string $path): int
    {
        $rows = University::query()
            ->where('institution_type', University::TYPE_SCHOOL)
            ->whereNotNull('directory_synced_at')
            ->whereNotNull('code')->where('code', '!=', '')
            ->orderBy('code')
            ->toBase()
            ->get(['code', 'name', 'address'])
            ->map(fn ($u) => ['npsn' => (string) $u->code, 'name' => $u->name, 'address' => MasterUniversityService::cleanDirectoryAddress($u->address)])
            ->all();

        File::ensureDirectoryExists(dirname($path));
        File::put($path, gzencode(json_encode(
            ['generated_at' => now()->toIso8601String(), 'rows' => $rows],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
        ), 9));

        return count($rows);
    }

    /**
     * Aman diulang: NPSN yang sudah ada dilewati sehingga data yang sudah dikoreksi tidak tertimpa.
     *
     * @return array{created: int, filled: int, skipped: int}
     */
    public function import(string $path): array
    {
        $json = @gzdecode(File::get($path));
        $data = $json === false ? null : json_decode($json, true);
        if (! is_array($data) || ! is_array($data['rows'] ?? null)) {
            throw new RuntimeException('File data awal sekolah rusak atau formatnya tidak dikenal.');
        }
        $syncedAt = Carbon::parse($data['generated_at'] ?? 'now')->setTimezone(config('app.timezone'));

        return DB::transaction(fn () => $this->insertMissing($data['rows'], $syncedAt));
    }

    /**
     * @param  array<int, array{npsn?: string, name?: string, address?: ?string}>  $rows
     * @return array{created: int, filled: int, skipped: int}
     */
    private function insertMissing(array $rows, Carbon $syncedAt): array
    {
        // Dimuat sekali: memeriksa puluhan ribu baris satu per satu ke database terlalu lambat.
        $codes = array_fill_keys(University::whereNotNull('code')->pluck('code')->merge(DB::table('excluded_school_codes')->pluck('code'))->all(), true);
        $result = ['created' => 0, 'filled' => 0, 'skipped' => 0];
        $batch = [];
        $now = now();
        foreach ($rows as $row) {
            $npsn = trim((string) ($row['npsn'] ?? ''));
            $name = trim((string) ($row['name'] ?? ''));
            if ($npsn === '' || $name === '' || isset($codes[$npsn])) {
                $result['skipped']++;

                continue;
            }
            $codes[$npsn] = true;

            // insert() melewati event model, jadi slug dan nilai bawaan diisi di sini.
            $batch[] = [
                'name' => $name,
                'slug' => Str::slug($name) ?: null,
                'code' => $npsn,
                'address' => MasterUniversityService::cleanDirectoryAddress($row['address'] ?? null),
                'is_verified' => true,
                'status' => 'active',
                'institution_type' => University::TYPE_SCHOOL,
                'evaluation_scheme' => 'mentor_only',
                'weight_mentor' => 100,
                'weight_lecturer' => 0,
                'require_dpl' => false,
                'directory_synced_at' => $syncedAt,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $result['created']++;

            if (count($batch) === 1000) {
                University::insert($batch);
                $batch = [];
            }
        }
        if ($batch !== []) {
            University::insert($batch);
        }

        return $result;
    }
}
