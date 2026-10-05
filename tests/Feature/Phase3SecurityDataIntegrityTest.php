<?php

namespace Tests\Feature;

use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\Evaluation;
use App\Models\FinalReport;
use App\Models\Logbook;
use App\Models\Placement;
use App\Models\StudentProfile;
use App\Models\SystemFeedback;
use App\Models\SystemNotification;
use App\Models\Unit;
use App\Models\University;
use App\Models\User;
use App\Services\UniversityResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Paket 3: pilihan DPL oleh mahasiswa, peringatan password bawaan, validasi mentor/DPL di form
 * status, berkas pribadi di disk privat, masa magang berakhir, dan pencocokan kampus tanpa LIKE.
 */
class Phase3SecurityDataIntegrityTest extends TestCase
{
    use RefreshDatabase;

    private AgencyProfile $agency;

    private Unit $unit;

    private University $unesa;

    private University $its;

    private User $admin;

    private User $mentor;

    private User $student;

    private User $dosenUnesa;

    private User $dosenIts;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = AgencyProfile::create(['agency_name' => 'Dinas A']);
        $this->unit = Unit::create(['agency_profile_id' => $this->agency->id, 'name' => 'Bidang A', 'quota' => 10]);
        $this->unesa = University::create(['name' => 'Universitas Negeri Surabaya', 'code' => 'UNESA', 'acronym' => 'UNESA']);
        $this->its = University::create(['name' => 'Institut Teknologi Sepuluh Nopember', 'code' => 'ITS', 'acronym' => 'ITS']);

        $this->admin = User::factory()->create(['role' => 'admin', 'agency_profile_id' => $this->agency->id, 'password' => 'RahasiaAdmin123']);
        $this->mentor = User::factory()->create(['role' => 'mentor', 'agency_profile_id' => $this->agency->id]);
        $this->student = User::factory()->create(['role' => 'mahasiswa', 'university_id' => $this->unesa->id, 'password' => 'RahasiaMhs123']);
        StudentProfile::create(['user_id' => $this->student->id, 'nim' => '1', 'universitas' => $this->unesa->name, 'university_id' => $this->unesa->id, 'jurusan' => 'TI', 'phone' => '08']);
        $this->dosenUnesa = User::factory()->create(['role' => 'dosen', 'university_id' => $this->unesa->id]);
        $this->dosenIts = User::factory()->create(['role' => 'dosen', 'university_id' => $this->its->id]);
    }

    private function application(string $status = 'active', array $placement = [], array $app = []): Application
    {
        $application = Application::create(array_merge([
            'user_id' => $this->student->id,
            'unit_id' => $this->unit->id,
            'status' => $status,
            'start_date' => Carbon::now()->subDays(20)->toDateString(),
            'end_date' => Carbon::now()->addDays(20)->toDateString(),
        ], $app));
        Placement::create(array_merge(['application_id' => $application->id, 'mentor_id' => $this->mentor->id], $placement));

        return $application->fresh('placement');
    }

    // ---- 2. Pemilihan DPL oleh mahasiswa --------------------------------------------------

    public function test_student_cannot_pick_dpl_from_another_university(): void
    {
        $app = $this->application();

        $this->actingAs($this->student)->post(route('student.select_advisor'), ['academic_advisor_id' => $this->dosenIts->id])
            ->assertSessionHas('error');
        $this->assertNull($app->placement->fresh()->academic_advisor_id);

        $this->actingAs($this->student)->post(route('student.select_advisor'), ['academic_advisor_id' => $this->dosenUnesa->id])
            ->assertSessionHas('success');
        $this->assertSame($this->dosenUnesa->id, (int) $app->placement->fresh()->academic_advisor_id);
    }

    public function test_student_cannot_change_dpl_after_grading_or_completion(): void
    {
        $app = $this->application('active', ['academic_advisor_id' => $this->dosenUnesa->id]);
        Evaluation::create(['placement_id' => $app->placement->id, 'nilai_akademik' => 85]);
        $other = User::factory()->create(['role' => 'dosen', 'university_id' => $this->unesa->id]);

        $this->actingAs($this->student)->post(route('student.select_advisor'), ['academic_advisor_id' => $other->id])
            ->assertSessionHas('error');
        $this->actingAs($this->student)->post(route('student.create_advisor'), ['name' => 'Dosen Baru', 'email' => 'baru@unesa.ac.id'])
            ->assertSessionHas('error');
        $this->assertSame($this->dosenUnesa->id, (int) $app->placement->fresh()->academic_advisor_id);
        $this->assertNull(User::where('email', 'baru@unesa.ac.id')->first());

        Application::whereKey($app->id)->update(['status' => 'completed']);
        Evaluation::where('placement_id', $app->placement->id)->delete();
        $this->actingAs($this->student)->post(route('student.select_advisor'), ['academic_advisor_id' => $other->id])
            ->assertSessionHas('error');
    }

    public function test_new_advisor_is_always_registered_to_students_university(): void
    {
        $this->application();

        $this->actingAs($this->student)->post(route('student.create_advisor'), [
            'name' => 'Dr. Baru', 'email' => 'dr.baru@kampus.ac.id', 'university_id' => $this->its->id,
        ]);

        $this->assertSame($this->unesa->id, (int) User::where('email', 'dr.baru@kampus.ac.id')->value('university_id'));

        // Email milik dosen kampus lain tidak boleh dipakai
        $this->actingAs($this->student)->post(route('student.create_advisor'), ['name' => 'X', 'email' => $this->dosenIts->email])
            ->assertSessionHas('error');
    }

    // ---- 3. Peringatan password bawaan -----------------------------------------------------

    public function test_default_password_shows_persistent_warning_until_changed(): void
    {
        $user = User::factory()->create(['role' => 'mentor', 'agency_profile_id' => $this->agency->id, 'password' => 'password']);
        $this->assertTrue($user->fresh()->must_change_password);

        $this->actingAs($user)->get(route('profile.edit'))->assertOk()->assertSee('Akun Anda masih memakai password bawaan');
        $this->actingAs($user)->get(route('mentor.dashboard'))->assertOk()->assertSee('Akun Anda masih memakai password bawaan');

        // Tidak boleh "mengganti" ke password bawaan lagi
        $this->actingAs($user)->from(route('profile.edit'))->put(route('password.update'), [
            'current_password' => 'password', 'password' => 'password', 'password_confirmation' => 'password',
        ])->assertSessionHasErrorsIn('updatePassword', 'password');

        $this->actingAs($user)->from(route('profile.edit'))->put(route('password.update'), [
            'current_password' => 'password', 'password' => 'PasswordBaru#2026', 'password_confirmation' => 'PasswordBaru#2026',
        ])->assertSessionHasNoErrors();

        $this->assertFalse($user->fresh()->must_change_password);
        $this->actingAs($user->fresh())->get(route('profile.edit'))->assertDontSee('Akun Anda masih memakai password bawaan');
    }

    public function test_admin_reset_to_default_password_flags_account(): void
    {
        $mentor = User::factory()->create(['role' => 'mentor', 'agency_profile_id' => $this->agency->id, 'password' => 'PunyaSendiri99']);
        $this->assertFalse($mentor->fresh()->must_change_password);

        $this->actingAs($this->admin)->post(route('admin.mentors.reset_password', $mentor->id));

        $this->assertTrue($mentor->fresh()->must_change_password);
        $this->assertTrue(Hash::check('password', $mentor->fresh()->password));
    }

    // ---- 4. Mentor & DPL di form ubah status ---------------------------------------------

    public function test_update_status_rejects_mentor_or_dpl_outside_scope(): void
    {
        $app = $this->application('verified', [], ['start_date' => now()->addDays(3)->toDateString(), 'end_date' => now()->addDays(40)->toDateString()]);
        $otherAgency = AgencyProfile::create(['agency_name' => 'Dinas B']);
        $foreignMentor = User::factory()->create(['role' => 'mentor', 'agency_profile_id' => $otherAgency->id]);

        $this->actingAs($this->admin)->put(route('admin.applications.updateStatus', $app->id), [
            'status' => 'accepted', 'mentor_id' => $foreignMentor->id,
        ])->assertSessionHasErrors('mentor_id');

        $this->actingAs($this->admin)->put(route('admin.applications.updateStatus', $app->id), [
            'status' => 'accepted', 'mentor_id' => $this->student->id, // bukan akun mentor
        ])->assertSessionHasErrors('mentor_id');

        $this->actingAs($this->admin)->put(route('admin.applications.updateStatus', $app->id), [
            'status' => 'accepted', 'academic_advisor_id' => $this->dosenIts->id,
        ])->assertSessionHasErrors('academic_advisor_id');

        $this->assertSame('verified', $app->fresh()->statusValue());

        $this->actingAs($this->admin)->put(route('admin.applications.updateStatus', $app->id), [
            'status' => 'accepted', 'mentor_id' => $this->mentor->id, 'academic_advisor_id' => $this->dosenUnesa->id,
        ])->assertSessionHasNoErrors();
        $this->assertSame('accepted', $app->fresh()->statusValue());
    }

    // ---- 5. Berkas pribadi di disk privat -------------------------------------------------

    public function test_logbook_attachment_is_private_and_authorized(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        $app = $this->application('active', ['academic_advisor_id' => $this->dosenUnesa->id]);

        $this->actingAs($this->student)->post(route('student.logbook.store'), [
            'date' => now()->toDateString(),
            'activity' => 'Menyusun dokumentasi sistem.',
            'attachment' => UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf'),
        ])->assertSessionHasNoErrors();

        $log = Logbook::first();
        Storage::disk('local')->assertExists($log->attachment);
        Storage::disk('public')->assertMissing($log->attachment);
        $url = route('logbooks.attachment', $log->id);
        $this->assertSame($url, $log->attachment_url);

        $otherStudent = User::factory()->create(['role' => 'mahasiswa']);
        $otherMentor = User::factory()->create(['role' => 'mentor', 'agency_profile_id' => AgencyProfile::create(['agency_name' => 'X'])->id]);

        $this->actingAs($this->student)->get($url)->assertOk();
        $this->actingAs($this->mentor)->get($url)->assertOk();
        $this->actingAs($this->admin)->get($url)->assertOk();
        $this->actingAs($this->dosenUnesa)->get($url)->assertOk();
        $this->actingAs($otherStudent)->get($url)->assertForbidden();
        $this->actingAs($otherMentor)->get($url)->assertForbidden();
        $this->actingAs($this->dosenIts)->get($url)->assertForbidden();
    }

    public function test_feedback_attachment_and_student_photo_are_private(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $this->actingAs($this->student)->post(route('feedbacks.store'), [
            'category' => 'error_bug', 'subject' => 'Error', 'message' => 'Halaman error saat unggah',
            'attachment' => UploadedFile::fake()->image('ss.png'),
        ]);
        $feedback = SystemFeedback::first();
        $this->assertNotNull($feedback);
        Storage::disk('local')->assertExists($feedback->attachment);
        Storage::disk('public')->assertMissing($feedback->attachment);

        $this->actingAs($this->student)->get(route('feedbacks.attachment', $feedback->id))->assertOk();
        $this->actingAs(User::factory()->create(['role' => 'mahasiswa']))->get(route('feedbacks.attachment', $feedback->id))->assertForbidden();

        // Foto profil
        $this->actingAs($this->student)->post(route('student.profile.update'), [
            'name' => 'Mhs', 'nim' => '1', 'university_id' => (string) $this->unesa->id, 'jurusan' => 'TI', 'phone' => '08',
            'photo' => UploadedFile::fake()->image('foto.jpg'),
        ])->assertSessionHasNoErrors();
        $photo = $this->student->studentProfile()->first()->photo;
        Storage::disk('local')->assertExists($photo);
        $this->actingAs($this->student)->get(route('student.photo', $this->student->id))->assertOk();
        $this->actingAs($this->mentor)->get(route('student.photo', $this->student->id))->assertOk();
        $this->actingAs(User::factory()->create(['role' => 'mahasiswa']))->get(route('student.photo', $this->student->id))->assertForbidden();
    }

    public function test_move_private_files_command_moves_legacy_public_files(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        $app = $this->application();
        Storage::disk('public')->put('documents/logbooks/lama.pdf', '%PDF lama');
        $log = Logbook::create(['placement_id' => $app->placement->id, 'date' => now()->toDateString(), 'activity' => 'x', 'attachment' => 'documents/logbooks/lama.pdf']);

        // Sebelum dipindah tetap bisa dibuka (fallback)
        $this->actingAs($this->student)->get(route('logbooks.attachment', $log->id))->assertOk();

        Artisan::call('app:move-private-files');

        Storage::disk('local')->assertExists('documents/logbooks/lama.pdf');
        Storage::disk('public')->assertMissing('documents/logbooks/lama.pdf');
        $this->actingAs($this->student)->get(route('logbooks.attachment', $log->id))->assertOk();
    }

    // ---- 8. Zona waktu & bahasa -----------------------------------------------------------

    public function test_indonesian_validation_messages_are_available(): void
    {
        app()->setLocale('id');
        $this->assertSame('nama wajib diisi.', __('validation.required', ['attribute' => 'nama']));
        $this->assertSame('Asia/Jakarta', config('app.timezone'));
    }

    // ---- 10. Masa magang berakhir ---------------------------------------------------------

    public function test_ended_internship_is_completed_when_eligible_or_reminded(): void
    {
        $ready = $this->application('active', [], ['end_date' => now()->subDay()->toDateString()]);
        FinalReport::create(['placement_id' => $ready->placement->id, 'file_path' => 'x.pdf', 'status' => 'approved']);
        Evaluation::create(['placement_id' => $ready->placement->id, 'nilai_disiplin' => 90, 'nilai_kinerja' => 90, 'nilai_laporan' => 90, 'nilai_akademik' => 90]);

        $other = User::factory()->create(['role' => 'mahasiswa']);
        $late = Application::create([
            'user_id' => $other->id, 'unit_id' => $this->unit->id, 'status' => 'active',
            'start_date' => now()->subDays(40)->toDateString(), 'end_date' => now()->subDay()->toDateString(),
        ]);
        Placement::create(['application_id' => $late->id, 'mentor_id' => $this->mentor->id]);

        $this->assertTrue($late->fresh()->isPastEndDate());
        Artisan::call('app:sync-internship-status');

        $this->assertSame('completed', $ready->fresh()->statusValue());
        $this->assertSame('active', $late->fresh()->statusValue());
        $this->assertTrue(SystemNotification::where('user_id', $other->id)->where('title', 'Masa Magang Telah Berakhir')->exists());
        $this->assertTrue(SystemNotification::where('user_id', $this->mentor->id)->exists());

        $this->actingAs($this->admin)->get(route('admin.applications.index'))->assertOk()->assertSee('Lewat masa magang');
    }

    // ---- 11. Pencocokan kampus & cegah kampus dobel --------------------------------------

    public function test_university_resolver_matches_exactly_and_suggests_typos(): void
    {
        $resolver = app(UniversityResolver::class);
        University::create(['name' => 'Universitas Surabaya', 'code' => 'UBAYA']);

        $this->assertSame($this->unesa->id, $resolver->findExact('unesa')->id);
        $this->assertSame($this->its->id, $resolver->findExact('Institut Teknologi Sepuluh Nopember.')->id);
        $this->assertNull($resolver->findExact('Surabaya')); // tidak menebak lewat LIKE
        $this->assertSame($this->unesa->id, $resolver->suggest('Universitas Negri Surabaya')->first()['university']->id);
    }

    public function test_student_typing_existing_campus_is_matched_or_asked_to_confirm(): void
    {
        $payload = ['name' => 'Mhs', 'nim' => '1', 'jurusan' => 'TI', 'phone' => '08', 'university_id' => 'other'];

        // Akronim persis → otomatis memakai kampus yang ada, tidak membuat entri baru
        $this->actingAs($this->student)->post(route('student.profile.update'), $payload + ['custom_university_name' => 'its'])
            ->assertSessionHasNoErrors();
        $this->assertSame($this->its->id, (int) $this->student->fresh()->university_id);
        $this->assertSame(2, University::count());

        // Salah ketik sangat mirip → ditahan dengan saran
        $this->actingAs($this->student)->post(route('student.profile.update'), $payload + ['custom_university_name' => 'Universitas Negri Surabaya'])
            ->assertSessionHasErrors('custom_university_name')
            ->assertSessionHas('university_suggestions');
        $this->assertSame(2, University::count());

        // Mahasiswa yakin kampusnya berbeda → dibuat sebagai kampus baru (menunggu verifikasi)
        $this->actingAs($this->student)->post(route('student.profile.update'), $payload + [
            'custom_university_name' => 'Universitas Negri Surabaya', 'confirm_new_university' => '1',
        ])->assertSessionHasNoErrors();
        $this->assertFalse(University::where('name', 'Universitas Negri Surabaya')->first()->is_verified);
    }

    public function test_super_admin_can_merge_duplicate_university(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $dup = University::create(['name' => 'Univ Negeri Surabaya', 'is_verified' => false]);
        $dupStudent = User::factory()->create(['role' => 'mahasiswa', 'university_id' => $dup->id]);
        StudentProfile::create(['user_id' => $dupStudent->id, 'nim' => '2', 'universitas' => $dup->name, 'university_id' => $dup->id, 'jurusan' => 'TI', 'phone' => '08']);

        $this->actingAs($superAdmin)->get(route('admin.universities.index'))->assertOk()->assertSee('Gabungkan');

        $this->actingAs($this->admin)->post(route('admin.universities.merge', $dup->id), ['target_university_id' => $this->unesa->id])
            ->assertForbidden();

        $this->actingAs($superAdmin)->post(route('admin.universities.merge', $dup->id), ['target_university_id' => $this->unesa->id])
            ->assertRedirect();

        $this->assertNull(University::find($dup->id));
        $this->assertSame($this->unesa->id, (int) $dupStudent->fresh()->university_id);
        $this->assertSame($this->unesa->name, $dupStudent->studentProfile()->first()->universitas);
    }
}
