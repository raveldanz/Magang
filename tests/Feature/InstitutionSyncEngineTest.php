<?php

namespace Tests\Feature;

use App\Console\Commands\SyncExternalInstitutions;
use App\Exceptions\SyncAlreadyRunning;
use App\Models\InstitutionSyncRun;
use App\Models\University;
use App\Services\Institutions\InstitutionSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InstitutionSyncEngineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        config(['services.school_directory.base_url' => 'https://dir.test', 'services.school_directory.enabled' => true]);
    }

    private function page(array $rows): array
    {
        return ['status' => 'success', 'total_data' => count($rows), 'dataSekolah' => $rows];
    }

    private function engine(): InstitutionSyncService
    {
        return app(InstitutionSyncService::class);
    }

    public function test_sinkron_kedua_ditolak_selama_masih_aktif(): void
    {
        $this->engine()->start('manual');

        try {
            $this->engine()->start('schedule');
            $this->fail('Sinkron kedua seharusnya ditolak.');
        } catch (SyncAlreadyRunning) {
            $this->assertSame(1, InstitutionSyncRun::count());
        }
    }

    public function test_proses_macet_digantikan_proses_baru(): void
    {
        $old = $this->engine()->start('manual');
        $this->travel(InstitutionSyncRun::STALL_MINUTES + 1)->minutes();

        $new = $this->engine()->start('manual');

        $this->assertSame('failed', $old->fresh()->status);
        $this->assertSame('queued', $new->status);
        // Langkah milik proses lama yang terlambat dijalankan tidak berbuat apa pun.
        Http::fake();
        $this->assertNull($this->engine()->processStep($old->fresh(), 'SMA', 1));
        Http::assertNothingSent();
    }

    public function test_command_dilewati_saat_ada_sinkron_aktif(): void
    {
        Http::fake();
        $this->engine()->start('manual');

        $this->artisan('institutions:sync-external')->assertSuccessful();

        Http::assertNothingSent();
        $this->assertSame(1, InstitutionSyncRun::count());
    }

    public function test_semua_bentuk_kosong_dianggap_gagal(): void
    {
        Http::fake(['dir.test/*' => Http::response($this->page([]))]);

        $this->artisan('institutions:sync-external')->assertFailed();

        $this->assertNull(SyncExternalInstitutions::lastFullSync());
        $this->assertSame('Direktori sekolah tidak mengembalikan data.', InstitutionSyncRun::first()->message);
    }

    public function test_ma_kosong_saja_tetap_sukses(): void
    {
        Http::fake([
            'dir.test/sekolah/SMK*' => Http::response($this->page([['npsn' => '90000001', 'sekolah' => 'SMKN 1 UJI', 'bentuk' => 'SMK']])),
            'dir.test/*' => Http::response($this->page([])),
        ]);

        $this->artisan('institutions:sync-external')->assertSuccessful();

        $run = InstitutionSyncRun::first();
        $this->assertSame('success', $run->status);
        $this->assertSame(1, $run->created_count);
        $this->assertNotNull(SyncExternalInstitutions::lastFullSync());
        $this->assertNotNull(University::where('code', '90000001')->value('directory_synced_at'));
    }

    public function test_satu_bentuk_gagal_bentuk_lain_tetap_tersimpan(): void
    {
        Http::fake([
            'dir.test/sekolah/SMA*' => Http::response('rusak', 500),
            'dir.test/sekolah/SMK*' => Http::response($this->page([['npsn' => '90000002', 'sekolah' => 'SMKN 2 UJI', 'bentuk' => 'SMK']])),
            'dir.test/*' => Http::response($this->page([])),
        ]);

        $this->artisan('institutions:sync-external')->assertFailed();

        $run = InstitutionSyncRun::first();
        $this->assertSame(['SMA'], $run->failed_steps);
        $this->assertDatabaseHas('universities', ['code' => '90000002']);
        $this->assertNull(SyncExternalInstitutions::lastFullSync());
    }
}
