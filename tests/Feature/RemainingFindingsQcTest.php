<?php

namespace Tests\Feature;

use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\StudentProfile;
use App\Models\Unit;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Temuan QC yang semula ditunda (16 Nov 2026):
 *  - halaman detail kampus memperlihatkan mahasiswa bidang/instansi lain kepada Admin Bidang/Dinas,
 *  - daftar pengguna Admin Dinas tidak memuat mahasiswanya tetapi memuat akun Supervisor,
 *  - NIM, nomor telepon, dan semester diterima apa adanya,
 *  - laporan kendala menerima judul dan pesan satu huruf.
 */
class RemainingFindingsQcTest extends TestCase
{
    use RefreshDatabase;

    private AgencyProfile $agency;

    private Unit $unitA;

    private Unit $unitB;

    private University $univ;

    private User $adminDinas;

    private User $adminBidang;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = AgencyProfile::create(['agency_name' => 'Dinas Uji QC']);
        $this->unitA = Unit::create(['agency_profile_id' => $this->agency->id, 'name' => 'Bidang Alfa', 'quota' => 10]);
        $this->unitB = Unit::create(['agency_profile_id' => $this->agency->id, 'name' => 'Bidang Beta', 'quota' => 10]);
        $this->univ = University::create(['name' => 'Universitas Uji', 'code' => 'UU']);
        $this->adminDinas = User::factory()->create(['role' => 'admin', 'agency_profile_id' => $this->agency->id]);
        $this->adminBidang = User::factory()->create(['role' => 'admin', 'agency_profile_id' => $this->agency->id, 'unit_id' => $this->unitA->id]);
    }

    private function student(string $name, ?Unit $unit): User
    {
        $student = User::factory()->create(['role' => 'mahasiswa', 'name' => $name, 'university_id' => $this->univ->id]);
        StudentProfile::create(['user_id' => $student->id, 'nim' => (string) (1000 + $student->id), 'universitas' => $this->univ->name, 'university_id' => $this->univ->id, 'jurusan' => 'TI', 'phone' => '081234567890']);
        if ($unit) {
            Application::create([
                'user_id' => $student->id,
                'unit_id' => $unit->id,
                'status' => 'active',
                'start_date' => Carbon::now()->subDays(5)->toDateString(),
                'end_date' => Carbon::now()->addDays(30)->toDateString(),
            ]);
        }

        return $student;
    }

    public function test_detail_kampus_hanya_menampilkan_mahasiswa_dalam_wewenang_admin(): void
    {
        $otherAgency = AgencyProfile::create(['agency_name' => 'Dinas Tetangga']);
        $otherUnit = Unit::create(['agency_profile_id' => $otherAgency->id, 'name' => 'Bidang Luar', 'quota' => 5]);

        $this->student('Mahasiswa Bidang Alfa', $this->unitA);
        $this->student('Mahasiswa Bidang Beta', $this->unitB);
        $this->student('Mahasiswa Dinas Tetangga', $otherUnit);
        $this->student('Mahasiswa Belum Mengajukan', null);

        $url = route('admin.universities.show', ['university' => $this->univ->id, 'tab' => 'mahasiswa']);

        $this->actingAs($this->adminBidang)->get($url)->assertOk()
            ->assertSee('Mahasiswa Bidang Alfa')
            ->assertDontSee('Mahasiswa Bidang Beta')
            ->assertDontSee('Mahasiswa Dinas Tetangga')
            ->assertDontSee('Mahasiswa Belum Mengajukan');

        $this->actingAs($this->adminDinas)->get($url)->assertOk()
            ->assertSee('Mahasiswa Bidang Alfa')
            ->assertSee('Mahasiswa Bidang Beta')
            ->assertDontSee('Mahasiswa Dinas Tetangga');

        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $this->actingAs($superAdmin)->get($url)->assertOk()
            ->assertSee('Mahasiswa Dinas Tetangga')
            ->assertSee('Mahasiswa Belum Mengajukan');
    }

    public function test_daftar_pengguna_admin_memuat_mahasiswanya_dan_tidak_memuat_supervisor(): void
    {
        User::factory()->create(['role' => 'supervisor', 'name' => 'Atasan Kepegawaian Uji', 'agency_profile_id' => $this->agency->id, 'unit_id' => $this->unitA->id]);
        User::factory()->create(['role' => 'mentor', 'name' => 'Mentor Bidang Alfa', 'agency_profile_id' => $this->agency->id, 'unit_id' => $this->unitA->id]);
        $this->student('Mahasiswa Bidang Alfa', $this->unitA);
        $this->student('Mahasiswa Bidang Beta', $this->unitB);

        $this->actingAs($this->adminBidang)->get(route('admin.users.index'))->assertOk()
            ->assertSee('Mentor Bidang Alfa')
            ->assertSee('Mahasiswa Bidang Alfa')
            ->assertDontSee('Mahasiswa Bidang Beta')
            ->assertDontSee('Atasan Kepegawaian Uji');

        $this->actingAs($this->adminDinas)->get(route('admin.users.index'))->assertOk()
            ->assertSee('Mahasiswa Bidang Alfa')
            ->assertSee('Mahasiswa Bidang Beta')
            ->assertDontSee('Atasan Kepegawaian Uji');

        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $this->actingAs($superAdmin)->get(route('admin.users.index'))->assertOk()->assertSee('Atasan Kepegawaian Uji');
    }

    public function test_profil_mahasiswa_menolak_nim_telepon_dan_semester_tidak_wajar(): void
    {
        $student = $this->student('Mahasiswa Profil', null);
        $payload = ['name' => 'Mahasiswa Profil', 'university_id' => (string) $this->univ->id, 'jurusan' => 'TI'];

        $this->actingAs($student)->post(route('student.profile.update'), $payload + ['nim' => 'abc<script>', 'phone' => '12', 'semester' => '99'])
            ->assertSessionHasErrors(['nim', 'phone', 'semester']);

        $this->actingAs($student)->post(route('student.profile.update'), $payload + ['nim' => '22.05.1204/099', 'phone' => '+62 812-3456-7890', 'semester' => '5'])
            ->assertSessionHasNoErrors();
        $this->assertSame('22.05.1204/099', $student->studentProfile->fresh()->nim);
    }

    public function test_laporan_kendala_menolak_judul_dan_pesan_terlalu_pendek(): void
    {
        $student = $this->student('Mahasiswa Pelapor', null);

        $this->actingAs($student)->post(route('feedbacks.store'), ['category' => 'error_bug', 'target_role' => 'super_admin', 'priority' => 'low', 'subject' => 'x', 'message' => 'y'])
            ->assertSessionHasErrors(['subject', 'message']);
    }
}
