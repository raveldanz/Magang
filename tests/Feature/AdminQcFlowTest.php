<?php

namespace Tests\Feature;

use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\Evaluation;
use App\Models\FinalReport;
use App\Models\Logbook;
use App\Models\Placement;
use App\Models\StudentProfile;
use App\Models\Unit;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Temuan QC peran Admin Dinas dan Admin Bidang (16 Nov 2026):
 *  - Admin Dinas/Bidang bisa mereset, mengubah peran, dan menghapus akun Supervisor (melompati Double ACC),
 *  - ekspor mahasiswa per kampus tidak dibatasi instansi/bidang,
 *  - sertifikat yang belum diterbitkan tidak punya tombol terbitkan,
 *  - mentor bernama sama tidak bisa dibedakan saat dipilih,
 *  - klik ganda membuat dua bidang bernama sama,
 *  - warna tema sertifikat menerima teks bebas,
 *  - formulir pengguna menawarkan peran yang tidak diizinkan dan tidak menampilkan pesan galat.
 */
class AdminQcFlowTest extends TestCase
{
    use RefreshDatabase;

    private AgencyProfile $agency;

    private Unit $unitA;

    private Unit $unitB;

    private University $univ;

    private User $adminDinas;

    private User $adminBidang;

    private User $supervisor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = AgencyProfile::create(['agency_name' => 'Dinas Perpustakaan dan Kearsipan Kota Surabaya', 'alias' => 'Dispusip Surabaya']);
        $this->unitA = Unit::create(['agency_profile_id' => $this->agency->id, 'name' => 'Bidang Sekretariat', 'quota' => 10]);
        $this->unitB = Unit::create(['agency_profile_id' => $this->agency->id, 'name' => 'Bidang Perpustakaan', 'quota' => 10]);
        $this->univ = University::create(['name' => 'Universitas Uji', 'code' => 'UU', 'evaluation_scheme' => 'mentor_only', 'weight_mentor' => 100, 'weight_lecturer' => 0]);

        $this->adminDinas = User::factory()->create(['role' => 'admin', 'agency_profile_id' => $this->agency->id]);
        $this->adminBidang = User::factory()->create(['role' => 'admin', 'agency_profile_id' => $this->agency->id, 'unit_id' => $this->unitA->id]);
        $this->supervisor = User::factory()->create(['role' => 'supervisor', 'agency_profile_id' => $this->agency->id, 'unit_id' => $this->unitA->id]);
    }

    private function studentWithApplication(Unit $unit, string $name, string $status = 'active'): Placement
    {
        $student = User::factory()->create(['role' => 'mahasiswa', 'name' => $name, 'university_id' => $this->univ->id]);
        StudentProfile::create(['user_id' => $student->id, 'nim' => (string) random_int(100000, 999999), 'universitas' => $this->univ->name, 'university_id' => $this->univ->id, 'jurusan' => 'TI', 'phone' => '08']);
        $application = Application::create([
            'user_id' => $student->id,
            'unit_id' => $unit->id,
            'status' => $status,
            'start_date' => Carbon::now()->subDays(40)->toDateString(),
            'end_date' => Carbon::now()->subDays(2)->toDateString(),
        ]);

        return Placement::create(['application_id' => $application->id]);
    }

    public function test_admin_dinas_dan_admin_bidang_tidak_bisa_mengelola_akun_supervisor(): void
    {
        $oldHash = $this->supervisor->password;

        foreach ([$this->adminDinas, $this->adminBidang] as $admin) {
            $this->actingAs($admin)->get(route('admin.users.edit', $this->supervisor->id))->assertForbidden();
            $this->actingAs($admin)->post(route('admin.users.reset_password', $this->supervisor->id))->assertForbidden();
            $this->actingAs($admin)->put(route('admin.users.update', $this->supervisor->id), [
                'name' => $this->supervisor->name, 'email' => $this->supervisor->email, 'role' => 'mentor', 'status' => 'inactive',
            ])->assertForbidden();
            $this->actingAs($admin)->delete(route('admin.users.destroy', $this->supervisor->id))->assertForbidden();
            $this->actingAs($admin)->post(route('admin.users.bulk_reset_password'), ['user_ids' => [$this->supervisor->id]])
                ->assertSessionMissing('temporary_credentials');
        }

        $fresh = $this->supervisor->fresh();
        $this->assertNotNull($fresh);
        $this->assertSame('supervisor', $fresh->role);
        $this->assertSame($oldHash, $fresh->password);

        // Super Admin tetap boleh.
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $this->actingAs($superAdmin)->get(route('admin.users.edit', $this->supervisor->id))->assertOk();
    }

    public function test_ekspor_mahasiswa_kampus_dibatasi_instansi_dan_bidang(): void
    {
        $otherAgency = AgencyProfile::create(['agency_name' => 'Dinas Lain']);
        $otherUnit = Unit::create(['agency_profile_id' => $otherAgency->id, 'name' => 'Bidang Luar', 'quota' => 5]);

        $this->studentWithApplication($this->unitA, 'Mahasiswa Sekretariat');
        $this->studentWithApplication($this->unitB, 'Mahasiswa Perpustakaan');
        $this->studentWithApplication($otherUnit, 'Mahasiswa Dinas Lain');

        $url = route('admin.universities.export_students', $this->univ->id);

        $bidang = $this->actingAs($this->adminBidang)->get($url)->assertOk()->streamedContent();
        $this->assertStringContainsString('Mahasiswa Sekretariat', $bidang);
        $this->assertStringNotContainsString('Mahasiswa Perpustakaan', $bidang);
        $this->assertStringNotContainsString('Mahasiswa Dinas Lain', $bidang);

        $dinas = $this->actingAs($this->adminDinas)->get($url)->assertOk()->streamedContent();
        $this->assertStringContainsString('Mahasiswa Sekretariat', $dinas);
        $this->assertStringContainsString('Mahasiswa Perpustakaan', $dinas);
        $this->assertStringNotContainsString('Mahasiswa Dinas Lain', $dinas);
    }

    public function test_sertifikat_belum_diterbitkan_punya_tombol_terbitkan(): void
    {
        $mentor = User::factory()->create(['role' => 'mentor', 'agency_profile_id' => $this->agency->id, 'unit_id' => $this->unitA->id]);
        $placement = $this->studentWithApplication($this->unitA, 'Lulusan Tanpa Sertifikat', 'completed');
        $placement->update(['mentor_id' => $mentor->id]);
        Logbook::create(['placement_id' => $placement->id, 'date' => Carbon::now()->subDays(10)->toDateString(), 'activity' => 'Kegiatan harian magang.', 'status' => 'approved']);
        FinalReport::create(['placement_id' => $placement->id, 'title' => 'Laporan', 'file_path' => 'final_reports/a.pdf', 'final_report_path' => 'final_reports/a.pdf', 'status' => 'approved']);
        Evaluation::create(['placement_id' => $placement->id, 'nilai_disiplin' => 85, 'nilai_kinerja' => 85, 'nilai_laporan' => 85, 'final_score' => 85, 'grade' => 'A']);

        $this->actingAs($this->adminDinas)->get(route('admin.certificates.index'))
            ->assertOk()
            ->assertSee('Lulusan Tanpa Sertifikat')
            ->assertSee(route('admin.certificates.generate', $placement->id), false)
            ->assertSee('Terbitkan &amp; Ajukan ke Atasan', false);
    }

    public function test_mentor_bernama_sama_dibedakan_dengan_email_saat_dipilih(): void
    {
        User::factory()->create(['role' => 'mentor', 'name' => 'Budi Santoso', 'email' => 'budi.satu@uji.test', 'agency_profile_id' => $this->agency->id, 'unit_id' => $this->unitB->id]);
        User::factory()->create(['role' => 'mentor', 'name' => 'Budi Santoso', 'email' => 'budi.dua@uji.test', 'agency_profile_id' => $this->agency->id, 'unit_id' => $this->unitB->id]);
        User::factory()->create(['role' => 'mentor', 'name' => 'Siti Aminah', 'email' => 'siti@uji.test', 'agency_profile_id' => $this->agency->id, 'unit_id' => $this->unitB->id]);
        $placement = $this->studentWithApplication($this->unitB, 'Pemohon Baru', 'pending');

        $this->actingAs($this->adminDinas)->get(route('admin.applications.show', $placement->application_id))
            ->assertOk()
            ->assertSee('Budi Santoso (budi.satu@uji.test)')
            ->assertSee('Budi Santoso (budi.dua@uji.test)')
            ->assertDontSee('Siti Aminah (siti@uji.test)');
    }

    public function test_nama_bidang_kembar_dalam_satu_instansi_ditolak(): void
    {
        $payload = ['name' => 'Bidang Baru', 'quota' => 3];

        $this->actingAs($this->adminDinas)->post(route('admin.units.store'), $payload)->assertSessionHasNoErrors();
        $this->actingAs($this->adminDinas)->post(route('admin.units.store'), ['name' => '  bidang baru ', 'quota' => 3])
            ->assertSessionHasErrors('name');

        $this->assertSame(1, Unit::where('agency_profile_id', $this->agency->id)->whereRaw('LOWER(name) = ?', ['bidang baru'])->count());

        // Mengubah bidang tanpa mengganti namanya tetap boleh; memakai nama bidang lain ditolak.
        $this->actingAs($this->adminDinas)->put(route('admin.units.update', $this->unitA->id), ['name' => 'Bidang Sekretariat', 'quota' => 12])
            ->assertSessionHasNoErrors();
        $this->actingAs($this->adminDinas)->put(route('admin.units.update', $this->unitA->id), ['name' => 'Bidang Perpustakaan', 'quota' => 12])
            ->assertSessionHasErrors('name');
    }

    public function test_warna_tema_sertifikat_harus_kode_heksadesimal(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $payload = [
            'year' => 2026, 'theme_name' => 'Tema Uji', 'primary_color' => 'javascript:alert(1)', 'secondary_color' => '#zzz',
            'accent_color' => 'red;x:y', 'number_format_pattern' => 'X/{NUMBER}/{YEAR}', 'signee_name' => 'Nama', 'signee_position' => 'Jabatan', 'city' => 'Surabaya',
        ];

        $this->actingAs($superAdmin)->post(route('admin.certificates.updateTheme'), $payload)
            ->assertSessionHasErrors(['primary_color', 'secondary_color', 'accent_color']);

        $this->actingAs($superAdmin)->post(route('admin.certificates.updateTheme'), array_merge($payload, [
            'primary_color' => '#059669', 'secondary_color' => '#FFF', 'accent_color' => '#d97706',
        ]))->assertSessionHasNoErrors();
    }

    public function test_formulir_pengguna_admin_dinas_hanya_menawarkan_peran_yang_diizinkan(): void
    {
        $this->actingAs($this->adminDinas)->get(route('admin.users.create'))
            ->assertOk()
            ->assertSee('value="mentor"', false)
            ->assertDontSee('value="supervisor"', false)
            ->assertDontSee('value="universitas"', false);

        $this->actingAs($this->adminDinas)
            ->from(route('admin.users.create'))
            ->followingRedirects()
            ->post(route('admin.users.store'), ['name' => 'Atasan Gadungan', 'email' => 'gadungan@uji.test', 'role' => 'supervisor'])
            ->assertSee('Data belum dapat disimpan:')
            ->assertSee('peran akun');

        $this->assertNull(User::where('email', 'gadungan@uji.test')->first());

        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $this->actingAs($superAdmin)->get(route('admin.users.create'))->assertOk()->assertSee('value="supervisor"', false);
    }
}
