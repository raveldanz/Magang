<?php

namespace App\Console\Commands\Concerns;

use App\Models\University;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Hash;

/**
 * Pembuatan akun Admin Kampus & Dosen oleh command universities:process-batch*.
 *
 * Akun baru memakai password bawaan demo, sehingga command ini hanya boleh berjalan di lingkungan
 * demo (sama seperti DatabaseSeeder). Akun yang sudah ada tidak pernah diubah peran, status,
 * maupun penanda wajib-ganti-password-nya.
 */
trait ProvisionsUniversityAccounts
{
    /** Peran yang dianggap setara saat mencocokkan akun yang sudah ada. */
    private const EQUIVALENT_ROLES = [
        'universitas' => ['universitas'],
        'dosen' => ['dosen', 'academic_advisor'],
    ];

    /** @var list<string> */
    protected array $skippedAccounts = [];

    protected function refusesToRunOutsideDemoEnvironment(): bool
    {
        if (app()->environment(DatabaseSeeder::DEMO_ENVIRONMENTS)) {
            return false;
        }

        $this->error('Command ini dilewati: ia membuat akun dengan password bawaan demo dan hanya boleh dijalankan di lingkungan lokal/pengujian.');
        $this->line('Di produksi, buat akun kampus lewat menu Manajemen Pengguna agar mendapat password sementara acak.');

        return true;
    }

    /**
     * @param  array<string, mixed>  $profile  Data profil non-hak-akses (mis. phone).
     */
    protected function provisionUniversityAccount(University $univ, string $email, string $name, string $role, array $profile = []): void
    {
        $profile = array_merge($profile, [
            'name' => $name,
            'university_id' => $univ->id,
            'university' => $univ->name,
        ]);

        $user = User::where('email', $email)->first();

        if (! $user) {
            // must_change_password ditetapkan otomatis oleh User::booted() (true untuk password bawaan)
            User::create(array_merge($profile, [
                'email' => $email,
                'password' => Hash::make(User::DEFAULT_PASSWORD),
                'role' => $role,
                'status' => 'active',
                'email_verified_at' => now(),
            ]));

            return;
        }

        $sameRole = in_array($user->role, self::EQUIVALENT_ROLES[$role] ?? [$role], true);
        $sameUniversity = $user->university_id === null || (int) $user->university_id === (int) $univ->id;

        if (! $sameRole || ! $sameUniversity) {
            $this->skippedAccounts[] = "{$email} (peran: {$user->role}) sudah terdaftar dengan peran/kampus lain";

            return;
        }

        $user->update($profile);
    }

    protected function reportProvisionedAccounts(): void
    {
        $this->info('Akun Pembimbing & Admin baru memakai password bawaan demo dan ditandai wajib ganti password.');

        if ($this->skippedAccounts === []) {
            return;
        }

        $this->warn(count($this->skippedAccounts).' akun yang sudah ada TIDAK diubah, periksa secara manual:');
        foreach ($this->skippedAccounts as $skipped) {
            $this->line('  - '.$skipped);
        }
    }
}
