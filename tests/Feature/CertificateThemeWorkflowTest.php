<?php

namespace Tests\Feature;

use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\Certificate;
use App\Models\CertificateTheme;
use App\Models\Evaluation;
use App\Models\FinalReport;
use App\Models\Logbook;
use App\Models\Placement;
use App\Models\StudentProfile;
use App\Models\Unit;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CertificateThemeWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate');
    }

    public function test_default_theme_is_seeded_and_active(): void
    {
        $theme = CertificateTheme::getActive();

        $this->assertNotNull($theme);
        $this->assertEquals(2026, $theme->year);
        $this->assertEquals('#00A3C4', $theme->primary_color);
        $this->assertEquals('#F36F21', $theme->secondary_color);
        $this->assertEquals('Dra. NOVELIJA, SH', $theme->signee_name);
        $this->assertEquals('Sekretaris', $theme->signee_position);
    }

    public function test_admin_can_update_certificate_theme_and_signee(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@gmail.com',
            'agency_profile_id' => null,
            'unit_id' => null,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.certificates.updateTheme'), [
            'year' => 2027,
            'theme_name' => 'Tema Dispusip Emerald 2027',
            'primary_color' => '#059669',
            'secondary_color' => '#D97706',
            'accent_color' => '#B45309',
            'number_format_pattern' => '400.14.5.4/{NUMBER}/436.7.17/{YEAR}',
            'signee_name' => 'Drs. H. M. NASER, M.Si',
            'signee_position' => 'Kepala Dinas',
            'signee_rank' => 'PEMBINA UTAMA MUDA',
            'signee_nip' => '19700101 199503 1 002',
            'mandate_text' => 'Kepala Dinas Perpustakaan dan Kearsipan Kota Surabaya',
            'city' => 'Surabaya',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.certificates.index', ['tab' => 'settings']));
        $response->assertSessionHas('success');

        $activeTheme = CertificateTheme::getActive();
        $this->assertEquals(2027, $activeTheme->year);
        $this->assertEquals('#059669', $activeTheme->primary_color);
        $this->assertEquals('Drs. H. M. NASER, M.Si', $activeTheme->signee_name);
        $this->assertEquals('Kepala Dinas', $activeTheme->signee_position);
    }

    public function test_certificate_view_renders_new_layout_and_tte_box(): void
    {
        $agency = AgencyProfile::create([
            'government_name' => 'Pemerintah Kota Surabaya',
            'agency_name' => 'Dinas Perpustakaan dan Kearsipan',
            'signee_name' => 'Mia Santi Dewi, S.H., M.Si.',
            'signee_nip' => '19720315 199803 2 004',
            'signee_position' => 'Kepala Dinas',
            'city' => 'Surabaya',
        ]);

        $unit = Unit::create([
            'agency_profile_id' => $agency->id,
            'name' => 'Bidang Pengelolaan Arsip',
            'quota' => 10,
        ]);

        $univ = University::create([
            'name' => 'Universitas 17 Agustus 1945 Surabaya',
            'evaluation_scheme' => 'mentor_only',
        ]);

        $student = User::factory()->create([
            'name' => 'Achmad Dhafa Sholahudin F',
            'role' => 'mahasiswa',
            'university_id' => $univ->id,
        ]);

        StudentProfile::create([
            'user_id' => $student->id,
            'nim' => '1212200055',
            'phone' => '081234567890',
            'jurusan' => 'Manajemen',
            'fakultas' => 'Ekonomi dan Bisnis',
            'universitas' => 'Universitas 17 Agustus 1945 Surabaya',
            'university_id' => $univ->id,
        ]);

        $app = Application::create([
            'user_id' => $student->id,
            'unit_id' => $unit->id,
            'status' => 'completed',
            'start_date' => '2026-08-10',
            'end_date' => '2026-10-10',
        ]);

        $placement = Placement::create([
            'application_id' => $app->id,
            'status' => 'active',
            'certificate_number' => '400.14.5.4/4692/436.7.17/2026',
            'certificate_hash' => 'dummy-hash-test-token-1234567890',
        ]);

        Evaluation::create([
            'placement_id' => $placement->id,
            'nilai_pembimbing' => 95,
            'nilai_akhir' => 95,
            'grade' => 'A',
            'status' => 'submitted',
        ]);

        FinalReport::create([
            'placement_id' => $placement->id,
            'file_path' => 'reports/test.pdf',
            'status' => 'approved',
        ]);

        Logbook::create([
            'placement_id' => $placement->id,
            'date' => '2026-08-11',
            'activity' => 'Merapikan arsip statis dinas',
            'status' => 'approved',
        ]);

        Certificate::create([
            'placement_id' => $placement->id,
            'certificate_number' => $placement->certificate_number,
            'file_path' => 'certificates/cert.pdf',
            'approval_status' => 'approved',
            'issued_at' => now()->toDateString(),
        ]);

        $admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@gmail.com',
            'agency_profile_id' => null,
            'unit_id' => null,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.certificates.show', $placement->id));

        $response->assertStatus(200);
        $response->assertSee('SERTIFIKAT');
        $response->assertSee('MAGANG');
        $response->assertSee('Nomor : 400.14.5.4/4692/436.7.17/2026');
        $response->assertSee('Achmad Dhafa Sholahudin F');
        $response->assertSee('Mahasiswa Program Studi Manajemen Fakultas Ekonomi dan Bisnis');
        $response->assertSee('Universitas 17 Agustus 1945 Surabaya');
        $response->assertSee('Ditandatangani secara elektronik oleh:');
        $response->assertSee('Dra. NOVELIJA, SH');
        $response->assertSee('PEMBINA TK I');
        $response->assertSee('NIP. 197111141992012001');
        $response->assertSee('Lampiran Transkrip Nilai Magang');
    }

    public function test_certificate_renders_with_custom_dynamic_theme_colors(): void
    {
        $theme = CertificateTheme::getActive();
        $theme->update([
            'primary_color' => '#059669', // Emerald
            'secondary_color' => '#7C3AED', // Purple
            'accent_color' => '#F59E0B',
        ]);

        $agency = AgencyProfile::first() ?? AgencyProfile::create([
            'government_name' => 'Pemerintah Kota Surabaya',
            'agency_name' => 'Dinas Perpustakaan dan Kearsipan',
            'signee_name' => 'Mia Santi Dewi, S.H., M.Si.',
            'signee_nip' => '19720315 199803 2 004',
            'signee_position' => 'Kepala Dinas',
            'city' => 'Surabaya',
        ]);

        $unit = Unit::first() ?? Unit::create([
            'agency_profile_id' => $agency->id,
            'name' => 'Bidang Pengelolaan Arsip',
            'quota' => 10,
        ]);

        $univ = University::first() ?? University::create([
            'name' => 'Universitas 17 Agustus 1945 Surabaya',
            'evaluation_scheme' => 'mentor_only',
        ]);

        $student = User::factory()->create([
            'name' => 'Achmad Dhafa Sholahudin F',
            'role' => 'mahasiswa',
            'university_id' => $univ->id,
        ]);

        StudentProfile::create([
            'user_id' => $student->id,
            'nim' => '1212200055',
            'phone' => '081234567890',
            'jurusan' => 'Manajemen',
            'fakultas' => 'Ekonomi dan Bisnis',
            'universitas' => 'Universitas 17 Agustus 1945 Surabaya',
            'university_id' => $univ->id,
        ]);

        $app = Application::create([
            'user_id' => $student->id,
            'unit_id' => $unit->id,
            'status' => 'completed',
            'start_date' => '2026-08-10',
            'end_date' => '2026-10-10',
        ]);

        $placement = Placement::create([
            'application_id' => $app->id,
            'status' => 'active',
            'certificate_number' => '400.14.5.4/4692/436.7.17/2026',
            'certificate_hash' => 'dummy-hash-test-token-color-custom',
        ]);

        Evaluation::create([
            'placement_id' => $placement->id,
            'nilai_pembimbing' => 95,
            'nilai_akhir' => 95,
            'grade' => 'A',
            'status' => 'submitted',
        ]);

        FinalReport::create([
            'placement_id' => $placement->id,
            'file_path' => 'reports/test.pdf',
            'status' => 'approved',
        ]);

        Logbook::create([
            'placement_id' => $placement->id,
            'date' => '2026-08-11',
            'activity' => 'Merapikan arsip statis dinas',
            'status' => 'approved',
        ]);

        Certificate::create([
            'placement_id' => $placement->id,
            'certificate_number' => $placement->certificate_number,
            'file_path' => 'certificates/cert.pdf',
            'approval_status' => 'approved',
            'issued_at' => now()->toDateString(),
        ]);

        $admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin_color@gmail.com',
            'agency_profile_id' => null,
            'unit_id' => null,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.certificates.show', $placement->id));

        $response->assertStatus(200);
        // Assert dynamic shades from emerald and purple are rendered in the SVG
        $response->assertSee('#059669'); // base primary
        $response->assertSee('#7C3AED'); // base secondary
        $response->assertSee('SERTIFIKAT');
        $response->assertSee('Achmad Dhafa Sholahudin F');
    }

    public function test_batch_print_renders_approved_certificates(): void
    {
        $agency = AgencyProfile::create([
            'government_name' => 'Pemerintah Kota Surabaya',
            'agency_name' => 'Dinas Perpustakaan dan Kearsipan',
            'city' => 'Surabaya',
        ]);

        $unit = Unit::create([
            'agency_profile_id' => $agency->id,
            'name' => 'Bidang Pengelolaan Arsip',
            'quota' => 10,
        ]);

        $univ = University::create([
            'name' => 'Universitas Airlangga',
            'evaluation_scheme' => 'mentor_only',
        ]);

        $student = User::factory()->create([
            'name' => 'Budi Santoso',
            'role' => 'mahasiswa',
            'university_id' => $univ->id,
        ]);

        StudentProfile::create([
            'user_id' => $student->id,
            'nim' => '1122334455',
            'phone' => '081234567890',
            'jurusan' => 'Ilmu Informasi dan Perpustakaan',
            'universitas' => 'Universitas Airlangga',
            'university_id' => $univ->id,
        ]);

        $app = Application::create([
            'user_id' => $student->id,
            'unit_id' => $unit->id,
            'status' => 'completed',
            'start_date' => '2026-08-01',
            'end_date' => '2026-10-01',
        ]);

        $placement = Placement::create([
            'application_id' => $app->id,
            'status' => 'active',
            'certificate_number' => '400.14.5.4/9999/436.7.17/2026',
            'certificate_hash' => 'hash-test-batch-print-123456789',
        ]);

        Evaluation::create([
            'placement_id' => $placement->id,
            'nilai_pembimbing' => 90,
            'nilai_akhir' => 90,
            'grade' => 'A',
            'status' => 'submitted',
        ]);

        FinalReport::create([
            'placement_id' => $placement->id,
            'file_path' => 'reports/budi.pdf',
            'status' => 'approved',
        ]);

        Logbook::create([
            'placement_id' => $placement->id,
            'date' => '2026-08-02',
            'activity' => 'Review buku langka',
            'status' => 'approved',
        ]);

        Certificate::create([
            'placement_id' => $placement->id,
            'certificate_number' => $placement->certificate_number,
            'file_path' => 'certificates/budi.pdf',
            'approval_status' => 'approved',
            'issued_at' => now()->toDateString(),
        ]);

        $admin = User::factory()->create(['role' => 'admin', 'agency_profile_id' => null]);

        $response = $this->actingAs($admin)->get(route('admin.certificates.batchPrint'));

        $response->assertStatus(200);
        $response->assertSee('Cetak Kolektif Sertifikat Magang');
        $response->assertSee('Budi Santoso');
        $response->assertSee('Nomor : 400.14.5.4/9999/436.7.17/2026');
        $response->assertSee('Cetak Semua Halaman (Ctrl + P)');
    }

    public function test_theme_preview_renders_unsaved_values_without_persisting(): void
    {
        $agency = AgencyProfile::create([
            'government_name' => 'Pemerintah Kota Surabaya',
            'agency_name' => 'Dinas Perpustakaan dan Kearsipan',
            'city' => 'Surabaya',
        ]);
        $unit = Unit::create(['agency_profile_id' => $agency->id, 'name' => 'Bidang Arsip', 'quota' => 5]);
        $univ = University::create(['name' => 'Universitas Airlangga', 'evaluation_scheme' => 'mentor_only']);
        $student = User::factory()->create(['name' => 'Siti Contoh', 'role' => 'mahasiswa', 'university_id' => $univ->id]);
        StudentProfile::create([
            'user_id' => $student->id, 'nim' => '99887766', 'phone' => '081200000000',
            'jurusan' => 'Ilmu Perpustakaan', 'universitas' => 'Universitas Airlangga', 'university_id' => $univ->id,
        ]);
        $app = Application::create([
            'user_id' => $student->id, 'unit_id' => $unit->id, 'status' => 'completed',
            'start_date' => '2026-08-01', 'end_date' => '2026-10-01',
        ]);
        // sengaja tanpa certificate_number/hash: pratinjau tidak boleh menerbitkannya
        $placement = Placement::create(['application_id' => $app->id, 'status' => 'active']);
        Evaluation::create(['placement_id' => $placement->id, 'nilai_pembimbing' => 88, 'nilai_akhir' => 88, 'grade' => 'A', 'status' => 'submitted']);

        $admin = User::factory()->create(['role' => 'admin', 'agency_profile_id' => null]);
        $themesBefore = CertificateTheme::count();
        $activeColor = CertificateTheme::getActive()->primary_color;

        $response = $this->actingAs($admin)->post(route('admin.certificates.previewTheme'), [
            'primary_color' => '#12ABCD',
            'secondary_color' => '#FF8800',
            'accent_color' => '#FFB800',
            'signee_name' => 'Pejabat Uji Pratinjau',
            'sample_placement_id' => $placement->id,
        ]);

        $response->assertStatus(200);
        $response->assertSee('Siti Contoh');
        $response->assertSee('Pratinjau tema');
        $response->assertSee('#12ABCD');
        $response->assertSee('Pejabat Uji Pratinjau');

        $this->assertSame($themesBefore, CertificateTheme::count());
        $this->assertSame($activeColor, CertificateTheme::getActive()->primary_color);
        $this->assertNull($placement->fresh()->certificate_hash);
        $this->assertNull($placement->fresh()->certificate_number);
    }

    public function test_theme_preview_rejects_invalid_color(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'agency_profile_id' => null]);
        $this->actingAs($admin)
            ->post(route('admin.certificates.previewTheme'), ['primary_color' => 'bukan-warna'])
            ->assertSessionHasErrors('primary_color');
    }

    public function test_batch_print_redirects_when_empty(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'agency_profile_id' => null]);

        $response = $this->actingAs($admin)->get(route('admin.certificates.batchPrint'));

        $response->assertRedirect(route('admin.certificates.index'));
        $response->assertSessionHas('error');
    }

    public function test_agency_show_displays_completed_students_tab_and_quick_links(): void
    {
        $agency = AgencyProfile::create([
            'government_name' => 'Pemerintah Kota Surabaya',
            'agency_name' => 'Dinas Perpustakaan dan Kearsipan',
            'city' => 'Surabaya',
        ]);

        $unit = Unit::create([
            'agency_profile_id' => $agency->id,
            'name' => 'Bidang Sekretariat',
            'quota' => 10,
        ]);

        $admin = User::factory()->create(['role' => 'admin', 'agency_profile_id' => null]);

        $response = $this->actingAs($admin)->get(route('admin.agencies.show', $agency->id));

        $response->assertStatus(200);
        $response->assertSee('Unit Bidang Magang');
        $response->assertSee('Unit Bidang & Kuota', false);
        $response->assertSee('Kelulusan & Sertifikat', false);
        $response->assertSee('Atur TTE & Tema Sertifikat', false);
        $response->assertSee('Kelola Sertifikat');
    }
}
