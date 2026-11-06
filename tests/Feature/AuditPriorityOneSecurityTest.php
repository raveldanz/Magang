<?php

namespace Tests\Feature;

use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\Placement;
use App\Models\StudentProfile;
use App\Models\Unit;
use App\Models\University;
use App\Models\User;
use App\Support\SafeUpload;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Regresi untuk temuan audit keamanan Prioritas 1 (security-audit run-1).
 */
class AuditPriorityOneSecurityTest extends TestCase
{
    use RefreshDatabase;

    private AgencyProfile $agencyA;

    private AgencyProfile $agencyB;

    private Unit $unitA;

    private Unit $unitB;

    private University $unesa;

    private User $superAdmin;

    private User $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agencyA = AgencyProfile::create(['agency_name' => 'Dinas A']);
        $this->agencyB = AgencyProfile::create(['agency_name' => 'Dinas B']);
        $this->unitA = Unit::create(['agency_profile_id' => $this->agencyA->id, 'name' => 'Bidang A', 'quota' => 10]);
        $this->unitB = Unit::create(['agency_profile_id' => $this->agencyB->id, 'name' => 'Bidang B', 'quota' => 10]);
        $this->unesa = University::create(['name' => 'Universitas Negeri Surabaya', 'code' => 'UNESA', 'acronym' => 'UNESA']);

        $this->superAdmin = User::factory()->create(['role' => 'super_admin', 'password' => 'Rahasia-Super-1']);
        $this->student = User::factory()->create(['role' => 'mahasiswa', 'university_id' => $this->unesa->id, 'password' => 'Rahasia-Mhs-1']);
        StudentProfile::create(['user_id' => $this->student->id, 'nim' => '1', 'universitas' => $this->unesa->name, 'university_id' => $this->unesa->id, 'jurusan' => 'TI', 'phone' => '08']);
    }

    private function application(User $student, Unit $unit, string $status = 'active'): Application
    {
        $application = Application::create([
            'user_id' => $student->id,
            'unit_id' => $unit->id,
            'status' => $status,
            'start_date' => Carbon::now()->subDays(20)->toDateString(),
            'end_date' => Carbon::now()->addDays(20)->toDateString(),
        ]);
        Placement::create(['application_id' => $application->id]);

        return $application->fresh('placement');
    }

    // ---------------------------------------------------------------
    // magang.auth.login-request.hardcoded-super-admin-password-bypass
    // ---------------------------------------------------------------

    public static function hardcodedAdminCredentials(): array
    {
        $pairs = [];
        foreach (['admin@gmail.com', 'admin@surabaya.go.id'] as $email) {
            foreach (['admin', 'password', 'admin123'] as $password) {
                $pairs["{$email} / {$password}"] = [$email, $password];
            }
        }

        return $pairs;
    }

    #[DataProvider('hardcodedAdminCredentials')]
    public function test_seeded_admin_email_cannot_log_in_with_hardcoded_password(string $email, string $password): void
    {
        User::factory()->create([
            'email' => $email,
            'role' => 'super_admin',
            'status' => 'active',
            'password' => 'Unrelated-Strong-Pass-91!',
        ]);

        $this->post('/login', ['email' => $email, 'password' => $password])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    // ---------------------------------------------------------------
    // magang.student.self-created-advisor-account-known-password
    // ---------------------------------------------------------------

    public function test_student_created_advisor_is_inactive_and_student_never_learns_a_password(): void
    {
        $this->application($this->student, $this->unitA);

        $this->actingAs($this->student)
            ->post(route('student.create_advisor'), ['name' => 'Dr Baru', 'email' => 'dr.baru@unesa.ac.id'])
            ->assertSessionMissing('new_advisor_credential.password');

        $dosen = User::where('email', 'dr.baru@unesa.ac.id')->firstOrFail();
        $this->assertSame('inactive', $dosen->status instanceof \BackedEnum ? $dosen->status->value : $dosen->status);
        $this->assertFalse(Hash::check(User::DEFAULT_PASSWORD, $dosen->password));

        auth()->logout();
        $this->post('/login', ['email' => 'dr.baru@unesa.ac.id', 'password' => User::DEFAULT_PASSWORD]);
        $this->assertGuest();
    }

    public function test_student_dashboard_renders_pending_advisor_modal_without_password(): void
    {
        $this->application($this->student, $this->unitA);

        $this->actingAs($this->student)
            ->followingRedirects()
            ->post(route('student.create_advisor'), ['name' => 'Dr Baru', 'email' => 'dr.render@unesa.ac.id'])
            ->assertOk()
            ->assertSee('Menunggu Aktivasi')
            ->assertDontSee('Password Default');
    }

    public function test_temporary_credentials_panel_renders_once_after_reset(): void
    {
        $mentor = User::factory()->create(['role' => 'mentor', 'agency_profile_id' => $this->agencyA->id, 'password' => 'Lama-Mentor-1']);

        $this->actingAs($this->superAdmin)
            ->from(route('admin.users.index'))
            ->post(route('admin.users.reset_password', $mentor->id))
            ->assertRedirect(route('admin.users.index'));

        $plain = collect(session('temporary_credentials'))->firstWhere('email', $mentor->email)['password'] ?? null;
        $this->assertNotNull($plain);

        $this->actingAs($this->superAdmin)->get(route('admin.users.index'))
            ->assertOk()->assertSee('Password sementara')->assertSee($plain);

        $this->actingAs($this->superAdmin)->get(route('admin.users.index'))->assertDontSee($plain);
    }

    // ---------------------------------------------------------------
    // magang.auth.default-password-not-enforced
    // ---------------------------------------------------------------

    private function assertTemporaryPasswordIssued(User $target): void
    {
        $target->refresh();
        $this->assertFalse(Hash::check(User::DEFAULT_PASSWORD, $target->password), 'Password tidak boleh direset ke nilai bawaan yang diketahui umum.');
        $this->assertTrue((bool) $target->must_change_password, 'Akun dengan password sementara wajib ditandai harus ganti password.');

        $credentials = collect(session('temporary_credentials', []));
        $entry = $credentials->firstWhere('email', $target->email);
        $this->assertNotNull($entry, 'Password sementara harus ditampilkan sekali kepada staf yang mereset.');
        $this->assertTrue(Hash::check($entry['password'], $target->password));
    }

    public function test_admin_user_reset_issues_random_temporary_password(): void
    {
        $mentor = User::factory()->create(['role' => 'mentor', 'agency_profile_id' => $this->agencyA->id, 'password' => 'Lama-Mentor-1']);

        $this->actingAs($this->superAdmin)->post(route('admin.users.reset_password', $mentor->id))->assertRedirect();

        $this->assertTemporaryPasswordIssued($mentor);
    }

    public function test_bulk_reset_issues_distinct_temporary_passwords(): void
    {
        $a = User::factory()->create(['role' => 'mentor', 'agency_profile_id' => $this->agencyA->id, 'password' => 'Lama-1']);
        $b = User::factory()->create(['role' => 'mentor', 'agency_profile_id' => $this->agencyA->id, 'password' => 'Lama-2']);

        $this->actingAs($this->superAdmin)->post(route('admin.users.bulk_reset_password'), ['user_ids' => [$a->id, $b->id]]);

        $this->assertTemporaryPasswordIssued($a);
        $this->assertTemporaryPasswordIssued($b);
        $this->assertNotSame($a->fresh()->password, $b->fresh()->password);
    }

    public function test_mentor_reset_issues_random_temporary_password(): void
    {
        $mentor = User::factory()->create(['role' => 'mentor', 'agency_profile_id' => $this->agencyA->id, 'password' => 'Lama-Mentor-1']);

        $this->actingAs($this->superAdmin)->post(route('admin.mentors.reset_password', $mentor->id))->assertRedirect();

        $this->assertTemporaryPasswordIssued($mentor);
    }

    public function test_university_lecturer_reset_issues_random_temporary_password(): void
    {
        $campusAdmin = User::factory()->create(['role' => 'universitas', 'university_id' => $this->unesa->id, 'university' => $this->unesa->name]);
        $dosen = User::factory()->create(['role' => 'dosen', 'university_id' => $this->unesa->id, 'university' => $this->unesa->name, 'password' => 'Lama-Dosen-1']);

        $this->actingAs($campusAdmin)->post(route('university.lecturers.reset_password', $dosen->id))->assertRedirect();

        $this->assertTemporaryPasswordIssued($dosen);
    }

    public function test_flagged_account_is_forced_to_change_password_before_using_the_app(): void
    {
        config(['auth.force_password_change' => true]);
        $mentor = User::factory()->create(['role' => 'mentor', 'agency_profile_id' => $this->agencyA->id, 'password' => User::DEFAULT_PASSWORD]);
        $this->assertTrue((bool) $mentor->fresh()->must_change_password);

        $this->actingAs($mentor)->get('/dashboard')->assertRedirect(route('profile.edit'));
        $this->actingAs($mentor)->get(route('profile.edit'))->assertOk();

        $this->actingAs($mentor)->put(route('password.update'), [
            'current_password' => User::DEFAULT_PASSWORD,
            'password' => 'Password-Baru-Kuat-77',
            'password_confirmation' => 'Password-Baru-Kuat-77',
        ])->assertSessionHasNoErrors();

        $this->assertFalse((bool) $mentor->fresh()->must_change_password);
        $after = $this->actingAs($mentor->fresh())->get('/dashboard');
        $this->assertNotSame(route('profile.edit'), $after->headers->get('Location'));
    }

    // ---------------------------------------------------------------
    // magang.admin-users.agency-admin-manages-any-student
    // ---------------------------------------------------------------

    public function test_agency_admin_cannot_manage_student_without_application_in_their_agency(): void
    {
        $adminA = User::factory()->create(['role' => 'admin', 'agency_profile_id' => $this->agencyA->id]);
        $outsider = User::factory()->create(['role' => 'mahasiswa', 'password' => 'Rahasia-Luar-1']);
        $this->application($outsider, $this->unitB);

        $this->actingAs($adminA)->post(route('admin.users.reset_password', $outsider->id))->assertForbidden();
        $this->actingAs($adminA)->put(route('admin.users.update', $outsider->id), [
            'name' => 'X', 'email' => 'diambil@example.test', 'role' => 'admin', 'password' => 'Penyerang-123',
        ])->assertForbidden();
        $this->actingAs($adminA)->delete(route('admin.users.destroy', $outsider->id))->assertForbidden();

        $outsider->refresh();
        $this->assertTrue(Hash::check('Rahasia-Luar-1', $outsider->password));
        $this->assertSame('mahasiswa', $outsider->role);
    }

    public function test_agency_admin_can_still_manage_student_applying_to_their_agency(): void
    {
        $adminA = User::factory()->create(['role' => 'admin', 'agency_profile_id' => $this->agencyA->id]);
        $this->application($this->student, $this->unitA);

        $this->actingAs($adminA)->post(route('admin.users.reset_password', $this->student->id))->assertRedirect();
        $this->assertTemporaryPasswordIssued($this->student);
    }

    public function test_bidang_admin_cannot_manage_student_of_another_unit_in_same_agency(): void
    {
        $unitA2 = Unit::create(['agency_profile_id' => $this->agencyA->id, 'name' => 'Bidang A2', 'quota' => 10]);
        $bidangAdmin = User::factory()->create(['role' => 'admin', 'agency_profile_id' => $this->agencyA->id, 'unit_id' => $unitA2->id]);
        $this->application($this->student, $this->unitA);

        $this->actingAs($bidangAdmin)->post(route('admin.users.reset_password', $this->student->id))->assertForbidden();
    }

    // ---------------------------------------------------------------
    // magang.private-file.staff-reads-any-student-document
    // ---------------------------------------------------------------

    private function studentWithKtm(): Application
    {
        Storage::fake('local');
        Storage::disk('local')->put('documents/ktm_test.pdf', '%PDF-1.4 dummy KTM');
        $this->student->studentProfile()->update(['id_card_path' => 'documents/ktm_test.pdf']);

        return $this->application($this->student, $this->unitA);
    }

    private function ktmUrl(): string
    {
        return route('student.document', ['userId' => $this->student->id, 'type' => 'ktm']);
    }

    public function test_unrelated_staff_cannot_download_student_ktm(): void
    {
        $this->studentWithKtm();
        $otherUniversity = University::create(['name' => 'Institut Teknologi Sepuluh Nopember', 'code' => 'ITS', 'acronym' => 'ITS']);

        $viewers = [
            'admin kampus lain' => User::factory()->create(['role' => 'universitas', 'university_id' => $otherUniversity->id]),
            'dosen bukan DPL' => User::factory()->create(['role' => 'dosen', 'university_id' => $this->unesa->id]),
            'mentor instansi lain' => User::factory()->create(['role' => 'mentor', 'agency_profile_id' => $this->agencyB->id]),
            'admin instansi lain' => User::factory()->create(['role' => 'admin', 'agency_profile_id' => $this->agencyB->id]),
            'supervisor instansi lain' => User::factory()->create(['role' => 'supervisor', 'agency_profile_id' => $this->agencyB->id]),
        ];

        foreach ($viewers as $label => $viewer) {
            $this->actingAs($viewer)->get($this->ktmUrl())->assertForbidden();
        }
    }

    public function test_related_parties_can_still_download_student_ktm(): void
    {
        $application = $this->studentWithKtm();
        $advisor = User::factory()->create(['role' => 'dosen', 'university_id' => $this->unesa->id]);
        $application->placement->update(['academic_advisor_id' => $advisor->id]);

        $viewers = [
            'pemilik' => $this->student,
            'super admin' => $this->superAdmin,
            'admin instansi tujuan' => User::factory()->create(['role' => 'admin', 'agency_profile_id' => $this->agencyA->id]),
            'mentor instansi tujuan' => User::factory()->create(['role' => 'mentor', 'agency_profile_id' => $this->agencyA->id]),
            'admin kampus asal' => User::factory()->create(['role' => 'universitas', 'university_id' => $this->unesa->id]),
            'DPL mahasiswa' => $advisor,
        ];

        foreach ($viewers as $label => $viewer) {
            $this->actingAs($viewer)->get($this->ktmUrl())->assertOk();
        }
    }

    // ---------------------------------------------------------------
    // magang.upload.client-extension-public-webroot-html
    // ---------------------------------------------------------------

    public function test_university_logo_is_stored_with_content_derived_extension_not_client_extension(): void
    {
        $campusAdmin = User::factory()->create(['role' => 'universitas', 'university_id' => $this->unesa->id, 'university' => $this->unesa->name]);
        $png = UploadedFile::fake()->image('asli.png', 10, 10);
        $polyglot = new UploadedFile($png->getPathname(), 'x.html', 'text/html', null, true);

        $before = glob(public_path('images/logos/*.html')) ?: [];

        $this->actingAs($campusAdmin)->post(route('university.profile.update'), [
            'name' => $this->unesa->name, 'code' => 'TSTAUDIT', 'evaluation_scheme' => 'mentor_only', 'logo' => $polyglot,
        ])->assertSessionHasNoErrors();

        $logo = (string) $this->unesa->fresh()->logo;
        foreach (glob(public_path('images/logos/tstaudit_*')) ?: [] as $created) {
            @unlink($created);
        }

        $this->assertSame([], array_values(array_diff(glob(public_path('images/logos/*.html')) ?: [], $before)), 'Tidak boleh ada file .html baru di web root.');
        $this->assertStringEndsWith('.png', $logo);
    }

    public function test_safe_upload_extension_ignores_client_extension(): void
    {
        $png = UploadedFile::fake()->image('asli.png', 10, 10);
        $polyglot = new UploadedFile($png->getPathname(), 'x.html', 'text/html', null, true);

        $this->assertSame('png', SafeUpload::extension($polyglot, ['jpg', 'jpeg', 'png', 'webp']));
        $this->assertSame('bin', SafeUpload::extension($polyglot, ['pdf'], 'bin'));
    }

    // ---------------------------------------------------------------
    // magang.seeders.unguarded-demo-superadmin-credentials
    // ---------------------------------------------------------------

    public function test_database_seeder_creates_no_demo_accounts_in_production(): void
    {
        $this->app['env'] = 'production';
        $usersBefore = User::count();

        $this->artisan('db:seed', ['--class' => DatabaseSeeder::class, '--force' => true])->assertSuccessful();

        $this->assertSame($usersBefore, User::count(), 'Seeder tidak boleh membuat akun demo ber-password tetap di production.');
        $this->assertFalse(User::whereIn('email', ['admin@gmail.com', 'admin@surabaya.go.id'])->exists());
    }

    public function test_create_super_admin_command_issues_temporary_password(): void
    {
        $this->artisan('app:create-super-admin', ['email' => 'it@dispusip.test', '--name' => 'Admin IT'])
            ->expectsOutputToContain('Password sementara')
            ->assertSuccessful();

        $admin = User::where('email', 'it@dispusip.test')->firstOrFail();
        $this->assertSame('super_admin', $admin->role);
        $this->assertTrue((bool) $admin->must_change_password);
        $this->assertFalse(Hash::check(User::DEFAULT_PASSWORD, $admin->password));
    }

    public function test_api_login_refuses_token_while_password_change_is_pending(): void
    {
        config(['auth.force_password_change' => true]);
        User::factory()->create(['role' => 'mentor', 'email' => 'm@example.test', 'password' => User::DEFAULT_PASSWORD]);

        $this->postJson('/api/login', ['email' => 'm@example.test', 'password' => User::DEFAULT_PASSWORD])
            ->assertStatus(403);
    }
}
