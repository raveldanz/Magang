<?php

namespace Tests\Feature;

use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\AuditLog;
use App\Models\Certificate;
use App\Models\InternshipGroup;
use App\Models\InternshipProject;
use App\Models\Logbook;
use App\Models\Placement;
use App\Models\ProjectComment;
use App\Models\Unit;
use App\Models\University;
use App\Models\User;
use App\Support\CsvSafe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Regresi untuk temuan audit keamanan Prioritas 2/3 (security-audit run-1, NEEDS-VALIDATION.md).
 */
class AuditPriorityTwoSecurityTest extends TestCase
{
    use RefreshDatabase;

    private AgencyProfile $agencyA;

    private AgencyProfile $agencyB;

    private Unit $unitA;

    private Unit $unitA2;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agencyA = AgencyProfile::create(['agency_name' => 'Dinas A']);
        $this->agencyB = AgencyProfile::create(['agency_name' => 'Dinas B']);
        $this->unitA = Unit::create(['agency_profile_id' => $this->agencyA->id, 'name' => 'Bidang A1', 'quota' => 10]);
        $this->unitA2 = Unit::create(['agency_profile_id' => $this->agencyA->id, 'name' => 'Bidang A2', 'quota' => 10]);
        $this->superAdmin = User::factory()->create(['role' => 'super_admin', 'status' => 'active']);
    }

    private function placementIn(Unit $unit, array $placement = []): Placement
    {
        $student = User::factory()->create(['role' => 'mahasiswa', 'status' => 'active']);
        $application = Application::create([
            'user_id' => $student->id,
            'unit_id' => $unit->id,
            'status' => 'active',
            'start_date' => Carbon::now()->subDays(10)->toDateString(),
            'end_date' => Carbon::now()->addDays(20)->toDateString(),
        ]);

        return Placement::create(['application_id' => $application->id] + $placement);
    }

    private function privateProjectIn(Unit $unit): InternshipProject
    {
        return InternshipProject::create([
            'placement_id' => $this->placementIn($unit)->id,
            'title' => 'Proyek Rahasia Dinas',
            'category' => 'teknologi',
            'is_public' => false,
            'description' => 'x',
            'community_benefit' => 'x',
            'photo_path' => 'internship_projects/x.jpg',
        ]);
    }

    // magang.admin-audit-log.global-read-by-agency-admin

    public function test_agency_admins_cannot_read_global_audit_log(): void
    {
        AuditLog::create([
            'user_name' => 'Admin B', 'user_role' => 'admin', 'action' => 'USER_PASSWORD_RESET',
            'ip_address' => '10.9.8.7', 'details' => json_encode(['email' => 'victim-b@example.com']),
        ]);

        $adminDinas = User::factory()->create(['role' => 'admin', 'agency_profile_id' => $this->agencyA->id]);
        $adminBidang = User::factory()->create(['role' => 'admin', 'agency_profile_id' => $this->agencyA->id, 'unit_id' => $this->unitA->id]);

        foreach ([$adminDinas, $adminBidang] as $admin) {
            foreach (['admin.audit_logs.index', 'admin.audit-logs.index'] as $route) {
                $this->actingAs($admin)->get(route($route))->assertForbidden();
            }
        }

        $this->actingAs($this->superAdmin)->get(route('admin.audit_logs.index'))
            ->assertOk()->assertSee('10.9.8.7');
    }

    // magang.impersonation.stale-impersonator-authority-not-revalidated

    public function test_impersonation_ends_when_original_super_admin_is_demoted(): void
    {
        $student = User::factory()->create(['role' => 'mahasiswa', 'status' => 'active']);
        $otherAdmin = User::factory()->create(['role' => 'admin', 'agency_profile_id' => $this->agencyA->id, 'status' => 'active']);

        $this->actingAs($this->superAdmin)->post(route('admin.impersonate', $student->id));
        $this->assertAuthenticatedAs($student);

        $this->superAdmin->forceFill(['role' => 'mahasiswa'])->save();

        $this->post(route('admin.impersonate', $otherAdmin->id))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_impersonation_still_works_for_active_super_admin(): void
    {
        $student = User::factory()->create(['role' => 'mahasiswa', 'status' => 'active']);
        $otherAdmin = User::factory()->create(['role' => 'admin', 'agency_profile_id' => $this->agencyA->id, 'status' => 'active']);

        $this->actingAs($this->superAdmin)->post(route('admin.impersonate', $student->id));
        $this->post(route('admin.impersonate', $otherAdmin->id));
        $this->assertAuthenticatedAs($otherAdmin);

        $this->post(route('admin.impersonate.leave'));
        $this->assertAuthenticatedAs($this->superAdmin);
    }

    // magang.lecturer.same-university-dosen-writes-unadvised-logbook-status

    public function test_same_university_dosen_cannot_verify_logbook_of_unadvised_student(): void
    {
        $university = University::create(['name' => 'Kampus U', 'code' => 'KU']);
        $advisor = User::factory()->create(['role' => 'dosen', 'university_id' => $university->id]);
        $otherDosen = User::factory()->create(['role' => 'dosen', 'university_id' => $university->id]);

        $placement = $this->placementIn($this->unitA, ['academic_advisor_id' => $advisor->id]);
        $placement->application->user->forceFill(['university_id' => $university->id])->save();
        $logbook = Logbook::create(['placement_id' => $placement->id, 'date' => now()->toDateString(), 'activity' => 'x', 'status' => 'pending']);

        $this->actingAs($otherDosen)->put(route('lecturer.logbooks.updateStatus', $logbook->id), ['status' => 'approved'])
            ->assertForbidden();
        $this->assertNotSame('approved', $logbook->fresh()->lecturer_status);

        $this->actingAs($advisor)->put(route('lecturer.logbooks.updateStatus', $logbook->id), ['status' => 'approved']);
        $this->assertSame('approved', $logbook->fresh()->lecturer_status);
    }

    // magang.admin-users.bidang-admin-manages-null-unit-accounts

    public function test_admin_bidang_cannot_reset_same_agency_staff_without_unit(): void
    {
        $adminBidang = User::factory()->create(['role' => 'admin', 'agency_profile_id' => $this->agencyA->id, 'unit_id' => $this->unitA->id]);
        $supervisor = User::factory()->create(['role' => 'supervisor', 'agency_profile_id' => $this->agencyA->id, 'unit_id' => null, 'password' => 'rahasia-sup-1']);
        $mentorOwnUnit = User::factory()->create(['role' => 'mentor', 'agency_profile_id' => $this->agencyA->id, 'unit_id' => $this->unitA->id, 'password' => 'rahasia-mtr-1']);

        $this->actingAs($adminBidang)->post(route('admin.users.reset_password', $supervisor->id))->assertForbidden();
        $this->assertTrue(Hash::check('rahasia-sup-1', $supervisor->fresh()->password));

        $this->actingAs($adminBidang)->post(route('admin.users.reset_password', $mentorOwnUnit->id))->assertRedirect();
        $this->assertFalse(Hash::check('rahasia-mtr-1', $mentorOwnUnit->fresh()->password));
    }

    // magang.admin-users-create.return-to-javascript-href + magang.admin.return-to-unvalidated-redirect

    public function test_return_to_rejects_javascript_and_external_urls(): void
    {
        foreach (['javascript:alert(1)', 'https://attacker.example/x', '//attacker.example', '/\\attacker.example',
            url('/').'\\@attacker.example', url('/').'@attacker.example', url('/').'.attacker.example'] as $evil) {
            $this->actingAs($this->superAdmin)->get(route('admin.users.create', ['return_to' => $evil]))
                ->assertOk()->assertDontSee($evil, false);
        }

        $admin = User::factory()->create(['role' => 'admin', 'agency_profile_id' => $this->agencyA->id]);
        $this->actingAs($admin)->post(route('admin.units.store'), [
            'name' => 'Bidang Baru', 'quota' => 1, 'return_to' => 'https://attacker.example/x',
        ])->assertRedirect()->assertRedirectContains(url('/'));

        $this->actingAs($admin)->post(route('admin.units.store'), [
            'name' => 'Bidang Lain', 'quota' => 1, 'return_to' => '/admin/units',
        ])->assertRedirect('/admin/units');
    }

    // magang.agency.bidang-admin-edits-agency-profile-via-agency-controller

    public function test_admin_bidang_cannot_edit_agency_profile_through_agency_controller(): void
    {
        $adminBidang = User::factory()->create(['role' => 'admin', 'agency_profile_id' => $this->agencyA->id, 'unit_id' => $this->unitA->id]);

        $this->actingAs($adminBidang)->get(route('admin.agencies.edit', $this->agencyA->id))->assertForbidden();
        $this->actingAs($adminBidang)->put(route('admin.agencies.update', $this->agencyA->id), [
            'agency_name' => 'X', 'signee_name' => 'Attacker',
        ])->assertForbidden();
        $this->assertNotSame('Attacker', $this->agencyA->fresh()->signee_name);

        $adminDinas = User::factory()->create(['role' => 'admin', 'agency_profile_id' => $this->agencyA->id]);
        $this->actingAs($adminDinas)->get(route('admin.agencies.edit', $this->agencyA->id))->assertOk();
    }

    // magang.project-moderation.role-only-no-agency-scope

    public function test_other_agency_admin_cannot_view_or_moderate_private_project(): void
    {
        $project = $this->privateProjectIn($this->unitA);
        $comment = ProjectComment::create(['internship_project_id' => $project->id, 'author_name' => 'Tamu', 'comment' => 'halo']);
        $adminB = User::factory()->create(['role' => 'admin', 'agency_profile_id' => $this->agencyB->id]);

        $this->actingAs($adminB)->get(route('projects.show', $project->id))->assertForbidden();
        $this->actingAs($adminB)->post(route('projects.toggle_visibility', $project->id))->assertForbidden();
        $this->assertFalse($project->fresh()->is_public);
        $this->actingAs($adminB)->deleteJson(route('projects.comments.destroy', $comment->id))->assertForbidden();
        $this->assertNotNull($comment->fresh());
        $this->actingAs($adminB)->get(route('public.projects.index'))->assertOk()->assertDontSee('Proyek Rahasia Dinas');

        $adminA = User::factory()->create(['role' => 'admin', 'agency_profile_id' => $this->agencyA->id]);
        $this->actingAs($adminA)->get(route('projects.show', $project->id))->assertOk();
        $this->actingAs($adminA)->get(route('public.projects.index'))->assertSee('Proyek Rahasia Dinas');
        $this->actingAs($adminA)->post(route('projects.toggle_visibility', $project->id));
        $this->assertTrue($project->fresh()->is_public);
    }

    // magang.admin-application.agency-admin-bypasses-double-acc-via-active

    public function test_admin_dinas_cannot_skip_supervisor_approval_by_setting_active(): void
    {
        $adminDinas = User::factory()->create(['role' => 'admin', 'agency_profile_id' => $this->agencyA->id]);
        $application = Application::create([
            'user_id' => User::factory()->create(['role' => 'mahasiswa'])->id,
            'unit_id' => $this->unitA->id,
            'status' => 'pending',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(30)->toDateString(),
        ]);

        $this->actingAs($adminDinas)->put(route('admin.applications.updateStatus', $application->id), ['status' => 'active']);

        $this->assertSame('waiting_approval', $application->fresh()->statusValue());
    }

    // magang.admin-application.group-status-no-transition-or-mentor-validation

    public function test_admin_dinas_cannot_activate_or_complete_group_without_approval(): void
    {
        $adminDinas = User::factory()->create(['role' => 'admin', 'agency_profile_id' => $this->agencyA->id]);
        $leader = User::factory()->create(['role' => 'mahasiswa']);
        $group = InternshipGroup::create([
            'code' => InternshipGroup::generateUniqueCode(), 'name' => 'Tim Uji', 'leader_user_id' => $leader->id,
            'unit_id' => $this->unitA->id, 'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(30)->toDateString(), 'status' => 'pending',
        ]);
        $member = Application::create([
            'user_id' => $leader->id, 'unit_id' => $this->unitA->id, 'group_id' => $group->id, 'status' => 'pending',
            'start_date' => $group->start_date, 'end_date' => $group->end_date,
        ]);

        $this->actingAs($adminDinas)->put(route('admin.groups.update_status', $group->id), ['status' => 'active']);
        $this->assertSame('waiting_approval', $member->fresh()->statusValue());

        $member->forceFill(['status' => 'active'])->save();
        $this->actingAs($adminDinas)->put(route('admin.groups.update_status', $group->id), ['status' => 'completed'])
            ->assertSessionHas('error');
        $this->assertSame('active', $member->fresh()->statusValue());
    }

    // magang.supervisor.approvals-unscoped-and-state-unchecked

    public function test_agency_supervisor_cannot_process_other_agency_records(): void
    {
        $supervisorB = User::factory()->create(['role' => 'supervisor', 'agency_profile_id' => $this->agencyB->id]);
        $placement = $this->placementIn($this->unitA);
        $application = $placement->application;
        $application->forceFill(['status' => 'waiting_approval'])->save();
        $certificate = Certificate::create([
            'placement_id' => $placement->id, 'certificate_number' => 'SERT/1', 'file_path' => 'certificates/1.pdf',
            'issued_at' => now()->toDateString(), 'approval_status' => 'waiting_approval',
        ]);

        $this->actingAs($supervisorB)->get(route('supervisor.applications.show', $application->id))->assertForbidden();
        $this->actingAs($supervisorB)->post(route('supervisor.applications.approve', $application->id))->assertForbidden();
        $this->assertSame('waiting_approval', $application->fresh()->statusValue());
        $this->actingAs($supervisorB)->post(route('supervisor.certificates.approve', $certificate->id))->assertForbidden();
        $this->actingAs($supervisorB)->get(route('supervisor.reports.show', $placement->id))->assertForbidden();
        $this->assertSame('waiting_approval', $certificate->fresh()->approval_status);

        // Supervisor dinas A boleh memproses
        $supervisorA = User::factory()->create(['role' => 'supervisor', 'agency_profile_id' => $this->agencyA->id]);
        $this->actingAs($supervisorA)->get(route('supervisor.applications.show', $application->id))->assertOk();
    }

    public function test_supervisor_cannot_approve_certificate_that_is_not_waiting_for_approval(): void
    {
        $supervisor = User::factory()->create(['role' => 'supervisor', 'agency_profile_id' => $this->agencyA->id]);
        $certificate = Certificate::create([
            'placement_id' => $this->placementIn($this->unitA)->id, 'certificate_number' => 'SERT/2',
            'file_path' => 'certificates/2.pdf', 'issued_at' => now()->toDateString(), 'approval_status' => 'revision',
        ]);

        $this->actingAs($supervisor)->post(route('supervisor.certificates.approve', $certificate->id))->assertSessionHas('error');
        $this->assertSame('revision', $certificate->fresh()->approval_status);
    }

    // magang.university.name-fallback-cross-tenant-lecturer-control
    // magang.university.substring-match-cross-tenant-student-read

    public function test_university_admin_cannot_reach_other_campus_by_renaming_or_short_code(): void
    {
        $kampusA = University::create(['name' => 'Kampus A', 'code' => 'KA']);
        $kampusV = University::create(['name' => 'Universitas Victoria', 'code' => 'UV']);
        $adminA = User::factory()->create(['role' => 'universitas', 'university_id' => $kampusA->id, 'university' => 'Kampus A']);
        $dosenV = User::factory()->create(['role' => 'dosen', 'university_id' => $kampusV->id, 'university' => 'Universitas Victoria', 'password' => 'Dosen-V-Rahasia1']);

        // Ganti nama kampus menjadi nama kampus lain ditolak
        $this->actingAs($adminA)->post(route('university.profile.update'), [
            'name' => 'Universitas Victoria', 'code' => 'KA', 'evaluation_scheme' => 'mentor_only',
        ])->assertSessionHasErrors('name');
        $this->assertSame('Kampus A', $kampusA->fresh()->name);

        // Dosen kampus lain tetap tidak bisa direset
        $this->actingAs($adminA)->post(route('university.lecturers.reset_password', $dosenV->id))->assertForbidden();
        $this->assertTrue(Hash::check('Dosen-V-Rahasia1', $dosenV->fresh()->password));

        // Variasi penulisan nama kampus lain (huruf/tanda baca/spasi) atau akronimnya juga ditolak
        foreach (['universitas  victoria', 'Universitas-Victoria!', 'uv'] as $samaran) {
            $this->actingAs($adminA)->post(route('university.profile.update'), [
                'name' => $samaran, 'code' => 'KA', 'evaluation_scheme' => 'mentor_only',
            ])->assertSessionHasErrors('name');
        }

        // Kode satu huruf ditolak, dan pencocokan substring tidak lagi membuka data kampus lain
        $this->actingAs($adminA)->post(route('university.profile.update'), [
            'name' => 'Kampus A', 'code' => 'a', 'evaluation_scheme' => 'mentor_only',
        ])->assertSessionHasErrors('code');

        $placement = $this->placementIn($this->unitA);
        $placement->application->user->forceFill(['university_id' => $kampusV->id, 'university' => 'Universitas Victoria'])->save();
        $this->actingAs($adminA)->get(route('university.students.show', $placement->id))->assertForbidden();
        $this->actingAs($adminA)->get(route('university.students.letter', $placement->application_id))->assertForbidden();

        // Mahasiswa tanpa data kampus sama sekali tidak lagi cocok dengan semua kampus
        $orphan = $this->placementIn($this->unitA);
        $this->actingAs($adminA)->get(route('university.students.show', $orphan->id))->assertForbidden();
        $this->assertNull($orphan->application->user->fresh()->university_id);
    }

    public function test_legacy_student_with_exact_campus_name_is_still_recognised(): void
    {
        $kampusA = University::create(['name' => 'Kampus A', 'code' => 'KA']);
        $adminA = User::factory()->create(['role' => 'universitas', 'university_id' => $kampusA->id, 'university' => 'Kampus A']);

        $placement = $this->placementIn($this->unitA);
        $student = $placement->application->user;
        $student->forceFill(['university_id' => null, 'university' => 'Kampus A'])->save();

        $this->actingAs($adminA)->get(route('university.students.show', $placement->id))->assertOk();
        // Membaca data tidak lagi mengubah kampus mahasiswa sebagai efek samping
        $this->assertNull($student->fresh()->university_id);
    }

    // magang.admin-certificate.revision-missing-agency-scope

    public function test_other_agency_admin_cannot_open_or_rewrite_certificate_revision(): void
    {
        $adminB = User::factory()->create(['role' => 'admin', 'agency_profile_id' => $this->agencyB->id]);
        $placement = $this->placementIn($this->unitA);
        $studentName = $placement->application->user->name;

        $this->actingAs($adminB)->get(route('admin.certificates.revision', $placement->id))->assertForbidden();
        $this->assertSame(0, Certificate::where('placement_id', $placement->id)->count());

        $this->actingAs($adminB)->post(route('admin.certificates.updateRevision', $placement->id), [
            'student_name' => 'X', 'nim' => '1', 'universitas' => 'U', 'jurusan' => 'J',
            'start_date' => '2026-01-01', 'end_date' => '2026-02-01',
            'nilai_disiplin' => 100, 'nilai_kinerja' => 100, 'nilai_laporan' => 100, 'action' => 'save_draft',
        ])->assertForbidden();
        $this->assertSame($studentName, $placement->application->user->fresh()->name);
    }

    // magang.certificate-theme.global-theme-editable-by-any-agency-admin

    public function test_only_super_admin_or_dispusip_main_admin_can_change_global_certificate_theme(): void
    {
        // Migrasi standardisasi sudah membuat profil Dispusip
        $dispusip = AgencyProfile::where('agency_name', 'like', '%Perpustakaan%')->first()
            ?? AgencyProfile::create(['agency_name' => 'Dinas Perpustakaan dan Kearsipan']);
        $payload = [
            'year' => 2026, 'theme_name' => 'x', 'primary_color' => '#000000', 'secondary_color' => '#000000',
            'accent_color' => '#000000', 'number_format_pattern' => '{NUMBER}', 'signee_name' => 'Mallory',
            'signee_position' => 'P', 'city' => 'C',
        ];

        $adminOtherAgency = User::factory()->create(['role' => 'admin', 'agency_profile_id' => $this->agencyB->id]);
        $this->actingAs($adminOtherAgency)->post(route('admin.certificates.updateTheme'), $payload)->assertForbidden();

        // Mengganti nama instansi sendiri menjadi "...Perpustakaan..." tidak menjadikannya penerbit
        $this->agencyB->update(['agency_name' => 'Dinas Perpustakaan Palsu']);
        $this->actingAs($adminOtherAgency)->post(route('admin.certificates.updateTheme'), $payload)->assertForbidden();
        $this->assertDatabaseMissing('certificate_themes', ['signee_name' => 'Mallory']);

        // Bila ditetapkan lewat config, hanya instansi itu yang menjadi penerbit
        config(['app.certificate_issuer_agency_id' => $this->agencyB->id]);
        $this->assertSame($this->agencyB->id, AgencyProfile::certificateIssuerId());
        config(['app.certificate_issuer_agency_id' => null]);

        $adminDispusip = User::factory()->create(['role' => 'admin', 'agency_profile_id' => $dispusip->id]);
        $this->actingAs($adminDispusip)->post(route('admin.certificates.updateTheme'), ['signee_name' => 'Kepala Dispusip'] + $payload)
            ->assertRedirect();
        $this->assertDatabaseHas('certificate_themes', ['signee_name' => 'Kepala Dispusip']);
    }

    // magang.csv-export.formula-injection

    public function test_csv_cells_starting_with_formula_characters_are_neutralised(): void
    {
        $this->assertSame(
            ["'=HYPERLINK(\"x\")", "'@SUM(1)", "'+A1", "'-1+2", '-', '-5', 12, 'Budi', ''],
            CsvSafe::row(['=HYPERLINK("x")', '@SUM(1)', '+A1', '-1+2', '-', '-5', 12, 'Budi', ''])
        );
    }

    // magang.auth.password-change-does-not-revoke-sessions-or-tokens

    public function test_password_change_revokes_api_tokens_and_other_sessions(): void
    {
        config(['session.driver' => 'database']);

        $user = User::factory()->create(['role' => 'mahasiswa', 'status' => 'active', 'password' => 'Lama-Rahasia-1']);
        $token = $user->createToken('lama')->plainTextToken;
        $rememberBefore = $user->fresh()->remember_token;

        DB::table('sessions')->insert([
            'id' => 'sesi-penyerang', 'user_id' => $user->id, 'ip_address' => '1.2.3.4',
            'user_agent' => 'x', 'payload' => base64_encode('x'), 'last_activity' => time(),
        ]);

        $user->update(['password' => 'Baru-Rahasia-2']);

        $this->assertSame(0, $user->tokens()->count());
        $this->assertFalse(DB::table('sessions')->where('id', 'sesi-penyerang')->exists());
        $this->assertNotSame($rememberBefore, $user->fresh()->remember_token);

        $this->getJson('/api/me', ['Authorization' => 'Bearer '.$token])->assertUnauthorized();
    }

    // magang.project-interaction.private-project-anonymous-writes

    public function test_guest_cannot_like_or_comment_on_private_project(): void
    {
        $project = $this->privateProjectIn($this->unitA);

        $this->postJson(route('projects.like', $project->id))->assertForbidden();
        $this->postJson(route('projects.comments.store', $project->id), ['comment' => 'halo semua', 'author_name' => 'Tamu'])
            ->assertForbidden();

        $this->assertSame(0, (int) $project->fresh()->likes_count);
        $this->assertSame(0, ProjectComment::where('internship_project_id', $project->id)->count());
    }
}
