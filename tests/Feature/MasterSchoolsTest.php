<?php

namespace Tests\Feature;

use App\Models\University;
use App\Services\MasterUniversityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Master sekolah SMA/SMK/MA (database/data/master_schools.json) untuk pilihan "Sekolah" di profil peserta.
 */
class MasterSchoolsTest extends TestCase
{
    use RefreshDatabase;

    private function service(): MasterUniversityService
    {
        return app(MasterUniversityService::class);
    }

    private function datasetCount(): int
    {
        return count(json_decode(file_get_contents(database_path('data/master_schools.json')), true));
    }

    public function test_dataset_is_present_and_has_required_fields(): void
    {
        $schools = $this->service()->allSchools();

        $this->assertGreaterThan(200, count($schools));
        foreach ($schools as $school) {
            $this->assertMatchesRegularExpression('/^\w+$/', (string) $school['npsn']);
            $this->assertNotSame('', trim($school['name']));
        }
        $this->assertSame(count($schools), count(array_unique(array_column($schools, 'npsn'))), 'NPSN harus unik.');
    }

    public function test_short_and_long_school_names_find_the_same_school(): void
    {
        $short = $this->service()->search('smkn 7', 5)->firstWhere('institution_type', University::TYPE_SCHOOL);
        $long = $this->service()->search('smk negeri 7 surabaya', 5)->firstWhere('institution_type', University::TYPE_SCHOOL);

        $this->assertNotNull($short);
        $this->assertNotNull($long);
        $this->assertSame($short['npsn'], $long['npsn']);
        $this->assertSame('SMK NEGERI 7 SURABAYA', $long['name']);
    }

    public function test_campus_search_still_marks_colleges(): void
    {
        $first = $this->service()->search('Gadjah Mada', 5)->first();

        $this->assertSame(University::TYPE_COLLEGE, $first['institution_type']);
    }

    public function test_sync_imports_schools_once_and_is_repeatable(): void
    {
        $this->artisan('universities:sync-master', ['--only-schools' => true])->assertSuccessful();

        $schoolCount = University::where('institution_type', University::TYPE_SCHOOL)->count();
        $this->assertSame($this->datasetCount(), $schoolCount);
        $this->assertSame(0, University::where('institution_type', University::TYPE_COLLEGE)->count(), '--only-schools tidak menyentuh kampus.');

        $school = University::where('code', $this->service()->allSchools()[0]['npsn'])->firstOrFail();
        $this->assertTrue($school->is_verified);
        $this->assertTrue($school->isSchool());

        $this->artisan('universities:sync-master', ['--only-schools' => true])->assertSuccessful();
        $this->assertSame($schoolCount, University::count(), 'Impor kedua tidak boleh menggandakan sekolah.');
    }

    public function test_sync_reuses_school_typed_manually_by_a_participant(): void
    {
        $typed = University::findOrCreateUnverified('SMKN 7 Surabaya');
        $this->assertFalse((bool) $typed->is_verified);

        $this->service()->syncSchools();

        $typed->refresh();
        $this->assertTrue($typed->is_verified);
        $this->assertTrue($typed->isSchool());
        // Entri ketikan peserta dipakai untuk SMKN 7 (tidak ada entri kedua dari master)
        $this->assertSame($this->datasetCount(), University::count());
    }

    public function test_full_sync_marks_campuses_as_colleges(): void
    {
        $this->artisan('universities:sync-master', ['--skip-schools' => true])->assertSuccessful();

        $this->assertGreaterThan(0, University::count());
        $this->assertSame(0, University::where('institution_type', University::TYPE_SCHOOL)->count());
    }

    public function test_resolving_a_school_search_result_creates_a_verified_school(): void
    {
        $item = $this->service()->search('smkn 7', 5)->firstWhere('institution_type', University::TYPE_SCHOOL);

        $school = $this->service()->resolveToUniversity($item);

        $this->assertTrue($school->isSchool());
        $this->assertSame($item['npsn'], $school->code);
        $this->assertSame($school->id, $this->service()->resolveToUniversity($item)->id);
    }
}
