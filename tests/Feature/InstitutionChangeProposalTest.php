<?php

namespace Tests\Feature;

use App\Console\Commands\SyncExternalInstitutions;
use App\Models\InstitutionChangeProposal;
use App\Models\InstitutionSyncRun;
use App\Models\University;
use App\Models\User;
use App\Services\Institutions\ChangeProposalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InstitutionChangeProposalTest extends TestCase
{
    use RefreshDatabase;

    private function school(string $name, ?string $acronym = null, ?string $address = null): University
    {
        return University::create(['name' => $name, 'acronym' => $acronym, 'address' => $address, 'code' => '90000001', 'institution_type' => University::TYPE_SCHOOL, 'is_verified' => false, 'status' => 'inactive']);
    }

    private function proposals(): ChangeProposalService
    {
        return app(ChangeProposalService::class);
    }

    private function admin(): int
    {
        return User::factory()->create(['role' => 'super_admin'])->id;
    }

    public function test_kampus_senama_dengan_kode_resmi_berbeda_dapat_diterima(): void
    {
        $rows = [
            ['name' => 'Institut Uji', 'official_code' => '001111', 'state_province' => 'Malang'],
            ['name' => 'Institut Uji', 'official_code' => '001112', 'state_province' => 'Surabaya'],
        ];
        $this->assertSame(2, $this->proposals()->forColleges($rows));
        $this->proposals()->decide(InstitutionChangeProposal::pluck('id')->all(), 'accept', $this->admin());
        foreach ($rows as $row) {
            $this->assertDatabaseHas('universities', ['name' => $row['name'], 'code' => $row['official_code']]);
        }
    }

    public function test_usul_lama_ditarik_ketika_direktori_kembali_sama(): void
    {
        $school = $this->school('SMK ASLI');
        $this->proposals()->forSchool($school, ['name' => 'SMK GANTI']);
        $this->proposals()->forSchool($school, ['name' => 'SMK ASLI']);
        $this->assertDatabaseMissing('institution_change_proposals', ['university_id' => $school->id, 'status' => 'pending']);
    }

    public function test_perubahan_nama_jadi_usul_dan_data_lokal_tidak_tertimpa(): void
    {
        $school = $this->school('SMK LAMA');

        $this->assertSame(1, $this->proposals()->forSchool($school, ['name' => 'SMK BARU']));

        $this->assertSame('SMK LAMA', $school->fresh()->name);
        $this->assertDatabaseHas('institution_change_proposals', ['university_id' => $school->id, 'kind' => 'name', 'current_value' => 'SMK LAMA', 'proposed_value' => 'SMK BARU', 'status' => 'pending']);
    }

    public function test_beda_huruf_spasi_tanda_baca_tidak_diusulkan(): void
    {
        $school = $this->school('SMKN 1  Contoh.', null, 'Jl. A,  Surabaya, Jawa Timur');

        $count = $this->proposals()->forSchool($school, ['name' => 'smkn 1 contoh', 'address' => 'JL A', 'city' => 'Surabaya', 'province' => 'Jawa Timur']);

        $this->assertSame(0, $count);
        $this->assertSame(0, InstitutionChangeProposal::count());
    }

    public function test_awalan_kota_atau_kabupaten_pada_alamat_tidak_diusulkan(): void
    {
        $school = $this->school('SMA UJI', null, 'JL. GADUNG 17-19, Kota Surabaya, Prov. Jawa Timur');
        $other = University::create(['name' => 'SMA KAB', 'code' => '90000002', 'address' => 'Jl. B, Kab. Gresik, Provinsi Jawa Timur', 'institution_type' => University::TYPE_SCHOOL]);

        $this->assertSame(0, $this->proposals()->forSchool($school, ['name' => 'SMA UJI', 'address' => 'JL. GADUNG 17-19', 'city' => 'Surabaya', 'province' => 'Jawa Timur']));
        $this->assertSame(0, $this->proposals()->forSchool($other, ['name' => 'SMA KAB', 'address' => 'Jl. B', 'city' => 'Gresik', 'province' => 'Jawa Timur']));
    }

    public function test_nama_sama_dengan_nama_resmi_tidak_diusulkan(): void
    {
        $school = $this->school('SMA Negeri 5 Surabaya', 'SMAN 5 SURABAYA');

        $this->assertSame(0, $this->proposals()->forSchool($school, ['name' => 'SMAN 5 SURABAYA']));
    }

    public function test_usul_ditolak_tidak_muncul_lagi(): void
    {
        $school = $this->school('SMK LAMA');
        $this->proposals()->forSchool($school, ['name' => 'SMK BARU']);

        $this->assertSame(1, $this->proposals()->decide([InstitutionChangeProposal::first()->id], 'reject', $this->admin()));

        $this->assertSame(0, $this->proposals()->forSchool($school->fresh(), ['name' => 'SMK BARU']));
        $this->assertSame(1, InstitutionChangeProposal::count());
        $this->assertSame('SMK LAMA', $school->fresh()->name);
    }

    public function test_usul_pending_lama_diganti_nilai_terbaru(): void
    {
        $school = $this->school('SMK LAMA');
        $this->proposals()->forSchool($school, ['name' => 'SMK SATU']);
        $this->proposals()->forSchool($school, ['name' => 'SMK DUA']);

        $this->assertSame(['SMK DUA'], InstitutionChangeProposal::pluck('proposed_value')->all());
    }

    public function test_terima_usul_mengubah_hanya_kolom_itu(): void
    {
        $school = $this->school('SMK LAMA', null, 'Jl. Lama, Surabaya, Jawa Timur');
        $this->proposals()->forSchool($school, ['name' => 'SMK LAMA', 'address' => 'Jl. Baru', 'city' => 'Surabaya', 'province' => 'Jawa Timur']);
        $id = InstitutionChangeProposal::first()->id;
        $admin = $this->admin();

        $this->assertSame(1, $this->proposals()->decide([$id], 'accept', $admin));
        $this->assertSame(0, $this->proposals()->decide([$id], 'accept', $admin)); // sudah diputuskan

        $school->refresh();
        $this->assertSame('Jl. Baru, Surabaya, Jawa Timur', $school->address);
        $this->assertSame('SMK LAMA', $school->name);
        $this->assertFalse($school->is_verified);
        $this->assertSame('inactive', $school->status);
        $this->assertSame(University::TYPE_SCHOOL, $school->institution_type);
    }

    public function test_kampus_baru_menunggu_persetujuan(): void
    {
        University::create(['name' => 'Universitas Sudah Ada', 'acronym' => 'USA', 'institution_type' => University::TYPE_COLLEGE, 'is_verified' => true, 'status' => 'active']);
        $rows = [['name' => 'Universitas  Sudah Ada.', 'acronym' => 'XX'], ['name' => 'Institut Baru Uji', 'acronym' => 'IBU', 'state_province' => 'Bali']];

        $this->assertSame(1, $this->proposals()->forColleges($rows));
        $this->assertSame(0, $this->proposals()->forColleges($rows)); // tidak diusulkan dua kali
        $this->assertSame(1, University::count());

        $this->proposals()->decide([InstitutionChangeProposal::first()->id], 'accept', $this->admin());

        $this->assertSame(2, University::count());
        $this->assertDatabaseHas('universities', ['name' => 'Institut Baru Uji', 'institution_type' => University::TYPE_COLLEGE]);
    }

    public function test_kegagalan_api_kampus_tidak_menghapus_hasil_sekolah(): void
    {
        Storage::fake('local');
        config([
            'services.school_directory.base_url' => 'https://dir.test', 'services.school_directory.enabled' => true,
            'services.college_directory.base_url' => 'http://colleges.test', 'services.college_directory.enabled' => true,
        ]);
        Http::fake([
            'colleges.test/*' => Http::response('timeout', 504),
            'dir.test/sekolah/SMK*' => Http::response(['status' => 'success', 'total_data' => 1, 'dataSekolah' => [['npsn' => '90000009', 'sekolah' => 'SMKN 9 UJI', 'bentuk' => 'SMK']]]),
            'dir.test/*' => Http::response(['status' => 'success', 'total_data' => 0, 'dataSekolah' => []]),
        ]);

        $this->artisan('institutions:sync-external')->assertSuccessful();
        $this->artisan('institutions:sync-external', ['--kind' => 'college'])->assertFailed();

        $this->assertDatabaseHas('universities', ['code' => '90000009']);
        $this->assertSame(['KAMPUS'], InstitutionSyncRun::where('kind', 'college')->firstOrFail()->failed_steps);
        // API kampus mati tidak boleh membuat jadwal menarik ulang seluruh sekolah tiap jam.
        $this->assertFalse(SyncExternalInstitutions::isDue());
    }

    public function test_sinkron_penuh_mengusulkan_perubahan_dan_kampus_baru(): void
    {
        Storage::fake('local');
        config([
            'services.school_directory.base_url' => 'https://dir.test', 'services.school_directory.enabled' => true,
            'services.college_directory.base_url' => 'http://colleges.test', 'services.college_directory.enabled' => true,
        ]);
        $this->school('SMK LAMA');
        Http::fake([
            'colleges.test/*' => Http::response(['recordsFiltered' => 1, 'data' => [['nama_pt' => 'Institut Baru Uji', 'npsn' => '123456', 'kabupaten_pt' => 'Kota Malang']]]),
            'dir.test/sekolah/SMK*' => Http::response(['status' => 'success', 'total_data' => 1, 'dataSekolah' => [['npsn' => '90000001', 'sekolah' => 'SMK GANTI NAMA', 'bentuk' => 'SMK']]]),
            'dir.test/*' => Http::response(['status' => 'success', 'total_data' => 0, 'dataSekolah' => []]),
        ]);

        $this->artisan('institutions:sync-external')->assertSuccessful();

        $this->artisan('institutions:sync-external', ['--kind' => 'college'])->assertSuccessful();
        $this->assertEquals(2, InstitutionSyncRun::sum('proposed_count'));
        $this->assertSame(2, InstitutionChangeProposal::where('status', 'pending')->count());
        $this->assertDatabaseHas('universities', ['code' => '90000001', 'name' => 'SMK LAMA']);
    }
}
