<?php

namespace App\Console\Commands;

use App\Models\University;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ProcessUniversityBatch1 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'universities:process-batch1';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Memproses Batch 1 (50 Universitas Pertama): Sinkronisasi profil resmi, Rektor/Penandatangan, Logo, Akun Admin, dan Dosen Pembimbing Asli';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('======================================================================');
        $this->info('MEMPROSES BATCH 1: 50 PERGURUAN TINGGI SE-INDONESIA (SURABAYA & JABODETABEK)');
        $this->info('======================================================================');

        $dataPath = database_path('data/universities_batch1.json');
        if (! file_exists($dataPath)) {
            $this->error("Berkas data {$dataPath} tidak ditemukan!");

            return Command::FAILURE;
        }

        $items = json_decode(file_get_contents($dataPath), true);
        if (! is_array($items) || count($items) === 0) {
            $this->error('Format data Batch 1 tidak valid.');

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

            // Cari universitas yang sudah ada berdasarkan nama atau kode
            $univ = University::whereRaw('LOWER(name) = ?', [strtolower($name)])
                ->orWhere(function ($q) use ($code) {
                    $q->whereNotNull('code')->where('code', $code);
                })
                ->orWhere(function ($q) use ($acronym) {
                    $q->whereNotNull('acronym')->where('acronym', $acronym);
                })
                ->first();

            // Logo rule: jika unair, UI, unesa, upn sudah punya logo di DB dan filenya ada, jangan diubah
            $exemptLogos = ['UNESA', 'UNAIR', 'UI', 'UPN-JATIM', 'UPN', 'UPNVJ'];
            $logoToSet = $logo;
            if ($univ && in_array(strtoupper($univ->code ?? ''), $exemptLogos)) {
                if (! empty($univ->logo) && file_exists(public_path($univ->logo))) {
                    $logoToSet = $univ->logo;
                }
            }

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

            // 1. Akun Admin Kampus
            $adminEmail = trim($item['admin_account']['email']);
            $adminName = trim($item['admin_account']['name']);
            $adminUser = User::where('email', $adminEmail)->first();
            if ($adminUser) {
                $adminUser->update([
                    'name' => $adminName,
                    'role' => 'universitas',
                    'university_id' => $univ->id,
                    'university' => $univ->name,
                    'status' => 'active',
                    'email_verified_at' => $adminUser->email_verified_at ?: now(),
                    'must_change_password' => false,
                ]);
            } else {
                User::create([
                    'name' => $adminName,
                    'email' => $adminEmail,
                    'password' => Hash::make('password'),
                    'role' => 'universitas',
                    'university_id' => $univ->id,
                    'university' => $univ->name,
                    'status' => 'active',
                    'email_verified_at' => now(),
                    'must_change_password' => false,
                ]);
            }

            // 2. Akun Dosen Pembimbing Lapangan (DPL) Asli
            $dosenEmail = trim($item['dosen_account']['email']);
            $dosenName = trim($item['dosen_account']['name']);
            $dosenPhone = trim($item['dosen_account']['phone'] ?? '081234567890');
            $dosenUser = User::where('email', $dosenEmail)->first();
            if ($dosenUser) {
                $dosenUser->update([
                    'name' => $dosenName,
                    'role' => 'dosen',
                    'university_id' => $univ->id,
                    'university' => $univ->name,
                    'phone' => $dosenPhone,
                    'status' => 'active',
                    'email_verified_at' => $dosenUser->email_verified_at ?: now(),
                    'must_change_password' => false,
                ]);
            } else {
                User::create([
                    'name' => $dosenName,
                    'email' => $dosenEmail,
                    'password' => Hash::make('password'),
                    'role' => 'dosen',
                    'university_id' => $univ->id,
                    'university' => $univ->name,
                    'phone' => $dosenPhone,
                    'status' => 'active',
                    'email_verified_at' => now(),
                    'must_change_password' => false,
                ]);
            }

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

        $this->info("Berhasil memproses dan mengoptimalkan {$processed} universitas di Batch 1!");
        $this->info('Seluruh akun Dosen & Admin siap digunakan (password default: password).');

        return Command::SUCCESS;
    }
}
