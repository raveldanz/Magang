<?php

namespace Tests\Feature;

use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\InternshipProject;
use App\Models\InternshipProjectMember;
use App\Models\Placement;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CollaborativeProjectTeamTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_student_can_create_group_project_with_teammates_and_custom_roles()
    {
        $agency = AgencyProfile::create([
            'agency_name' => 'Dinas Perpustakaan dan Kearsipan Kota Surabaya',
            'email' => 'dispusip@surabaya.go.id',
            'phone' => '031-1234567',
            'address' => 'Jl. Rungkut Asri Timur No. 1 Surabaya',
        ]);

        $unit = Unit::create([
            'agency_profile_id' => $agency->id,
            'name' => 'Bidang Pembinaan Perpustakaan',
            'quota' => 10,
        ]);

        // 3 mahasiswa di unit yang sama
        $leader = User::factory()->create(['role' => 'mahasiswa', 'name' => 'Ahmad Fauzi']);
        $member1 = User::factory()->create(['role' => 'mahasiswa', 'name' => 'Siti Nurhaliza']);
        $member2 = User::factory()->create(['role' => 'mahasiswa', 'name' => 'Rian Pratama']);

        $mentor = User::factory()->create(['role' => 'mentor', 'name' => 'Mentor Lapangan']);
        $advisor = User::factory()->create(['role' => 'dosen', 'name' => 'Dosen Pembimbing']);

        $appLeader = Application::create([
            'user_id' => $leader->id,
            'unit_id' => $unit->id,
            'status' => 'active',
            'start_date' => now()->subMonth()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
        ]);
        $placeLeader = Placement::create([
            'application_id' => $appLeader->id,
            'mentor_id' => $mentor->id,
            'academic_advisor_id' => $advisor->id,
            'status' => 'active',
        ]);

        $appMem1 = Application::create([
            'user_id' => $member1->id,
            'unit_id' => $unit->id,
            'status' => 'active',
            'start_date' => now()->subMonth()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
        ]);
        $placeMem1 = Placement::create([
            'application_id' => $appMem1->id,
            'mentor_id' => $mentor->id,
            'academic_advisor_id' => $advisor->id,
            'status' => 'active',
        ]);

        $appMem2 = Application::create([
            'user_id' => $member2->id,
            'unit_id' => $unit->id,
            'status' => 'active',
            'start_date' => now()->subMonth()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
        ]);
        $placeMem2 = Placement::create([
            'application_id' => $appMem2->id,
            'mentor_id' => $mentor->id,
            'academic_advisor_id' => $advisor->id,
            'status' => 'active',
        ]);

        // Leader membuat proyek kelompok kategori "buku" (Karya Literasi)
        $resp = $this->actingAs($leader)->post(route('student.projects.store'), [
            'title' => 'Buku Antologi Kisah Literasi Surabaya',
            'category' => 'buku',
            'project_type' => 'group',
            'creator_role' => 'Ketua Tim & Penulis Naskah',
            'members' => [
                [
                    'placement_id' => $placeMem1->id,
                    'role' => 'Ilustrator & Layout',
                ],
                [
                    'placement_id' => $placeMem2->id,
                    'role' => 'Riset Cerita Rakyat & Editor',
                ],
            ],
            'is_public' => '1',
            'description' => 'Buku kumpulan kisah literasi dan budaya lokal.',
            'community_benefit' => 'Menjadi bacaan inspiratif bagi anak-anak di pojok baca kelurahan.',
            'photo' => UploadedFile::fake()->image('buku_cover.jpg'),
        ]);

        $resp->assertRedirect(route('student.final_report.index'));

        $project = InternshipProject::where('title', 'Buku Antologi Kisah Literasi Surabaya')->first();
        $this->assertNotNull($project);
        $this->assertEquals('Karya Literasi', $project->category_label);
        $this->assertEquals(3, $project->members()->count());

        // Verifikasi anggota dan peran
        $leaderMember = $project->members()->where('placement_id', $placeLeader->id)->first();
        $this->assertNotNull($leaderMember);
        $this->assertTrue($leaderMember->is_leader);
        $this->assertEquals('Ketua Tim & Penulis Naskah', $leaderMember->role_in_project);

        $m1 = $project->members()->where('placement_id', $placeMem1->id)->first();
        $this->assertNotNull($m1);
        $this->assertFalse($m1->is_leader);
        $this->assertEquals('Ilustrator & Layout', $m1->role_in_project);

        $m2 = $project->members()->where('placement_id', $placeMem2->id)->first();
        $this->assertNotNull($m2);
        $this->assertEquals('Riset Cerita Rakyat & Editor', $m2->role_in_project);

        // Verifikasi proyek otomatis muncul di Final Report mahasiswa anggota (Member 1)
        $mem1Resp = $this->actingAs($member1)->get(route('student.final_report.index'));
        $mem1Resp->assertStatus(200);
        $mem1Resp->assertSee('Buku Antologi Kisah Literasi Surabaya');

        // Verifikasi halaman detail publik menampilkan seluruh anggota tim
        $detailResp = $this->get(route('projects.show', $project->id));
        $detailResp->assertStatus(200);
        $detailResp->assertSee('Tim Pengembang Proyek (3 Anggota)');
        $detailResp->assertSee('Ahmad Fauzi');
        $detailResp->assertSee('Ketua Tim & Penulis Naskah');
        $detailResp->assertSee('Siti Nurhaliza');
        $detailResp->assertSee('Ilustrator & Layout');
        $detailResp->assertSee('Rian Pratama');
        $detailResp->assertSee('Riset Cerita Rakyat & Editor');
    }

    public function test_custom_category_for_inovasi_lainnya()
    {
        $agency = AgencyProfile::create([
            'agency_name' => 'Dinas Perpustakaan dan Kearsipan Kota Surabaya',
            'email' => 'dispusip@surabaya.go.id',
            'phone' => '031-1234567',
            'address' => 'Jl. Rungkut Asri Timur No. 1 Surabaya',
        ]);
        $unit = Unit::create([
            'agency_profile_id' => $agency->id,
            'name' => 'Bidang Layanan Kearsipan',
            'quota' => 5,
        ]);
        $student = User::factory()->create(['role' => 'mahasiswa', 'name' => 'Budi Utomo']);
        $mentor = User::factory()->create(['role' => 'mentor']);
        $advisor = User::factory()->create(['role' => 'dosen']);

        $application = Application::create([
            'user_id' => $student->id,
            'unit_id' => $unit->id,
            'status' => 'active',
            'start_date' => now()->subMonth()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
        ]);
        $placement = Placement::create([
            'application_id' => $application->id,
            'mentor_id' => $mentor->id,
            'academic_advisor_id' => $advisor->id,
            'status' => 'active',
        ]);

        // Input kategori "lainnya" dengan custom category "Klinik Literasi Inklusif"
        $resp = $this->actingAs($student)->post(route('student.projects.store'), [
            'title' => 'Inovasi Klinik Literasi Disabilitas',
            'category' => 'lainnya',
            'custom_category' => 'Klinik Literasi Inklusif',
            'project_type' => 'individual',
            'creator_role' => 'Inisiator Program',
            'is_public' => '1',
            'description' => 'Layanan literasi khusus bagi pemustaka disabilitas netra dan rungu.',
            'community_benefit' => 'Memberikan akses membaca yang setara bagi seluruh lapisan warga.',
            'photo' => UploadedFile::fake()->image('klinik.jpg'),
        ]);

        $resp->assertRedirect(route('student.final_report.index'));

        $project = InternshipProject::where('title', 'Inovasi Klinik Literasi Disabilitas')->first();
        $this->assertNotNull($project);
        $this->assertEquals('Klinik Literasi Inklusif', $project->category_label);
        $this->assertEquals('Klinik Literasi Inklusif', $project->custom_category);

        // Verifikasi deteksi pintar
        $detectedIT = InternshipProject::detectMatchingCategory('UI UX Mobile Desain');
        $this->assertEquals('teknologi', $detectedIT);

        $detectedRestorasi = InternshipProject::detectMatchingCategory('Restorasi Naskah Babat Surabaya');
        $this->assertEquals('preservasi', $detectedRestorasi);

        $detectedVideo = InternshipProject::detectMatchingCategory('Video Edukasi Kearsipan');
        $this->assertEquals('media', $detectedVideo);
    }
}

