<?php

namespace Tests\Feature;

use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\InternshipProject;
use App\Models\Placement;
use App\Models\ProjectComment;
use App\Models\ProjectLike;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProjectInteractionsAndCatalogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    protected function createSampleProject(bool $isPublic = true, ?User $student = null): array
    {
        $student = $student ?? User::factory()->create(['role' => 'mahasiswa', 'name' => 'Ahmad Mahasiswa']);
        $mentor = User::factory()->create(['role' => 'mentor', 'name' => 'Budi Mentor Dinas']);
        $advisor = User::factory()->create(['role' => 'dosen', 'name' => 'Dr. Suwandi']);

        $agency = AgencyProfile::create([
            'agency_name' => 'Dinas Perpustakaan dan Kearsipan Kota Surabaya',
            'email' => 'dispusip@surabaya.go.id',
            'phone' => '031-1234567',
            'address' => 'Jl. Rungkut Asri Timur No. 1 Surabaya',
        ]);

        $unit = Unit::create([
            'agency_profile_id' => $agency->id,
            'name' => 'Bidang Layanan Otomasi Perpustakaan',
            'quota' => 10,
        ]);

        $application = Application::create([
            'user_id' => $student->id,
            'unit_id' => $unit->id,
            'status' => 'active',
            'start_date' => now()->subMonths(3)->toDateString(),
            'end_date' => now()->toDateString(),
        ]);

        $placement = Placement::create([
            'application_id' => $application->id,
            'mentor_id' => $mentor->id,
            'academic_advisor_id' => $advisor->id,
            'status' => 'active',
        ]);

        $project = InternshipProject::create([
            'placement_id' => $placement->id,
            'title' => 'Sistem Otomasi Peminjaman Buku Mandiri',
            'category' => 'teknologi',
            'is_public' => $isPublic,
            'description' => 'Sistem katalog dan sirkulasi buku berbasis RFID untuk masyarakat.',
            'community_benefit' => 'Mempercepat antrean peminjaman buku di perpustakaan kota.',
            'photo_path' => 'internship_projects/sample.jpg',
            'demo_url' => 'https://otomasi.surabaya.go.id',
            'likes_count' => 0,
            'comments_count' => 0,
            'views_count' => 0,
        ]);

        return compact('student', 'mentor', 'advisor', 'agency', 'unit', 'application', 'placement', 'project');
    }

    public function test_public_user_can_access_project_catalog_and_view_public_projects()
    {
        $data1 = $this->createSampleProject(true);
        $data2 = $this->createSampleProject(false); // privat

        $response = $this->get(route('public.projects.index'));
        $response->assertStatus(200);
        $response->assertSee('Katalog Karya Inovasi dan Proyek Dinas');
        $response->assertSee('Sistem Otomasi Peminjaman Buku Mandiri');
        $response->assertSee('Tahun Perjalanan');

        // Proyek publik harus tampil
        $response->assertSee($data1['project']->title);

        // Proyek privat tidak boleh tampil di katalog publik
        $data2['project']->update(['title' => 'Proyek Rahasia Internal Server Dinas']);
        $resp2 = $this->get(route('public.projects.index'));
        $resp2->assertDontSee('Proyek Rahasia Internal Server Dinas');
    }

    public function test_guest_can_like_and_unlike_project_via_ajax()
    {
        $data = $this->createSampleProject(true);
        $project = $data['project'];

        // 1. Guest Like
        $likeResp = $this->postJson(route('projects.like', $project->id));
        $likeResp->assertStatus(200)
            ->assertJson([
                'success' => true,
                'liked' => true,
                'likes_count' => 1,
            ]);

        $this->assertEquals(1, $project->fresh()->likes_count);
        $this->assertDatabaseHas('project_likes', [
            'internship_project_id' => $project->id,
            'user_id' => null,
        ]);

        // 2. Guest Unlike (Toggle)
        $unlikeResp = $this->postJson(route('projects.like', $project->id));
        $unlikeResp->assertStatus(200)
            ->assertJson([
                'success' => true,
                'liked' => false,
                'likes_count' => 0,
            ]);

        $this->assertEquals(0, $project->fresh()->likes_count);
    }

    public function test_authenticated_user_can_like_and_unlike_project()
    {
        $data = $this->createSampleProject(true);
        $project = $data['project'];
        $user = User::factory()->create(['role' => 'mahasiswa', 'name' => 'Siti Reviewer']);

        $resp = $this->actingAs($user)->postJson(route('projects.like', $project->id));
        $resp->assertStatus(200)
            ->assertJson([
                'success' => true,
                'liked' => true,
                'likes_count' => 1,
            ]);

        $this->assertDatabaseHas('project_likes', [
            'internship_project_id' => $project->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_guest_can_post_clean_comment_and_live_filter_blocks_profanity()
    {
        $data = $this->createSampleProject(true);
        $project = $data['project'];

        // 1. Kirim komentar kotor / kasar (Harus Ditolak dengan 422 JSON)
        $dirtyResp = $this->postJson(route('projects.comments.store', $project->id), [
            'author_name' => 'Orang Jahat',
            'comment' => 'Dasar sistem anjing goblok ga guna!',
        ]);

        $dirtyResp->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
        $this->assertDatabaseMissing('project_comments', [
            'internship_project_id' => $project->id,
            'author_name' => 'Orang Jahat',
        ]);

        // 2. Kirim komentar kata aman yang berpotensi Scunthorpe Problem (Harus Lolos)
        $safeResp = $this->post(route('projects.comments.store', $project->id), [
            'author_name' => 'Warga Pantai Surabaya',
            'comment' => 'Karya ini sangat bermanfaat untuk menghimpun data asuransi perpustakaan kota Surabaya!',
        ]);

        $safeResp->assertRedirect();
        $this->assertDatabaseHas('project_comments', [
            'internship_project_id' => $project->id,
            'author_name' => 'Warga Pantai Surabaya',
            'is_guest' => true,
        ]);

        $this->assertEquals(1, $project->fresh()->comments_count);
    }

    public function test_super_admin_and_admin_dinas_can_delete_inappropriate_comment()
    {
        $data = $this->createSampleProject(true);
        $project = $data['project'];

        $comment = ProjectComment::create([
            'internship_project_id' => $project->id,
            'author_name' => 'Pengunjung Spam',
            'author_role' => 'Tamu',
            'is_guest' => true,
            'comment' => 'Ini tautan judi online slot gacor',
            'likes_count' => 0,
        ]);
        $project->increment('comments_count');

        // User biasa tidak boleh menghapus
        $randomUser = User::factory()->create(['role' => 'mahasiswa']);
        $unauthResp = $this->actingAs($randomUser)->deleteJson(route('projects.comments.destroy', $comment->id));
        $unauthResp->assertStatus(403);

        // Super Admin menghapus komentar
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $deleteResp = $this->actingAs($superAdmin)->deleteJson(route('projects.comments.destroy', $comment->id));
        $deleteResp->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseMissing('project_comments', ['id' => $comment->id]);
        $this->assertEquals(0, $project->fresh()->comments_count);
    }

    public function test_guest_and_user_can_like_comment()
    {
        $data = $this->createSampleProject(true);
        $project = $data['project'];

        $comment = ProjectComment::create([
            'internship_project_id' => $project->id,
            'author_name' => 'Budi Santoso',
            'author_role' => 'Praktisi',
            'is_guest' => true,
            'comment' => 'Sangat inovatif dan solutif!',
            'likes_count' => 0,
        ]);

        // Guest like komentar
        $likeResp = $this->postJson(route('projects.comments.like', $comment->id));
        $likeResp->assertStatus(200)
            ->assertJson([
                'success' => true,
                'liked' => true,
                'likes_count' => 1,
            ]);

        $this->assertEquals(1, $comment->fresh()->likes_count);
    }

    public function test_private_project_is_guarded_and_returns_403_for_public_users()
    {
        $data = $this->createSampleProject(false); // privat
        $project = $data['project'];

        // Guest mengakses detail proyek privat -> 403 Forbidden
        $guestResp = $this->get(route('projects.show', $project->id));
        $guestResp->assertStatus(403);

        // Random student mengakses detail proyek privat -> 403 Forbidden
        $stranger = User::factory()->create(['role' => 'mahasiswa']);
        $strangerResp = $this->actingAs($stranger)->get(route('projects.show', $project->id));
        $strangerResp->assertStatus(403);

        // Pemilik proyek dapat mengakses -> 200 OK
        $ownerResp = $this->actingAs($data['student'])->get(route('projects.show', $project->id));
        $ownerResp->assertStatus(200);

        // Super Admin dapat mengakses -> 200 OK
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $adminResp = $this->actingAs($superAdmin)->get(route('projects.show', $project->id));
        $adminResp->assertStatus(200);
    }

    public function test_student_can_set_project_as_private_during_create_and_update()
    {
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $mentor = User::factory()->create(['role' => 'mentor']);
        $advisor = User::factory()->create(['role' => 'dosen']);

        $agency = AgencyProfile::create([
            'agency_name' => 'Dinas Perpustakaan dan Kearsipan Kota Surabaya',
            'email' => 'dispusip@surabaya.go.id',
            'phone' => '031-1234567',
            'address' => 'Jl. Rungkut Asri Timur No. 1 Surabaya',
        ]);

        $unit = Unit::create([
            'agency_profile_id' => $agency->id,
            'name' => 'Bidang Preservasi Naskah',
            'quota' => 5,
        ]);

        $application = Application::create([
            'user_id' => $student->id,
            'unit_id' => $unit->id,
            'status' => 'active',
            'start_date' => now()->subMonths(2)->toDateString(),
            'end_date' => now()->toDateString(),
        ]);

        $placement = Placement::create([
            'application_id' => $application->id,
            'mentor_id' => $mentor->id,
            'academic_advisor_id' => $advisor->id,
            'status' => 'active',
        ]);

        // Simpan proyek dengan is_public = 0 (Privat)
        $storeResp = $this->actingAs($student)->post(route('student.projects.store'), [
            'title' => 'Preservasi Naskah Kuno Rahasia',
            'category' => 'preservasi',
            'is_public' => '0',
            'description' => 'Restorasi naskah kuno berharga.',
            'community_benefit' => 'Menyelamatkan warisan sejarah kota.',
            'photo' => UploadedFile::fake()->image('cover.jpg'),
        ]);

        $storeResp->assertRedirect(route('student.final_report.index'));
        $project = InternshipProject::where('title', 'Preservasi Naskah Kuno Rahasia')->first();
        $this->assertNotNull($project);
        $this->assertFalse($project->is_public);

        // Update menjadi Publik
        $updateResp = $this->actingAs($student)->put(route('student.projects.update', $project->id), [
            'title' => 'Preservasi Naskah Kuno Terbuka',
            'category' => 'preservasi',
            'is_public' => '1',
            'description' => 'Restorasi naskah kuno terbuka untuk umum.',
            'community_benefit' => 'Menyelamatkan warisan sejarah kota untuk edukasi publik.',
        ]);

        $updateResp->assertRedirect(route('student.final_report.index'));
        $this->assertTrue($project->fresh()->is_public);
    }

    public function test_blocks_javanese_and_english_profanity_including_evasion()
    {
        $data = $this->createSampleProject(true);
        $project = $data['project'];

        // Umpatan Jawa Timuran "jancok"
        $respJancok = $this->postJson(route('projects.comments.store', $project->id), [
            'author_name' => 'Warga Jatim',
            'comment' => 'Dasar web jancok!',
        ]);
        $respJancok->assertStatus(422)->assertJson(['success' => false]);

        // Umpatan Jawa "asu"
        $respAsu = $this->postJson(route('projects.comments.store', $project->id), [
            'author_name' => 'Warga Jatim',
            'comment' => 'Sistem ini asu!',
        ]);
        $respAsu->assertStatus(422)->assertJson(['success' => false]);

        // Umpatan Inggris "fuck"
        $respFuck = $this->postJson(route('projects.comments.store', $project->id), [
            'author_name' => 'Bule',
            'comment' => 'What the fuck is this?',
        ]);
        $respFuck->assertStatus(422)->assertJson(['success' => false]);

        // Evasion dengan titik/pemisah "j.a.n.c.o.k"
        $respEvasion = $this->postJson(route('projects.comments.store', $project->id), [
            'author_name' => 'Troll',
            'comment' => 'Aplikasi j.a.n.c.o.k!',
        ]);
        $respEvasion->assertStatus(422)->assertJson(['success' => false]);
    }

    public function test_super_admin_and_supervisor_can_toggle_project_visibility()
    {
        $data = $this->createSampleProject(true);
        $project = $data['project'];

        $superAdmin = User::factory()->create(['role' => 'super_admin', 'name' => 'Super Administrator']);
        $supervisor = User::factory()->create(['role' => 'supervisor', 'name' => 'Ibu Supervisor']);
        $student = $data['student'];

        // Mahasiswa tidak berhak toggle visibility
        $respStudent = $this->actingAs($student)->post(route('projects.toggle_visibility', $project->id));
        $respStudent->assertStatus(403);

        // Supervisor toggle visibility: publik -> privat
        $respSpv = $this->actingAs($supervisor)->post(route('projects.toggle_visibility', $project->id));
        $respSpv->assertRedirect();
        $this->assertFalse($project->fresh()->is_public);

        // Super Admin toggle visibility: privat -> publik
        $respAdmin = $this->actingAs($superAdmin)->post(route('projects.toggle_visibility', $project->id));
        $respAdmin->assertRedirect();
        $this->assertTrue($project->fresh()->is_public);
    }

    public function test_super_admin_can_delete_project_and_unauthorized_user_cannot()
    {
        $data = $this->createSampleProject(true);
        $project = $data['project'];

        $superAdmin = User::factory()->create(['role' => 'super_admin', 'name' => 'Super Administrator']);
        $supervisor = User::factory()->create(['role' => 'supervisor', 'name' => 'Ibu Supervisor']);

        // Supervisor tidak berhak menghapus proyek (hanya toggle sembunyikan)
        $respSpv = $this->actingAs($supervisor)->delete(route('projects.destroy', $project->id));
        $respSpv->assertStatus(403);
        $this->assertDatabaseHas('internship_projects', ['id' => $project->id]);

        // Super Admin berhak menghapus proyek
        $respAdmin = $this->actingAs($superAdmin)->delete(route('projects.destroy', $project->id));
        $respAdmin->assertRedirect(route('public.projects.index'));
        $this->assertDatabaseMissing('internship_projects', ['id' => $project->id]);
    }
}
