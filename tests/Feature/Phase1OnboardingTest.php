<?php

namespace Tests\Feature;

use App\Models\AgencyProfile;
use App\Models\Application;
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
 * Fase 1: Pondasi Master Data Dinamis & Onboarding
 *  1. Kampus baru (opsi "Perguruan Tinggi Lainnya") → is_verified = false + verifikasi Super Admin
 *  2. DPL nullable + penugasan DPL susulan pada penempatan ACTIVE
 *  3. Fallback validasi logbook oleh Admin Dinas & re-assign mentor tanpa merusak histori logbook
 */
class Phase1OnboardingTest extends TestCase
{
    use RefreshDatabase;

    private AgencyProfile $agencyA;

    private AgencyProfile $agencyB;

    private University $univ;

    private User $superAdmin;

    private User $adminA;

    private User $adminB;

    private User $mentorA;

    private User $mentorB;

    private User $dosen;

    private User $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agencyA = AgencyProfile::create(['agency_name' => 'Dinas A']);
        $this->agencyB = AgencyProfile::create(['agency_name' => 'Dinas B']);
        $this->univ = University::create(['name' => 'Universitas Terdaftar', 'code' => 'UT']);

        $this->superAdmin = User::factory()->create(['role' => 'super_admin']);
        $this->adminA = User::factory()->create(['role' => 'admin', 'agency_profile_id' => $this->agencyA->id]);
        $this->adminB = User::factory()->create(['role' => 'admin', 'agency_profile_id' => $this->agencyB->id]);
        $this->mentorA = User::factory()->create(['role' => 'mentor', 'agency_profile_id' => $this->agencyA->id]);
        $this->mentorB = User::factory()->create(['role' => 'mentor', 'agency_profile_id' => $this->agencyB->id]);
        $this->dosen = User::factory()->create(['role' => 'dosen', 'university_id' => $this->univ->id]);
        $this->student = User::factory()->create(['role' => 'mahasiswa', 'university_id' => $this->univ->id]);
    }

    private function profilePayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Budi Mahasiswa',
            'nim' => '123456',
            'university_id' => (string) $this->univ->id,
            'faculty' => 'Teknik',
            'jurusan' => 'Informatika',
            'semester' => '5',
            'phone' => '08123456789',
        ], $overrides);
    }

    private function activePlacement(array $placementData = []): Placement
    {
        $unit = Unit::create(['agency_profile_id' => $this->agencyA->id, 'name' => 'Bidang Aptika', 'quota' => 5]);
        $application = Application::create([
            'user_id' => $this->student->id,
            'unit_id' => $unit->id,
            'status' => 'active',
            'start_date' => Carbon::now()->subDays(2)->toDateString(),
            'end_date' => Carbon::now()->addDays(60)->toDateString(),
        ]);

        return Placement::create(array_merge(['application_id' => $application->id], $placementData));
    }

    // ---------------------------------------------------------------
    // 1. Dynamic University Onboarding
    // ---------------------------------------------------------------

    public function test_existing_universities_are_verified_by_default_and_get_slug(): void
    {
        $this->assertTrue($this->univ->fresh()->is_verified);
        $this->assertSame('universitas-terdaftar', $this->univ->fresh()->slug);
    }

    public function test_student_can_select_registered_university(): void
    {
        $this->actingAs($this->student)
            ->post(route('student.profile.update'), $this->profilePayload())
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $profile = StudentProfile::where('user_id', $this->student->id)->first();
        $this->assertSame($this->univ->id, (int) $profile->university_id);
        $this->assertSame('Universitas Terdaftar', $profile->universitas);
    }

    public function test_student_can_register_new_university_as_unverified(): void
    {
        $this->actingAs($this->student)
            ->post(route('student.profile.update'), $this->profilePayload([
                'university_id' => 'other',
                'custom_university_name' => '  Politeknik   Baru Nusantara ',
            ]))
            ->assertSessionHasNoErrors();

        $newUniv = University::where('name', 'Politeknik Baru Nusantara')->first();
        $this->assertNotNull($newUniv);
        $this->assertFalse($newUniv->is_verified);
        $this->assertSame('politeknik-baru-nusantara', $newUniv->slug);
        $this->assertSame($newUniv->id, (int) StudentProfile::where('user_id', $this->student->id)->value('university_id'));
        $this->assertSame($newUniv->id, (int) $this->student->fresh()->university_id);

        // Mahasiswa kedua dari kampus yang sama (beda huruf besar/kecil) tidak membuat duplikat
        $other = User::factory()->create(['role' => 'mahasiswa']);
        $this->actingAs($other)->post(route('student.profile.update'), $this->profilePayload([
            'nim' => '999', 'university_id' => 'other', 'custom_university_name' => 'politeknik baru nusantara',
        ]))->assertSessionHasNoErrors();
        $this->assertSame(1, University::whereRaw('LOWER(name) = ?', ['politeknik baru nusantara'])->count());
    }

    public function test_custom_university_name_is_required_when_other_selected(): void
    {
        $this->actingAs($this->student)
            ->post(route('student.profile.update'), $this->profilePayload(['university_id' => 'other']))
            ->assertSessionHasErrors('custom_university_name');

        $this->actingAs($this->student)
            ->post(route('student.profile.update'), $this->profilePayload(['university_id' => '999999']))
            ->assertSessionHasErrors('university_id');
    }

    public function test_profile_page_renders_with_other_option(): void
    {
        University::create(['name' => 'Kampus Orang Lain', 'is_verified' => false]);

        $this->actingAs($this->student)
            ->get(route('student.profile.edit'))
            ->assertOk()
            ->assertSee('custom_university_name')
            ->assertSee('Universitas Terdaftar')
            ->assertDontSee('Kampus Orang Lain'); // kampus belum terverifikasi milik orang lain tidak bocor
    }

    public function test_super_admin_sees_badge_and_can_verify_university(): void
    {
        $pending = University::create(['name' => 'Kampus Mandiri', 'is_verified' => false]);

        $this->actingAs($this->superAdmin)
            ->get(route('admin.universities.index'))
            ->assertOk()
            ->assertSee('Menunggu Verifikasi')
            ->assertSee('Terverifikasi')
            ->assertSee('Verifikasi Kampus');

        $this->actingAs($this->adminA)
            ->post(route('admin.universities.verify', $pending->id))
            ->assertForbidden();
        $this->assertFalse($pending->fresh()->is_verified);

        $this->actingAs($this->superAdmin)
            ->post(route('admin.universities.verify', $pending->id))
            ->assertRedirect();
        $this->assertTrue($pending->fresh()->is_verified);
    }

    // ---------------------------------------------------------------
    // 2. DPL nullable & penugasan susulan
    // ---------------------------------------------------------------

    public function test_student_can_fill_logbook_before_dpl_assigned(): void
    {
        $placement = $this->activePlacement(['mentor_id' => $this->mentorA->id]);

        $this->actingAs($this->student)->get(route('student.logbook.index'))->assertOk()->assertSee('Belum Ditugaskan');
        $this->actingAs($this->student)->get(route('student.logbook.create'))->assertOk();

        $this->actingAs($this->student)->post(route('student.logbook.store'), [
            'date' => Carbon::now()->toDateString(),
            'activity' => 'Orientasi hari pertama di bidang aptika.',
        ])->assertRedirect();

        $log = Logbook::where('placement_id', $placement->id)->first();
        $this->assertNotNull($log);
        $this->assertSame('pending', $log->lecturer_status);
    }

    public function test_admin_can_assign_dpl_on_active_placement(): void
    {
        $placement = $this->activePlacement();
        $appId = $placement->application_id;

        $this->actingAs($this->adminA)
            ->get(route('admin.applications.show', $appId))
            ->assertOk()
            ->assertSee('Penugasan Pembimbing (Susulan / Ganti)');

        $this->actingAs($this->adminA)
            ->patch(route('admin.applications.assignment', $appId), ['academic_advisor_id' => $this->dosen->id])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertSame($this->dosen->id, (int) $placement->fresh()->academic_advisor_id);
        $this->assertSame('active', Application::find($appId)->statusValue());
    }

    public function test_dpl_from_other_university_is_rejected(): void
    {
        $placement = $this->activePlacement();
        $otherUniv = University::create(['name' => 'Kampus Lain']);
        $foreignDosen = User::factory()->create(['role' => 'dosen', 'university_id' => $otherUniv->id]);

        $this->actingAs($this->adminA)
            ->patch(route('admin.applications.assignment', $placement->application_id), ['academic_advisor_id' => $foreignDosen->id])
            ->assertSessionHasErrors('academic_advisor_id');

        $this->assertNull($placement->fresh()->academic_advisor_id);
    }

    // ---------------------------------------------------------------
    // 3. Fallback mentor & re-assign
    // ---------------------------------------------------------------

    public function test_agency_admin_can_validate_logbook_while_mentor_not_assigned(): void
    {
        $placement = $this->activePlacement();
        $log = Logbook::create(['placement_id' => $placement->id, 'date' => now()->toDateString(), 'activity' => 'Hari pertama', 'status' => 'pending']);

        $this->actingAs($this->adminA)
            ->get(route('admin.logbooks.show', $log->id))
            ->assertOk()
            ->assertSee('Validasi Sementara oleh Admin Dinas');

        // Admin dinas lain tidak boleh
        $this->actingAs($this->adminB)
            ->put(route('admin.logbooks.review', $log->id), ['status' => 'approved'])
            ->assertForbidden();

        $this->actingAs($this->adminA)
            ->put(route('admin.logbooks.review', $log->id), ['status' => 'approved', 'feedback' => 'OK'])
            ->assertRedirect();

        $this->assertSame('approved', $log->fresh()->status);
    }

    public function test_admin_fallback_closes_once_mentor_assigned(): void
    {
        $placement = $this->activePlacement(['mentor_id' => $this->mentorA->id, 'pembimbing_id' => $this->mentorA->id]);
        $log = Logbook::create(['placement_id' => $placement->id, 'date' => now()->toDateString(), 'activity' => 'Kegiatan', 'status' => 'pending']);

        $this->actingAs($this->adminA)
            ->get(route('admin.logbooks.show', $log->id))
            ->assertOk()
            ->assertDontSee('Validasi Sementara oleh Admin Dinas');

        $this->actingAs($this->adminA)
            ->put(route('admin.logbooks.review', $log->id), ['status' => 'approved'])
            ->assertSessionHas('error');

        $this->assertSame('pending', $log->fresh()->status);
    }

    public function test_reassign_mentor_keeps_validated_logbook_history(): void
    {
        $placement = $this->activePlacement();
        $log = Logbook::create([
            'placement_id' => $placement->id, 'date' => now()->subDay()->toDateString(),
            'activity' => 'Hari pertama', 'status' => 'approved', 'feedback' => 'Divalidasi admin',
        ]);

        $this->actingAs($this->adminA)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee('Mahasiswa Belum Memiliki Mentor Dinas');

        // Mentor dari instansi lain ditolak
        $this->actingAs($this->adminA)
            ->patch(route('admin.applications.assignment', $placement->application_id), ['mentor_id' => $this->mentorB->id])
            ->assertSessionHasErrors('mentor_id');
        $this->assertNull($placement->fresh()->mentor_id);

        // Admin dinas lain tidak boleh menugaskan
        $this->actingAs($this->adminB)
            ->patch(route('admin.applications.assignment', $placement->application_id), ['mentor_id' => $this->mentorB->id])
            ->assertForbidden();

        $this->actingAs($this->adminA)
            ->patch(route('admin.applications.assignment', $placement->application_id), ['mentor_id' => $this->mentorA->id])
            ->assertSessionHasNoErrors();

        $fresh = $placement->fresh();
        $this->assertSame($this->mentorA->id, (int) $fresh->mentor_id);
        $this->assertSame($this->mentorA->id, (int) $fresh->pembimbing_id);

        $log->refresh();
        $this->assertSame('approved', $log->status);
        $this->assertSame('Divalidasi admin', $log->feedback);
        $this->assertSame(1, Logbook::where('placement_id', $placement->id)->count());

        // Mentor baru kini bisa memvalidasi logbook berikutnya
        $log2 = Logbook::create(['placement_id' => $placement->id, 'date' => now()->toDateString(), 'activity' => 'Hari kedua', 'status' => 'pending']);
        $this->actingAs($this->mentorA)
            ->put(route('mentor.logbooks.updateStatus', $log2->id), ['status' => 'approved'])
            ->assertRedirect();
        $this->assertSame('approved', $log2->fresh()->status);
    }

    public function test_assignment_rejected_for_non_running_placement(): void
    {
        $placement = $this->activePlacement();
        Application::whereKey($placement->application_id)->update(['status' => 'completed']);

        $this->actingAs($this->adminA)
            ->patch(route('admin.applications.assignment', $placement->application_id), ['mentor_id' => $this->mentorA->id])
            ->assertSessionHasErrors('assignment');
    }
}
