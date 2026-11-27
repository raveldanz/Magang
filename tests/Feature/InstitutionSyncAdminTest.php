<?php

namespace Tests\Feature;

use App\Jobs\RunInstitutionSyncStep;
use App\Models\AgencyProfile;
use App\Models\InstitutionChangeProposal;
use App\Models\InstitutionSyncRun;
use App\Models\University;
use App\Models\User;
use App\Services\Institutions\InstitutionSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InstitutionSyncAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        config(['services.school_directory.base_url' => 'https://dir.test', 'services.school_directory.enabled' => true]);
        $this->superAdmin = User::factory()->create(['role' => 'super_admin', 'agency_profile_id' => null]);
    }

    public function test_hanya_super_admin_yang_boleh(): void
    {
        $agency = AgencyProfile::create(['agency_name' => 'Dinas Uji']);
        $others = [
            User::factory()->create(['role' => 'admin', 'agency_profile_id' => $agency->id]),
            User::factory()->create(['role' => 'supervisor', 'agency_profile_id' => $agency->id]),
            User::factory()->create(['role' => 'mahasiswa']),
        ];
        Queue::fake();

        foreach ($others as $user) {
            $responses = [
                $this->actingAs($user)->post(route('admin.universities.sync.start')),
                $this->actingAs($user)->get(route('admin.universities.sync.status')),
                $this->actingAs($user)->get(route('admin.universities.sync.proposals')),
                $this->actingAs($user)->post(route('admin.universities.sync.decide'), ['ids' => [1], 'decision' => 'accept']),
            ];
            foreach ($responses as $response) {
                $response->assertForbidden();
            }
        }
        $this->assertSame(0, InstitutionSyncRun::count());
        Queue::assertNothingPushed();
    }

    public function test_tombol_membuat_run_dan_mengantrekan_satu_job(): void
    {
        Queue::fake();

        $this->actingAs($this->superAdmin)->post(route('admin.universities.sync.start'))->assertRedirect()->assertSessionHas('success');

        $this->assertSame('manual', InstitutionSyncRun::first()->trigger);
        $this->assertSame($this->superAdmin->id, InstitutionSyncRun::first()->started_by);
        Queue::assertPushed(RunInstitutionSyncStep::class, 1);
        $this->assertDatabaseHas('audit_logs', ['action' => 'INSTITUTION_SYNC_START']);
    }

    public function test_admin_bisa_memulai_kedua_jenis_sinkron_secara_bersamaan(): void
    {
        Queue::fake();
        config(['services.college_directory.enabled' => true, 'services.college_directory.base_url' => 'https://colleges.test']);
        foreach (['school', 'college'] as $kind) {
            $this->actingAs($this->superAdmin)->post(route('admin.universities.sync.start'), ['kind' => $kind])
                ->assertSessionHas('success');
            $this->assertDatabaseHas('institution_sync_runs', ['kind' => $kind]);
        }
        Queue::assertPushed(RunInstitutionSyncStep::class, 2);
    }

    public function test_klik_kedua_ditolak_dengan_pesan(): void
    {
        Queue::fake();
        $this->actingAs($this->superAdmin)->post(route('admin.universities.sync.start'));

        $this->actingAs($this->superAdmin)->post(route('admin.universities.sync.start'))->assertSessionHas('error', 'Sinkron masih berjalan.');

        Queue::assertPushed(RunInstitutionSyncStep::class, 1);
    }

    public function test_job_untuk_run_yang_sudah_gagal_tidak_berbuat_apa_pun(): void
    {
        Http::fake();
        Queue::fake();
        $sync = app(InstitutionSyncService::class);
        $run = $sync->start('manual');
        $sync->fail($run, 'dihentikan');

        (new RunInstitutionSyncStep($run->id, 'SMA', 1))->handle($sync);

        Http::assertNothingSent();
        Queue::assertNothingPushed();
        $this->assertSame('failed', $run->fresh()->status);
    }

    public function test_job_yang_gagal_menandai_run_gagal(): void
    {
        $run = app(InstitutionSyncService::class)->start('manual');

        (new RunInstitutionSyncStep($run->id, 'SMA', 1))->failed(new \RuntimeException('waktu habis'));

        $this->assertSame('failed', $run->fresh()->status);
    }

    public function test_batas_waktu_job_di_bawah_retry_after_antrean(): void
    {
        $job = new RunInstitutionSyncStep(1, 'SMA', 1);

        $this->assertSame(1, $job->tries);
        $this->assertLessThan((int) config('queue.connections.database.retry_after'), $job->timeout);
    }

    public function test_status_melaporkan_antrean_belum_berjalan(): void
    {
        Queue::fake();
        $this->actingAs($this->superAdmin)->post(route('admin.universities.sync.start'));
        $this->actingAs($this->superAdmin)->getJson(route('admin.universities.sync.status'))
            ->assertOk()->assertJsonPath('active', true)->assertJsonPath('queue_waiting', false);

        $this->travel(61)->seconds();

        $this->actingAs($this->superAdmin)->getJson(route('admin.universities.sync.status'))
            ->assertJsonPath('queue_waiting', true)->assertJsonPath('stalled', false);
    }

    public function test_rantai_job_selesai_dengan_status_sukses(): void
    {
        Http::fake([
            'dir.test/sekolah/SMK*' => Http::response(['status' => 'success', 'total_data' => 1, 'dataSekolah' => [['npsn' => '90000001', 'sekolah' => 'SMKN 1 UJI', 'bentuk' => 'SMK']]]),
            'dir.test/*' => Http::response(['status' => 'success', 'total_data' => 0, 'dataSekolah' => []]),
        ]);

        $this->actingAs($this->superAdmin)->post(route('admin.universities.sync.start'));

        $this->actingAs($this->superAdmin)->getJson(route('admin.universities.sync.status'))
            ->assertJsonPath('run.status', 'success')->assertJsonPath('run.created', 1)->assertJsonPath('active', false);
    }

    public function test_halaman_sekolah_dan_usul_menampilkan_panel(): void
    {
        $school = University::create(['name' => 'SMK LAMA', 'code' => '90000001', 'institution_type' => University::TYPE_SCHOOL, 'is_verified' => true, 'status' => 'active']);
        InstitutionChangeProposal::create(['university_id' => $school->id, 'kind' => 'name', 'current_value' => 'SMK LAMA', 'proposed_value' => 'SMK BARU', 'fingerprint' => sha1('a'), 'status' => 'pending']);

        foreach (['school' => 1, 'college' => 0] as $kind => $expected) {
            $this->actingAs($this->superAdmin)->getJson(route('admin.universities.sync.status', ['kind' => $kind]))
                ->assertJsonPath('pending_proposals', $expected);
        }
        $this->actingAs($this->superAdmin)->get(route('admin.universities.index'))->assertOk()->assertSee('Perbarui Sekolah')->assertSee('Perbarui Kampus');
        $this->actingAs($this->superAdmin)->get(route('admin.universities.sync.proposals'))->assertOk()->assertSee('SMK LAMA')->assertSee('SMK BARU');
    }

    public function test_keputusan_usul_dicatat_audit(): void
    {
        $school = University::create(['name' => 'SMK LAMA', 'code' => '90000001', 'institution_type' => University::TYPE_SCHOOL, 'is_verified' => true, 'status' => 'active']);
        $make = fn (string $value) => InstitutionChangeProposal::create(['university_id' => $school->id, 'kind' => 'name', 'proposed_value' => $value, 'fingerprint' => sha1($value), 'status' => 'pending']);

        $this->actingAs($this->superAdmin)->post(route('admin.universities.sync.decide'), ['ids' => [$make('SMK A')->id], 'decision' => 'accept'])->assertSessionHas('success');
        $this->actingAs($this->superAdmin)->post(route('admin.universities.sync.decide'), ['ids' => [$make('SMK B')->id], 'decision' => 'reject'])->assertSessionHas('success');

        $this->assertSame('SMK A', $school->fresh()->name);
        $this->assertDatabaseHas('audit_logs', ['action' => 'INSTITUTION_PROPOSAL_ACCEPT']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'INSTITUTION_PROPOSAL_REJECT']);
    }
}
