<?php

namespace Tests\Feature;

use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UniversityBatch3Test extends TestCase
{
    use RefreshDatabase;

    public function test_batch_3_universities_and_real_personnel_sync_successfully(): void
    {
        $this->artisan('universities:process-batch3')
            ->assertExitCode(0);

        $dataPath = database_path('data/universities_batch3.json');
        $this->assertFileExists($dataPath);

        $items = json_decode(file_get_contents($dataPath), true);
        $this->assertCount(50, $items);

        foreach ($items as $item) {
            $code = $item['code'];
            $acronym = $item['acronym'];
            $name = $item['name'];

            $univ = University::where('code', $code)
                ->orWhere('name', $name)
                ->first();

            $this->assertNotNull($univ, "Universitas {$name} ({$code}) harus ada di database.");
            $this->assertEquals($item['pic_name'], $univ->pic_name, "PIC {$name} harus sesuai data asli.");
            $this->assertEquals($item['pic_position'], $univ->pic_position, "Jabatan PIC {$name} harus sesuai data asli.");
            $this->assertTrue($univ->is_verified);
            $this->assertEquals('active', $univ->status);

            // Cek file logo
            $this->assertNotEmpty($univ->logo);
            $this->assertFileExists(public_path($univ->logo), "File logo untuk {$name} di {$univ->logo} harus ada.");

            // Cek Admin Universitas
            $admin = User::where('email', $item['admin_account']['email'])->first();
            $this->assertNotNull($admin, "Akun admin {$item['admin_account']['email']} harus ada.");
            $this->assertEquals('universitas', $admin->role);
            $this->assertEquals($univ->id, $admin->university_id);

            // Cek Dosen Pembimbing (DPL)
            $dosen = User::where('email', $item['dosen_account']['email'])->first();
            $this->assertNotNull($dosen, "Akun dosen {$item['dosen_account']['email']} harus ada.");
            $this->assertEquals('dosen', $dosen->role);
            $this->assertEquals($univ->id, $dosen->university_id);
            $this->assertEquals($item['dosen_account']['name'], $dosen->name);
        }
    }
}
