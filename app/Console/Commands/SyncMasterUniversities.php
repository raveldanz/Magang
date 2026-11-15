<?php

namespace App\Console\Commands;

use App\Models\University;
use App\Services\MasterUniversityService;
use Illuminate\Console\Command;

class SyncMasterUniversities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'universities:sync-master';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi master data perguruan tinggi se-Indonesia (PDDikti/Kemdikbud) ke database';

    /**
     * Execute the console command.
     */
    public function handle(MasterUniversityService $service): int
    {
        $this->info('Memulai sinkronisasi Master Perguruan Tinggi se-Indonesia...');

        $masterList = $service->allMaster();
        $total = count($masterList);
        $this->line("Ditemukan {$total} kampus dalam Master Dataset.");

        $created = 0;
        $updated = 0;

        foreach ($masterList as $item) {
            $name = trim($item['name'] ?? '');
            $code = trim($item['code'] ?? ($item['acronym'] ?? 'UNIV'));
            $acronym = trim($item['acronym'] ?? $code);
            $city = trim($item['city'] ?? 'Indonesia');
            $province = trim($item['province'] ?? 'Indonesia');

            if ($name === '') {
                continue;
            }

            $univ = University::whereRaw('LOWER(name) = ?', [strtolower($name)])
                ->orWhere(function ($q) use ($code) {
                    $q->whereNotNull('code')->where('code', $code);
                })
                ->first();

            if ($univ) {
                $univ->update([
                    'acronym' => $univ->acronym ?: $acronym,
                    'city' => $univ->city ?: $city,
                    'province' => $univ->province ?: $province,
                    'is_verified' => true,
                    'status' => 'active',
                ]);
                $updated++;
            } else {
                University::create([
                    'name' => $name,
                    'code' => $code,
                    'acronym' => $acronym,
                    'address' => $city.', '.$province,
                    'city' => $city,
                    'province' => $province,
                    'is_verified' => true,
                    'status' => 'active',
                    'evaluation_scheme' => 'mentor_only',
                    'weight_mentor' => 100,
                    'weight_lecturer' => 0,
                    'require_dpl' => false,
                ]);
                $created++;
            }
        }

        $this->info("Sinkronisasi selesai! {$created} kampus baru ditambahkan, {$updated} kampus diperbarui.");
        $this->info('Total kampus aktif di database sekarang: '.University::count());

        return Command::SUCCESS;
    }
}
