<?php

namespace Tests\Feature;

use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\FinalReport;
use App\Models\InternshipProject;
use App\Models\Placement;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InternshipProjectAndSupervisorReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_student_can_add_multiple_projects_with_community_benefit_and_photo()
    {
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $mentor = User::factory()->create(['role' => 'mentor']);
        $advisor = User::factory()->create(['role' => 'dosen']);

        $agency = AgencyProfile::where('agency_name', 'like', '%Perpustakaan%')->first()
            ?? AgencyProfile::create([
                'agency_name' => 'Dinas Perpustakaan dan Kearsipan Kota Surabaya',
                'email' => 'dispusip@surabaya.go.id',
                'phone' => '031-1234567',
                'address' => 'Jl. Rungkut Asri Timur No. 1 Surabaya',
            ]);

        $unit = Unit::where('agency_profile_id', $agency->id)->first()
            ?? Unit::create([
                'agency_profile_id' => $agency->id,
                'name' => 'Bidang Kearsipan',
                'quota' => 10,
            ]);

        $application = Application::create([
            'user_id' => $student->id,
            'unit_id' => $unit->id,
            'status' => 'active',
            'start_date' => now()->subDays(10)->toDateString(),
            'end_date' => now()->addDays(20)->toDateString(),
        ]);

        $placement = Placement::create([
            'application_id' => $application->id,
            'mentor_id' => $mentor->id,
            'academic_advisor_id' => $advisor->id,
            'status' => 'active',
        ]);

        // Student creates 1st project with multi-screenshots, community photos, video, documents, repo, and source code ZIP
        $response1 = $this->actingAs($student)->post(route('student.projects.store'), [
            'title' => 'Sistem Arsip Digital Publik',
            'description' => 'Aplikasi web pencarian arsip dinamis.',
            'community_benefit' => 'Mempermudah masyarakat mencari arsip secara mandiri dan cepat.',
            'photo' => UploadedFile::fake()->image('project1.jpg'),
            'demo_url' => 'https://arsip.surabaya.go.id',
            'repository_url' => 'https://github.com/magang/arsip',
            'source_code' => UploadedFile::fake()->create('arsip_v1.zip', 1024, 'application/zip'),
            'video_url' => 'https://youtu.be/jlKU3exv4Oc?si=8Kg2u9vv8zj0peED',
            'screenshots' => [
                UploadedFile::fake()->image('screen1.png'),
                UploadedFile::fake()->image('screen2.png'),
            ],
            'community_photos' => [
                UploadedFile::fake()->image('desa1.jpg'),
            ],
            'documents' => [
                UploadedFile::fake()->create('panduan.pdf', 500, 'application/pdf'),
            ],
        ]);

        $response1->assertRedirect(route('student.final_report.index'));
        $createdProject = InternshipProject::where('placement_id', $placement->id)
            ->where('title', 'Sistem Arsip Digital Publik')
            ->first();
        $this->assertNotNull($createdProject);
        $this->assertEquals('https://arsip.surabaya.go.id', $createdProject->demo_url);
        $this->assertEquals('https://github.com/magang/arsip', $createdProject->repository_url);
        $this->assertNotNull($createdProject->source_code_path);
        $this->assertEquals('arsip_v1.zip', $createdProject->source_code_name);
        $this->assertEquals('https://youtu.be/jlKU3exv4Oc?si=8Kg2u9vv8zj0peED', $createdProject->video_url);
        $this->assertEquals('https://www.youtube.com/embed/jlKU3exv4Oc', $createdProject->youtube_embed_url);
        $this->assertCount(2, $createdProject->screenshots);
        $this->assertCount(1, $createdProject->community_photos);
        $this->assertCount(1, $createdProject->documents);

        // Student visits final report index:
        // Verifies "Informasi Penempatan Magang" is removed, but "Panduan dan Ketentuan Laporan Akhir Magang" is visible
        $responseIndex = $this->actingAs($student)->get(route('student.final_report.index'));
        $responseIndex->assertStatus(200);
        $responseIndex->assertDontSee('Informasi Penempatan Magang');
        $responseIndex->assertSee('Panduan dan Ketentuan Laporan Akhir Magang');
        $responseIndex->assertSee('Fleksibel & Bisa Dicicil', false);
        $responseIndex->assertSee('Evaluasi 2 Pihak');
        $responseIndex->assertSee('Penerbitan E-Sertifikat');

        // Student access dedicated create project page
        $responseCreate = $this->actingAs($student)->get(route('student.projects.create'));
        $responseCreate->assertStatus(200);
        $responseCreate->assertSee('Tambah Proyek Inovasi Magang');
        $responseCreate->assertSee('Pusat Akses Proyek');
        $responseCreate->assertSee('Maksimal 10 berkas');

        // Student creates project with 10 documents (should succeed)
        $tenDocs = [];
        for ($i = 1; $i <= 10; $i++) {
            $tenDocs[] = UploadedFile::fake()->create("dokumen_{$i}.pdf", 100, 'application/pdf');
        }
        $responseTenDocs = $this->actingAs($student)->post(route('student.projects.store'), [
            'title' => 'Proyek Inovasi 10 Dokumen',
            'description' => 'Uji coba kelengkapan 10 dokumen lampiran.',
            'community_benefit' => 'Memberikan panduan lengkap kepada warga.',
            'photo' => UploadedFile::fake()->image('project_ten.jpg'),
            'documents' => $tenDocs,
        ]);
        $responseTenDocs->assertRedirect(route('student.final_report.index'));
        $projectWith10Docs = InternshipProject::where('title', 'Proyek Inovasi 10 Dokumen')->first();
        $this->assertNotNull($projectWith10Docs);
        $this->assertCount(10, $projectWith10Docs->documents);

        // Student tries to upload 11 documents (should fail validation)
        $elevenDocs = [];
        for ($i = 1; $i <= 11; $i++) {
            $elevenDocs[] = UploadedFile::fake()->create("dokumen_extra_{$i}.pdf", 100, 'application/pdf');
        }
        $responseElevenDocs = $this->actingAs($student)->post(route('student.projects.store'), [
            'title' => 'Proyek Melebihi Kuota Dokumen',
            'description' => 'Uji coba 11 dokumen gagal.',
            'community_benefit' => 'Manfaat.',
            'photo' => UploadedFile::fake()->image('project_fail.jpg'),
            'documents' => $elevenDocs,
        ]);
        $responseElevenDocs->assertSessionHasErrors(['documents']);

        // Student creates 2nd project (>1 projects)
        $response2 = $this->actingAs($student)->post(route('student.projects.store'), [
            'title' => 'Pojok Baca Interaktif',
            'description' => 'Program revitalisasi ruang baca warga.',
            'community_benefit' => 'Meningkatkan literasi generasi muda dan minat baca masyarakat.',
            'photo' => UploadedFile::fake()->image('project2.png'),
        ]);

        $response2->assertRedirect(route('student.final_report.index'));
        $this->assertEquals(3, $placement->internshipProjects()->count());

        // Student access dedicated edit project page
        $project1 = $placement->internshipProjects()->where('title', 'Sistem Arsip Digital Publik')->first();
        $responseEdit = $this->actingAs($student)->get(route('student.projects.edit', $project1->id));
        $responseEdit->assertStatus(200);
        $responseEdit->assertSee('Ubah Proyek Inovasi');
        $responseEdit->assertSee('Lihat Halaman Proyek');
        $responseEdit->assertDontSee('Lihat Halaman Proyek ↗');
        $responseEdit->assertSee('arsip_v1.zip');
        $responseEdit->assertSee('Maksimal 10 berkas');

        // Update project
        $responseUpdate = $this->actingAs($student)->put(route('student.projects.update', $project1->id), [
            'title' => 'Sistem Arsip Digital Publik Terpadu',
            'description' => 'Aplikasi web pencarian arsip dinamis versi 2.',
            'community_benefit' => 'Memangkas waktu birokrasi permohonan arsip dari 3 hari menjadi 10 menit.',
            'demo_url' => 'https://arsip-v2.surabaya.go.id',
            'repository_url' => 'https://github.com/magang/arsip-v2',
        ]);

        $responseUpdate->assertRedirect(route('student.final_report.index'));
        $this->assertDatabaseHas('internship_projects', [
            'id' => $project1->id,
            'title' => 'Sistem Arsip Digital Publik Terpadu',
            'demo_url' => 'https://arsip-v2.surabaya.go.id',
            'repository_url' => 'https://github.com/magang/arsip-v2',
        ]);

        // Delete project
        $project2 = $placement->internshipProjects()->where('title', 'Pojok Baca Interaktif')->first();
        $responseDelete = $this->actingAs($student)->delete(route('student.projects.destroy', $project2->id));
        $responseDelete->assertRedirect(route('student.final_report.index'));
        $this->assertEquals(2, $placement->fresh()->internshipProjects()->count());
    }

    public function test_supervisor_can_access_reports_menu_and_view_student_outputs()
    {
        $supervisor = User::factory()->create(['role' => 'supervisor']);
        $student = User::factory()->create(['role' => 'mahasiswa', 'name' => 'Aditya Pratama']);
        \App\Models\StudentProfile::create([
            'user_id' => $student->id,
            'nim' => '2208101',
            'universitas' => 'Universitas Indonesia',
            'jurusan' => 'Informatika',
            'phone' => '081234567890',
        ]);
        $mentor = User::factory()->create(['role' => 'mentor']);
        $advisor = User::factory()->create(['role' => 'dosen']);

        $agency = AgencyProfile::where('agency_name', 'like', '%Perpustakaan%')->first()
            ?? AgencyProfile::create([
                'agency_name' => 'Dinas Perpustakaan dan Kearsipan Kota Surabaya',
                'email' => 'dispusip@surabaya.go.id',
                'phone' => '031-1234567',
                'address' => 'Jl. Rungkut Asri Timur No. 1 Surabaya',
            ]);

        $unit = Unit::where('agency_profile_id', $agency->id)->first()
            ?? Unit::create([
                'agency_profile_id' => $agency->id,
                'name' => 'Bidang Pengolahan dan Pelestarian',
                'quota' => 10,
            ]);

        $application = Application::create([
            'user_id' => $student->id,
            'unit_id' => $unit->id,
            'status' => 'active',
            'start_date' => now()->subDays(5)->toDateString(),
            'end_date' => now()->addDays(25)->toDateString(),
        ]);

        $placement = Placement::create([
            'application_id' => $application->id,
            'mentor_id' => $mentor->id,
            'academic_advisor_id' => $advisor->id,
            'status' => 'active',
        ]);

        $project = InternshipProject::create([
            'placement_id' => $placement->id,
            'title' => 'Katalog Naskah Kuno Surabaya',
            'description' => 'Preservasi digital manuskrip sejarah.',
            'community_benefit' => 'Menjaga warisan budaya Kota Surabaya agar dapat diakses oleh peneliti dan masyarakat luas.',
            'photo_path' => 'internship_projects/sample.jpg',
        ]);

        $finalReport = FinalReport::create([
            'placement_id' => $placement->id,
            'title' => 'Laporan Akhir Preservasi Naskah Kuno',
            'file_path' => 'final_reports/sample.pdf',
            'status' => 'approved',
        ]);

        // 1. Supervisor access index
        $response = $this->actingAs($supervisor)->get(route('supervisor.reports.index'));
        $response->assertStatus(200);
        $response->assertSee('Laporan Mahasiswa');
        $response->assertSee('Aditya Pratama');
        $response->assertSee($unit->name);
        $response->assertSee('1 Proyek');
        $response->assertDontSee('alt="Thumbnail"', false);

        // 2. Supervisor access show detail
        $responseShow = $this->actingAs($supervisor)->get(route('supervisor.reports.show', $placement->id));
        $responseShow->assertStatus(200);
        $responseShow->assertSee('Katalog Naskah Kuno Surabaya');
        $responseShow->assertSee('Menjaga warisan budaya Kota Surabaya');
        $responseShow->assertSee('Laporan Akhir Preservasi Naskah Kuno');
        $responseShow->assertSee(route('projects.show', $project->id));
        $responseShow->assertSee('Buka Halaman Detail');

        // 3. Supervisor navigate to dedicated project detail page
        $responseProject = $this->actingAs($supervisor)->get(route('projects.show', $project->id));
        $responseProject->assertStatus(200);
        $responseProject->assertSee('Katalog Naskah Kuno Surabaya');
        $responseProject->assertSee('Preservasi digital manuskrip sejarah.');
        $responseProject->assertSee('Menjaga warisan budaya Kota Surabaya');
        $responseProject->assertSee('Dampak Sosial');
    }

    public function test_student_cannot_access_supervisor_reports()
    {
        $student = User::factory()->create(['role' => 'mahasiswa']);
        $response = $this->actingAs($student)->get(route('supervisor.reports.index'));
        $response->assertStatus(403);
    }

    public function test_guest_and_authenticated_user_navigation_and_public_project_access()
    {
        $student = User::factory()->create(['role' => 'mahasiswa', 'name' => 'Eka Putri']);
        $agency = AgencyProfile::create([
            'agency_name' => 'Dinas Perpustakaan dan Kearsipan Kota Surabaya',
            'email' => 'dispusip@surabaya.go.id',
            'phone' => '031-1234567',
            'address' => 'Jl. Rungkut Asri Timur No. 1 Surabaya',
        ]);
        $unit = Unit::create([
            'agency_profile_id' => $agency->id,
            'name' => 'Bidang Layanan Kearsipan',
            'quota' => 10,
        ]);
        $application = Application::create([
            'user_id' => $student->id,
            'unit_id' => $unit->id,
            'status' => 'active',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(30)->toDateString(),
        ]);
        $advisor = User::factory()->create(['role' => 'dosen']);
        $placement = Placement::create([
            'application_id' => $application->id,
            'academic_advisor_id' => $advisor->id,
            'status' => 'active',
        ]);
        $project = InternshipProject::create([
            'placement_id' => $placement->id,
            'title' => 'Portal Layanan Literasi Digital',
            'description' => 'Platform literasi membaca daring untuk anak-anak kota Surabaya.',
            'community_benefit' => 'Meningkatkan minat baca dan literasi digital generasi muda.',
            'category' => 'pendidikan',
            'photo_path' => 'internship_projects/sample.jpg',
        ]);

        // 1. Guest visits Welcome page ('/'): Should see login & register, and NOT see Pindah ke Dashboard
        $responseGuestWelcome = $this->get('/');
        $responseGuestWelcome->assertStatus(200);
        $responseGuestWelcome->assertSee(route('login'));
        $responseGuestWelcome->assertSee(route('register'));
        $responseGuestWelcome->assertDontSee('Pindah ke Dashboard');

        // 2. Authenticated user visits Welcome page ('/'): Should see Pindah ke Dashboard, and NOT see login or register
        $responseAuthWelcome = $this->actingAs($student)->get('/');
        $responseAuthWelcome->assertStatus(200);
        $responseAuthWelcome->assertSee('Pindah ke Dashboard');
        $responseAuthWelcome->assertDontSee(route('login'));
        $responseAuthWelcome->assertDontSee(route('register'));
        $responseAuthWelcome->assertDontSee('Masuk ke Akun');
        $responseAuthWelcome->assertDontSee('Daftar Magang Sekarang');

        // 3. Authenticated user views Navigation (e.g. final report index):
        // Navbar logo must point to url('/') and profile dropdown must include "Home"
        $responseAuthNav = $this->actingAs($student)->get(route('student.final_report.index'));
        $responseAuthNav->assertStatus(200);
        // Logo points to root url ('/')
        $responseAuthNav->assertSee('<a href="' . url('/') . '" class="flex items-center gap-2.5 sm:gap-3 group py-1.5 shrink-0"', false);
        // Menu Home is present in dropdown
        $responseAuthNav->assertSee('Home');

        // 4. Guest can view project detail page directly without login!
        $responseGuestProject = $this->get(route('projects.show', $project->id));
        $responseGuestProject->assertStatus(200);
        $responseGuestProject->assertSee('Portal Layanan Literasi Digital');
        $responseGuestProject->assertSee('Karya Inovasi Magang');
        $responseGuestProject->assertSee(url('/'));

        // 5. Authenticated user can also view project detail page
        $responseAuthProject = $this->actingAs($student)->get(route('projects.show', $project->id));
        $responseAuthProject->assertStatus(200);
        $responseAuthProject->assertSee('Portal Layanan Literasi Digital');
    }
}

