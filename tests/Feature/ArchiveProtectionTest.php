<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureAccountIsActive;
use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\Evaluation;
use App\Models\FinalReport;
use App\Models\Placement;
use App\Models\Unit;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Arsip alumni (pengajuan, logbook, nilai, sertifikat) wajib dipertahankan:
 * - applications.unit_id / applications.user_id ON DELETE CASCADE → hapus divisi/instansi/mahasiswa = hapus arsip.
 * - placements.mentor_id / academic_advisor_id ON DELETE SET NULL → hapus pembimbing = nama hilang dari arsip.
 * Akses akun yang tidak boleh dihapus dicabut lewat status Nonaktif.
 */
class ArchiveProtectionTest extends TestCase
{
    use RefreshDatabase;

    private University $univ;
    private AgencyProfile $agency;
    private Unit $unit;
    private User $superAdmin;
    private User $mentor;
    private User $dosen;
    private User $alumnus;
    private Application $alumniApplication;

    protected function setUp(): void
    {
        parent::setUp();

        $this->univ = University::create(['name' => 'Universitas Arsip', 'code' => 'UAR']);
        $this->agency = AgencyProfile::create(['agency_name' => 'Dinas Perpustakaan dan Kearsipan']);
        $this->unit = Unit::create(['agency_profile_id' => $this->agency->id, 'name' => 'Bidang Arsip', 'quota' => 5]);
        $this->superAdmin = User::factory()->create(['role' => 'super_admin']);
        $this->mentor = User::factory()->create(['role' => 'mentor', 'agency_profile_id' => $this->agency->id]);
        $this->dosen = User::factory()->create(['role' => 'dosen', 'university_id' => $this->univ->id]);

        // Alumni: magang selesai, bernilai, bersertifikat
        $this->alumnus = User::factory()->create(['role' => 'mahasiswa', 'university_id' => $this->univ->id]);
        $this->alumniApplication = Application::create([
            'user_id' => $this->alumnus->id,
            'unit_id' => $this->unit->id,
            'status' => 'completed',
            'start_date' => '2026-01-05',
            'end_date' => '2026-04-05',
        ]);
        $placement = Placement::create([
            'application_id' => $this->alumniApplication->id,
            'mentor_id' => $this->mentor->id,
            'academic_advisor_id' => $this->dosen->id,
            'certificate_hash' => 'arsipalumni1234567890abcdefghijk',
        ]);
        FinalReport::create(['placement_id' => $placement->id, 'file_path' => 'laporan.pdf', 'status' => 'approved']);
        Evaluation::create(['placement_id' => $placement->id, 'nilai_disiplin' => 90, 'nilai_kinerja' => 90, 'nilai_laporan' => 90, 'nilai_akademik' => 90]);
    }

    private function assertArchiveIntact(): void
    {
        $placement = $this->alumniApplication->fresh()?->placement;
        $this->assertNotNull($placement, 'Pengajuan/penempatan alumni ikut terhapus');
        $this->assertSame($this->mentor->id, $placement->mentor_id, 'Mentor pada arsip alumni hilang');
        $this->assertSame($this->dosen->id, $placement->academic_advisor_id, 'DPL pada arsip alumni hilang');
        $this->assertNotNull($placement->evaluation);
        $this->assertNotNull($placement->finalreport);
    }

    public function test_unit_and_agency_with_alumni_cannot_be_deleted(): void
    {
        $this->actingAs($this->superAdmin)->delete(route('admin.units.destroy', $this->unit->id))->assertSessionHas('error');
        $this->actingAs($this->superAdmin)->delete(route('admin.agencies.destroy', $this->agency->id))->assertSessionHas('error');

        $this->assertNotNull($this->unit->fresh());
        $this->assertNotNull($this->agency->fresh());
        $this->assertArchiveIntact();
    }

    public function test_empty_unit_can_still_be_deleted(): void
    {
        $emptyUnit = Unit::create(['agency_profile_id' => $this->agency->id, 'name' => 'Divisi Kosong', 'quota' => 3]);

        $this->actingAs($this->superAdmin)->delete(route('admin.units.destroy', $emptyUnit->id));

        $this->assertNull($emptyUnit->fresh());
    }

    public function test_supervisors_with_alumni_cannot_be_deleted(): void
    {
        $univAdmin = User::factory()->create(['role' => 'universitas', 'university_id' => $this->univ->id]);
        $pembimbing = User::factory()->create(['role' => 'pembimbing', 'agency_profile_id' => $this->agency->id]);
        $this->alumniApplication->placement->update(['pembimbing_id' => $pembimbing->id]);

        $this->actingAs($this->superAdmin)->delete(route('admin.mentors.destroy', $this->mentor->id))->assertSessionHas('error');
        $this->actingAs($this->superAdmin)->delete(route('admin.universities.dosens.destroy', [$this->univ->id, $this->dosen->id]))->assertSessionHas('error');
        $this->actingAs($univAdmin)->delete(route('university.lecturers.destroy', $this->dosen->id))->assertSessionHas('error');
        // Master Pengguna: kolom pembimbing_id dulu tidak diperiksa
        $this->actingAs($this->superAdmin)->delete(route('admin.users.destroy', $pembimbing->id))->assertSessionHas('error');

        $this->assertNotNull($this->mentor->fresh());
        $this->assertNotNull($this->dosen->fresh());
        $this->assertNotNull($pembimbing->fresh());
        $this->assertArchiveIntact();
    }

    public function test_alumnus_cannot_delete_own_account(): void
    {
        $this->actingAs($this->alumnus)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSeeText('wajib diarsipkan');

        $this->actingAs($this->alumnus)
            ->delete(route('profile.destroy'), ['password' => 'password'])
            ->assertSessionHas('error');

        $this->assertNotNull($this->alumnus->fresh());
        $this->assertArchiveIntact();
    }

    public function test_inactive_account_cannot_log_in(): void
    {
        $this->mentor->update(['status' => 'inactive']);

        $this->post('/login', ['email' => $this->mentor->email, 'password' => 'password'])
            ->assertSessionHasErrors(['email' => EnsureAccountIsActive::MESSAGE]);
        $this->assertGuest();

        $this->postJson('/api/login', ['email' => $this->mentor->email, 'password' => 'password'])
            ->assertForbidden();
    }

    public function test_running_session_of_inactive_account_is_terminated(): void
    {
        $this->mentor->update(['status' => 'inactive']);

        $this->actingAs($this->mentor)->get('/dashboard')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_super_admin_is_never_locked_out(): void
    {
        $this->superAdmin->update(['status' => 'inactive']);

        $this->post('/login', ['email' => $this->superAdmin->email, 'password' => 'password']);
        $this->assertAuthenticatedAs($this->superAdmin);
    }
}
