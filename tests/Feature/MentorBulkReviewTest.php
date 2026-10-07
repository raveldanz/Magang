<?php

namespace Tests\Feature;

use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\Logbook;
use App\Models\Placement;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MentorBulkReviewTest extends TestCase
{
    use RefreshDatabase;

    private function setupScenario(): array
    {
        $agency = AgencyProfile::create(['agency_name' => 'Dinas Komunikasi dan Informatika']);
        $unit = Unit::create([
            'agency_profile_id' => $agency->id,
            'name' => 'Bidang Aplikasi Informatika',
            'quota' => 10,
        ]);

        $assignedMentor = User::factory()->create([
            'role' => 'mentor',
            'name' => 'Mentor Utama',
            'agency_profile_id' => $agency->id,
        ]);

        $otherMentor = User::factory()->create([
            'role' => 'mentor',
            'name' => 'Mentor Lain Instansi Sama',
            'agency_profile_id' => $agency->id,
        ]);

        $unitHead = User::factory()->create([
            'role' => 'mentor',
            'name' => 'Kepala Unit',
            'agency_profile_id' => $agency->id,
        ]);
        $unit->update(['head_user_id' => $unitHead->id]);

        $student = User::factory()->create([
            'role' => 'mahasiswa',
            'name' => 'Siti Mahasiswi',
        ]);
        $student->studentProfile()->create([
            'nim' => '22081010099',
            'universitas' => 'Universitas Airlangga',
            'jurusan' => 'Sistem Informasi',
            'phone' => '08123456789',
        ]);

        $application = Application::create([
            'user_id' => $student->id,
            'unit_id' => $unit->id,
            'status' => 'active',
            'start_date' => now()->subWeeks(2)->toDateString(),
            'end_date' => now()->addWeeks(2)->toDateString(),
        ]);

        $placement = Placement::create([
            'application_id' => $application->id,
            'mentor_id' => $assignedMentor->id,
        ]);

        $logbook1 = Logbook::create([
            'placement_id' => $placement->id,
            'date' => now()->subDays(2)->toDateString(),
            'activity' => 'Mengerjakan modul autentikasi',
            'status' => 'pending',
        ]);

        $logbook2 = Logbook::create([
            'placement_id' => $placement->id,
            'date' => now()->subDays(1)->toDateString(),
            'activity' => 'Melakukan testing unit testing',
            'status' => 'pending',
        ]);

        return compact('agency', 'unit', 'assignedMentor', 'otherMentor', 'unitHead', 'student', 'placement', 'logbook1', 'logbook2');
    }

    public function test_assigned_mentor_can_bulk_approve_logbooks(): void
    {
        $data = $this->setupScenario();

        $response = $this->actingAs($data['assignedMentor'])
            ->post(route('mentor.logbooks.bulk_review'), [
                'logbook_ids' => [$data['logbook1']->id, $data['logbook2']->id],
                'status' => 'approved',
                'feedback' => 'Bagus sekali, teruskan kinerjanya!',
            ]);

        $response->assertRedirect();
        $this->assertSame('approved', $data['logbook1']->fresh()->status);
        $this->assertSame('approved', $data['logbook2']->fresh()->status);
        $this->assertSame('Bagus sekali, teruskan kinerjanya!', $data['logbook1']->fresh()->feedback);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'MENTOR_LOGBOOK_REVIEW',
            'target_type' => 'Logbook',
            'target_id' => $data['logbook1']->id,
        ]);
    }

    public function test_assigned_mentor_can_bulk_reject_logbooks(): void
    {
        $data = $this->setupScenario();

        $response = $this->actingAs($data['assignedMentor'])
            ->post(route('mentor.logbooks.bulk_review'), [
                'logbook_ids' => [$data['logbook1']->id, $data['logbook2']->id],
                'status' => 'rejected',
                'feedback' => 'Mohon sertakan bukti lampiran foto kegiatan.',
            ]);

        $response->assertRedirect();
        $this->assertSame('rejected', $data['logbook1']->fresh()->status);
        $this->assertSame('rejected', $data['logbook2']->fresh()->status);
        $this->assertSame('Mohon sertakan bukti lampiran foto kegiatan.', $data['logbook1']->fresh()->feedback);
    }

    public function test_other_mentor_in_same_agency_gets_403_when_bulk_reviewing(): void
    {
        $data = $this->setupScenario();

        $response = $this->actingAs($data['otherMentor'])
            ->post(route('mentor.logbooks.bulk_review'), [
                'logbook_ids' => [$data['logbook1']->id, $data['logbook2']->id],
                'status' => 'approved',
                'feedback' => 'Coba meng-ACC sembarangan',
            ]);

        $response->assertForbidden();
        // Pastikan status logbook tetap pending dan tidak berubah
        $this->assertSame('pending', $data['logbook1']->fresh()->status);
        $this->assertSame('pending', $data['logbook2']->fresh()->status);
    }

    public function test_unit_head_can_bulk_review_logbooks_when_mentor_not_assigned(): void
    {
        $data = $this->setupScenario();

        // Kosongkan penugasan mentor agar masuk skenario fallback Kepala Unit
        $data['placement']->update(['mentor_id' => null, 'pembimbing_id' => null]);

        $response = $this->actingAs($data['unitHead'])
            ->post(route('mentor.logbooks.bulk_review'), [
                'logbook_ids' => [$data['logbook1']->id, $data['logbook2']->id],
                'status' => 'approved',
                'feedback' => 'Disetujui sementara oleh Kepala Unit.',
            ]);

        $response->assertRedirect();
        $this->assertSame('approved', $data['logbook1']->fresh()->status);
        $this->assertSame('approved', $data['logbook2']->fresh()->status);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'UNIT_HEAD_LOGBOOK_FALLBACK_REVIEW',
            'target_type' => 'Logbook',
            'target_id' => $data['logbook1']->id,
        ]);
    }
}
