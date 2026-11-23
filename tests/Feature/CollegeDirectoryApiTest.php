<?php

namespace Tests\Feature;

use App\Models\University;
use App\Models\User;
use App\Services\Directory\CollegeDirectoryClient;
use App\Services\MasterUniversityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CollegeDirectoryApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_pencarian_kampus_mengambil_saran_dari_api_ketika_data_lokal_sedikit(): void
    {
        config([
            'services.college_directory.enabled' => true,
            'services.college_directory.base_url' => 'http://colleges.test',
        ]);

        Http::fake([
            'colleges.test/search*' => Http::response([
                [
                    'name' => 'Institut Teknologi Luar Biasa',
                    'domains' => ['itlb.ac.id'],
                    'web_pages' => ['http://www.itlb.ac.id/'],
                    'country' => 'Indonesia',
                    'state-province' => 'Jawa Timur',
                ],
            ]),
        ]);

        $user = User::factory()->create(['role' => 'mahasiswa']);

        $res = $this->actingAs($user)->getJson(route('institutions.search', [
            'q' => 'luar biasa',
            'type' => University::TYPE_COLLEGE,
        ]))->assertOk();

        $data = $res->json('data');
        $this->assertNotEmpty($data);
        $this->assertStringStartsWith('college:', $data[0]['id']);
        $this->assertSame('Institut Teknologi Luar Biasa', $data[0]['name']);
        $this->assertSame(University::TYPE_COLLEGE, $data[0]['institution_type']);

        // Pastikan GET pencarian read-only dan belum menyimpan record ke database
        $this->assertDatabaseMissing('universities', [
            'name' => 'Institut Teknologi Luar Biasa',
        ]);
    }

    public function test_resolve_kampus_dari_api_otomatis_merekam_ke_database_lokal(): void
    {
        config([
            'services.college_directory.enabled' => true,
            'services.college_directory.base_url' => 'http://colleges.test',
        ]);

        $fakeName = 'Politeknik Negeri Uji Otomatis';
        Http::fake([
            'colleges.test/search*' => Http::response([
                [
                    'name' => $fakeName,
                    'domains' => ['pnuo.ac.id'],
                    'web_pages' => ['http://www.pnuo.ac.id/'],
                    'country' => 'Indonesia',
                    'state-province' => 'Jawa Tengah',
                ],
            ]),
        ]);

        $user = User::factory()->create(['role' => 'mahasiswa']);

        // 1. Jalankan pencarian agar data tersimpan di server cache API
        $searchRes = $this->actingAs($user)->getJson(route('institutions.search', [
            'q' => 'Uji Otomatis',
            'type' => University::TYPE_COLLEGE,
        ]))->assertOk();

        $collegeId = $searchRes->json('data.0.id');
        $this->assertNotNull($collegeId);
        $collegeKey = str_replace('college:', '', $collegeId);

        // 2. Resolve lewat POST endpoint
        $resolveRes = $this->actingAs($user)->postJson(route('institutions.resolve'), [
            'college_key' => $collegeKey,
        ])->assertOk();

        $realId = $resolveRes->json('data.id');
        $this->assertIsInt($realId);

        // 3. Verifikasi data otomatis tercatat dan tersimpan di database lokal
        $this->assertDatabaseHas('universities', [
            'id' => $realId,
            'name' => $fakeName,
            'institution_type' => University::TYPE_COLLEGE,
            'is_verified' => true,
            'status' => 'active',
        ]);

        $univ = University::find($realId);
        $this->assertNotNull($univ);
        $this->assertStringContainsString('Jawa Tengah', $univ->address);
    }

    public function test_pencarian_tetap_berjalan_mulus_dan_mengandalkan_db_lokal_jika_api_gagal(): void
    {
        config([
            'services.college_directory.enabled' => true,
            'services.college_directory.base_url' => 'http://colleges.test',
        ]);

        // Simulasikan API luar timeout / 500 server error
        Http::fake([
            'colleges.test/search*' => Http::response('Gateway Timeout', 504),
        ]);

        // Siapkan kampus di database lokal
        $localUniv = University::create([
            'name' => 'Universitas Surabaya Lokal Induk',
            'code' => 'USLI',
            'acronym' => 'USLI',
            'institution_type' => University::TYPE_COLLEGE,
            'is_verified' => true,
            'status' => 'active',
        ]);

        $user = User::factory()->create(['role' => 'mahasiswa']);

        // Pencarian harus tetap HTTP 200 OK dan melayani data lokal
        $res = $this->actingAs($user)->getJson(route('institutions.search', [
            'q' => 'Surabaya Lokal',
            'type' => University::TYPE_COLLEGE,
        ]))->assertOk();

        $this->assertSame($localUniv->id, $res->json('data.0.id'));
        $this->assertSame('Universitas Surabaya Lokal Induk', $res->json('data.0.name'));
    }

    public function test_master_service_dapat_mencari_dan_upsert_kampus_dari_api(): void
    {
        $client = $this->createMock(CollegeDirectoryClient::class);
        $client->method('enabled')->willReturn(true);
        $client->method('search')->willReturn([
            [
                'key' => 'col_test123',
                'name' => 'Institut Seni Budaya Uji',
                'acronym' => 'ISBU',
                'domain' => 'isbu.ac.id',
                'web_page' => 'http://isbu.ac.id',
                'country' => 'Indonesia',
                'state_province' => 'Bali',
            ],
        ]);

        $service = app(MasterUniversityService::class);
        $result = $service->upsertDirectoryCollege([
            'name' => 'Institut Seni Budaya Uji',
            'acronym' => 'ISBU',
            'state_province' => 'Bali',
            'web_page' => 'http://isbu.ac.id',
        ]);

        $this->assertTrue($result['created']);
        $this->assertNotNull($result['university']);
        $this->assertSame('Institut Seni Budaya Uji', $result['university']->name);
        $this->assertSame(University::TYPE_COLLEGE, $result['university']->institution_type);
        $this->assertTrue($result['university']->is_verified);
    }
}
