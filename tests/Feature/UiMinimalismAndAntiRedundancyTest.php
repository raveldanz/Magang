<?php

namespace Tests\Feature;

use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\InternshipGroup;
use App\Models\StudentProfile;
use App\Models\Unit;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UiMinimalismAndAntiRedundancyTest extends TestCase
{
    use RefreshDatabase;

    private University $university;

    private Unit $unit;

    private User $leader;

    private User $member;

    protected function setUp(): void
    {
        parent::setUp();

        $this->university = University::create([
            'name' => 'Universitas Negeri Surabaya',
            'acronym' => 'UNESA',
            'address' => 'Jl. Ketintang Surabaya',
            'is_verified' => true,
        ]);

        $agency = AgencyProfile::create([
            'agency_name' => 'Dinas Perpustakaan dan Kearsipan Kota Surabaya',
            'acronym' => 'Dispusip',
            'address' => 'Jl. Rungkut Asri Tengah No. 5-7, Rungkut Kidul, Surabaya 60293',
        ]);

        $this->unit = Unit::create([
            'agency_profile_id' => $agency->id,
            'name' => 'Bidang Pembinaan dan Pengelolaan Perpustakaan',
            'quota' => 15,
        ]);

        $this->leader = User::factory()->create([
            'name' => 'Joko Susilo',
            'role' => 'mahasiswa',
            'university_id' => $this->university->id,
            'university' => $this->university->name,
        ]);
        StudentProfile::create([
            'user_id' => $this->leader->id,
            'nim' => '22051204091',
            'universitas' => $this->university->name,
            'university_id' => $this->university->id,
            'jurusan' => 'S1 Sistem Informasi',
            'phone' => '081234500091',
            'cv_path' => 'documents/student-profiles/cv_joko.pdf',
            'transcript_path' => 'documents/student-profiles/transkrip_joko.pdf',
            'id_card_path' => 'documents/student-profiles/ktm_joko.pdf',
        ]);

        $this->member = User::factory()->create([
            'name' => 'Karina Damayanti',
            'role' => 'mahasiswa',
            'university_id' => $this->university->id,
            'university' => $this->university->name,
        ]);
        StudentProfile::create([
            'user_id' => $this->member->id,
            'nim' => '22051204092',
            'universitas' => $this->university->name,
            'university_id' => $this->university->id,
            'jurusan' => 'S1 Ilmu Komunikasi',
            'phone' => '081234500092',
            'cv_path' => 'documents/student-profiles/cv_karina.pdf',
            'transcript_path' => 'documents/student-profiles/transkrip_karina.pdf',
            'id_card_path' => 'documents/student-profiles/ktm_karina.pdf',
        ]);
    }

    public function test_rejected_group_member_hides_registration_mode_cards_and_has_minimal_non_redundant_copy(): void
    {
        $group = InternshipGroup::create([
            'code' => 'GRP-TOLAK-05',
            'name' => 'Tim Pengembangan Literasi Komunitas',
            'leader_user_id' => $this->leader->id,
            'unit_id' => $this->unit->id,
            'start_date' => now()->addDays(14)->toDateString(),
            'end_date' => now()->addDays(90)->toDateString(),
            'status' => 'accepted',
        ]);

        // Ketua sudah di-ACC, sedangkan 1 anggota (Karina) ditolak karena berkas perlu perbaikan
        Application::create([
            'user_id' => $this->leader->id,
            'unit_id' => $this->unit->id,
            'start_date' => $group->start_date,
            'end_date' => $group->end_date,
            'group_id' => $group->id,
            'role_in_group' => 'leader',
            'status' => 'accepted',
        ]);

        $memberApp = Application::create([
            'user_id' => $this->member->id,
            'unit_id' => $this->unit->id,
            'start_date' => $group->start_date,
            'end_date' => $group->end_date,
            'group_id' => $group->id,
            'role_in_group' => 'member',
            'status' => 'rejected',
            'rejection_note' => 'Transkrip nilai buram, mohon unggah ulang.',
        ]);

        $response = $this->actingAs($this->member)->get(route('student.application.create'));
        $response->assertOk();

        // 1. Menampilkan kartu perbaikan berkas kelompok dan opsi keluar dari kelompok
        $response->assertSee('Kembalikan Pengajuan ke Kelompok: Tim Pengembangan Literasi Komunitas');
        $response->assertSee('Kirim Perbaikan & Kembali ke Kelompok');
        $response->assertSee('Ingin keluar dari kelompok ini?');
        $response->assertSee('Keluar dari Kelompok');

        // 2. Menyembunyikan opsi Pilih Jalur Pendaftaran Magang (Individu & Kelompok) selama masih di dalam kelompok
        $response->assertDontSee('Pilih Jalur Pendaftaran Magang');
        $response->assertDontSee('Pilih Pendaftaran Individu');
        $response->assertDontSee('Pilih Pendaftaran Kelompok');

        // 3. Bebas dari teks redundan & tombol tidak mengulang nama kelompok di dalam kurung
        $response->assertDontSee('Anda dapat memperbaiki/mengunggah ulang berkas yang salah pada kartu');
        $response->assertDontSee('tanpa perlu membuat kelompok baru');
        $response->assertDontSee('Kirim Perbaikan & Kembali ke Kelompok (Tim Pengembangan Literasi Komunitas)');

        // 4. Ketika mahasiswa memilih Keluar dari Kelompok, ia dilepas dari kelompok dan barulah opsi Individu & Kelompok muncul
        $leaveResponse = $this->actingAs($this->member)->post(route('student.group.leave'));
        $leaveResponse->assertRedirect(route('student.application.create'));

        $memberApp->refresh();
        $this->assertNull($memberApp->group_id);

        $afterLeavePage = $this->actingAs($this->member)->get(route('student.application.create'));
        $afterLeavePage->assertOk();
        $afterLeavePage->assertSee('Pilih Jalur Pendaftaran Magang');
        $afterLeavePage->assertSee('Pilih Pendaftaran Individu');
        $afterLeavePage->assertSee('Pilih Pendaftaran Kelompok');
        $afterLeavePage->assertDontSee('Kembalikan Pengajuan ke Kelompok');
    }

    public function test_student_application_blade_template_has_balanced_tags_and_zero_duplicate_inputs(): void
    {
        $bladePath = resource_path('views/student/application/create.blade.php');
        $content = file_get_contents($bladePath);
        $this->assertNotFalse($content);

        $openDivs = preg_match_all('/<div\b/i', $content);
        $closeDivs = preg_match_all('/<\/div\s*>/i', $content);
        $this->assertSame($openDivs, $closeDivs, 'Tag <div> dan </div> pada student/application/create.blade.php harus seimbang.');

        $openForms = preg_match_all('/<form\b/i', $content);
        $closeForms = preg_match_all('/<\/form\s*>/i', $content);
        $this->assertSame($openForms, $closeForms, 'Tag <form> dan </form> pada student/application/create.blade.php harus seimbang.');

        $groupNameInputs = preg_match_all('/name=["\']name["\']/i', $content);
        $groupCodeInputs = preg_match_all('/name=["\']group_code["\']/i', $content);
        $this->assertSame(1, $groupNameInputs, 'Input name="name" (Nama Kelompok) hanya boleh ada 1 kali.');
        $this->assertSame(1, $groupCodeInputs, 'Input name="group_code" (Kode Undangan Kelompok) hanya boleh ada 1 kali.');
    }

    public function test_group_registration_cards_are_minimalist_and_free_from_redundant_callouts(): void
    {
        $bladePath = resource_path('views/student/application/create.blade.php');
        $content = file_get_contents($bladePath);
        $this->assertNotFalse($content);

        // Anti-pattern 1: Tidak boleh ada banner redundan "Panduan Peran Pendaftaran Kelompok"
        $this->assertStringNotContainsString('Panduan Peran Pendaftaran Kelompok', $content);
        $this->assertStringNotContainsString('Untuk Ketua Kelompok:', $content);
        $this->assertStringNotContainsString('Untuk Anggota Tim:', $content);

        // Anti-pattern 2: Tidak boleh ada callout box pengulangan di dalam Card A & Card B
        $this->assertStringNotContainsString('Formulir ini khusus untuk Ketua Kelompok yang menginisiasi', $content);
        $this->assertStringNotContainsString('Formulir ini khusus untuk Anggota Tim. Pastikan Ketua Kelompok', $content);
        $this->assertStringNotContainsString('Tersedia 4 Bidang resmi: Sekretariat', $content);

        // Render check: Kartu kelompok harus menampilkan badge dan subjudul minimalis
        $response = $this->actingAs($this->leader)->get(route('student.application.create', ['mode' => 'group']));
        $response->assertOk();
        $response->assertSee('Khusus Ketua Tim');
        $response->assertSee('Buat Kelompok Baru');
        $response->assertSee('Khusus Anggota Tim');
        $response->assertSee('Gabung ke Kelompok Rekan');
    }

    public function test_student_profile_guidelines_are_concise_without_bold_spam(): void
    {
        $bladePath = resource_path('views/student/profile/edit.blade.php');
        $content = file_get_contents($bladePath);
        $this->assertNotFalse($content);

        // Petunjuk profil harus bebas dari bolding berlebihan (<strong>) pada list item
        preg_match('/<!-- Petunjuk Kelengkapan Profil -->[\s\S]*?<\/div>/', $content, $matches);
        $this->assertNotEmpty($matches, 'Blok Petunjuk Kelengkapan Profil harus ditemukan.');
        $guideBlock = $matches[0];

        $this->assertStringNotContainsString('<strong>', $guideBlock, 'Petunjuk profil tidak boleh spam tag <strong> pada setiap butir kalimat.');

        // Maksimal 3 butir petunjuk untuk menjaga minimalisme UI
        $liCount = substr_count($guideBlock, '<li>');
        $this->assertLessThanOrEqual(3, $liCount, 'Petunjuk profil maksimal 3 butir panduan.');
    }
}
