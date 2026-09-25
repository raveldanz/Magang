<?php

namespace Tests\Feature;

use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\FinalReport;
use App\Models\Placement;
use App\Models\Unit;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Regresi keamanan:
 * 1. Admin Dinas tidak boleh menaikkan role ke super_admin / mereset password Super Admin
 *    atau akun Admin/Mentor instansi lain (privilege escalation).
 * 2. Dokumen mahasiswa (CV, KTM, transkrip, laporan akhir) disimpan di disk private
 *    dan hanya bisa dibuka lewat route yang memeriksa hak akses.
 */
class SecurityAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private AgencyProfile $agencyA;
    private AgencyProfile $agencyB;
    private User $superAdmin;
    private User $adminA;
    private User $adminB;
    private User $mentorA;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agencyA = AgencyProfile::create(['agency_name' => 'Dinas A']);
        $this->agencyB = AgencyProfile::create(['agency_name' => 'Dinas B']);

        $this->superAdmin = User::factory()->create(['role' => 'super_admin', 'password' => 'rahasia-super']);
        $this->adminA = User::factory()->create(['role' => 'admin', 'agency_profile_id' => $this->agencyA->id]);
        $this->adminB = User::factory()->create(['role' => 'admin', 'agency_profile_id' => $this->agencyB->id, 'password' => 'rahasia-b']);
        $this->mentorA = User::factory()->create(['role' => 'mentor', 'agency_profile_id' => $this->agencyA->id, 'password' => 'rahasia-mentor']);
    }

    // ---------------------------------------------------------------
    // 1. Privilege escalation lewat Master Pengguna
    // ---------------------------------------------------------------

    public function test_admin_dinas_cannot_promote_self_to_super_admin(): void
    {
        $this->actingAs($this->adminA)
            ->put(route('admin.users.update', $this->adminA->id), [
                'name' => $this->adminA->name,
                'email' => $this->adminA->email,
                'role' => 'super_admin',
            ])
            ->assertSessionHasErrors('role');

        $this->assertSame('admin', $this->adminA->fresh()->role);
    }

    public function test_admin_dinas_cannot_become_system_admin_by_clearing_agency(): void
    {
        $this->actingAs($this->adminA)
            ->put(route('admin.users.update', $this->adminA->id), [
                'name' => $this->adminA->name,
                'email' => $this->adminA->email,
                'role' => 'admin',
                'agency_profile_id' => null,
            ]);

        $this->assertSame($this->agencyA->id, (int) $this->adminA->fresh()->agency_profile_id);
        $this->assertFalse($this->adminA->fresh()->isSuperAdmin());
    }

    public function test_admin_dinas_cannot_reset_super_admin_password(): void
    {
        $this->actingAs($this->adminA)
            ->post(route('admin.users.reset_password', $this->superAdmin->id))
            ->assertForbidden();

        $this->assertTrue(Hash::check('rahasia-super', $this->superAdmin->fresh()->password));
    }

    public function test_admin_dinas_cannot_reset_admin_of_other_agency(): void
    {
        $this->actingAs($this->adminA)
            ->post(route('admin.users.reset_password', $this->adminB->id))
            ->assertForbidden();

        $this->assertTrue(Hash::check('rahasia-b', $this->adminB->fresh()->password));
    }

    public function test_admin_dinas_bulk_reset_skips_accounts_outside_agency(): void
    {
        $this->actingAs($this->adminA)
            ->post(route('admin.users.bulk_reset_password'), ['user_ids' => [$this->adminB->id, $this->mentorA->id]]);

        $this->assertTrue(Hash::check('rahasia-b', $this->adminB->fresh()->password));
        $this->assertTrue(Hash::check('password', $this->mentorA->fresh()->password));
    }

    public function test_admin_dinas_can_still_manage_own_agency_mentor(): void
    {
        $this->actingAs($this->adminA)
            ->post(route('admin.users.reset_password', $this->mentorA->id))
            ->assertRedirect();

        $this->assertTrue(Hash::check('password', $this->mentorA->fresh()->password));
    }

    public function test_admin_dinas_cannot_create_admin_for_other_agency(): void
    {
        $this->actingAs($this->adminA)
            ->post(route('admin.users.store'), [
                'name' => 'Penyusup',
                'email' => 'penyusup@example.com',
                'role' => 'admin',
                'agency_profile_id' => $this->agencyB->id,
            ]);

        $created = User::where('email', 'penyusup@example.com')->first();
        $this->assertNotNull($created);
        $this->assertSame($this->agencyA->id, (int) $created->agency_profile_id);
    }

    public function test_super_admin_can_reset_any_password(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('admin.users.reset_password', $this->adminB->id))
            ->assertRedirect();

        $this->assertTrue(Hash::check('password', $this->adminB->fresh()->password));
    }

    public function test_return_to_rejects_external_url(): void
    {
        $this->actingAs($this->superAdmin)
            ->put(route('admin.users.update', $this->mentorA->id), [
                'name' => $this->mentorA->name,
                'email' => $this->mentorA->email,
                'role' => 'mentor',
                'agency_profile_id' => $this->agencyA->id,
                'return_to' => 'https://evil.example.com/phish',
            ])
            ->assertRedirect(route('admin.agencies.show', $this->agencyA->id));
    }

    public function test_admin_dinas_cannot_manage_other_agency_or_create_admin_accounts(): void
    {
        $this->actingAs($this->adminA)->get(route('admin.agencies.edit', $this->agencyB->id))->assertForbidden();
        $this->actingAs($this->adminA)->post(route('admin.agencies.create_account', $this->agencyB->id))->assertForbidden();
        $this->actingAs($this->adminA)->delete(route('admin.agencies.destroy', $this->agencyB->id))->assertForbidden();
    }

    // ---------------------------------------------------------------
    // 1b. Menu Perguruan Tinggi: aksi ubah hanya Super Admin
    // ---------------------------------------------------------------

    private function makeUniversityWithDosen(): array
    {
        $univ = University::create(['name' => 'Universitas Uji']);
        $dosen = User::factory()->create(['role' => 'dosen', 'university_id' => $univ->id, 'password' => 'rahasia-dosen']);
        $adminKampus = User::factory()->create(['role' => 'universitas', 'university_id' => $univ->id, 'password' => 'rahasia-kampus']);

        return [$univ, $dosen, $adminKampus];
    }

    public function test_admin_dinas_cannot_reset_or_delete_dosen(): void
    {
        [$univ, $dosen, $adminKampus] = $this->makeUniversityWithDosen();

        $this->actingAs($this->adminA)->post(route('admin.universities.dosens.reset_password', [$univ->id, $dosen->id]))->assertForbidden();
        $this->actingAs($this->adminA)->post(route('admin.users.reset_password', $dosen->id))->assertForbidden();
        $this->actingAs($this->adminA)->post(route('admin.users.reset_password', $adminKampus->id))->assertForbidden();
        $this->actingAs($this->adminA)->delete(route('admin.universities.dosens.destroy', [$univ->id, $dosen->id]))->assertForbidden();
        $this->actingAs($this->adminA)->post(route('admin.universities.create_account', $univ->id))->assertForbidden();
        $this->actingAs($this->adminA)->get(route('admin.universities.edit', $univ->id))->assertForbidden();

        $this->assertTrue(Hash::check('rahasia-dosen', $dosen->fresh()->password));
        $this->assertTrue(Hash::check('rahasia-kampus', $adminKampus->fresh()->password));
        $this->assertNotNull($dosen->fresh());
    }

    public function test_admin_dinas_cannot_create_dosen_account(): void
    {
        [$univ] = $this->makeUniversityWithDosen();

        $this->actingAs($this->adminA)->post(route('admin.users.store'), [
            'name' => 'Dosen Palsu',
            'email' => 'dosen.palsu@example.com',
            'role' => 'dosen',
            'university_id' => $univ->id,
        ])->assertSessionHasErrors('role');

        $this->assertNull(User::where('email', 'dosen.palsu@example.com')->first());
    }

    public function test_admin_dinas_can_view_university_but_without_action_buttons(): void
    {
        [$univ, $dosen] = $this->makeUniversityWithDosen();
        $resetUrl = route('admin.universities.dosens.reset_password', [$univ->id, $dosen->id]);

        $this->actingAs($this->adminA)->get(route('admin.universities.index'))->assertOk();
        $this->actingAs($this->adminA)->get(route('admin.universities.show', $univ->id))
            ->assertOk()
            ->assertDontSee($resetUrl, false);

        $this->actingAs($this->superAdmin)->get(route('admin.universities.show', $univ->id))
            ->assertOk()
            ->assertSee($resetUrl, false);
    }

    public function test_super_admin_can_reset_dosen_password(): void
    {
        [$univ, $dosen] = $this->makeUniversityWithDosen();

        $this->actingAs($this->superAdmin)
            ->post(route('admin.universities.dosens.reset_password', [$univ->id, $dosen->id]))
            ->assertRedirect();

        $this->assertTrue(Hash::check('password', $dosen->fresh()->password));
    }

    // ---------------------------------------------------------------
    // 2. Dokumen mahasiswa tidak lagi publik
    // ---------------------------------------------------------------

    private function makeApplication(User $student, AgencyProfile $agency, string $status = 'pending'): Application
    {
        $unit = Unit::create(['agency_profile_id' => $agency->id, 'name' => 'Unit ' . $agency->id, 'quota' => 5]);

        return Application::create([
            'user_id' => $student->id,
            'unit_id' => $unit->id,
            'status' => $status,
            'start_date' => Carbon::now()->addDays(5)->toDateString(),
            'end_date' => Carbon::now()->addDays(60)->toDateString(),
        ]);
    }

    public function test_new_application_documents_are_stored_on_private_disk(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $student = User::factory()->create(['role' => 'mahasiswa']);
        $student->studentProfile()->create(['nim' => '123', 'universitas' => 'Unesa', 'jurusan' => 'TI', 'phone' => '08']);
        $unit = Unit::create(['agency_profile_id' => $this->agencyA->id, 'name' => 'Unit Upload', 'quota' => 5]);

        $this->actingAs($student)->post(route('student.application.store'), [
            'unit_id' => $unit->id,
            'start_date' => Carbon::now()->addDays(5)->toDateString(),
            'end_date' => Carbon::now()->addDays(60)->toDateString(),
            'surat_pengantar' => UploadedFile::fake()->create('surat.pdf', 10, 'application/pdf'),
            'cv' => UploadedFile::fake()->create('cv.pdf', 10, 'application/pdf'),
            'transkrip' => UploadedFile::fake()->create('transkrip.pdf', 10, 'application/pdf'),
            'id_card' => UploadedFile::fake()->create('ktm.pdf', 10, 'application/pdf'),
        ])->assertSessionHasNoErrors();

        $docs = ApplicationDocument::all();
        $this->assertCount(4, $docs);
        foreach ($docs as $doc) {
            Storage::disk('local')->assertExists($doc->file_path);
            Storage::disk('public')->assertMissing($doc->file_path);
        }
    }

    public function test_application_document_route_enforces_authorization(): void
    {
        Storage::fake('local');

        $student = User::factory()->create(['role' => 'mahasiswa']);
        $otherStudent = User::factory()->create(['role' => 'mahasiswa']);
        $application = $this->makeApplication($student, $this->agencyA);

        Storage::disk('local')->put('documents/applications/ktm.pdf', '%PDF-1.4 test');
        $doc = ApplicationDocument::create([
            'application_id' => $application->id,
            'document_type' => 'KTM / Kartu Identitas',
            'file_path' => 'documents/applications/ktm.pdf',
        ]);

        $url = route('documents.application', $doc->id);

        $this->get($url)->assertRedirect(route('login'));
        $this->actingAs($otherStudent)->get($url)->assertForbidden();
        $this->actingAs($this->adminB)->get($url)->assertForbidden();

        $this->actingAs($student)->get($url)->assertOk();
        $this->actingAs($this->adminA)->get($url)->assertOk();
        $this->actingAs($this->superAdmin)->get($url)->assertOk();
    }

    public function test_legacy_public_documents_still_open_through_route(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $student = User::factory()->create(['role' => 'mahasiswa']);
        $application = $this->makeApplication($student, $this->agencyA);

        Storage::disk('public')->put('documents/applications/lama.pdf', '%PDF-1.4 lama');
        $doc = ApplicationDocument::create([
            'application_id' => $application->id,
            'document_type' => 'CV',
            'file_path' => 'documents/applications/lama.pdf',
        ]);

        $this->actingAs($student)->get(route('documents.application', $doc->id))->assertOk();
    }

    public function test_final_report_on_private_disk_is_served_only_to_authorized_users(): void
    {
        Storage::fake('local');

        $student = User::factory()->create(['role' => 'mahasiswa']);
        $otherStudent = User::factory()->create(['role' => 'mahasiswa']);
        $application = $this->makeApplication($student, $this->agencyA, 'active');
        $placement = Placement::create(['application_id' => $application->id, 'mentor_id' => $this->mentorA->id]);

        Storage::disk('local')->put('final_reports/Laporan_Akhir_123_budi_1.pdf', '%PDF-1.4 laporan');
        $report = FinalReport::create([
            'placement_id' => $placement->id,
            'file_path' => 'final_reports/Laporan_Akhir_123_budi_1.pdf',
            'status' => 'pending',
        ]);

        $url = route('final_reports.show', $report->id);

        $this->actingAs($otherStudent)->get($url)->assertForbidden();
        $this->actingAs($student)->get($url)->assertOk();
        $this->actingAs($this->mentorA)->get($url)->assertOk();
    }
}
