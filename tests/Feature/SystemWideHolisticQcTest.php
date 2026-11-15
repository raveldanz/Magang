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
use Tests\TestCase;

class SystemWideHolisticQcTest extends TestCase
{
    use RefreshDatabase;

    private AgencyProfile $agency;

    private Unit $unit;

    private University $university;

    private User $superAdmin;

    private User $supervisor;

    private User $mentor;

    private User $lecturer;

    private User $universityUser;

    private User $student;

    private Application $application;

    private Placement $placement;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agency = AgencyProfile::create([
            'agency_name' => 'Dinas Perpustakaan dan Kearsipan Kota Surabaya',
            'acronym' => 'Dispusip',
            'address' => 'Jl. Rungkut Asri Tengah No. 5-7, Rungkut Kidul, Surabaya 60293',
        ]);

        $this->unit = Unit::create([
            'agency_profile_id' => $this->agency->id,
            'name' => 'Bidang Pembinaan dan Pengelolaan Perpustakaan',
            'quota' => 20,
        ]);

        $this->university = University::create([
            'name' => 'Universitas Negeri Surabaya',
            'acronym' => 'UNESA',
            'address' => 'Jl. Ketintang, Surabaya',
            'is_verified' => true,
        ]);

        // 1. Akun Pengguna 6 Peran
        $this->superAdmin = User::factory()->create([
            'name' => 'Super Admin Dispusip',
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
            'name' => 'Dosen Pembimbing UNESA',
            'role' => 'dosen',
            'university_id' => $this->university->id,
        ]);

        $this->universityUser = User::factory()->create([
            'name' => 'Admin Kampus UNESA',
            'role' => 'universitas',
            'university_id' => $this->university->id,
        ]);

        $this->student = User::factory()->create([
            'name' => 'Mahasiswa Holistik',
            'role' => 'mahasiswa',
            'university_id' => $this->university->id,
            'university' => $this->university->name,
        ]);

        StudentProfile::create([
            'user_id' => $this->student->id,
            'nim' => '22051204001',
            'universitas' => $this->university->name,
            'university_id' => $this->university->id,
            'jurusan' => 'S1 Sistem Informasi',
            'phone' => '081234567890',
        ]);

        // 2. Data Penempatan Magang Aktif
        $this->application = Application::create([
            'user_id' => $this->student->id,
            'unit_id' => $this->unit->id,
            'start_date' => now()->subDays(10)->toDateString(),
            'end_date' => now()->addDays(60)->toDateString(),
            'status' => 'active',
            'letter_token' => 'SURAT-TEST-TOKEN-12345',
        ]);

        $this->placement = Placement::create([
            'application_id' => $this->application->id,
            'mentor_id' => $this->mentor->id,
            'pembimbing_id' => $this->mentor->id,
            'academic_advisor_id' => $this->lecturer->id,
            'certificate_hash' => 'CERT-TEST-HASH-67890',
        ]);

        Logbook::create([
            'placement_id' => $this->placement->id,
            'date' => now()->subDay()->toDateString(),
            'activity' => 'Melakukan digitalisasi arsip dan inventarisasi buku perpustakaan.',
            'status' => 'approved',
        ]);

        FinalReport::create([
            'placement_id' => $this->placement->id,
            'file_path' => 'documents/reports/report_dummy.pdf',
            'title' => 'Laporan Akhir Magang Dispusip',
            'status' => 'pending',
        ]);
    }

    public function test_domain_1_public_and_verification_routes_render_http_200(): void
    {
        // 1. Beranda utama
        $resWelcome = $this->get(url('/'));
        $resWelcome->assertOk();
        $resWelcome->assertSee('Portal Magang Dispusip');

        // 2. Katalog proyek dinas publik
        $resProjects = $this->get(route('public.projects.index'));
        $resProjects->assertOk();

        // 3. Login & Register
        $this->get(route('login'))->assertOk();
        $this->get(route('register'))->assertOk();

        // 4. Verifikasi Surat Penerimaan Publik (Tanpa Login)
        $resLetter = $this->get(route('verify.letter', $this->application->letter_token));
        $resLetter->assertOk();

        // 5. Verifikasi E-Sertifikat Publik (Tanpa Login)
        $resCert = $this->get(route('verify.certificate', $this->placement->certificate_hash));
        $resCert->assertOk();
    }

    public function test_domain_2_student_portal_all_routes_render_http_200(): void
    {
        $this->actingAs($this->student);

        $this->get(route('dashboard'))->assertOk();
        $this->get(route('student.application.create'))->assertOk();
        $this->get(route('student.profile.edit'))->assertOk();
        $this->get(route('student.logbook.index'))->assertOk();
        $this->get(route('student.final_report.index'))->assertOk();
        $this->get(route('student.projects.create'))->assertOk();
        $this->get(route('feedbacks.create'))->assertOk();
    }

    public function test_domain_3_mentor_portal_all_routes_render_http_200(): void
    {
        $this->actingAs($this->mentor);

        $this->get(route('mentor.dashboard'))->assertOk();
        $this->get(route('mentor.logbooks.index'))->assertOk();
        $this->get(route('mentor.students.show', $this->placement->id))->assertOk();
        $this->get(route('mentor.evaluations.create', $this->placement->id))->assertOk();
    }

    public function test_domain_4_lecturer_portal_all_routes_render_http_200(): void
    {
        $this->actingAs($this->lecturer);

        $this->get(route('lecturer.dashboard'))->assertOk();
        $this->get(route('lecturer.monitoring.index'))->assertOk();
        $this->get(route('lecturer.logbooks.index'))->assertOk();
        $this->get(route('lecturer.students.show', $this->placement->id))->assertOk();
        $this->get(route('lecturer.evaluations.create', $this->placement->id))->assertOk();
    }

    public function test_domain_5_supervisor_portal_all_routes_render_http_200(): void
    {
        $this->actingAs($this->supervisor);

        $this->get(route('supervisor.dashboard'))->assertOk();
        $this->get(route('supervisor.applications.index'))->assertOk();
        $this->get(route('supervisor.reports.index'))->assertOk();
        $this->get(route('supervisor.certificates.index'))->assertOk();
    }

    public function test_domain_6_super_admin_portal_all_routes_render_http_200(): void
    {
        $this->actingAs($this->superAdmin);

        $this->get(route('admin.dashboard'))->assertOk();
        $this->get(route('admin.applications.index'))->assertOk();
        $this->get(route('admin.users.index'))->assertOk();
        $this->get(route('admin.mentors.index'))->assertOk();
        $this->get(route('admin.agencies.index'))->assertOk();
        $this->get(route('admin.universities.index'))->assertOk();
        $this->get(route('admin.audit_logs.index'))->assertOk();
        $this->get(route('admin.feedbacks.index'))->assertOk();
    }

    public function test_domain_7_university_partner_portal_all_routes_render_http_200(): void
    {
        $this->actingAs($this->universityUser);

        $this->get(route('university.dashboard'))->assertOk();
        $this->get(route('university.lecturers.index'))->assertOk();
        $this->get(route('university.profile.index'))->assertOk();
    }

    public function test_domain_8_notification_center_and_push_for_all_roles(): void
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

        // Web Push test
        $resPush = $this->actingAs($this->student)->postJson(route('push.store'), [
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/holistic-test-endpoint',
            'keys' => [
                'auth' => 'holistic_auth_key',
                'p256dh' => 'holistic_p256dh_key',
            ],
        ]);
        $resPush->assertOk();
        $resPush->assertJson(['success' => true]);
    }

    public function test_domain_9_chat_system_and_apis_for_all_roles(): void
    {
        // 1. Mahasiswa membuka /chat
        $resChatStudent = $this->actingAs($this->student)->get(route('chat.index'));
        $resChatStudent->assertOk();
        $resChatStudent->assertSee('Pesan');

        // 2. Dosen membuka /chat
        $resChatLecturer = $this->actingAs($this->lecturer)->get(route('chat.index'));
        $resChatLecturer->assertOk();

        // 3. Mentor membuka /chat
        $resChatMentor = $this->actingAs($this->mentor)->get(route('chat.index'));
        $resChatMentor->assertOk();

        // 4. Supervisor membuka /chat
        $resChatSupervisor = $this->actingAs($this->supervisor)->get(route('chat.index'));
        $resChatSupervisor->assertOk();

        // 5. Admin membuka /chat
        $resChatAdmin = $this->actingAs($this->superAdmin)->get(route('chat.index'));
        $resChatAdmin->assertOk();

        // 6. Chat API Endpoints (Summary, Conversations, Contacts)
        $resSummary = $this->actingAs($this->student)->getJson(route('chat.api.summary'));
        $resSummary->assertOk();

        $resConvs = $this->actingAs($this->student)->getJson(route('chat.api.conversations'));
        $resConvs->assertOk();

        $resContacts = $this->actingAs($this->student)->getJson(route('chat.api.contacts'));
        $resContacts->assertOk();
    }
}
