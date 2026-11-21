<?php

namespace Tests\Feature;

use App\Enums\AccountStatus;
use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\Placement;
use App\Models\StudentProfile;
use App\Models\Unit;
use App\Models\University;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\PlacementAssignmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Pembimbing (dosen/guru) boleh mendaftar sendiri, tetapi baru bisa dipilih atau ditugaskan
 * membimbing setelah disetujui Admin Kampus/Sekolah atau Super Admin (LRN-156).
 */
class AdvisorSelfRegistrationTest extends TestCase
{
    use RefreshDatabase;

    private University $unesa;

    private University $its;

    private Unit $unit;

    private User $mentor;

    private User $student;

    protected function setUp(): void
    {
        parent::setUp();

        $agency = AgencyProfile::create(['agency_name' => 'Dinas A']);
        $this->unit = Unit::create(['agency_profile_id' => $agency->id, 'name' => 'Bidang A', 'quota' => 10]);
        $this->unesa = University::create(['name' => 'Universitas Negeri Surabaya', 'code' => 'UNESA', 'acronym' => 'UNESA']);
        $this->its = University::create(['name' => 'Institut Teknologi Sepuluh Nopember', 'code' => 'ITS', 'acronym' => 'ITS']);
        $this->mentor = User::factory()->create(['role' => 'mentor', 'agency_profile_id' => $agency->id]);
        $this->student = User::factory()->create(['role' => 'mahasiswa', 'university_id' => $this->unesa->id]);
        StudentProfile::create(['user_id' => $this->student->id, 'nim' => '1', 'universitas' => $this->unesa->name, 'university_id' => $this->unesa->id, 'jurusan' => 'TI', 'phone' => '08']);
    }

    private function registerAdvisor(string $institution, string $email = 'guru.baru@example.test'): User
    {
        $this->post('/register', [
            'register_as' => 'advisor',
            'name' => 'Budi Santoso, S.Pd.',
            'email' => $email,
            'university' => $institution,
            'password' => 'RahasiaGuru123',
            'password_confirmation' => 'RahasiaGuru123',
        ])->assertRedirect(route('dashboard'));
        auth()->logout();

        return User::where('email', $email)->firstOrFail();
    }

    private function activeApplication(): Placement
    {
        $application = Application::create([
            'user_id' => $this->student->id,
            'unit_id' => $this->unit->id,
            'status' => 'active',
            'start_date' => Carbon::now()->subDays(10)->toDateString(),
            'end_date' => Carbon::now()->addDays(30)->toDateString(),
        ]);

        return Placement::create(['application_id' => $application->id, 'mentor_id' => $this->mentor->id]);
    }

    public function test_advisor_registration_is_pending_and_reuses_known_institution(): void
    {
        $advisor = $this->registerAdvisor('unesa');

        $this->assertSame('dosen', $advisor->role);
        $this->assertSame(AccountStatus::PENDING->value, $advisor->status);
        $this->assertTrue($advisor->isPendingApproval());
        $this->assertSame($this->unesa->id, (int) $advisor->university_id);
        $this->assertSame('Universitas Negeri Surabaya', $advisor->university);
        $this->assertSame(2, University::count(), 'Akronim yang dikenal tidak boleh membuat kampus baru.');
    }

    public function test_unknown_institution_is_created_unverified(): void
    {
        $advisor = $this->registerAdvisor('SMK Negeri 99 Surabaya');

        $school = University::where('name', 'SMK Negeri 99 Surabaya')->firstOrFail();
        $this->assertFalse((bool) $school->is_verified);
        $this->assertSame($school->id, (int) $advisor->university_id);
    }

    public function test_pending_advisor_can_log_in_and_sees_waiting_notice(): void
    {
        $this->post('/register', [
            'register_as' => 'advisor',
            'name' => 'Budi Santoso, S.Pd.',
            'email' => 'guru.baru@example.test',
            'university' => 'Universitas Negeri Surabaya',
            'password' => 'RahasiaGuru123',
            'password_confirmation' => 'RahasiaGuru123',
        ]);

        $this->get(route('dashboard'))->assertRedirect(route('lecturer.dashboard'));
        $this->get(route('lecturer.dashboard'))
            ->assertOk()
            ->assertSee('Akun Anda menunggu persetujuan');
    }

    public function test_student_registration_is_unchanged(): void
    {
        $this->post('/register', [
            'name' => 'Siswa Baru',
            'email' => 'siswa.baru@example.test',
            'password' => 'RahasiaSiswa123',
            'password_confirmation' => 'RahasiaSiswa123',
        ])->assertRedirect(route('student.profile.edit'));

        $user = User::where('email', 'siswa.baru@example.test')->firstOrFail();
        $this->assertSame('mahasiswa', $user->role);
        $this->assertFalse($user->isPendingApproval());
    }

    public function test_invalid_role_choice_and_missing_institution_are_rejected(): void
    {
        $this->post('/register', [
            'register_as' => 'super_admin',
            'name' => 'Penyusup',
            'email' => 'penyusup@example.test',
            'password' => 'RahasiaPenyusup123',
            'password_confirmation' => 'RahasiaPenyusup123',
        ])->assertSessionHasErrors('register_as');

        $this->post('/register', [
            'register_as' => 'advisor',
            'name' => 'Tanpa Institusi',
            'email' => 'tanpa.institusi@example.test',
            'password' => 'RahasiaGuru123',
            'password_confirmation' => 'RahasiaGuru123',
        ])->assertSessionHasErrors('university');

        $this->assertDatabaseMissing('users', ['email' => 'penyusup@example.test']);
        $this->assertDatabaseMissing('users', ['email' => 'tanpa.institusi@example.test']);
    }

    public function test_personal_email_is_rejected_but_campus_subdomain_is_accepted(): void
    {
        foreach (['siswa@gmail.com', 'siswa@yahoo.co.id', 'siswa@outlook.com'] as $email) {
            $this->post('/register', [
                'name' => 'Email Pribadi',
                'email' => $email,
                'password' => 'RahasiaSiswa123',
                'password_confirmation' => 'RahasiaSiswa123',
            ])->assertSessionHasErrors('email');
            $this->assertDatabaseMissing('users', ['email' => $email]);
        }

        $this->post('/register', [
            'name' => 'Mahasiswa UGM',
            'email' => 'mahasiswa@mail.ugm.ac.id',
            'password' => 'RahasiaSiswa123',
            'password_confirmation' => 'RahasiaSiswa123',
        ])->assertRedirect(route('student.profile.edit'));
        $this->assertDatabaseHas('users', ['email' => 'mahasiswa@mail.ugm.ac.id', 'role' => 'mahasiswa']);
    }

    public function test_register_page_escapes_role_choice_for_javascript(): void
    {
        $this->get('/register')->assertOk()->assertSee("roleType: 'student'", false);
    }

    public function test_student_cannot_pick_pending_advisor_by_list_or_by_email(): void
    {
        $placement = $this->activeApplication();
        $fake = $this->registerAdvisor('Universitas Negeri Surabaya', 'palsu@example.test');

        $this->assertFalse(User::assignableAdvisors()->whereKey($fake->id)->exists());

        $this->actingAs($this->student)
            ->post(route('student.select_advisor'), ['academic_advisor_id' => $fake->id])
            ->assertSessionHas('error');
        $this->assertNull($placement->fresh()->academic_advisor_id);

        $this->actingAs($this->student)
            ->post(route('student.create_advisor'), ['name' => 'Siapa Saja', 'email' => 'palsu@example.test'])
            ->assertSessionHas('error');
        $this->assertNull($placement->fresh()->academic_advisor_id);
    }

    public function test_staff_assignment_refuses_pending_advisor(): void
    {
        $placement = $this->activeApplication();
        $fake = $this->registerAdvisor('Universitas Negeri Surabaya', 'palsu@example.test');
        $campusAdmin = User::factory()->create(['role' => 'universitas', 'university_id' => $this->unesa->id]);

        $this->actingAs($campusAdmin)
            ->post(route('university.students.assign_advisor', $placement->application_id), ['academic_advisor_id' => $fake->id])
            ->assertSessionHas('error');
        $this->assertNull($placement->fresh()->academic_advisor_id);

        $this->expectException(ValidationException::class);
        app(PlacementAssignmentService::class)->resolveAdvisor($placement->application, $fake->id);
    }

    public function test_campus_admin_cannot_assign_advisor_to_rejected_application(): void
    {
        $advisor = User::factory()->create(['role' => 'dosen', 'university_id' => $this->unesa->id, 'status' => AccountStatus::ACTIVE->value]);
        $campusAdmin = User::factory()->create(['role' => 'universitas', 'university_id' => $this->unesa->id]);
        $rejected = Application::create([
            'user_id' => $this->student->id,
            'unit_id' => $this->unit->id,
            'status' => 'rejected',
            'start_date' => Carbon::now()->addDays(5)->toDateString(),
            'end_date' => Carbon::now()->addDays(40)->toDateString(),
        ]);

        $this->actingAs($campusAdmin)
            ->post(route('university.students.assign_advisor', $rejected->id), ['academic_advisor_id' => $advisor->id])
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('placements', ['application_id' => $rejected->id]);

        // Tombol penugasan tidak ditampilkan untuk pengajuan yang ditolak
        $this->actingAs($campusAdmin)->get(route('university.dashboard'))
            ->assertOk()
            ->assertDontSee("appId: '{$rejected->id}'", false);
    }

    public function test_campus_admin_of_same_institution_approves_then_student_can_pick(): void
    {
        $placement = $this->activeApplication();
        $advisor = $this->registerAdvisor('Universitas Negeri Surabaya');
        $campusAdmin = User::factory()->create(['role' => 'universitas', 'university_id' => $this->unesa->id]);

        $this->actingAs($campusAdmin)->get(route('university.lecturers.index'))
            ->assertOk()
            ->assertSee('1 Pembimbing Menunggu Persetujuan Anda')
            ->assertSee($advisor->email);

        $ids = array_column(NotificationService::getNotificationsForUser($campusAdmin), 'id');
        $this->assertContains('univ_advisors_pending_approval', $ids);

        // Dasbor Admin Kampus/Sekolah ikut mengingatkan dan menautkan ke halaman persetujuan
        $this->actingAs($campusAdmin)->get(route('university.dashboard'))
            ->assertOk()
            ->assertSee('1 pembimbing menunggu persetujuan Anda')
            ->assertSee(route('university.lecturers.index'), false);

        $this->actingAs($campusAdmin)->post(route('university.lecturers.approve', $advisor->id))
            ->assertRedirect(route('university.lecturers.index'))
            ->assertSessionHas('success');

        $this->actingAs($campusAdmin)->get(route('university.dashboard'))
            ->assertOk()
            ->assertDontSee('pembimbing menunggu persetujuan Anda');
        $this->assertSame(AccountStatus::ACTIVE->value, $advisor->fresh()->status);
        $this->assertDatabaseHas('system_notifications', ['user_id' => $advisor->id, 'title' => 'Akun Pembimbing Anda Sudah Disetujui']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'ADVISOR_ACCOUNT_APPROVE', 'target_id' => $advisor->id]);

        $this->actingAs($this->student)
            ->post(route('student.select_advisor'), ['academic_advisor_id' => $advisor->id])
            ->assertSessionHas('success');
        $this->assertSame($advisor->id, (int) $placement->fresh()->academic_advisor_id);
    }

    public function test_campus_admin_of_other_institution_and_agency_admin_cannot_approve(): void
    {
        $advisor = $this->registerAdvisor('Universitas Negeri Surabaya');
        $itsAdmin = User::factory()->create(['role' => 'universitas', 'university_id' => $this->its->id]);
        $agencyAdmin = User::factory()->create(['role' => 'admin', 'agency_profile_id' => $this->unit->agency_profile_id]);

        $this->actingAs($itsAdmin)->post(route('university.lecturers.approve', $advisor->id))->assertForbidden();
        $this->actingAs($agencyAdmin)->post(route('admin.users.approve_advisor', $advisor->id))->assertForbidden();

        $this->assertTrue($advisor->fresh()->isPendingApproval());
    }

    public function test_super_admin_handles_institutions_without_admin(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $teacher = $this->registerAdvisor('SMK Negeri 99 Surabaya', 'guru.smk@example.test');
        $lecturer = $this->registerAdvisor('Universitas Negeri Surabaya', 'dosen.unesa@example.test');
        User::factory()->create(['role' => 'universitas', 'university_id' => $this->unesa->id]);

        $items = collect(NotificationService::getNotificationsForUser($superAdmin))->keyBy('id');
        $this->assertTrue($items->has('advisors_pending_approval'));
        $this->assertStringStartsWith('1 Pembimbing', $items['advisors_pending_approval']['title'], 'Hanya institusi tanpa Admin Kampus/Sekolah yang dihitung untuk Super Admin.');

        $this->actingAs($superAdmin)
            ->get(route('admin.users.index', ['role' => 'dosen', 'account_status' => 'pending']))
            ->assertOk()
            ->assertSee('guru.smk@example.test')
            ->assertSee('Setujui Pembimbing');

        $this->actingAs($superAdmin)->post(route('admin.users.approve_advisor', $teacher->id))->assertSessionHas('success');
        $this->assertFalse($teacher->fresh()->isPendingApproval());
        $this->assertTrue($lecturer->fresh()->isPendingApproval());
    }

    public function test_pembimbing_role_alias_is_labelled_as_field_mentor(): void
    {
        $this->assertSame('Mentor Lapangan', User::factory()->make(['role' => 'pembimbing'])->roleLabel());
        $this->assertSame('Pembimbing', User::factory()->make(['role' => 'dosen'])->roleLabel());
    }
}
