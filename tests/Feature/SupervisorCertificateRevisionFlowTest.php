<?php

namespace Tests\Feature;

use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\Certificate;
use App\Models\Evaluation;
use App\Models\Placement;
use App\Models\Unit;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupervisorCertificateRevisionFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $adminDinas;
    private User $supervisor;
    private User $student;
    private Placement $placement;
    private Certificate $certificate;

    protected function setUp(): void
    {
        parent::setUp();

        $agency = AgencyProfile::create([
            'agency_name' => 'Dinas Perpustakaan dan Kearsipan Kota Surabaya',
            'alias' => 'Dispusip Surabaya',
        ]);

        $unit = Unit::create([
            'agency_profile_id' => $agency->id,
            'name' => 'Bidang Pembinaan dan Pengelolaan Perpustakaan',
            'quota' => 15,
        ]);

        $univ = University::create([
            'name' => 'Universitas Negeri Surabaya',
            'code' => 'UNESA',
            'evaluation_scheme' => 'mentor_only',
            'weight_mentor' => 100,
            'weight_lecturer' => 0,
        ]);

        $this->adminDinas = User::factory()->create([
            'name' => 'Admin Dispusip Surabaya',
            'role' => 'admin',
            'agency_profile_id' => $agency->id,
        ]);

        $this->supervisor = User::factory()->create([
            'name' => 'Bambang Supriyadi, S.Sos., M.AP',
            'role' => 'supervisor',
            'agency_profile_id' => $agency->id,
        ]);

        $this->student = User::factory()->create([
            'name' => 'Hana Pertiwi',
            'role' => 'mahasiswa',
            'university_id' => $univ->id,
        ]);

        $this->student->studentProfile()->create([
            'nim' => '22081010018',
            'universitas' => 'Universitas Negeri Surabaya',
            'jurusan' => 'S1 Ilmu Perpustakaan',
            'phone' => '081234567890',
        ]);

        $app = Application::create([
            'user_id' => $this->student->id,
            'unit_id' => $unit->id,
            'status' => 'completed',
            'start_date' => '2026-08-02',
            'end_date' => '2026-10-31',
        ]);

        $this->placement = Placement::create([
            'application_id' => $app->id,
            'certificate_number' => 'SERT/181/DISPUSIP-SBY/2026',
            'certificate_hash' => 'hash-cert-hana-181',
        ]);

        Evaluation::create([
            'placement_id' => $this->placement->id,
            'nilai_disiplin' => 95,
            'nilai_kinerja' => 94,
            'nilai_laporan' => 94,
            'nilai_akademik' => 0,
            'final_score' => 94.33,
            'grade' => 'A',
            'catatan' => 'Hana memiliki dedikasi tinggi dan pemahaman otomasi perpustakaan yang unggul.',
        ]);

        $this->placement->finalreport()->create([
            'file_path' => 'final_reports/laporan_hana.pdf',
            'status' => 'approved',
        ]);

        $this->placement->logbooks()->create([
            'date' => '2026-08-03',
            'activity' => 'Melakukan katalogisasi buku perpustakaan digital.',
            'status' => 'approved',
        ]);

        $this->certificate = Certificate::create([
            'placement_id' => $this->placement->id,
            'certificate_number' => 'SERT/181/DISPUSIP-SBY/2026',
            'file_path' => 'certificates/181.pdf',
            'issued_at' => now()->toDateString(),
            'approval_status' => 'waiting_approval',
        ]);
    }

    /**
     * 1. Halaman /supervisor/certificates menampilkan tombol 'Revisi' (bukan 'Kembalikan') dan tab 'Perlu Revisi'.
     */
    public function test_supervisor_certificates_index_shows_revisi_button_without_overlapping_popup(): void
    {
        $response = $this->actingAs($this->supervisor)->get(route('supervisor.certificates.index'));
        $response->assertOk();

        // Memastikan tombol Revisi ada dan mengarah ke route supervisor.certificates.revision
        $response->assertSee('Revisi');
        $response->assertSee(route('supervisor.certificates.revision', $this->placement->id));

        // Memastikan teks 'Kembalikan' yang sebelumnya tumpang tindih sudah tidak ada di tombol aksi
        $response->assertDontSee('Kembalikan');

        // Memastikan tab 'Perlu Revisi' tidak lagi memuat tanda kurung (Revisi)
        $response->assertSee('Perlu Revisi');
        $response->assertDontSee('Perlu Ditinjau Ulang (Revisi)');

        // Memastikan tombol Sahkan tidak memuat tanda kurung (ACC)
        $response->assertDontSee('Sahkan (ACC)');
    }

    /**
     * 2. Halaman khusus revisi /supervisor/certificates/{placementId}/revision dapat diakses dengan baik dan menampilkan 3 aspek.
     */
    public function test_supervisor_can_access_dedicated_certificate_revision_page(): void
    {
        $response = $this->actingAs($this->supervisor)->get(route('supervisor.certificates.revision', $this->placement->id));
        $response->assertOk();

        // Verifikasi konteks mahasiswa & sertifikat
        $response->assertSee('Pengembalian Revisi Sertifikat Magang');
        $response->assertSee('Hana Pertiwi');
        $response->assertSee('22081010018');
        $response->assertSee('S1 Ilmu Perpustakaan');
        $response->assertSee('Universitas Negeri Surabaya');

        // Verifikasi pilihan aspek-aspek revisi
        $response->assertSee('Koreksi Nilai Evaluasi');
        $response->assertSee('Data Mahasiswa');
        $response->assertSee('Validasi Logbook');
        $response->assertSee('Aspek Lainnya');

        // Verifikasi nilai evaluasi aktual tampil di layar
        $response->assertSee('95'); // nilai disiplin
        $response->assertSee('94.33'); // simulasi nilai akhir
        $response->assertSee('Kirim Catatan Revisi');
    }

    /**
     * 3. Validasi form revisi: menolak jika tidak ada aspek yang dipilih atau catatan < 5 karakter.
     */
    public function test_supervisor_revision_validation_rejects_empty_aspects_or_short_notes(): void
    {
        $response = $this->actingAs($this->supervisor)->post(route('supervisor.certificates.submitRevision', $this->placement->id), [
            'aspects' => [],
            'notes' => 'pendek',
        ]);
        $response->assertSessionHasErrors(['aspects']);

        $response2 = $this->actingAs($this->supervisor)->post(route('supervisor.certificates.submitRevision', $this->placement->id), [
            'aspects' => ['identitas'],
            'notes' => 'abc',
        ]);
        $response2->assertSessionHasErrors(['notes']);
    }

    /**
     * 4. Supervisor sukses mengirim revisi dengan aspek identitas dan nilai, status berubah ke revision.
     */
    public function test_supervisor_can_submit_revision_with_multiple_aspects_and_notes(): void
    {
        $response = $this->actingAs($this->supervisor)->post(route('supervisor.certificates.submitRevision', $this->placement->id), [
            'aspects' => ['identitas', 'nilai'],
            'aspect_notes' => [
                'identitas' => 'Ejaan nama mohon dicek kembali.',
                'nilai' => 'Skor presensi mohon disinkronkan dengan logbook.',
            ],
            'notes' => 'Terdapat koreksi pada ejaan nama mahasiswa dan skor kehadiran.',
        ]);

        $response->assertRedirect(route('supervisor.certificates.index'));
        $response->assertSessionHas('success');

        // Verifikasi sertifikat berubah status menjadi 'revision'
        $this->certificate->refresh();
        $this->assertEquals('revision', $this->certificate->approval_status);
        $this->assertNull($this->certificate->approved_by);
        $this->assertNull($this->certificate->approved_at);

        // Verifikasi catatan memuat aspek yang dipilih
        $this->assertStringContainsString('Aspek Revisi: Data Mahasiswa & Magang, Nilai Evaluasi & Transkrip', $this->certificate->notes);
        $this->assertStringContainsString('Terdapat koreksi pada ejaan nama mahasiswa', $this->certificate->notes);
        $this->assertStringContainsString('Ejaan nama mohon dicek kembali', $this->certificate->notes);

        // Verifikasi Admin Dinas dapat melihat catatan revisi dari supervisor di halaman perbaikan admin
        $adminResp = $this->actingAs($this->adminDinas)->get(route('admin.certificates.revision', $this->placement->id));
        $adminResp->assertOk();
        $adminResp->assertSee('Catatan Revisi dari Atasan Kepegawaian');
        $adminResp->assertSee('Data Mahasiswa');
        $adminResp->assertSee('Nilai Evaluasi');
    }

    /**
     * 5. Audit ketat: Memastikan di seluruh antarmuka supervisor tidak ada kata dalam kurung () seperti (ACC), (Revisi), dll.
     */
    public function test_supervisor_views_contain_no_parentheses_or_redundancies(): void
    {
        $urls = [
            route('supervisor.dashboard'),
            route('supervisor.applications.index'),
            route('supervisor.applications.show', $this->placement->application_id),
            route('supervisor.certificates.index'),
            route('supervisor.certificates.revision', $this->placement->id),
            route('supervisor.reports.index'),
            route('supervisor.reports.show', $this->placement->id),
        ];

        foreach ($urls as $url) {
            $resp = $this->actingAs($this->supervisor)->get($url);
            $resp->assertOk();

            $content = $resp->getContent();

            // Memastikan tidak ada teks (ACC)
            $this->assertStringNotContainsString('(ACC)', $content, "Ditemukan '(ACC)' pada {$url}");

            // Memastikan tidak ada teks (Revisi)
            $this->assertStringNotContainsString('(Revisi)', $content, "Ditemukan '(Revisi)' pada {$url}");

            // Memastikan tidak ada tombol 'Kembalikan'
            $this->assertStringNotContainsString('>Kembalikan<', $content, "Ditemukan 'Kembalikan' pada {$url}");
        }
    }
}
