<?php

namespace Tests\Feature;

use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\InternshipGroup;
use App\Models\Unit;
use App\Models\University;
use App\Models\User;
use App\Services\MasterUniversityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterUniversityAndFileValidationQcTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $supervisor;

    protected User $student;

    protected AgencyProfile $agency;

    protected Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = AgencyProfile::create([
            'agency_name' => 'Dinas Perpustakaan dan Kearsipan Kota Surabaya',
            'address' => 'Jl. Rungkut Asri Tengah No. 5-7, Surabaya',
        ]);

        $this->unit = Unit::create([
            'agency_profile_id' => $this->agency->id,
            'name' => 'Bidang Layanan Otomasi Perpustakaan',
            'quota' => 10,
        ]);

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'agency_profile_id' => $this->agency->id,
            'status' => 'active',
        ]);

        $this->supervisor = User::factory()->create([
            'role' => 'supervisor',
            'agency_profile_id' => $this->agency->id,
            'status' => 'active',
        ]);

        $this->student = User::factory()->create([
            'role' => 'mahasiswa',
            'status' => 'active',
        ]);
    }

    /**
     * QC 1: API Master Universitas mengembalikan data pencarian resmi
     */
    public function test_master_university_api_search_returns_json_results(): void
    {
        $response = $this->getJson(route('api.master-universities.search', ['q' => 'unesa']));

        $response->assertOk()
            ->assertJsonStructure([
                'status',
                'query',
                'total',
                'data' => [
                    '*' => ['name', 'code', 'acronym', 'city', 'province'],
                ],
            ]);

        $data = $response->json('data');
        $this->assertNotEmpty($data);
        $this->assertTrue(collect($data)->contains(fn ($u) => str_contains(strtolower($u['name']), 'surabaya') || $u['code'] === 'UNESA'));
    }

    /**
     * QC 2: MasterUniversityService dapat melakukan resolve & persistensi ke database
     */
    public function test_master_university_service_resolves_and_persists_campus(): void
    {
        $service = app(MasterUniversityService::class);
        $search = $service->search('Gadjah Mada', 5);

        $this->assertNotEmpty($search);
        $first = $search->first();

        $resolved = $service->resolveToUniversity($first);
        $this->assertInstanceOf(University::class, $resolved);
        $this->assertTrue($resolved->is_verified);
        $this->assertDatabaseHas('universities', ['name' => $resolved->name]);
    }

    /**
     * QC 3: Kelompok magang tidak dobel di tabel supervisor & admin
     */
    public function test_group_applications_are_not_duplicated_in_supervisor_and_admin_index(): void
    {
        $group = InternshipGroup::create([
            'name' => 'Tim Pengembang Sistem',
            'code' => 'GRP-QC-1234',
            'leader_user_id' => $this->student->id,
            'unit_id' => $this->unit->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(3)->toDateString(),
            'status' => 'waiting_approval',
        ]);

        // Leader application
        $leaderApp = Application::create([
            'user_id' => $this->student->id,
            'unit_id' => $this->unit->id,
            'group_id' => $group->id,
            'role_in_group' => 'leader',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(3)->toDateString(),
            'status' => 'waiting_approval',
        ]);

        // Member 1 application
        $member1 = User::factory()->create(['role' => 'mahasiswa', 'status' => 'active']);
        $memberApp1 = Application::create([
            'user_id' => $member1->id,
            'unit_id' => $this->unit->id,
            'group_id' => $group->id,
            'role_in_group' => 'member',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(3)->toDateString(),
            'status' => 'waiting_approval',
        ]);

        // Member 2 application
        $member2 = User::factory()->create(['role' => 'mahasiswa', 'status' => 'active']);
        $memberApp2 = Application::create([
            'user_id' => $member2->id,
            'unit_id' => $this->unit->id,
            'group_id' => $group->id,
            'role_in_group' => 'member',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(3)->toDateString(),
            'status' => 'waiting_approval',
        ]);

        // Supervisor Index: Harus TEPAT 1 baris untuk kelompok ini, BUKAN 3 baris!
        $responseSupervisor = $this->actingAs($this->supervisor)
            ->get(route('supervisor.applications.index', ['status' => 'waiting_approval']));

        $responseSupervisor->assertOk();
        $supervisorApps = $responseSupervisor->viewData('applications');
        $this->assertEquals(1, $supervisorApps->total(), 'Kelompok magang muncul lebih dari 1 kali di halaman supervisor!');

        // Admin Index: Harus TEPAT 1 baris untuk kelompok ini, BUKAN 3 baris!
        $responseAdmin = $this->actingAs($this->admin)
            ->get(route('admin.applications.index'));

        $responseAdmin->assertOk();
        $adminApps = $responseAdmin->viewData('applications');
        $this->assertEquals(1, $adminApps->total(), 'Kelompok magang muncul lebih dari 1 kali di halaman admin!');
    }

    /**
     * QC 4: Label bahasa Indonesia pada formulir profil mahasiswa & tidak ada label Inggris
     */
    public function test_student_profile_edit_view_uses_indonesian_file_labels(): void
    {
        $this->student->studentProfile()->create([
            'nim' => '22051204001',
            'universitas' => 'Universitas Negeri Surabaya',
            'jurusan' => 'Teknik Informatika',
            'phone' => '081234567890',
        ]);

        $response = $this->actingAs($this->student)
            ->get(route('student.profile.edit'));

        $response->assertOk();
        $content = $response->getContent();

        // Label Indonesia harus ada
        $this->assertStringContainsString('Pilih Berkas', $content);
        $this->assertStringContainsString('Belum ada berkas dipilih', $content);

        // Label Inggris mentah dilarang ada di antarmuka
        $this->assertStringNotContainsString('>Choose File<', $content);
        $this->assertStringNotContainsString('>No file chosen<', $content);
    }

    /**
     * QC 5: Ekspor database artisan command berjalan sukses
     */
    public function test_database_export_artisan_command_generates_sql_file(): void
    {
        $testFileName = 'test_qc_export_'.time().'.sql';
        $path = storage_path('app/backups/'.$testFileName);

        try {
            $this->artisan('db:export-sql', ['--filename' => $testFileName])
                ->assertSuccessful();

            $this->assertFileExists($path);
            $content = (string) file_get_contents($path);
            $this->assertNotSame('', trim($content));

            $isPgDump = str_contains($content, 'PostgreSQL database dump');
            $isPhpExport = str_contains($content, 'SIP-MAGANG DATABASE EXPORT');
            $this->assertTrue(
                $isPgDump || $isPhpExport,
                'Berkas SQL ekspor harus berisi header pg_dump atau SIP-MAGANG DATABASE EXPORT.'
            );
        } finally {
            @unlink($path);
        }
    }
}
