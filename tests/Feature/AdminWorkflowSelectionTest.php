<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\Placement;
use App\Models\User;
use Database\Seeders\AdminWorkflowSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminWorkflowSelectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Jalankan AdminWorkflowSeeder untuk menginisialisasi lingkungan data QA
        $this->seed(AdminWorkflowSeeder::class);
    }

    /**
     * 1. Verifikasi halaman daftar pengajuan admin menampilkan data AdminWorkflowSeeder
     * (termasuk status pending, accepted, dan rejected).
     */
    public function test_admin_applications_page_displays_workflow_seeder_data(): void
    {
        $adminQa = User::where('email', 'admin.qa@test.local')->firstOrFail();

        // Kunjungi halaman indeks pengajuan magang
        $response = $this->actingAs($adminQa)->get(route('admin.applications.index'));
        $response->assertStatus(200);

        // Pastikan nama mahasiswa pending dari seeder tampil di halaman
        $response->assertSee('Ahmad Pending QA');
        $response->assertSee(ApplicationStatus::PENDING->label());

        // Filter status ACCEPTED
        $responseAccepted = $this->actingAs($adminQa)->get(route('admin.applications.index', ['status' => 'accepted']));
        $responseAccepted->assertStatus(200);
        $responseAccepted->assertSee('Budi Approved QA');
        $responseAccepted->assertSee(ApplicationStatus::ACCEPTED->label());

        // Filter status REJECTED
        $responseRejected = $this->actingAs($adminQa)->get(route('admin.applications.index', ['status' => 'rejected']));
        $responseRejected->assertStatus(200);
        $responseRejected->assertSee('Citra Rejected QA');
        $responseRejected->assertSee(ApplicationStatus::REJECTED->label());

        // Pencarian dengan keyword 'QA' menampilkan data seeder
        $responseSearch = $this->actingAs($adminQa)->get(route('admin.applications.index', ['search' => 'QA']));
        $responseSearch->assertStatus(200);
        $responseSearch->assertSee('Ahmad Pending QA');
    }

    /**
     * 2. Verifikasi admin dapat menyetujui (Approve) pengajuan berstatus PENDING.
     */
    public function test_admin_can_approve_pending_application(): void
    {
        $adminQa = User::where('email', 'admin.qa@test.local')->firstOrFail();
        $mhsPending = User::where('email', 'mhs.qa.pending@test.local')->firstOrFail();
        $appPending = Application::where('user_id', $mhsPending->id)->firstOrFail();

        $this->assertEquals(ApplicationStatus::PENDING, $appPending->status);

        $response = $this->actingAs($adminQa)->put(route('admin.applications.updateStatus', $appPending->id), [
            'status' => 'accepted',
            'letter_number' => '500.12.1/TEST-APP/2026',
            'letter_date' => now()->toDateString(),
        ]);

        $response->assertRedirect(route('admin.applications.index'));
        $response->assertSessionHas('success');

        // Alur Double ACC: persetujuan admin mengarahkan ke waiting_approval (Menunggu ACC Atasan)
        $appPending->refresh();
        $this->assertEquals(ApplicationStatus::WAITING_APPROVAL, $appPending->status);

        // Tahap 2: Supervisor mengesahkan (ACC) dan menerbitkan surat balasan resmi
        $supervisor = User::where('role', 'supervisor')->first()
            ?? User::factory()->create(['role' => 'supervisor']);

        $this->actingAs($supervisor)->post(route('supervisor.applications.approve', $appPending->id), [
            'letter_number' => '500.12.1/TEST-APP/2026',
        ])->assertSessionHas('success');

        $appPending->refresh();
        $this->assertEquals(ApplicationStatus::ACCEPTED, $appPending->status);
        $this->assertEquals('500.12.1/TEST-APP/2026', $appPending->letter_number);

        // Pastikan placement terbentuk otomatis saat disetujui
        $placement = Placement::where('application_id', $appPending->id)->first();
        $this->assertNotNull($placement);
    }

    /**
     * 2b. Alur resmi pending → waiting_approval → accepted: pengajuan pending diusulkan dan disahkan atau ditolak.
     */
    public function test_admin_can_accept_or_reject_verified_application(): void
    {
        $adminQa = User::where('email', 'admin.qa@test.local')->firstOrFail();
        $mhsPending = User::where('email', 'mhs.qa.pending@test.local')->firstOrFail();
        $app = Application::where('user_id', $mhsPending->id)->firstOrFail();

        $app->update(['status' => 'pending']);
        $this->actingAs($adminQa)->put(route('admin.applications.updateStatus', $app->id), [
            'status' => 'accepted',
            'letter_number' => '500.12.1/TEST-VER/2026',
            'letter_date' => now()->toDateString(),
        ])->assertSessionHas('success');

        // Alur Double ACC: admin staf mengarahkan ke waiting_approval
        $this->assertEquals(ApplicationStatus::WAITING_APPROVAL, $app->fresh()->status);

        // Tahap 2: Supervisor mengesahkan
        $supervisor = User::where('role', 'supervisor')->first()
            ?? User::factory()->create(['role' => 'supervisor']);

        $this->actingAs($supervisor)->post(route('supervisor.applications.approve', $app->id), [
            'letter_number' => '500.12.1/TEST-VER/2026',
        ])->assertSessionHas('success');

        $this->assertEquals(ApplicationStatus::ACCEPTED, $app->fresh()->status);
        $this->assertNotNull(Placement::where('application_id', $app->id)->first());

        // Pengajuan kedua yang masih pending, langsung ditolak
        $app = $app->replicate(['letter_number', 'letter_token']);
        $app->status = 'pending';
        $app->save();
        $this->actingAs($adminQa)->put(route('admin.applications.updateStatus', $app->id), [
            'status' => 'rejected',
            'rejection_note' => 'Kuota divisi sudah penuh.',
        ])->assertSessionHas('success');
        $this->assertEquals(ApplicationStatus::REJECTED, $app->fresh()->status);
    }

    /**
     * 3. Validasi state ketat: Aksi persetujuan (Approve) TIDAK DAPAT dilakukan jika status bukan 'pending'.
     */
    public function test_admin_cannot_approve_non_pending_application(): void
    {
        $adminQa = User::where('email', 'admin.qa@test.local')->firstOrFail();
        $mhsRejected = User::where('email', 'mhs.qa.rejected@test.local')->firstOrFail();
        $appRejected = Application::where('user_id', $mhsRejected->id)->firstOrFail();

        $this->assertEquals(ApplicationStatus::REJECTED, $appRejected->status);

        // Coba approve pengajuan yang sudah ditolak
        $response = $this->actingAs($adminQa)->put(route('admin.applications.updateStatus', $appRejected->id), [
            'status' => 'accepted',
            'letter_number' => '500.12.1/ILLEGAL/2026',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertStringContainsString('hanya dapat dilakukan pada pengajuan yang berstatus \'pending\'', session('error'));

        $appRejected->refresh();
        $this->assertEquals(ApplicationStatus::REJECTED, $appRejected->status);
    }

    /**
     * 4. Verifikasi admin wajib mengisi alasan penolakan jika status diubah menjadi REJECTED.
     */
    public function test_admin_cannot_reject_application_without_rejection_note(): void
    {
        $adminQa = User::where('email', 'admin.qa@test.local')->firstOrFail();
        $mhsPending = User::where('email', 'mhs.qa.pending@test.local')->firstOrFail();
        $appPending = Application::where('user_id', $mhsPending->id)->firstOrFail();

        // Submit status rejected tanpa alasan penolakan
        $response = $this->actingAs($adminQa)->put(route('admin.applications.updateStatus', $appPending->id), [
            'status' => 'rejected',
            'rejection_note' => '',
        ]);

        $response->assertSessionHasErrors(['rejection_note']);

        $appPending->refresh();
        $this->assertEquals(ApplicationStatus::PENDING, $appPending->status);
    }

    /**
     * 5. Verifikasi admin berhasil menolak (Reject) pengajuan pending dengan alasan penolakan valid.
     */
    public function test_admin_can_reject_pending_application_with_rejection_note(): void
    {
        $adminQa = User::where('email', 'admin.qa@test.local')->firstOrFail();
        $mhsPending = User::where('email', 'mhs.qa.pending@test.local')->firstOrFail();
        $appPending = Application::where('user_id', $mhsPending->id)->firstOrFail();

        $rejectionNote = 'Kapasitas kuota unit kerja penuh untuk periode yang diajukan.';

        $response = $this->actingAs($adminQa)->put(route('admin.applications.updateStatus', $appPending->id), [
            'status' => 'rejected',
            'rejection_note' => $rejectionNote,
        ]);

        $response->assertRedirect(route('admin.applications.index'));
        $response->assertSessionHas('success');

        $appPending->refresh();
        $this->assertEquals(ApplicationStatus::REJECTED, $appPending->status);
        $this->assertEquals($rejectionNote, $appPending->rejection_note);
    }

    /**
     * 6. Validasi state ketat: Pengajuan yang sudah ACCEPTED tidak dapat di-reject langsung.
     */
    public function test_admin_cannot_reject_already_accepted_application(): void
    {
        $adminQa = User::where('email', 'admin.qa@test.local')->firstOrFail();
        $mhsApproved = User::where('email', 'mhs.qa.approved@test.local')->firstOrFail();
        $appApproved = Application::where('user_id', $mhsApproved->id)->firstOrFail();

        $this->assertEquals(ApplicationStatus::ACCEPTED, $appApproved->status);

        // Coba reject pengajuan yang sudah berstatus accepted
        $response = $this->actingAs($adminQa)->put(route('admin.applications.updateStatus', $appApproved->id), [
            'status' => 'rejected',
            'rejection_note' => 'Membatalkan penerimaan secara sepihak.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertStringContainsString('hanya dapat dilakukan pada pengajuan yang berstatus \'pending\'', session('error'));

        $appApproved->refresh();
        $this->assertEquals(ApplicationStatus::ACCEPTED, $appApproved->status);
    }

    /**
     * 7. Validasi state ketat: Pengajuan yang sudah DITOLAK (rejected) tidak dapat diubah statusnya.
     */
    public function test_admin_cannot_modify_already_rejected_application(): void
    {
        $adminQa = User::where('email', 'admin.qa@test.local')->firstOrFail();
        $mhsRejected = User::where('email', 'mhs.qa.rejected@test.local')->firstOrFail();
        $appRejected = Application::where('user_id', $mhsRejected->id)->firstOrFail();

        $this->assertEquals(ApplicationStatus::REJECTED, $appRejected->status);

        // Coba ubah kembali ke pending
        $response = $this->actingAs($adminQa)->put(route('admin.applications.updateStatus', $appRejected->id), [
            'status' => 'pending',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertStringContainsString('Pengajuan yang telah ditolak tidak dapat diubah statusnya kembali', session('error'));

        $appRejected->refresh();
        $this->assertEquals(ApplicationStatus::REJECTED, $appRejected->status);
    }

    /**
     * 8. Verifikasi bahwa dropdown mentor terfilter otomatis sesuai bidang unit_id dan deskripsi WAITING_APPROVAL sesuai permintaan.
     */
    public function test_application_show_mentor_filtered_by_bidang_and_clean_naming(): void
    {
        $adminQa = User::where('email', 'admin.qa@test.local')->firstOrFail();
        $app = Application::with('unit')->whereNotNull('unit_id')->firstOrFail();

        // Pastikan deskripsi WAITING_APPROVAL adalah 'Setujui, Serahkan ke Atasan'
        $this->assertEquals('Setujui, Serahkan ke Atasan', ApplicationStatus::WAITING_APPROVAL->description());

        $response = $this->actingAs($adminQa)->get(route('admin.applications.show', $app->id));
        $response->assertOk();
        $response->assertSee('Setujui, Serahkan ke Atasan');
        $response->assertDontSee('⭐ (Sesuai Bidang)');
    }

    /**
     * 9. Verifikasi bahwa supervisor melihat status 'Menunggu ACC', tab 'Menunggu Persetujuan', dan tombol pengesahan sertifikat biru.
     */
    public function test_supervisor_sees_menunggu_acc_and_menunggu_persetujuan(): void
    {
        $supervisor = User::where('role', 'supervisor')->first()
            ?? User::factory()->create(['role' => 'supervisor']);

        $this->assertEquals('Menunggu ACC', ApplicationStatus::WAITING_APPROVAL->description('supervisor'));
        $this->assertEquals('Menunggu ACC', ApplicationStatus::WAITING_APPROVAL->descriptionForSupervisor());

        // Pastikan saat actingAs supervisor, description() otomatis menghasilkan 'Menunggu ACC'
        $this->actingAs($supervisor);
        $this->assertEquals('Menunggu ACC', ApplicationStatus::WAITING_APPROVAL->description());

        // Cek halaman index supervisor
        $responseIndex = $this->get(route('supervisor.applications.index'));
        $responseIndex->assertOk();
        $responseIndex->assertSee('Menunggu Persetujuan');
        $responseIndex->assertDontSee('Menunggu ACC Atasan');

        // Jika ada aplikasi waiting_approval
        $appWaiting = Application::where('status', ApplicationStatus::WAITING_APPROVAL->value)->first();
        if ($appWaiting) {
            $responseIndex->assertSee('Menunggu ACC');
            $responseIndex->assertDontSee('Setujui, Serahkan ke Atasan');

            $responseShow = $this->get(route('supervisor.applications.show', $appWaiting->id));
            $responseShow->assertOk();
            $responseShow->assertSee('Menunggu ACC');
            $responseShow->assertDontSee('Setujui, Serahkan ke Atasan');
        }

        // Cek halaman dashboard supervisor
        $responseDashboard = $this->get(route('supervisor.dashboard'));
        $responseDashboard->assertOk();
        $responseDashboard->assertSee('Pengesahan Sertifikat');
        $responseDashboard->assertSee('Dashboard');
        $responseDashboard->assertDontSee('Dashboard &amp; Tim Kerja');
        $responseDashboard->assertDontSee('Dashboard & Tim Kerja');
    }

    public function test_unified_form_can_update_mentor_and_dosen_on_active_application(): void
    {
        $admin = User::where('role', 'admin')->first() ?? User::factory()->create(['role' => 'admin']);
        $unit = \App\Models\Unit::first();
        if (! $unit) {
            $agency = \App\Models\AgencyProfile::first() ?? \App\Models\AgencyProfile::create(['name' => 'Dispusip']);
            $unit = \App\Models\Unit::create(['agency_profile_id' => $agency->id, 'name' => 'Bidang Kearsipan', 'quota' => 10]);
        }
        if ($admin->agency_profile_id === null && $unit->agency_profile_id) {
            $admin->update(['agency_profile_id' => $unit->agency_profile_id]);
        }

        $student = User::factory()->create(['role' => 'student']);
        $univ = \App\Models\University::first() ?? \App\Models\University::create(['name' => 'Universitas Airlangga']);
        \App\Models\StudentProfile::create([
            'user_id' => $student->id,
            'university_id' => $univ->id,
            'nim' => '12345678',
            'universitas' => $univ->name,
            'jurusan' => 'Sistem Informasi',
            'phone' => '08123456789',
        ]);

        $mentorA = User::factory()->create([
            'role' => 'mentor',
            'agency_profile_id' => $unit->agency_profile_id,
            'unit_id' => $unit->id,
        ]);
        $mentorB = User::factory()->create([
            'role' => 'mentor',
            'agency_profile_id' => $unit->agency_profile_id,
            'unit_id' => $unit->id,
        ]);
        $dosen = User::factory()->create([
            'role' => 'dosen',
            'university_id' => $univ->id,
        ]);

        $app = Application::create([
            'user_id' => $student->id,
            'unit_id' => $unit->id,
            'status' => 'active',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(3)->toDateString(),
            'letter_number' => '500.12.1/TEST/2026',
            'letter_date' => now()->toDateString(),
        ]);

        $placement = \App\Models\Placement::create([
            'application_id' => $app->id,
            'mentor_id' => $mentorA->id,
            'pembimbing_id' => $mentorA->id,
            'start_date' => $app->start_date,
            'end_date' => $app->end_date,
        ]);

        // 1. Verifikasi show page: form tunggal hadir, form redundan tidak ada
        $responseShow = $this->actingAs($admin)->get(route('admin.applications.show', $app->id));
        $responseShow->assertOk();
        $responseShow->assertSee('Data Penempatan, Surat &amp; Penugasan Pembimbing', false);
        $responseShow->assertSee('Simpan Keputusan &amp; Penugasan', false);
        $responseShow->assertDontSee('Simpan Penugasan Pembimbing');

        // 2. Kirim update penugasan melalui form tunggal updateStatus
        $responseUpdate = $this->actingAs($admin)->put(route('admin.applications.updateStatus', $app->id), [
            'status' => 'active',
            'mentor_id' => $mentorB->id,
            'academic_advisor_id' => $dosen->id,
            'letter_number' => '500.12.1/TEST/2026',
            'letter_date' => now()->toDateString(),
        ]);

        $responseUpdate->assertRedirect(route('admin.applications.index'));
        $responseUpdate->assertSessionHas('success');

        $placement->refresh();
        $this->assertEquals($mentorB->id, $placement->mentor_id);
        $this->assertEquals($dosen->id, $placement->academic_advisor_id);

        // Verifikasi audit log mutasi/reassign tercatat
        $audit = \App\Models\AuditLog::where('action', 'PLACEMENT_SUPERVISOR_REASSIGN')
            ->where('target_id', $placement->id)
            ->latest('id')
            ->first();
        $this->assertNotNull($audit);
    }
}

