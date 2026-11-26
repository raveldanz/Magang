<?php

namespace Tests\Feature;

use App\Models\University;
use App\Services\Institutions\SchoolSnapshotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchoolSnapshotTest extends TestCase
{
    use RefreshDatabase;

    private string $path;

    protected function setUp(): void
    {
        parent::setUp();
        $this->path = storage_path('framework/testing/snapshot-'.uniqid().'.json.gz');
        @mkdir(dirname($this->path), 0777, true);
    }

    protected function tearDown(): void
    {
        @unlink($this->path);
        parent::tearDown();
    }

    private function make(array $attributes): University
    {
        return University::create($attributes + ['institution_type' => University::TYPE_SCHOOL, 'is_verified' => true, 'status' => 'active']);
    }

    private function write(array $rows): void
    {
        file_put_contents($this->path, gzencode(json_encode(['generated_at' => '2026-11-25T10:00:00+07:00', 'rows' => $rows])));
    }

    private function service(): SchoolSnapshotService
    {
        return app(SchoolSnapshotService::class);
    }

    public function test_impor_tidak_menghidupkan_npsn_yang_dihapus(): void
    {
        $this->make(['name' => 'SMK Uji', 'code' => '90000007'])->delete();
        $this->write([['npsn' => '90000007', 'name' => 'SMK Uji']]);
        $this->service()->import($this->path);
        $this->assertDatabaseMissing('universities', ['code' => '90000007']);
    }

    public function test_data_awal_membuang_nomor_telepon_dan_mempertahankan_kota(): void
    {
        $this->write([['npsn' => '90000009', 'name' => 'SMK UJI', 'address' => 'Jl. Mawar 12 No HP 081200000000, Surabaya']]);
        $this->service()->import($this->path);
        $this->assertDatabaseHas('universities', ['code' => '90000009', 'address' => 'Jl. Mawar 12, Surabaya']);
    }

    public function test_ekspor_hanya_sekolah_dari_direktori(): void
    {
        $this->make(['name' => 'SMA Direktori', 'code' => '90000001', 'address' => 'Jl. A', 'directory_synced_at' => now(), 'phone' => '0811', 'pic_name' => 'Orang', 'email' => 'a@b.test']);
        $this->make(['name' => 'SMA Manual', 'code' => null]);
        $this->make(['name' => 'SMA Ketik Admin', 'code' => '90000002']);
        $this->make(['name' => 'SMA Tanpa Kode', 'code' => null, 'directory_synced_at' => now()]);
        $this->make(['name' => 'Kampus', 'code' => 'K1', 'institution_type' => University::TYPE_COLLEGE, 'directory_synced_at' => now()]);

        $this->assertSame(1, $this->service()->export($this->path));

        $data = json_decode(gzdecode(file_get_contents($this->path)), true);
        $this->assertNotEmpty($data['generated_at']);
        $this->assertSame([['npsn' => '90000001', 'name' => 'SMA Direktori', 'address' => 'Jl. A']], $data['rows']);
    }

    public function test_impor_aman_diulang_dan_tidak_menimpa(): void
    {
        $corrected = $this->make(['name' => 'Nama Koreksi Admin', 'code' => '90000001', 'is_verified' => false, 'status' => 'inactive']);
        $this->write([
            ['npsn' => '90000001', 'name' => 'NAMA DARI DIREKTORI', 'address' => 'Jl. X'],
            ['npsn' => '90000002', 'name' => 'SMA BARU', 'address' => 'Jl. Y, Surabaya, Jawa Timur'],
        ]);

        $this->assertSame(['created' => 1, 'filled' => 0, 'skipped' => 1], $this->service()->import($this->path));
        $this->assertSame(['created' => 0, 'filled' => 0, 'skipped' => 2], $this->service()->import($this->path));

        $this->assertSame(2, University::count());
        $corrected->refresh();
        $this->assertSame('Nama Koreksi Admin', $corrected->name);
        $this->assertNull($corrected->address);
        $this->assertFalse($corrected->is_verified);
    }

    public function test_impor_menyimpan_nama_sama_beda_npsn_dan_mengisi_slug(): void
    {
        $manual = $this->make(['name' => 'SMK Manual', 'code' => null, 'is_verified' => false]);
        $this->write([
            ['npsn' => '90000003', 'name' => 'SMA PGRI 1', 'address' => null],
            ['npsn' => '90000004', 'name' => 'SMA PGRI 1', 'address' => null],
            ['npsn' => '90000005', 'name' => 'SMK MANUAL', 'address' => null],
        ]);

        $this->assertSame(['created' => 3, 'filled' => 0, 'skipped' => 0], $this->service()->import($this->path));

        $this->assertNull($manual->fresh()->code);
        $this->assertFalse($manual->fresh()->is_verified);
        $school = University::where('code', '90000004')->firstOrFail();
        $this->assertSame('sma-pgri-1', $school->slug);
        $this->assertSame(University::TYPE_SCHOOL, $school->institution_type);
        $this->assertTrue($school->is_verified);
        $this->assertSame('mentor_only', $school->evaluation_scheme);
        $this->assertNotNull($school->directory_synced_at);
    }

    public function test_file_tidak_ada_tidak_menggagalkan_pemasangan(): void
    {
        $this->artisan('institutions:import-snapshot', ['--path' => $this->path])->assertSuccessful();

        $this->assertSame(0, University::count());
    }

    public function test_file_rusak_dilaporkan_gagal(): void
    {
        file_put_contents($this->path, 'bukan gzip');

        $this->artisan('institutions:import-snapshot', ['--path' => $this->path])->assertFailed();
    }
}
