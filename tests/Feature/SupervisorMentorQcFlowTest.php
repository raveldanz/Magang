<?php

namespace Tests\Feature;

use App\Models\AgencyProfile;
use App\Models\Application;
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
 * Temuan QC peran Supervisor (Atasan) dan Mentor (16 Nov 2026):
 *  - Atasan bisa "menolak" magang yang sudah aktif/selesai lewat alamat langsung sehingga statusnya kembali pending,
 *  - jumlah mahasiswa aktif per bidang di dasbor selalu 0,
 *  - mentor bisa menolak logbook / meminta revisi laporan tanpa catatan,
 *  - sisa kata Inggris di tampilan peran senior.
 */
class SupervisorMentorQcFlowTest extends TestCase
{
    use RefreshDatabase;

    private AgencyProfile $agency;

    private Unit $unit;

    private User $supervisor;

    private User $mentor;

    private User $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = AgencyProfile::create(['agency_name' => 'Dinas Perpustakaan dan Kearsipan Kota Surabaya', 'alias' => 'Dispusip Surabaya']);
        $this->unit = Unit::create(['agency_profile_id' => $this->agency->id, 'name' => 'Bidang Uji Layanan', 'quota' => 5]);
        $univ = University::create(['name' => 'Universitas Uji', 'code' => 'UU', 'evaluation_scheme' => 'mentor_only', 'weight_mentor' => 100, 'weight_lecturer' => 0]);

        $this->supervisor = User::factory()->create(['role' => 'supervisor', 'agency_profile_id' => $this->agency->id]);
        $this->mentor = User::factory()->create(['role' => 'mentor', 'agency_profile_id' => $this->agency->id, 'unit_id' => $this->unit->id]);
        $this->student = User::factory()->create(['role' => 'mahasiswa', 'university_id' => $univ->id]);
        StudentProfile::create(['user_id' => $this->student->id, 'nim' => '1', 'universitas' => $univ->name, 'university_id' => $univ->id, 'jurusan' => 'TI', 'phone' => '08']);
    }

    private function placement(string $status): Placement
    {
        $application = Application::create([
            'user_id' => $this->student->id,
            'unit_id' => $this->unit->id,
            'status' => $status,
            'start_date' => Carbon::now()->subDays(10)->toDateString(),
            'end_date' => Carbon::now()->addDays(30)->toDateString(),
        ]);

        return Placement::create(['application_id' => $application->id, 'mentor_id' => $this->mentor->id]);
    }

    public function test_atasan_tidak_bisa_menolak_magang_yang_sudah_aktif_atau_selesai(): void
    {
        foreach (['active', 'completed', 'accepted'] as $status) {
            $placement = $this->placement($status);

            $this->actingAs($this->supervisor)
                ->post(route('supervisor.applications.revise', $placement->application_id), ['notes' => 'Mencoba menolak magang berjalan'])
                ->assertSessionHas('error');

            $application = $placement->application->fresh();
            $this->assertSame($status, $application->statusValue());
            $this->assertNull($application->rejection_note);

            $placement->application->delete();
        }

        // Pengajuan yang memang menunggu pengesahan tetap bisa ditolak.
        $waiting = $this->placement('waiting_approval');
        $this->actingAs($this->supervisor)
            ->post(route('supervisor.applications.revise', $waiting->application_id), ['notes' => 'Kuota mentor penuh'])
            ->assertSessionHasNoErrors();
        $this->assertSame('pending', $waiting->application->fresh()->statusValue());
    }

    public function test_dasbor_atasan_menghitung_mahasiswa_aktif_per_bidang(): void
    {
        // Dasbor Atasan menampilkan bidang milik instansi Dispusip pertama (bisa berasal dari data bawaan migrasi).
        $dispusip = AgencyProfile::where('agency_name', 'like', '%Perpustakaan%')->first();
        $unit = Unit::where('agency_profile_id', $dispusip->id)->orderBy('id')->first();
        $unit->update(['quota' => 7]);
        $supervisor = User::factory()->create(['role' => 'supervisor', 'agency_profile_id' => $dispusip->id]);

        Application::create([
            'user_id' => $this->student->id,
            'unit_id' => $unit->id,
            'status' => 'active',
            'start_date' => Carbon::now()->subDays(10)->toDateString(),
            'end_date' => Carbon::now()->addDays(30)->toDateString(),
        ]);

        $this->actingAs($supervisor)->get(route('supervisor.dashboard'))
            ->assertOk()
            ->assertSee($unit->name)
            ->assertSee('Sisa: 6')
            ->assertDontSee('Sisa: 7')
            ->assertSee('Pemantauan Bidang')
            ->assertDontSee('Monitoring Bidang');
    }

    public function test_mentor_wajib_memberi_catatan_saat_menolak_logbook(): void
    {
        $placement = $this->placement('active');
        $first = Logbook::create(['placement_id' => $placement->id, 'date' => Carbon::now()->subDays(2)->toDateString(), 'activity' => 'Kegiatan pertama.', 'status' => 'pending']);
        $second = Logbook::create(['placement_id' => $placement->id, 'date' => Carbon::now()->subDay()->toDateString(), 'activity' => 'Kegiatan kedua.', 'status' => 'pending']);

        $this->actingAs($this->mentor)
            ->put(route('mentor.logbooks.updateStatus', $first->id), ['status' => 'rejected'])
            ->assertSessionHasErrors(['feedback' => 'Catatan wajib diisi saat menolak atau meminta revisi, agar mahasiswa tahu apa yang perlu diperbaiki.']);
        $this->assertSame('pending', $first->fresh()->status);

        $this->actingAs($this->mentor)
            ->post(route('mentor.logbooks.bulk_review'), ['logbook_ids' => [$first->id, $second->id], 'status' => 'rejected'])
            ->assertSessionHasErrors('feedback');
        $this->assertSame('pending', $second->fresh()->status);

        $this->actingAs($this->mentor)
            ->put(route('mentor.logbooks.updateStatus', $first->id), ['status' => 'rejected', 'feedback' => 'Jelaskan apa yang dikerjakan.'])
            ->assertSessionHasNoErrors();
        $this->assertSame('rejected', $first->fresh()->status);

        // Menyetujui tetap boleh tanpa catatan.
        $this->actingAs($this->mentor)
            ->put(route('mentor.logbooks.updateStatus', $second->id), ['status' => 'approved'])
            ->assertSessionHasNoErrors();
        $this->assertSame('approved', $second->fresh()->status);
    }

    public function test_mentor_wajib_memberi_catatan_saat_meminta_revisi_laporan(): void
    {
        $placement = $this->placement('active');
        $report = FinalReport::create(['placement_id' => $placement->id, 'title' => 'Laporan', 'file_path' => 'final_reports/a.pdf', 'final_report_path' => 'final_reports/a.pdf', 'status' => 'pending']);

        $this->actingAs($this->mentor)
            ->put(route('mentor.final_report.updateStatus', $report->id), ['status' => 'revision'])
            ->assertSessionHasErrors('feedback');
        $this->assertSame('pending', $report->fresh()->status);

        $this->actingAs($this->mentor)
            ->put(route('mentor.final_report.updateStatus', $report->id), ['status' => 'revision', 'feedback' => 'Bab 4 belum memuat analisis.'])
            ->assertSessionHasNoErrors();
        $this->assertSame('revision', $report->fresh()->status);
    }
}
