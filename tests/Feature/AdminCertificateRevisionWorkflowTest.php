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

class AdminCertificateRevisionWorkflowTest extends TestCase
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
            'name' => 'Bidang Pengelolaan, Perlindungan, dan Penyelamatan Kearsipan',
            'quota' => 10,
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
            'name' => 'Atasan Kepegawaian Dispusip',
            'role' => 'supervisor',
            'agency_profile_id' => $agency->id,
        ]);

        $this->student = User::factory()->create([
            'name' => 'Kartika Sari',
            'role' => 'mahasiswa',
            'university_id' => $univ->id,
        ]);

        $this->student->studentProfile()->create([
            'nim' => '22081010021',
            'universitas' => 'Universitas Negeri Surabaya',
            'jurusan' => 'Kearsipan Digital',
            'phone' => '081234567890',
        ]);

        $app = Application::create([
            'user_id' => $this->student->id,
            'unit_id' => $unit->id,
            'status' => 'completed',
            'start_date' => '2026-07-15',
            'end_date' => '2026-10-10',
        ]);

        $this->placement = Placement::create([
            'application_id' => $app->id,
            'certificate_number' => 'SERT/014/DISPUSIP-SBY/2026',
            'certificate_hash' => 'hash-cert-12345',
        ]);

        Evaluation::create([
            'placement_id' => $this->placement->id,
            'nilai_disiplin' => 80,
            'nilai_kinerja' => 85,
            'nilai_laporan' => 84,
            'nilai_akademik' => 0,
            'final_score' => 83,
            'grade' => 'B+',
            'catatan' => 'Perlu ditingkatkan kedisiplinan presensi.',
        ]);

        $this->placement->finalreport()->create([
            'file_path' => 'final_reports/test_laporan.pdf',
            'status' => 'approved',
        ]);

        $this->placement->logbooks()->create([
            'date' => '2026-07-16',
            'activity' => 'Melakukan pemilahan arsip inaktif dinas.',
            'status' => 'approved',
        ]);

        $this->certificate = Certificate::create([
            'placement_id' => $this->placement->id,
            'certificate_number' => 'SERT/014/DISPUSIP-SBY/2026',
            'file_path' => 'certificates/14.pdf',
            'issued_at' => now()->toDateString(),
            'approval_status' => 'revision',
            'notes' => 'Catatan Atasan: Mohon diverifikasi kembali skor kehadiran pada lembar presensi sebelum pengesahan sertifikat.',
        ]);
    }

    /**
     * 1. Halaman /admin/certificates menampilkan tombol 'Perbaiki Sertifikat' untuk status revision.
     */
    public function test_admin_certificates_index_shows_perbaiki_sertifikat_for_revision(): void
    {
        $response = $this->actingAs($this->adminDinas)->get(route('admin.certificates.index'));
        $response->assertOk();
        $response->assertSee('Perlu Revisi');
        $response->assertSee('Perbaiki Sertifikat');
        $response->assertSee(route('admin.certificates.revision', $this->placement->id));
        // Pastikan tidak ada tombol cetak e-sertifikat untuk Kartika Sari
        $response->assertSee('Catatan Atasan: Mohon diverifikasi kembali skor kehadiran');
    }

    /**
     * 2. Halaman khusus revisi /admin/certificates/{placementId}/revision dapat dibuka dengan baik.
     */
    public function test_admin_can_access_dedicated_certificate_revision_page(): void
    {
        $response = $this->actingAs($this->adminDinas)->get(route('admin.certificates.revision', $this->placement->id));
        $response->assertOk();
        $response->assertSee('Pusat Perbaikan');
        $response->assertSee('Catatan Revisi dari Atasan Kepegawaian');
        $response->assertSee('Catatan Atasan: Mohon diverifikasi kembali skor kehadiran pada lembar presensi sebelum pengesahan sertifikat.');
        $response->assertSee('Kartika Sari');
        $response->assertSee('22081010021');
        $response->assertSee('Koreksi Nilai Evaluasi');
        $response->assertSee('Data Mahasiswa');
        $response->assertSee('Ajukan Ulang ke Atasan');
    }

    /**
     * 3. Submit revisi dengan aksi resubmit memperbarui nilai, biodata, dan merubah status sertifikat ke waiting_approval.
     */
    public function test_admin_can_submit_revision_and_resubmit_to_supervisor(): void
    {
        $response = $this->actingAs($this->adminDinas)->post(route('admin.certificates.updateRevision', $this->placement->id), [
            'student_name' => 'Kartika Sari, S.Tr.Ars',
            'nim' => '22081010021',
            'universitas' => 'Universitas Negeri Surabaya',
            'jurusan' => 'Kearsipan Digital Terapan',
            'start_date' => '2026-07-15',
            'end_date' => '2026-10-15',
            'nilai_disiplin' => 90, // Dinaikkan sesuai verifikasi presensi
            'nilai_kinerja' => 92,
            'nilai_laporan' => 91,
            'nilai_akademik' => 0,
            'catatan_evaluasi' => 'Kehadiran dan kinerja terverifikasi 100%.',
            'resolution_note' => 'Skor presensi telah diperiksa ulang sesuai buku kehadiran (90). Data mahasiswa diperbarui.',
            'action' => 'resubmit',
        ]);

        $response->assertRedirect(route('admin.certificates.index'));
        $response->assertSessionHas('success');

        // Verifikasi perubahan terintegrasi di User & StudentProfile
        $this->student->refresh();
        $this->assertEquals('Kartika Sari, S.Tr.Ars', $this->student->name);
        $this->assertEquals('Kearsipan Digital Terapan', $this->student->studentProfile->jurusan);

        // Verifikasi perubahan Application
        $this->placement->application->refresh();
        $this->assertEquals('2026-10-15', $this->placement->application->end_date);

        // Verifikasi perubahan Evaluation
        $eval = $this->placement->evaluation()->first();
        $this->assertEquals(90, $eval->nilai_disiplin);
        $this->assertEquals(91, (float) $eval->final_score);
        $this->assertEquals('A', $eval->grade);

        // Verifikasi Certificate berstatus waiting_approval
        $this->certificate->refresh();
        $this->assertEquals('waiting_approval', $this->certificate->approval_status);
        $this->assertStringContainsString('Skor presensi telah diperiksa ulang', $this->certificate->notes);

        // Verifikasi halaman index admin kini menampilkan status 'Menunggu ACC Atasan' dan tombol 'Pratinjau Draft'
        $indexResponse = $this->actingAs($this->adminDinas)->get(route('admin.certificates.index'));
        $indexResponse->assertSee('Menunggu ACC Atasan');
        $indexResponse->assertSee('Pratinjau Draft');
    }
}
