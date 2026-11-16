<?php

namespace Tests\Feature;

use App\Models\AgencyProfile;
use App\Models\SystemNotification;
use App\Models\Unit;
use App\Models\University;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationCenterTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    private User $student;

    private User $supervisor;

    private User $mentor;

    private User $lecturer;

    private User $universityUser;

    private AgencyProfile $agency;

    private Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = AgencyProfile::create([
            'agency_name' => 'Dinas Perpustakaan dan Kearsipan Kota Surabaya',
            'acronym' => 'Dispusip',
            'address' => 'Jl. Rungkut Asri Tengah No. 5-7, Surabaya',
        ]);

        $this->unit = Unit::create([
            'agency_profile_id' => $this->agency->id,
            'name' => 'Bidang Pembinaan dan Pengelolaan Perpustakaan',
            'quota' => 15,
        ]);

        $univ = University::create([
            'name' => 'Universitas Negeri Surabaya',
            'acronym' => 'UNESA',
            'address' => 'Ketintang, Surabaya',
            'is_verified' => true,
        ]);

        $this->superAdmin = User::factory()->create([
            'name' => 'Admin Utama',
            'role' => 'super_admin',
            'agency_profile_id' => null,
        ]);

        $this->supervisor = User::factory()->create([
            'name' => 'Supervisor Dispusip',
            'role' => 'supervisor',
            'agency_profile_id' => $this->agency->id,
        ]);

        $this->mentor = User::factory()->create([
            'name' => 'Mentor Dispusip',
            'role' => 'mentor',
            'agency_profile_id' => $this->agency->id,
        ]);

        $this->lecturer = User::factory()->create([
            'name' => 'Dosen Pembimbing',
            'role' => 'dosen',
            'university_id' => $univ->id,
        ]);

        $this->universityUser = User::factory()->create([
            'name' => 'Admin UNESA',
            'role' => 'universitas',
            'university_id' => $univ->id,
        ]);

        $this->student = User::factory()->create([
            'name' => 'Mahasiswa Test',
            'role' => 'mahasiswa',
            'university_id' => $univ->id,
        ]);
    }

    public function test_all_six_roles_can_access_notification_center_with_http_200(): void
    {
        $roles = [
            $this->superAdmin,
            $this->supervisor,
            $this->mentor,
            $this->lecturer,
            $this->universityUser,
            $this->student,
        ];

        foreach ($roles as $user) {
            $response = $this->actingAs($user)->get(route('notifications.index'));
            $response->assertOk();
            $response->assertSee('Pusat Pemberitahuan');
        }
    }

    public function test_notification_category_filters_work_cleanly(): void
    {
        // 1. Buat beberapa notifikasi contoh
        SystemNotification::create([
            'user_id' => $this->superAdmin->id,
            'title' => 'Pengajuan Mendesak',
            'message' => 'Segera tindak lanjuti berkas mahasiswa.',
            'type' => 'urgent',
            'category' => 'application',
            'action_url' => route('admin.applications.index'),
            'action_label' => 'Tinjau',
        ]);

        SystemNotification::create([
            'user_id' => $this->superAdmin->id,
            'title' => 'Laporan Evaluasi Selesai',
            'message' => 'Transkrip nilai magang telah diproses.',
            'type' => 'info',
            'category' => 'evaluation',
            'action_url' => route('admin.dashboard'),
            'action_label' => 'Lihat',
        ]);

        // Filter tab: all
        $resAll = $this->actingAs($this->superAdmin)->get(route('notifications.index', ['category' => 'all']));
        $resAll->assertOk();
        $allTitles = collect($resAll->viewData('notifications'))->pluck('title')->all();
        $this->assertContains('Pengajuan Mendesak', $allTitles);
        $this->assertContains('Laporan Evaluasi Selesai', $allTitles);

        // Filter tab: urgent
        $resUrgent = $this->actingAs($this->superAdmin)->get(route('notifications.index', ['category' => 'urgent']));
        $resUrgent->assertOk();
        $urgentTitles = collect($resUrgent->viewData('notifications'))->pluck('title')->all();
        $this->assertContains('Pengajuan Mendesak', $urgentTitles);
        $this->assertNotContains('Laporan Evaluasi Selesai', $urgentTitles);

        // Filter tab: evaluation
        $resEval = $this->actingAs($this->superAdmin)->get(route('notifications.index', ['category' => 'evaluation']));
        $resEval->assertOk();
        $evalTitles = collect($resEval->viewData('notifications'))->pluck('title')->all();
        $this->assertContains('Laporan Evaluasi Selesai', $evalTitles);
        $this->assertNotContains('Pengajuan Mendesak', $evalTitles);
    }

    public function test_user_can_mark_database_notification_as_read(): void
    {
        $notif = SystemNotification::create([
            'user_id' => $this->student->id,
            'title' => 'Pemberitahuan Khusus Mahasiswa',
            'message' => 'Selamat datang di sistem informasi magang.',
            'type' => 'info',
            'category' => 'general',
        ]);

        $this->assertNull($notif->read_at);

        $response = $this->actingAs($this->student)->post(route('notifications.read', "db_{$notif->id}"));
        $response->assertRedirect();

        $notif->refresh();
        $this->assertNotNull($notif->read_at);
    }

    public function test_user_can_mark_all_notifications_as_read_and_resets_unread_dot(): void
    {
        SystemNotification::create([
            'user_id' => $this->student->id,
            'title' => 'Notif 1',
            'message' => 'Pesan 1',
            'type' => 'info',
            'category' => 'general',
        ]);
        SystemNotification::create([
            'user_id' => $this->student->id,
            'title' => 'Notif 2',
            'message' => 'Pesan 2',
            'type' => 'info',
            'category' => 'general',
        ]);

        $this->assertTrue(NotificationService::hasUnreadDot($this->student));

        $response = $this->actingAs($this->student)->post(route('notifications.mark_all_read'));
        $response->assertRedirect();

        $this->student->refresh();
        $this->assertNotNull($this->student->last_notification_read_at);

        // Setelah tandai semua dibaca, dot tidak boleh menyala lagi untuk item info biasa
        $this->assertFalse(NotificationService::hasUnreadDot($this->student));
    }
}
