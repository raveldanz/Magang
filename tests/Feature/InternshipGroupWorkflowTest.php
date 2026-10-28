<?php

namespace Tests\Feature;

use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\InternshipGroup;
use App\Models\StudentProfile;
use App\Models\Unit;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InternshipGroupWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $leader;
    private User $member;
    private User $admin;
    private User $mentor;
    private Unit $unit;
    private University $university;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');

        $this->university = University::create([
            'name' => 'Universitas Surabaya',
            'acronym' => 'UBAYA',
            'address' => 'Jl. Ngagel Jaya',
            'is_verified' => true,
        ]);

        $agency = AgencyProfile::create([
            'agency_name' => 'Dinas Perpustakaan dan Kearsipan Kota Surabaya',
            'acronym' => 'DISPUSIP',
            'address' => 'Jl. Rungkut Asri',
        ]);

        $this->unit = Unit::create([
            'name' => 'Bidang Pengelolaan Perpustakaan',
            'agency_profile_id' => $agency->id,
            'quota' => 10,
        ]);

        $this->leader = User::factory()->create([
            'role' => 'mahasiswa',
            'university_id' => $this->university->id,
            'university' => $this->university->name,
        ]);
        StudentProfile::create([
            'user_id' => $this->leader->id,
            'nim' => '11001',
            'universitas' => $this->university->name,
            'jurusan' => 'Sistem Informasi',
            'phone' => '08123456789',
            'cv_path' => 'documents/student-profiles/cv-leader.pdf',
            'transcript_path' => 'documents/student-profiles/transcript-leader.pdf',
            'id_card_path' => 'documents/student-profiles/ktm-leader.pdf',
        ]);

        $this->member = User::factory()->create([
            'role' => 'mahasiswa',
            'university_id' => $this->university->id,
            'university' => $this->university->name,
        ]);
        StudentProfile::create([
            'user_id' => $this->member->id,
            'nim' => '11002',
            'universitas' => $this->university->name,
            'jurusan' => 'Sistem Informasi',
            'phone' => '08123456780',
            'cv_path' => 'documents/student-profiles/cv-member.pdf',
            'transcript_path' => 'documents/student-profiles/transcript-member.pdf',
            'id_card_path' => 'documents/student-profiles/ktm-member.pdf',
        ]);

        $this->mentor = User::factory()->create([
            'role' => 'mentor',
            'unit_id' => $this->unit->id,
            'agency_profile_id' => $agency->id,
        ]);

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'agency_profile_id' => $agency->id,
        ]);
    }

    public function test_student_can_view_group_registration_interface(): void
    {
        $response = $this->actingAs($this->leader)->get(route('student.application.create'));

        $response->assertStatus(200);
        $response->assertSee('Pendaftaran Individu');
        $response->assertSee('Pendaftaran Kelompok');
        $response->assertSee('Buat Kelompok Baru');
        $response->assertSee('Gabung ke Kelompok Rekan');
    }

    public function test_leader_can_create_group_and_member_can_join_via_code(): void
    {
        // 1. Leader membuat kelompok
        $response = $this->actingAs($this->leader)->post(route('student.group.create'), [
            'name' => 'Tim Riset Digital',
            'unit_id' => $this->unit->id,
            'start_date' => now()->addDays(5)->toDateString(),
            'end_date' => now()->addDays(65)->toDateString(),
        ]);

        $response->assertRedirect(route('student.application.create'));
        $this->assertDatabaseHas('internship_groups', [
            'name' => 'Tim Riset Digital',
            'leader_user_id' => $this->leader->id,
            'status' => 'draft',
        ]);

        $group = InternshipGroup::where('leader_user_id', $this->leader->id)->first();
        $this->assertNotNull($group->code);

        // Leader memiliki draft application
        $this->assertDatabaseHas('applications', [
            'user_id' => $this->leader->id,
            'group_id' => $group->id,
            'role_in_group' => 'leader',
            'status' => 'draft',
        ]);

        // 2. Member bergabung menggunakan kode
        $joinResponse = $this->actingAs($this->member)->post(route('student.group.join'), [
            'group_code' => $group->code,
        ]);

        $joinResponse->assertRedirect(route('student.application.create'));
        $this->assertDatabaseHas('applications', [
            'user_id' => $this->member->id,
            'group_id' => $group->id,
            'role_in_group' => 'member',
            'status' => 'draft',
        ]);

        $this->assertEquals(2, $group->applications()->count());
    }

    public function test_leader_can_submit_group_application_with_proposal(): void
    {
        $group = InternshipGroup::create([
            'code' => InternshipGroup::generateUniqueCode(),
            'name' => 'Tim Kearsipan MBKM',
            'leader_user_id' => $this->leader->id,
            'unit_id' => $this->unit->id,
            'start_date' => now()->addDays(5)->toDateString(),
            'end_date' => now()->addDays(65)->toDateString(),
            'status' => 'draft',
        ]);

        Application::create([
            'user_id' => $this->leader->id,
            'unit_id' => $this->unit->id,
            'start_date' => $group->start_date,
            'end_date' => $group->end_date,
            'group_id' => $group->id,
            'role_in_group' => 'leader',
            'status' => 'draft',
        ]);

        Application::create([
            'user_id' => $this->member->id,
            'unit_id' => $this->unit->id,
            'start_date' => $group->start_date,
            'end_date' => $group->end_date,
            'group_id' => $group->id,
            'role_in_group' => 'member',
            'status' => 'draft',
        ]);

        $file = UploadedFile::fake()->create('proposal_kelompok.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->leader)->post(route('student.group.submit'), [
            'surat_pengantar' => $file,
        ]);

        $response->assertRedirect(route('student.application.create'));

        $group->refresh();
        $this->assertEquals('pending', $group->status);
        $this->assertNotNull($group->proposal_letter_path);

        // Seluruh anggota kelompok berstatus pending
        $this->assertDatabaseHas('applications', [
            'user_id' => $this->leader->id,
            'status' => 'pending',
            'group_id' => $group->id,
        ]);
        $this->assertDatabaseHas('applications', [
            'user_id' => $this->member->id,
            'status' => 'pending',
            'group_id' => $group->id,
        ]);

        // Admin dapat melihat dan mengunduh proposal kelompok
        $viewProposalResponse = $this->actingAs($this->admin)->get(route('documents.group.proposal', $group->id));
        $viewProposalResponse->assertOk();

        $downloadProposalResponse = $this->actingAs($this->admin)->get(route('documents.group.proposal.download', $group->id));
        $downloadProposalResponse->assertOk();
    }

    public function test_admin_accepts_group_application_and_syncs_all_members(): void
    {
        $supervisor = User::factory()->create([
            'role' => 'supervisor',
            'agency_profile_id' => $this->admin->agency_profile_id,
        ]);

        $group = InternshipGroup::create([
            'code' => InternshipGroup::generateUniqueCode(),
            'name' => 'Tim Pengolahan',
            'leader_user_id' => $this->leader->id,
            'unit_id' => $this->unit->id,
            'start_date' => now()->addDays(5)->toDateString(),
            'end_date' => now()->addDays(65)->toDateString(),
            'status' => 'pending',
            'letter_token' => 'GRPTOKEN12345',
        ]);

        $leaderApp = Application::create([
            'user_id' => $this->leader->id,
            'unit_id' => $this->unit->id,
            'start_date' => $group->start_date,
            'end_date' => $group->end_date,
            'group_id' => $group->id,
            'role_in_group' => 'leader',
            'status' => 'pending',
            'letter_token' => 'LEADERTOKEN123',
        ]);

        $memberApp = Application::create([
            'user_id' => $this->member->id,
            'unit_id' => $this->unit->id,
            'start_date' => $group->start_date,
            'end_date' => $group->end_date,
            'group_id' => $group->id,
            'role_in_group' => 'member',
            'status' => 'pending',
            'letter_token' => 'MEMBERTOKEN123',
        ]);

        // 1. Admin Dinas staf memverifikasi berkas dan mengajukan ACC ke Atasan (Double ACC)
        $adminResponse = $this->actingAs($this->admin)->put(route('admin.applications.updateStatus', $leaderApp->id), [
            'status' => 'accepted',
            'mentor_id' => $this->mentor->id,
            'letter_number' => '500.12.1/045/DISPUSIP/2026',
        ]);

        $adminResponse->assertRedirect();

        $group->refresh();
        $leaderApp->refresh();
        $memberApp->refresh();

        // Status tersinkronisasi menjadi waiting_approval untuk seluruh anggota kelompok
        $this->assertEquals('waiting_approval', $group->status);
        $this->assertEquals('waiting_approval', $leaderApp->statusValue());
        $this->assertEquals('waiting_approval', $memberApp->statusValue());

        // 2. Supervisor / Atasan mengesahkan (ACC) pengajuan kelompok
        $supervisorResponse = $this->actingAs($supervisor)->post(route('supervisor.applications.approve', $leaderApp->id));
        $supervisorResponse->assertRedirect();

        $group->refresh();
        $leaderApp->refresh();
        $memberApp->refresh();

        $this->assertEquals('accepted', $group->status);
        $this->assertEquals('accepted', $leaderApp->statusValue());
        $this->assertEquals('accepted', $memberApp->statusValue());

        // Placement dibuat untuk masing-masing mahasiswa
        $this->assertDatabaseHas('placements', [
            'application_id' => $leaderApp->id,
        ]);
        $this->assertDatabaseHas('placements', [
            'application_id' => $memberApp->id,
        ]);
    }

    public function test_group_acceptance_letter_renders_all_members(): void
    {
        $group = InternshipGroup::create([
            'code' => InternshipGroup::generateUniqueCode(),
            'name' => 'Tim Layanan Kearsipan',
            'leader_user_id' => $this->leader->id,
            'unit_id' => $this->unit->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(60)->toDateString(),
            'status' => 'accepted',
            'letter_number' => '500.12.1/100/DISPUSIP/2026',
            'letter_token' => 'TOKENRESMI1234567890123456789012',
        ]);

        $leaderApp = Application::create([
            'user_id' => $this->leader->id,
            'unit_id' => $this->unit->id,
            'start_date' => $group->start_date,
            'end_date' => $group->end_date,
            'group_id' => $group->id,
            'role_in_group' => 'leader',
            'status' => 'accepted',
            'letter_token' => 'TOKENRESMI_LEADER_1234567890123',
        ]);

        $memberApp = Application::create([
            'user_id' => $this->member->id,
            'unit_id' => $this->unit->id,
            'start_date' => $group->start_date,
            'end_date' => $group->end_date,
            'group_id' => $group->id,
            'role_in_group' => 'member',
            'status' => 'accepted',
            'letter_token' => 'TOKENRESMI_MEMBER_1234567890123',
        ]);

        // Leader mengunduh surat
        $response = $this->actingAs($this->leader)->get(route('student.application.letter', $leaderApp->id));
        $response->assertStatus(200);
        $response->assertSee($this->leader->name);
        $response->assertSee($this->member->name);
        $response->assertSee('Ketua');
        $response->assertSee('Anggota');

        // Verifikasi publik via QR token
        $verifyResponse = $this->get(route('verify.letter', $group->letter_token));
        $verifyResponse->assertStatus(200);
        $verifyResponse->assertSee('Kelompok Magang');
        $verifyResponse->assertSee($this->leader->name);
        $verifyResponse->assertSee($this->member->name);
    }

    public function test_admin_can_filter_applications_by_registration_type(): void
    {
        // Pendaftar individu
        $individualUser = User::factory()->create([
            'name' => 'Budi Pendaftar Individu',
            'role' => 'mahasiswa',
            'university_id' => $this->university->id,
        ]);
        StudentProfile::create([
            'user_id' => $individualUser->id,
            'nim' => 'INDIV001',
            'universitas' => $this->university->name,
            'jurusan' => 'Statistika',
            'phone' => '08123450001',
        ]);
        Application::create([
            'user_id' => $individualUser->id,
            'unit_id' => $this->unit->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(60)->toDateString(),
            'status' => 'pending',
            'group_id' => null,
            'role_in_group' => null,
        ]);

        // Pendaftar kelompok
        $group = InternshipGroup::create([
            'code' => InternshipGroup::generateUniqueCode(),
            'name' => 'Tim Filter Khusus',
            'leader_user_id' => $this->leader->id,
            'unit_id' => $this->unit->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(60)->toDateString(),
            'status' => 'pending',
        ]);
        Application::create([
            'user_id' => $this->leader->id,
            'unit_id' => $this->unit->id,
            'start_date' => $group->start_date,
            'end_date' => $group->end_date,
            'group_id' => $group->id,
            'role_in_group' => 'leader',
            'status' => 'pending',
        ]);

        // Filter group only
        $groupResponse = $this->actingAs($this->admin)->get(route('admin.applications.index', ['registration_type' => 'group']));
        $groupResponse->assertStatus(200);
        $groupResponse->assertSee('Tim Filter Khusus');
        $groupResponse->assertDontSee('Budi Pendaftar Individu');

        // Filter individual only
        $indivResponse = $this->actingAs($this->admin)->get(route('admin.applications.index', ['registration_type' => 'individual']));
        $indivResponse->assertStatus(200);
        $indivResponse->assertSee('Budi Pendaftar Individu');
        $indivResponse->assertDontSee('Tim Filter Khusus');
    }

    public function test_student_dashboard_displays_group_panel_when_enrolled_in_group(): void
    {
        $group = InternshipGroup::create([
            'code' => InternshipGroup::generateUniqueCode(),
            'name' => 'Tim Kolaborasi Digital',
            'leader_user_id' => $this->leader->id,
            'unit_id' => $this->unit->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(60)->toDateString(),
            'status' => 'accepted',
        ]);

        Application::create([
            'user_id' => $this->leader->id,
            'unit_id' => $this->unit->id,
            'start_date' => $group->start_date,
            'end_date' => $group->end_date,
            'group_id' => $group->id,
            'role_in_group' => 'leader',
            'status' => 'accepted',
        ]);

        $response = $this->actingAs($this->leader)->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Kelompok Magang');
        $response->assertSee('Tim Kolaborasi Digital');
        $response->assertSee('Ketua Kelompok');
    }

    public function test_admin_show_displays_group_panel_and_sibling_members(): void
    {
        $group = InternshipGroup::create([
            'code' => InternshipGroup::generateUniqueCode(),
            'name' => 'Tim Pengujian Admin',
            'leader_user_id' => $this->leader->id,
            'unit_id' => $this->unit->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(60)->toDateString(),
            'status' => 'pending',
        ]);

        $leaderApp = Application::create([
            'user_id' => $this->leader->id,
            'unit_id' => $this->unit->id,
            'start_date' => $group->start_date,
            'end_date' => $group->end_date,
            'group_id' => $group->id,
            'role_in_group' => 'leader',
            'status' => 'pending',
        ]);

        $memberApp = Application::create([
            'user_id' => $this->member->id,
            'unit_id' => $this->unit->id,
            'start_date' => $group->start_date,
            'end_date' => $group->end_date,
            'group_id' => $group->id,
            'role_in_group' => 'member',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.applications.show', $leaderApp->id));
        $response->assertStatus(200);
        $response->assertSee('Pengajuan Kelompok');
        $response->assertSee('Tim Pengujian Admin');
        $response->assertSee('Ketua Kelompok');
        $response->assertSee($this->member->name);
    }

    public function test_mentor_and_lecturer_student_detail_display_group_badges(): void
    {
        $group = InternshipGroup::create([
            'code' => InternshipGroup::generateUniqueCode(),
            'name' => 'Tim Riset Unggulan',
            'leader_user_id' => $this->leader->id,
            'unit_id' => $this->unit->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(60)->toDateString(),
            'status' => 'accepted',
        ]);

        $leaderApp = Application::create([
            'user_id' => $this->leader->id,
            'unit_id' => $this->unit->id,
            'start_date' => $group->start_date,
            'end_date' => $group->end_date,
            'group_id' => $group->id,
            'role_in_group' => 'leader',
            'status' => 'active',
        ]);

        $placement = \App\Models\Placement::create([
            'application_id' => $leaderApp->id,
            'mentor_id' => $this->mentor->id,
            'agency_profile_id' => $this->admin->agency_profile_id,
        ]);

        // Mentor student detail
        $mentorResponse = $this->actingAs($this->mentor)->get(route('mentor.students.show', $placement->id));
        $mentorResponse->assertStatus(200);
        $mentorResponse->assertSee('Tim Riset Unggulan');
        $mentorResponse->assertSee('Ketua');

        // Lecturer student detail
        $dosen = User::factory()->create([
            'role' => 'dosen',
            'university_id' => $this->university->id,
        ]);
        $placement->update(['academic_advisor_id' => $dosen->id]);

        $lecturerResponse = $this->actingAs($dosen)->get(route('lecturer.students.show', $placement->id));
        $lecturerResponse->assertStatus(200);
        $lecturerResponse->assertSee('Tim Riset Unggulan');
        $lecturerResponse->assertSee('Ketua');
    }

    public function test_admin_bulk_reject_syncs_rejection_to_all_group_members(): void
    {
        $group = InternshipGroup::create([
            'code' => InternshipGroup::generateUniqueCode(),
            'name' => 'Tim Bulk Test',
            'leader_user_id' => $this->leader->id,
            'unit_id' => $this->unit->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(60)->toDateString(),
            'status' => 'pending',
        ]);

        $leaderApp = Application::create([
            'user_id' => $this->leader->id,
            'unit_id' => $this->unit->id,
            'start_date' => $group->start_date,
            'end_date' => $group->end_date,
            'group_id' => $group->id,
            'role_in_group' => 'leader',
            'status' => 'pending',
        ]);

        $memberApp = Application::create([
            'user_id' => $this->member->id,
            'unit_id' => $this->unit->id,
            'start_date' => $group->start_date,
            'end_date' => $group->end_date,
            'group_id' => $group->id,
            'role_in_group' => 'member',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.applications.bulk_update'), [
            'bulk_action' => 'rejected',
            'application_ids' => [$leaderApp->id],
            'bulk_rejection_note' => 'Kuota unit bidang telah terisi penuh.',
        ]);

        $response->assertRedirect();

        $this->assertEquals('rejected', $group->fresh()->status);
        $this->assertEquals('rejected', $leaderApp->fresh()->statusValue());
        $this->assertEquals('rejected', $memberApp->fresh()->statusValue());
        $this->assertStringContainsString('Kuota unit bidang telah terisi penuh', (string) $leaderApp->fresh()->rejection_note);
        $this->assertStringContainsString('Kuota unit bidang telah terisi penuh', (string) $memberApp->fresh()->rejection_note);
    }

    public function test_supervisor_revise_group_application_syncs_pending_status_to_all_members(): void
    {
        $supervisor = User::factory()->create([
            'role' => 'supervisor',
            'agency_profile_id' => $this->admin->agency_profile_id,
        ]);

        $group = InternshipGroup::create([
            'code' => InternshipGroup::generateUniqueCode(),
            'name' => 'Tim Revisi Atasan',
            'leader_user_id' => $this->leader->id,
            'unit_id' => $this->unit->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(60)->toDateString(),
            'status' => 'waiting_approval',
        ]);

        $leaderApp = Application::create([
            'user_id' => $this->leader->id,
            'unit_id' => $this->unit->id,
            'start_date' => $group->start_date,
            'end_date' => $group->end_date,
            'group_id' => $group->id,
            'role_in_group' => 'leader',
            'status' => 'waiting_approval',
        ]);

        $memberApp = Application::create([
            'user_id' => $this->member->id,
            'unit_id' => $this->unit->id,
            'start_date' => $group->start_date,
            'end_date' => $group->end_date,
            'group_id' => $group->id,
            'role_in_group' => 'member',
            'status' => 'waiting_approval',
        ]);

        $response = $this->actingAs($supervisor)->post(route('supervisor.applications.revise', $leaderApp->id), [
            'notes' => 'Harap sesuaikan proposal dan jadwal kegiatan magang.',
        ]);

        $response->assertRedirect(route('supervisor.applications.index'));

        $this->assertEquals('pending', $group->fresh()->status);
        $this->assertEquals('pending', $leaderApp->fresh()->statusValue());
        $this->assertEquals('pending', $memberApp->fresh()->statusValue());
        $this->assertStringContainsString('Harap sesuaikan proposal', (string) $leaderApp->fresh()->rejection_note);
        $this->assertStringContainsString('Harap sesuaikan proposal', (string) $memberApp->fresh()->rejection_note);
    }

    public function test_university_and_lecturer_csv_exports_include_group_details(): void
    {
        $group = InternshipGroup::create([
            'code' => InternshipGroup::generateUniqueCode(),
            'name' => 'Tim Ekspor Mahasiswa',
            'leader_user_id' => $this->leader->id,
            'unit_id' => $this->unit->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(60)->toDateString(),
            'status' => 'accepted',
        ]);

        $leaderApp = Application::create([
            'user_id' => $this->leader->id,
            'unit_id' => $this->unit->id,
            'start_date' => $group->start_date,
            'end_date' => $group->end_date,
            'group_id' => $group->id,
            'role_in_group' => 'leader',
            'status' => 'accepted',
        ]);

        $memberApp = Application::create([
            'user_id' => $this->member->id,
            'unit_id' => $this->unit->id,
            'start_date' => $group->start_date,
            'end_date' => $group->end_date,
            'group_id' => $group->id,
            'role_in_group' => 'member',
            'status' => 'accepted',
        ]);

        $dosen = User::factory()->create([
            'role' => 'dosen',
            'university_id' => $this->university->id,
        ]);

        $placement = \App\Models\Placement::create([
            'application_id' => $leaderApp->id,
            'mentor_id' => $this->mentor->id,
            'academic_advisor_id' => $dosen->id,
            'agency_profile_id' => $this->admin->agency_profile_id,
        ]);

        $univPic = User::factory()->create([
            'role' => 'universitas',
            'university_id' => $this->university->id,
            'university' => $this->university->name,
        ]);

        // Test University Export
        $univResponse = $this->actingAs($univPic)->get(route('university.students.export'));
        $univResponse->assertStatus(200);
        $univContent = $univResponse->streamedContent();
        $this->assertStringContainsString('Tipe Pendaftaran', $univContent);
        $this->assertStringContainsString('Kelompok Magang', $univContent);
        $this->assertStringContainsString('Tim Ekspor Mahasiswa', $univContent);
        $this->assertStringContainsString('Kelompok (Ketua)', $univContent);

        // Test Lecturer Export
        $lecturerResponse = $this->actingAs($dosen)->get(route('lecturer.monitoring.export'));
        $lecturerResponse->assertStatus(200);
        $lecturerContent = $lecturerResponse->streamedContent();
        $this->assertStringContainsString('Tipe Pendaftaran', $lecturerContent);
        $this->assertStringContainsString('Kelompok Magang', $lecturerContent);
        $this->assertStringContainsString('Tim Ekspor Mahasiswa', $lecturerContent);
    }

    public function test_admin_applications_index_displays_group_as_single_row_with_members_accordion(): void
    {
        $group = InternshipGroup::create([
            'code' => InternshipGroup::generateUniqueCode(),
            'name' => 'Tim Kolaborasi Surabaya',
            'leader_user_id' => $this->leader->id,
            'unit_id' => $this->unit->id,
            'start_date' => now()->addDays(10)->toDateString(),
            'end_date' => now()->addDays(70)->toDateString(),
            'status' => 'pending',
            'letter_token' => 'GRPVIEWTOKEN',
        ]);

        $leaderApp = Application::create([
            'user_id' => $this->leader->id,
            'unit_id' => $this->unit->id,
            'start_date' => $group->start_date,
            'end_date' => $group->end_date,
            'group_id' => $group->id,
            'role_in_group' => 'leader',
            'status' => 'pending',
        ]);

        $memberApp = Application::create([
            'user_id' => $this->member->id,
            'unit_id' => $this->unit->id,
            'start_date' => $group->start_date,
            'end_date' => $group->end_date,
            'group_id' => $group->id,
            'role_in_group' => 'member',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.applications.index'));

        $response->assertOk();
        $response->assertSee('Jalur Kelompok');
        $response->assertSee('Tim Kolaborasi Surabaya');
        $response->assertSee($group->code);
        $response->assertSee('Daftar Mahasiswa Anggota: Tim Kolaborasi Surabaya');
        $response->assertSee($this->leader->name);
        $response->assertSee($this->member->name);

        // Aksi Kelompok sekarang dipusatkan di halaman Detail sesuai permintaan user
        $detailResponse = $this->actingAs($this->admin)->get(route('admin.applications.show', $leaderApp->id));
        $detailResponse->assertOk();
        $detailResponse->assertSee('Aksi Serentak Kelompok');
    }

    public function test_admin_can_update_entire_group_status_serentak(): void
    {
        $group = InternshipGroup::create([
            'code' => InternshipGroup::generateUniqueCode(),
            'name' => 'Tim Terpadu',
            'leader_user_id' => $this->leader->id,
            'unit_id' => $this->unit->id,
            'start_date' => now()->addDays(10)->toDateString(),
            'end_date' => now()->addDays(70)->toDateString(),
            'status' => 'pending',
        ]);

        $leaderApp = Application::create([
            'user_id' => $this->leader->id,
            'unit_id' => $this->unit->id,
            'start_date' => $group->start_date,
            'end_date' => $group->end_date,
            'group_id' => $group->id,
            'role_in_group' => 'leader',
            'status' => 'pending',
        ]);

        $memberApp = Application::create([
            'user_id' => $this->member->id,
            'unit_id' => $this->unit->id,
            'start_date' => $group->start_date,
            'end_date' => $group->end_date,
            'group_id' => $group->id,
            'role_in_group' => 'member',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->put(route('admin.groups.update_status', $group->id), [
            'status' => 'accepted',
            'mentor_id' => $this->mentor->id,
        ]);

        $response->assertRedirect();
        $group->refresh();
        $leaderApp->refresh();
        $memberApp->refresh();

        $this->assertEquals('waiting_approval', $group->status);
        $this->assertEquals('waiting_approval', $leaderApp->statusValue());
        $this->assertEquals('waiting_approval', $memberApp->statusValue());
    }

    public function test_admin_can_update_individual_member_status_independently(): void
    {
        $group = InternshipGroup::create([
            'code' => InternshipGroup::generateUniqueCode(),
            'name' => 'Tim Parsial',
            'leader_user_id' => $this->leader->id,
            'unit_id' => $this->unit->id,
            'start_date' => now()->addDays(10)->toDateString(),
            'end_date' => now()->addDays(70)->toDateString(),
            'status' => 'pending',
        ]);

        $leaderApp = Application::create([
            'user_id' => $this->leader->id,
            'unit_id' => $this->unit->id,
            'start_date' => $group->start_date,
            'end_date' => $group->end_date,
            'group_id' => $group->id,
            'role_in_group' => 'leader',
            'status' => 'pending',
        ]);

        $memberApp = Application::create([
            'user_id' => $this->member->id,
            'unit_id' => $this->unit->id,
            'start_date' => $group->start_date,
            'end_date' => $group->end_date,
            'group_id' => $group->id,
            'role_in_group' => 'member',
            'status' => 'pending',
        ]);

        // Tolak hanya memberApp dengan sync_group = 0
        $response = $this->actingAs($this->admin)->put(route('admin.applications.updateStatus', $memberApp->id), [
            'status' => 'rejected',
            'rejection_note' => 'Dokumen transkrip tidak valid',
            'sync_group' => '0',
        ]);

        $response->assertRedirect();
        $leaderApp->refresh();
        $memberApp->refresh();

        $this->assertEquals('rejected', $memberApp->statusValue());
        // Ketua tetap pending, tidak ikut ditolak
        $this->assertEquals('pending', $leaderApp->statusValue());
    }

    public function test_group_member_limit_enforces_safe_maximum_of_100(): void
    {
        $this->assertEquals(100, Unit::MAX_GROUP_MEMBERS);
    }

    public function test_supervisor_approve_from_member_application_approves_all_group_members_together(): void
    {
        $supervisor = User::factory()->create([
            'role' => 'supervisor',
            'agency_profile_id' => $this->unit->agency_profile_id,
        ]);

        $group = InternshipGroup::create([
            'code' => InternshipGroup::generateUniqueCode(),
            'name' => 'Tim Riset Naskah Kuno',
            'leader_user_id' => $this->leader->id,
            'unit_id' => $this->unit->id,
            'start_date' => now()->addDays(5)->toDateString(),
            'end_date' => now()->addMonths(3)->toDateString(),
            'status' => 'waiting_approval',
        ]);

        $leaderApp = Application::create([
            'user_id' => $this->leader->id,
            'unit_id' => $this->unit->id,
            'start_date' => $group->start_date,
            'end_date' => $group->end_date,
            'group_id' => $group->id,
            'role_in_group' => 'leader',
            'status' => 'waiting_approval',
        ]);

        $memberApp = Application::create([
            'user_id' => $this->member->id,
            'unit_id' => $this->unit->id,
            'start_date' => $group->start_date,
            'end_date' => $group->end_date,
            'group_id' => $group->id,
            'role_in_group' => 'member',
            'status' => 'waiting_approval',
        ]);

        // Supervisor approves directly through memberApp (not leader)
        $response = $this->actingAs($supervisor)->post(route('supervisor.applications.approve', $memberApp->id));
        $response->assertRedirect(route('supervisor.applications.index'));
        $response->assertSessionHas('success');

        $group->refresh();
        $leaderApp->refresh();
        $memberApp->refresh();

        // Both members and group are now approved
        $this->assertEquals('accepted', $group->status);
        $this->assertEquals('accepted', $leaderApp->statusValue());
        $this->assertEquals('accepted', $memberApp->statusValue());

        // Both have placements created
        $this->assertDatabaseHas('placements', ['application_id' => $leaderApp->id]);
        $this->assertDatabaseHas('placements', ['application_id' => $memberApp->id]);

        // Check menu names in navbar
        $dashboardResponse = $this->actingAs($supervisor)->get(route('supervisor.dashboard'));
        $dashboardResponse->assertOk();
        $dashboardResponse->assertSee('Pengajuan');
        $dashboardResponse->assertSee('Sertifikat');
        $dashboardResponse->assertDontSee('Persetujuan Pengajuan');
        $dashboardResponse->assertDontSee('Pengesahan Sertifikat');
    }
}

