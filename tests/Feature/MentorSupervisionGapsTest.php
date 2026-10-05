<?php

namespace Tests\Feature;

use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\Evaluation;
use App\Models\FinalReport;
use App\Models\Logbook;
use App\Models\Placement;
use App\Models\SystemNotification;
use App\Models\Unit;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Kelengkapan fitur Mentor Dinas:
 *  - angka notifikasi logbook hanya mahasiswa bimbingan sendiri,
 *  - Kepala Unit (fallback) bisa membuka detail mahasiswa tetapi tidak menilai,
 *  - nilai terkunci sebelum magang berjalan & setelah selesai,
 *  - mahasiswa mendapat notifikasi saat diminta revisi / dinilai.
 */
class MentorSupervisionGapsTest extends TestCase
{
    use RefreshDatabase;

    private AgencyProfile $agency;

    private Unit $unit;

    private User $mentor;

    private User $otherMentor;

    private User $unitHead;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = AgencyProfile::create(['agency_name' => 'Dinas Komunikasi dan Informatika']);
        $this->unitHead = User::factory()->create(['role' => 'mentor', 'name' => 'Kepala Bidang', 'agency_profile_id' => $this->agency->id]);
        $this->unit = Unit::create([
            'agency_profile_id' => $this->agency->id,
            'name' => 'Bidang Aplikasi Informatika',
            'quota' => 10,
            'head_user_id' => $this->unitHead->id,
        ]);
        $this->mentor = User::factory()->create(['role' => 'mentor', 'name' => 'Mentor Utama', 'agency_profile_id' => $this->agency->id]);
        $this->otherMentor = User::factory()->create(['role' => 'mentor', 'name' => 'Mentor Lain', 'agency_profile_id' => $this->agency->id]);
    }

    private function placementFor(?User $mentor, string $status = 'active', ?string $startDate = null, string $nim = '22081010001'): Placement
    {
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $student->studentProfile()->create([
            'nim' => $nim,
            'universitas' => 'Universitas Airlangga',
            'jurusan' => 'Sistem Informasi',
            'phone' => '08123456789',
        ]);

        $application = Application::create([
            'user_id' => $student->id,
            'unit_id' => $this->unit->id,
            'status' => $status,
            'start_date' => $startDate ?? now()->subWeeks(2)->toDateString(),
            'end_date' => now()->addWeeks(4)->toDateString(),
        ]);

        return Placement::create([
            'application_id' => $application->id,
            'mentor_id' => $mentor?->id,
        ]);
    }

    private function pendingLogbooks(Placement $placement, int $count): void
    {
        for ($i = 1; $i <= $count; $i++) {
            Logbook::create([
                'placement_id' => $placement->id,
                'date' => now()->subDays($i)->toDateString(),
                'activity' => "Kegiatan hari ke-{$i}",
                'status' => 'pending',
            ]);
        }
    }

    private function validScores(): array
    {
        return ['nilai_disiplin' => 85, 'nilai_kinerja' => 80, 'nilai_laporan' => 90, 'catatan' => 'Baik'];
    }

    public function test_mentor_notification_counts_only_own_students_logbooks(): void
    {
        $this->pendingLogbooks($this->placementFor($this->mentor, nim: '22081010001'), 2);
        $this->pendingLogbooks($this->placementFor($this->otherMentor, nim: '22081010002'), 3);

        $items = collect(NotificationService::getNotificationsForUser($this->mentor->fresh()));
        $logbookItem = $items->firstWhere('id', 'mentor_logbook_pending');

        $this->assertNotNull($logbookItem);
        $this->assertStringStartsWith('2 Logbook', $logbookItem['title']);
    }

    public function test_unit_head_can_open_student_detail_but_cannot_evaluate(): void
    {
        $placement = $this->placementFor(null);
        $this->pendingLogbooks($placement, 1);

        $this->actingAs($this->unitHead)->get(route('mentor.students.show', $placement->id))
            ->assertOk()
            ->assertSee('Kepala Unit')
            ->assertDontSee(route('mentor.evaluations.create', $placement->id), false);

        $this->actingAs($this->unitHead)
            ->post(route('mentor.evaluations.store', $placement->id), $this->validScores())
            ->assertNotFound();

        $this->assertDatabaseMissing('evaluations', ['placement_id' => $placement->id]);

        // Mentor lain di instansi yang sama tetap tidak boleh membuka
        $this->actingAs($this->otherMentor)->get(route('mentor.students.show', $placement->id))->assertForbidden();
    }

    public function test_evaluation_is_locked_after_internship_completed(): void
    {
        $placement = $this->placementFor($this->mentor, 'completed');

        $this->actingAs($this->mentor)->get(route('mentor.evaluations.create', $placement->id))
            ->assertOk()
            ->assertSee('Form penilaian terkunci');

        $this->actingAs($this->mentor)
            ->post(route('mentor.evaluations.store', $placement->id), $this->validScores())
            ->assertRedirect(route('mentor.students.show', $placement->id))
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('evaluations', ['placement_id' => $placement->id]);
    }

    public function test_evaluation_is_locked_before_internship_starts(): void
    {
        $placement = $this->placementFor($this->mentor, 'accepted', now()->addWeek()->toDateString());

        $this->actingAs($this->mentor)
            ->post(route('mentor.evaluations.store', $placement->id), $this->validScores())
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('evaluations', ['placement_id' => $placement->id]);
    }

    public function test_evaluation_allowed_when_start_date_passed_but_status_not_synced(): void
    {
        $placement = $this->placementFor($this->mentor, 'accepted', now()->subDay()->toDateString());

        $this->actingAs($this->mentor)
            ->post(route('mentor.evaluations.store', $placement->id), $this->validScores())
            ->assertSessionHasNoErrors()
            ->assertSessionMissing('error');

        $this->assertDatabaseHas('evaluations', ['placement_id' => $placement->id, 'nilai_disiplin' => 85]);
    }

    public function test_student_notified_once_when_mentor_requests_bulk_logbook_revision(): void
    {
        $placement = $this->placementFor($this->mentor);
        $this->pendingLogbooks($placement, 2);

        $this->actingAs($this->mentor)->post(route('mentor.logbooks.bulk_review'), [
            'logbook_ids' => $placement->logbooks()->pluck('id')->all(),
            'status' => 'rejected',
            'feedback' => 'Tambahkan foto kegiatan.',
        ])->assertRedirect();

        $notifs = SystemNotification::where('user_id', $placement->application->user_id)->get();
        $this->assertCount(1, $notifs);
        $this->assertSame('Logbook Perlu Diperbaiki', $notifs->first()->title);
        $this->assertStringContainsString('2 logbook', $notifs->first()->message);
    }

    public function test_bulk_approval_does_not_notify_student(): void
    {
        $placement = $this->placementFor($this->mentor);
        $this->pendingLogbooks($placement, 2);

        $this->actingAs($this->mentor)->post(route('mentor.logbooks.bulk_review'), [
            'logbook_ids' => $placement->logbooks()->pluck('id')->all(),
            'status' => 'approved',
        ])->assertRedirect();

        $this->assertSame(0, SystemNotification::where('user_id', $placement->application->user_id)->count());
    }

    public function test_student_notified_when_mentor_requests_final_report_revision(): void
    {
        $placement = $this->placementFor($this->mentor);
        $report = FinalReport::create([
            'placement_id' => $placement->id,
            'file_path' => 'final_reports/laporan.pdf',
            'status' => 'pending',
        ]);

        $this->actingAs($this->mentor)
            ->put(route('mentor.final_report.updateStatus', $report->id), ['status' => 'revision', 'feedback' => 'Perbaiki Bab 3'])
            ->assertRedirect();

        $this->assertDatabaseHas('system_notifications', [
            'user_id' => $placement->application->user_id,
            'title' => 'Laporan Akhir Perlu Revisi',
        ]);
    }

    public function test_student_notified_when_mentor_submits_evaluation(): void
    {
        $placement = $this->placementFor($this->mentor);

        $this->actingAs($this->mentor)
            ->post(route('mentor.evaluations.store', $placement->id), $this->validScores())
            ->assertRedirect(route('mentor.students.show', $placement->id));

        $this->assertInstanceOf(Evaluation::class, $placement->fresh()->evaluation);
        $this->assertDatabaseHas('system_notifications', [
            'user_id' => $placement->application->user_id,
            'title' => 'Nilai Magang Telah Diisi',
        ]);
    }

    public function test_logbook_detail_page_shows_photo_preview_and_same_review_buttons(): void
    {
        $placement = $this->placementFor($this->mentor);
        $log = Logbook::create([
            'placement_id' => $placement->id,
            'date' => now()->subDay()->toDateString(),
            'activity' => 'Dokumentasi rapat koordinasi',
            'attachment' => 'logbooks/bukti-rapat.jpg',
            'status' => 'pending',
        ]);

        $this->actingAs($this->mentor)->get(route('mentor.logbooks.show', $log->id))
            ->assertOk()
            ->assertSee('Dokumentasi rapat koordinasi')
            ->assertSee('<img src="'.$log->attachment_url.'"', false)
            ->assertSee(route('mentor.logbooks.updateStatus', $log->id), false)
            ->assertSeeInOrder(['Minta Revisi', 'Setujui']);
    }
}
