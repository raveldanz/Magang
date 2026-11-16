<?php

namespace App\Console\Commands;

use App\Console\Commands\Concerns\ProvisionsUniversityAccounts;
use App\Models\University;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ProcessUniversityBatch4 extends Command
{
    use ProvisionsUniversityAccounts;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'universities:process-batch4';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Memproses Batch 4 (50 Universitas Keempat, 151-200): Sinkronisasi profil resmi, Rektor/Penandatangan, Logo, Akun Admin, dan Dosen Pembimbing Asli';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if ($this->refusesToRunOutsideDemoEnvironment()) {
            return Command::FAILURE;
        }

        $this->info('======================================================================');
        $this->info('MEMPROSES BATCH 4: 50 PERGURUAN TINGGI SE-INDONESIA (NO. 151 - 200)');
        $this->info('======================================================================');

        $dataPath = database_path('data/universities_batch4.json');
        if (! file_exists($dataPath)) {
            $this->error("Berkas data {$dataPath} tidak ditemukan!");

            return Command::FAILURE;
        }

        $items = json_decode(file_get_contents($dataPath), true);
        if (! is_array($items) || count($items) === 0) {
            $this->error('Format data Batch 4 tidak valid.');

            return Command::FAILURE;
        }

        $processed = 0;
        $tableRows = [];

        foreach ($items as $item) {
            $name = trim($item['name']);
            $code = trim($item['code']);
            $acronym = trim($item['acronym']);
            $city = trim($item['city']);
            $province = trim($item['province']);
            $address = trim($item['address']);
            $phone = trim($item['phone']);
            $email = trim($item['email']);
            $picName = trim($item['pic_name']);
            $picPosition = trim($item['pic_position']);
            $picNip = trim($item['pic_nip'] ?? '');
            $logo = trim($item['logo'] ?? '');

            // Cari universitas yang sudah ada berdasarkan nama atau kode atau akronim
            $univ = University::whereRaw('LOWER(name) = ?', [strtolower($name)])
                ->orWhere(function ($q) use ($code) {
                    $q->whereNotNull('code')->where('code', $code);
                })
                ->orWhere(function ($q) use ($acronym) {
                    $q->whereNotNull('acronym')->where('acronym', $acronym);
                })
                ->first();

            $logoToSet = $logo;

            $univData = [
                'name' => $name,
                'code' => $code,
                'acronym' => $acronym,
                'slug' => Str::slug($name),
                'city' => $city,
                'address' => $address,
                'phone' => $phone,
                'email' => $email,
                'logo' => $logoToSet,
                'pic_name' => $picName,
                'pic_position' => $picPosition,
                'pic_nip' => $picNip,
                'evaluation_scheme' => 'both',
                'weight_mentor' => 40,
                'weight_lecturer' => 60,
                'require_dpl' => true,
                'is_verified' => true,
                'status' => 'active',
            ];

            if ($univ) {
                $univ->update($univData);
            } else {
                $univ = University::create($univData);
            }

            // Akun Admin Kampus & Dosen Pembimbing Lapangan (DPL): akun yang sudah ada tidak diubah hak aksesnya
            $adminEmail = trim($item['admin_account']['email']);
            $dosenName = trim($item['dosen_account']['name']);
            $this->provisionUniversityAccount($univ, $adminEmail, trim($item['admin_account']['name']), 'universitas');
            $this->provisionUniversityAccount($univ, trim($item['dosen_account']['email']), $dosenName, 'dosen', [
                'phone' => trim($item['dosen_account']['phone'] ?? '081234567890'),
            ]);
            $processed++;
            $tableRows[] = [
                $item['order'],
                $univ->code,
                $univ->name,
                $picName,
                $dosenName,
                $adminEmail,
            ];
        }

        $this->table(
            ['No', 'Kode', 'Universitas', 'Penandatangan (Rektor/Direktur)', 'Dosen Pembimbing (DPL)', 'Akun Admin'],
            $tableRows
        );

        $this->info("Berhasil memproses dan mengoptimalkan {$processed} universitas di Batch 4!");
        $this->reportProvisionedAccounts();

        return Command::SUCCESS;
    }
}
