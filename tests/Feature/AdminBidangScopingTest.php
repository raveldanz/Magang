<?php

namespace Tests\Feature;

use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\Placement;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminBidangScopingTest extends TestCase
{
    use RefreshDatabase;

    protected AgencyProfile $agency;
    protected Unit $unitSekretariat;
    protected Unit $unitPerpustakaan;
    protected User $adminSekretariat;
    protected User $adminPerpustakaan;
    protected User $superAdmin;
    protected User $adminDinasUtama;
    protected Application $appSekretariat;
    protected Application $appPerpustakaan;
    protected User $mentorSekretariat;
    protected User $mentorPerpustakaan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = AgencyProfile::create([
            'government_name' => 'Pemerintah Kota Surabaya',
            'agency_name' => 'Dinas Perpustakaan dan Kearsipan Kota Surabaya',
            'city' => 'Kota Surabaya',
            'signee_name' => 'Kepala Dinas',
            'signee_position' => 'Kepala Dinas',
        ]);

        $this->unitSekretariat = Unit::create([
            'name' => 'Bidang Sekretariat',
            'agency_profile_id' => $this->agency->id,
            'quota' => 10,
        ]);

        $this->unitPerpustakaan = Unit::create([
            'name' => 'Bidang Pembinaan dan Pengelolaan Perpustakaan',
            'agency_profile_id' => $this->agency->id,
            'quota' => 15,
        ]);

        // Akun Admin Bidang
        $this->adminSekretariat = User::factory()->create([
            'name' => 'Admin Bidang Sekretariat',
            'email' => 'admin.sekretariat@surabaya.go.id',
            'role' => 'admin',
            'agency_profile_id' => $this->agency->id,
            'unit_id' => $this->unitSekretariat->id,
            'status' => 'active',
        ]);

        $this->adminPerpustakaan = User::factory()->create([
            'name' => 'Admin Bidang Perpustakaan',
            'email' => 'admin.perpustakaan@surabaya.go.id',
            'role' => 'admin',
            'agency_profile_id' => $this->agency->id,
            'unit_id' => $this->unitPerpustakaan->id,
            'status' => 'active',
        ]);

        // Akun Admin Dinas Utama (Seluruh Dinas)
        $this->adminDinasUtama = User::factory()->create([
            'name' => 'Admin Dispusip Surabaya',
            'email' => 'admin.dispusip@surabaya.go.id',
            'role' => 'admin',
            'agency_profile_id' => $this->agency->id,
            'unit_id' => null,
            'status' => 'active',
        ]);

        // Akun Super Admin
        $this->superAdmin = User::factory()->create([
            'name' => 'Super Admin Dispusip',
            'email' => 'admin@gmail.com',
            'role' => 'super_admin',
            'agency_profile_id' => $this->agency->id,
            'unit_id' => null,
            'status' => 'active',
        ]);

        // Mahasiswa & Aplikasi
        $mhs1 = User::factory()->create(['role' => 'mahasiswa', 'name' => 'Budi Sekretariat']);
        $this->appSekretariat = Application::create([
            'user_id' => $mhs1->id,
            'unit_id' => $this->unitSekretariat->id,
            'status' => 'pending',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(3)->toDateString(),
        ]);

        $mhs2 = User::factory()->create(['role' => 'mahasiswa', 'name' => 'Siti Perpustakaan']);
        $this->appPerpustakaan = Application::create([
            'user_id' => $mhs2->id,
            'unit_id' => $this->unitPerpustakaan->id,
            'status' => 'pending',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(3)->toDateString(),
        ]);

        // Mentor
        $this->mentorSekretariat = User::factory()->create([
            'name' => 'Mentor Sekre',
            'email' => 'mentor.sekre@dispusip.go.id',
            'role' => 'mentor',
            'agency_profile_id' => $this->agency->id,
            'unit_id' => $this->unitSekretariat->id,
            'status' => 'active',
        ]);

        $this->mentorPerpustakaan = User::factory()->create([
            'name' => 'Mentor Perpus',
            'email' => 'mentor.perpus@dispusip.go.id',
            'role' => 'mentor',
            'agency_profile_id' => $this->agency->id,
            'unit_id' => $this->unitPerpustakaan->id,
            'status' => 'active',
        ]);
    }

    public function test_admin_bidang_helper_and_role_label()
    {
        $this->assertTrue($this->adminSekretariat->isBidangAdmin());
        $this->assertFalse($this->adminSekretariat->isAgencyWideAdmin());
        $this->assertEquals('Admin Bidang Sekretariat', $this->adminSekretariat->roleLabel());

        $this->assertFalse($this->adminDinasUtama->isBidangAdmin());
        $this->assertTrue($this->adminDinasUtama->isAgencyWideAdmin());
        $this->assertEquals('Admin Dispusip', $this->adminDinasUtama->roleLabel());

        $this->assertTrue($this->superAdmin->isSuperAdmin());
        $this->assertTrue($this->superAdmin->isAgencyWideAdmin());
        $this->assertEquals('Super Admin', $this->superAdmin->roleLabel());
    }

    public function test_admin_bidang_only_sees_applications_of_own_bidang()
    {
        $response = $this->actingAs($this->adminSekretariat)->get(route('admin.applications.index'));
        $response->assertStatus(200);
        $response->assertSee('Budi Sekretariat');
        $response->assertDontSee('Siti Perpustakaan');
    }

    public function test_admin_bidang_cannot_view_or_update_other_bidang_application()
    {
        // View detail pengajuan bidang lain -> 403
        $response = $this->actingAs($this->adminSekretariat)->get(route('admin.applications.show', $this->appPerpustakaan->id));
        $response->assertStatus(403);

        // Update status pengajuan bidang lain -> 403
        $updateResp = $this->actingAs($this->adminSekretariat)->put(route('admin.applications.updateStatus', $this->appPerpustakaan->id), [
            'status' => 'rejected',
            'rejection_note' => 'Bukan wewenang bidang ini.',
        ]);
        $updateResp->assertStatus(403);
    }

    public function test_admin_bidang_only_sees_mentors_of_own_bidang()
    {
        $response = $this->actingAs($this->adminSekretariat)->get(route('admin.mentors.index'));
        $response->assertStatus(200);
        $response->assertSee('Mentor Sekre');
        $response->assertDontSee('Mentor Perpus');
    }

    public function test_admin_bidang_cannot_edit_other_bidang_mentor()
    {
        $response = $this->actingAs($this->adminSekretariat)->get(route('admin.mentors.edit', $this->mentorPerpustakaan->id));
        $response->assertStatus(403);

        $updateResp = $this->actingAs($this->adminSekretariat)->put(route('admin.mentors.update', $this->mentorPerpustakaan->id), [
            'name' => 'Mentor Diubah',
            'email' => 'mentor.perpus.ubah@dispusip.go.id',
        ]);
        $updateResp->assertStatus(403);
    }

    public function test_admin_bidang_unit_management_scoping_and_cannot_create_unit()
    {
        // Akses units.index langsung di-redirect ke halaman detail bidangnya sendiri
        $response = $this->actingAs($this->adminSekretariat)->get(route('admin.units.index'));
        $response->assertRedirect(route('admin.units.show', $this->unitSekretariat->id));

        // Akses detail bidangnya sendiri -> 200
        $showResp = $this->actingAs($this->adminSekretariat)->get(route('admin.units.show', $this->unitSekretariat->id));
        $showResp->assertStatus(200);
        $showResp->assertSee('Bidang Sekretariat');

        // Akses detail bidang lain -> 403
        $otherResp = $this->actingAs($this->adminSekretariat)->get(route('admin.units.show', $this->unitPerpustakaan->id));
        $otherResp->assertStatus(403);

        // Dilarang menambah unit baru -> 403
        $createResp = $this->actingAs($this->adminSekretariat)->get(route('admin.units.create'));
        $createResp->assertStatus(403);

        $storeResp = $this->actingAs($this->adminSekretariat)->post(route('admin.units.store'), [
            'name' => 'Bidang Ilegal Baru',
            'quota' => 5,
        ]);
        $storeResp->assertStatus(403);
    }

    public function test_super_admin_and_admin_dinas_see_all_applications_and_units()
    {
        // Super Admin
        $respSuper = $this->actingAs($this->superAdmin)->get(route('admin.applications.index'));
        $respSuper->assertStatus(200);
        $respSuper->assertSee('Budi Sekretariat');
        $respSuper->assertSee('Siti Perpustakaan');

        // Admin Dinas Utama
        $respDinas = $this->actingAs($this->adminDinasUtama)->get(route('admin.applications.index'));
        $respDinas->assertStatus(200);
        $respDinas->assertSee('Budi Sekretariat');
        $respDinas->assertSee('Siti Perpustakaan');
    }

    public function test_admin_bidang_logbook_scoping_and_protection()
    {
        $response = $this->actingAs($this->adminSekretariat)->get(route('admin.logbooks.index'));
        $response->assertStatus(200);

        // Akses profil dinas dilarang untuk admin bidang -> 403
        $agencyEditResp = $this->actingAs($this->adminSekretariat)->get(route('admin.agency_profile.edit'));
        $agencyEditResp->assertStatus(403);
    }

    public function test_admin_bidang_dashboard_scoping()
    {
        $response = $this->actingAs($this->adminSekretariat)->get(route('admin.dashboard'));
        $response->assertStatus(200);
        // Dashboard memuat nama user dan bidang
        $response->assertSee('Admin Bidang Sekretariat');
    }

    public function test_admin_bidang_can_edit_own_unit_and_redirects_to_show()
    {
        // Admin bidang dapat membuka form edit bidang miliknya sendiri
        $editResp = $this->actingAs($this->adminSekretariat)->get(route('admin.units.edit', $this->unitSekretariat->id));
        $editResp->assertStatus(200);
        $editResp->assertSee('Edit Pengaturan Bidang');

        // Admin bidang dilarang membuka form edit bidang lain -> 403
        $otherEditResp = $this->actingAs($this->adminSekretariat)->get(route('admin.units.edit', $this->unitPerpustakaan->id));
        $otherEditResp->assertStatus(403);

        // Update bidang sendiri berhasil dan me-redirect ke admin.units.show
        $updateResp = $this->actingAs($this->adminSekretariat)->put(route('admin.units.update', $this->unitSekretariat->id), [
            'name' => 'Bidang Sekretariat (Updated)',
            'description' => 'Tugas administrasi dan ketatausahaan dinas.',
            'quota' => 12,
        ]);
        $updateResp->assertRedirect(route('admin.units.show', $this->unitSekretariat->id));

        $this->assertEquals(12, $this->unitSekretariat->fresh()->quota);
    }

    public function test_admin_bidang_adaptive_headers_and_certificate_scoping()
    {
        // Mentors index memuat nama bidang pada header
        $mentorResp = $this->actingAs($this->adminSekretariat)->get(route('admin.mentors.index'));
        $mentorResp->assertStatus(200);
        $mentorResp->assertSee('Kelola Pembimbing Lapangan (Mentor) • Bidang Sekretariat');

        // Logbooks index memuat nama bidang pada header
        $logbookResp = $this->actingAs($this->adminSekretariat)->get(route('admin.logbooks.index'));
        $logbookResp->assertStatus(200);
        $logbookResp->assertSee('Rekapitulasi Logbook Mahasiswa • Bidang Sekretariat');

        // Certificates index memuat nama bidang pada header
        $certResp = $this->actingAs($this->adminSekretariat)->get(route('admin.certificates.index'));
        $certResp->assertStatus(200);
        $certResp->assertSee('Manajemen Sertifikat Kelulusan • Bidang Sekretariat');
    }

    public function test_admin_bidang_cannot_reassign_mentor_on_other_bidang()
    {
        $this->appSekretariat->update(['status' => 'accepted']);
        $this->appPerpustakaan->update(['status' => 'accepted']);

        // Re-assign mentor pada pengajuan bidang sendiri diperbolehkan
        $respOwn = $this->actingAs($this->adminSekretariat)->patch(
            route('admin.applications.assignment', $this->appSekretariat->id),
            ['mentor_id' => $this->mentorSekretariat->id]
        );
        $respOwn->assertRedirect(route('admin.applications.show', $this->appSekretariat->id));

        // Re-assign mentor pada pengajuan bidang lain dilarang -> 403
        $respOther = $this->actingAs($this->adminSekretariat)->patch(
            route('admin.applications.assignment', $this->appPerpustakaan->id),
            ['mentor_id' => $this->mentorPerpustakaan->id]
        );
        $respOther->assertStatus(403);
    }

    public function test_admin_bidang_user_management_scoping_and_protection()
    {
        // Index users hanya menampilkan user di bidangnya jika diakses Admin Bidang
        $respIndex = $this->actingAs($this->adminSekretariat)->get(route('admin.users.index'));
        $respIndex->assertStatus(200);
        $respIndex->assertSee('Mentor Sekre');
        $respIndex->assertDontSee('Mentor Perpus');

        // Admin bidang dilarang mengedit atau mereset password mentor/admin bidang lain
        $respEditOther = $this->actingAs($this->adminSekretariat)->get(route('admin.users.edit', $this->mentorPerpustakaan->id));
        $respEditOther->assertStatus(403);

        $respResetOther = $this->actingAs($this->adminSekretariat)->post(route('admin.users.reset_password', $this->mentorPerpustakaan->id));
        $respResetOther->assertStatus(403);
    }

    public function test_realtime_on_the_fly_auto_activation_across_roles()
    {
        // Mahasiswa berstatus ACCEPTED yang start_date nya hari ini
        $student = User::factory()->create(['role' => 'mahasiswa', 'name' => 'Peserta Uji Realtime']);
        $dosen = User::factory()->create(['role' => 'dosen', 'name' => 'Dosen Pembimbing Uji']);

        $application = Application::create([
            'user_id' => $student->id,
            'unit_id' => $this->unitSekretariat->id,
            'status' => 'accepted',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(3)->toDateString(),
        ]);

        $placement = Placement::create([
            'application_id' => $application->id,
            'mentor_id' => $this->mentorSekretariat->id,
            'pembimbing_id' => $this->mentorSekretariat->id,
            'academic_advisor_id' => $dosen->id,
        ]);

        $this->assertEquals('accepted', $application->fresh()->statusValue());

        // Saat Mentor membuka dashboard, status aplikasi mahasiswa langsung aktif otomatis
        $this->actingAs($this->mentorSekretariat)->get(route('mentor.dashboard'));
        $this->assertEquals('active', $application->fresh()->statusValue());

        // Set kembali ke accepted untuk menguji trigger dari Dosen
        $application->update(['status' => 'accepted']);
        $this->actingAs($dosen)->get(route('lecturer.dashboard'));
        $this->assertEquals('active', $application->fresh()->statusValue());
    }
}
