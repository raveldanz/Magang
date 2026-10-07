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

        $appPending->refresh();
        $this->assertEquals(ApplicationStatus::ACCEPTED, $appPending->status);
        $this->assertEquals('500.12.1/TEST-APP/2026', $appPending->letter_number);

        // Pastikan placement terbentuk otomatis saat disetujui
        $placement = Placement::where('application_id', $appPending->id)->first();
        $this->assertNotNull($placement);
    }

    /**
     * 2b. Alur resmi pending → verified → accepted: pengajuan VERIFIED dapat langsung diterima atau ditolak.
     */
    public function test_admin_can_accept_or_reject_verified_application(): void
    {
        $adminQa = User::where('email', 'admin.qa@test.local')->firstOrFail();
        $mhsPending = User::where('email', 'mhs.qa.pending@test.local')->firstOrFail();
        $app = Application::where('user_id', $mhsPending->id)->firstOrFail();

        $app->update(['status' => 'verified']);
        $this->actingAs($adminQa)->put(route('admin.applications.updateStatus', $app->id), [
            'status' => 'accepted',
            'letter_number' => '500.12.1/TEST-VER/2026',
            'letter_date' => now()->toDateString(),
        ])->assertSessionHas('success');
        $this->assertEquals(ApplicationStatus::ACCEPTED, $app->fresh()->status);
        $this->assertNotNull(Placement::where('application_id', $app->id)->first());

        // Pengajuan kedua yang juga VERIFIED, langsung ditolak
        $app = $app->replicate(['letter_number', 'letter_token']);
        $app->status = 'verified';
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
}
