<?php

namespace Tests\Feature;

use App\Models\University;
use App\Services\MasterUniversityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CollegeDirectoryApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_master_service_dapat_upsert_kampus_dari_api(): void
    {
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
