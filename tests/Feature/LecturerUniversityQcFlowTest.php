<?php

namespace Tests\Feature;

use App\Enums\ReviewStatus;
use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\Evaluation;
use App\Models\FinalReport;
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
 * Temuan QC peran Dosen dan Admin Universitas (16 Nov 2026):
 *  - dosen bisa menyetujui laporan akhir yang belum pernah diunggah (syarat laporan terlewati),
 *  - dosen/mentor bisa mengembalikan laporan ke revisi setelah mahasiswa lulus (sertifikat terkunci lagi),
 *  - dosen bisa menolak logbook dan meminta revisi laporan tanpa catatan,
 *  - catatan bimbingan menerima tanggal masa depan,
 *  - nilai dosen tampil dibulatkan berbeda antarbagian, lembar nilai memuat kode status Inggris.
 */
class LecturerUniversityQcFlowTest extends TestCase
{
    use RefreshDatabase;

    private University $univ;

    private Unit $unit;

    private User $mentor;

    private User $dosen;

    private User $student;

    protected function setUp(): void
    {
        parent::setUp();

        $agency = AgencyProfile::create(['agency_name' => 'Dinas Uji', 'alias' => 'Dinas Uji']);
        $this->unit = Unit::create(['agency_profile_id' => $agency->id, 'name' => 'Bidang Uji', 'quota' => 5]);
        $this->univ = University::create(['name' => 'Politeknik Uji', 'code' => 'PU', 'evaluation_scheme' => 'dual_evaluation', 'weight_mentor' => 40, 'weight_lecturer' => 60]);
        $this->mentor = User::factory()->create(['role' => 'mentor', 'agency_profile_id' => $agency->id, 'unit_id' => $this->unit->id]);
        $this->dosen = User::factory()->create(['role' => 'dosen', 'university_id' => $this->univ->id]);
        $this->student = User::factory()->create(['role' => 'mahasiswa', 'university_id' => $this->univ->id]);
        StudentProfile::create(['user_id' => $this->student->id, 'nim' => '1', 'universitas' => $this->univ->name, 'university_id' => $this->univ->id, 'jurusan' => 'TI', 'phone' => '08']);
    }

    private function placement(string $status = 'active'): Placement
    {
        $application = Application::create([
            'user_id' => $this->student->id,
            'unit_id' => $this->unit->id,
            'status' => $status,
            'start_date' => Carbon::now()->subDays(40)->toDateString(),
            'end_date' => Carbon::now()->addDays(5)->toDateString(),
        ]);

        return Placement::create(['application_id' => $application->id, 'mentor_id' => $this->mentor->id, 'academic_advisor_id' => $this->dosen->id]);
    }

    private function report(Placement $placement, string $status = 'pending'): FinalReport
    {
        return FinalReport::create(['placement_id' => $placement->id, 'title' => 'Laporan', 'file_path' => 'final_reports/naskah.pdf', 'final_report_path' => 'final_reports/naskah.pdf', 'status' => $status]);
    }

    public function test_dosen_tidak_bisa_menyetujui_laporan_yang_belum_diunggah(): void
    {
        $placement = $this->placement();

        $this->actingAs($this->dosen)
            ->post(route('lecturer.final_report.updateStatus', $placement->id), ['status' => 'approved'])
            ->assertSessionHas('error');

        $this->assertSame(0, FinalReport::where('placement_id', $placement->id)->count());
        $this->assertFalse((bool) $placement->application->fresh()->has_approved_report);

        // Setelah naskah benar-benar ada, persetujuan berjalan.
        $report = $this->report($placement);
        $this->actingAs($this->dosen)
            ->post(route('lecturer.final_report.updateStatus', $placement->id), ['status' => 'approved'])
            ->assertSessionHasNoErrors()
            ->assertSessionMissing('error');
        $this->assertSame('approved', $report->fresh()->status);
    }

    public function test_laporan_tidak_bisa_diubah_setelah_magang_selesai(): void
    {
        $placement = $this->placement('completed');
        $report = $this->report($placement, 'approved');

        $this->actingAs($this->dosen)
            ->post(route('lecturer.final_report.updateStatus', $placement->id), ['status' => 'revision', 'feedback' => 'Ubah setelah lulus'])
            ->assertSessionHas('error');
        $this->actingAs($this->mentor)
            ->put(route('mentor.final_report.updateStatus', $report->id), ['status' => 'revision', 'feedback' => 'Ubah setelah lulus'])
            ->assertSessionHas('error');

        $this->assertSame('approved', $report->fresh()->status);
        $this->assertSame('completed', $placement->application->fresh()->statusValue());
    }

    public function test_dosen_wajib_memberi_catatan_saat_menolak(): void
    {
        $placement = $this->placement();
        $report = $this->report($placement);
        $logbook = Logbook::create(['placement_id' => $placement->id, 'date' => Carbon::now()->subDay()->toDateString(), 'activity' => 'Kegiatan harian.', 'status' => 'approved', 'lecturer_status' => 'pending']);

        $this->actingAs($this->dosen)
            ->put(route('lecturer.logbooks.updateStatus', $logbook->id), ['status' => 'rejected'])
            ->assertSessionHasErrors('feedback');
        $this->assertSame('pending', $logbook->fresh()->lecturer_status);

        $this->actingAs($this->dosen)
            ->post(route('lecturer.final_report.updateStatus', $placement->id), ['status' => 'revision'])
            ->assertSessionHasErrors('feedback');
        $this->assertSame('pending', $report->fresh()->status);

        $this->actingAs($this->dosen)
            ->put(route('lecturer.logbooks.updateStatus', $logbook->id), ['status' => 'rejected', 'feedback' => 'Lengkapi uraian kegiatan.'])
            ->assertSessionHasNoErrors();
        $this->assertSame('rejected', $logbook->fresh()->lecturer_status);
    }

    public function test_catatan_bimbingan_menolak_tanggal_masa_depan(): void
    {
        $placement = $this->placement();

        $this->actingAs($this->dosen)
            ->post(route('lecturer.consultations.store', $placement->id), [
                'consultation_date' => Carbon::now()->addYears(5)->toDateString(),
                'stage' => 'Penyusunan Draf Laporan Akhir',
                'topic' => 'Bab 4',
                'notes' => 'Catatan bimbingan.',
            ])
            ->assertSessionHasErrors('consultation_date');
    }

    public function test_nilai_dosen_tampil_konsisten_dan_lembar_nilai_memakai_label_status_baku(): void
    {
        $placement = $this->placement();
        $this->report($placement, 'approved');
        Evaluation::create([
            'placement_id' => $placement->id,
            'nilai_disiplin' => 88, 'nilai_kinerja' => 90, 'nilai_laporan' => 86,
            'nilai_pembimbing' => 88,
            'score_mastery' => 80, 'score_report' => 90, 'score_attitude' => 85.5,
            'nilai_akademik' => 85.17,
            'final_score' => 86.3, 'grade' => 'A',
        ]);

        $this->actingAs($this->dosen)->get(route('lecturer.students.show', $placement->id))
            ->assertOk()
            ->assertSee('Skor: 85.17')
            ->assertDontSee('Skor: 85.2')
            ->assertSee('2. Laporan Akhir (Persetujuan Pembimbing)');

        $this->actingAs($this->dosen)->get(route('lecturer.students.grade_sheet', $placement->id))
            ->assertOk()
            ->assertSee('Status Naskah Laporan:')
            ->assertSee(ReviewStatus::APPROVED->localizedLabel('dosen'))
            ->assertDontSee('Dosen Pembimbing Lapangan');
    }

    public function test_halaman_admin_universitas_memakai_istilah_pembimbing(): void
    {
        $admin = User::factory()->create(['role' => 'universitas', 'university_id' => $this->univ->id]);

        // Istilah tim (revisi 16 Nov 2026): "Dosen Pembimbing" menjadi "Pembimbing"
        $this->actingAs($admin)->get(route('university.lecturers.index'))
            ->assertOk()
            ->assertSee('Daftar Pembimbing')
            ->assertDontSee('Dosen Pembimbing Lapangan');
    }
}
