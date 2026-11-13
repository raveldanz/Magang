<?php

namespace Tests\Feature;

use App\Enums\ApplicationStatus;
use App\Enums\ReviewStatus;
use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\Evaluation;
use App\Models\FinalReport;
use App\Models\Logbook;
use App\Models\Placement;
use App\Models\Unit;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MentorElderlyFriendlyOptimizationTest extends TestCase
{
    use RefreshDatabase;

    private function createScenario(): array
    {
        $agency = AgencyProfile::create(['agency_name' => 'Dinas Perpustakaan dan Kearsipan']);
        $unit = Unit::create(['agency_profile_id' => $agency->id, 'name' => 'Bidang Layanan Pustaka', 'quota' => 10]);

        $univCohort = University::create([
            'name' => 'Universitas Negeri Surabaya',
            'code' => 'UNESA',
            'evaluation_scheme' => 'dual_evaluation',
            'weight_mentor' => 40,
            'weight_lecturer' => 60,
            'require_dpl' => true,
        ]);
        $univOther = University::create(['name' => 'Universitas Luar Scope', 'code' => 'ULS']);

        $supervisor = User::factory()->create([
            'role' => 'supervisor',
            'name' => 'Kepala Dinas Senior',
            'agency_profile_id' => $agency->id,
        ]);
        $mentor = User::factory()->create([
            'role' => 'mentor',
            'name' => 'Mentor Senior',
            'agency_profile_id' => $agency->id,
        ]);
        $dosen = User::factory()->create([
            'role' => 'dosen',
            'name' => 'Dr. Siti Pembimbing',
            'university_id' => $univCohort->id,
        ]);

        // Mahasiswa aktif yang sudah berjalan & siap dinilai (tidak terkunci)
        $studentActive = User::factory()->create([
            'role' => 'mahasiswa',
            'name' => 'Budi Santoso',
            'university_id' => $univCohort->id,
        ]);
        $studentActive->studentProfile()->create([
            'nim' => '22051204001',
            'universitas' => $univCohort->name,
            'jurusan' => 'Teknik Informatika',
            'phone' => '081234567890',
        ]);
        $appActive = Application::create([
            'user_id' => $studentActive->id,
            'unit_id' => $unit->id,
            'status' => 'active',
            'start_date' => now()->subDays(20)->toDateString(),
            'end_date' => now()->addDays(25)->toDateString(),
        ]);
        $placementActive = Placement::create([
            'application_id' => $appActive->id,
            'mentor_id' => $mentor->id,
            'academic_advisor_id' => $dosen->id,
        ]);
        $logbookPending = Logbook::create([
            'placement_id' => $placementActive->id,
            'date' => now()->subDay()->toDateString(),
            'activity' => 'Menginput katalog arsip buku daerah',
            'status' => 'pending',
            'lecturer_status' => 'pending',
            'attachment' => 'logbooks/sample-evidence.jpg',
        ]);
        $reportRevision = FinalReport::create([
            'placement_id' => $placementActive->id,
            'file_path' => 'reports/budi-akhir.pdf',
            'status' => 'revision',
        ]);

        // Mahasiswa lain berstatus accepted (evaluasi masih terkunci sebelum hari mulai)
        $studentUpcoming = User::factory()->create([
            'role' => 'mahasiswa',
            'name' => 'Citra Lestari',
            'university_id' => $univCohort->id,
        ]);
        $studentUpcoming->studentProfile()->create([
            'nim' => '22051204002',
            'universitas' => $univCohort->name,
            'jurusan' => 'Sistem Informasi',
            'phone' => '081234567891',
        ]);
        $appUpcoming = Application::create([
            'user_id' => $studentUpcoming->id,
            'unit_id' => $unit->id,
            'status' => 'accepted',
            'start_date' => now()->addDays(10)->toDateString(),
            'end_date' => now()->addDays(70)->toDateString(),
        ]);
        $placementUpcoming = Placement::create([
            'application_id' => $appUpcoming->id,
            'mentor_id' => $mentor->id,
            'academic_advisor_id' => $dosen->id,
        ]);

        // Pengajuan menunggu persetujuan atasan untuk uji halaman supervisor
        $studentWaiting = User::factory()->create([
            'role' => 'mahasiswa',
            'name' => 'Dina Puspita',
            'university_id' => $univOther->id,
        ]);
        $studentWaiting->studentProfile()->create([
            'nim' => '22051204099',
            'universitas' => $univOther->name,
            'jurusan' => 'Manajemen',
            'phone' => '081234567892',
        ]);
        Application::create([
            'user_id' => $studentWaiting->id,
            'unit_id' => $unit->id,
            'status' => 'waiting_approval',
            'start_date' => now()->addDays(5)->toDateString(),
            'end_date' => now()->addDays(45)->toDateString(),
        ]);

        return compact(
            'agency', 'unit', 'univCohort', 'univOther',
            'supervisor', 'mentor', 'dosen',
            'studentActive', 'appActive', 'placementActive', 'logbookPending', 'reportRevision',
            'studentUpcoming', 'appUpcoming', 'placementUpcoming'
        );
    }

    public function test_viewport_allows_pinch_zoom_and_no_auto_notification_prompt(): void
    {
        $data = $this->createScenario();

        $html = $this->actingAs($data['mentor'])
            ->get(route('mentor.dashboard'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('<meta name="viewport" content="width=device-width, initial-scale=1">', $html);
        $this->assertStringNotContainsString('user-scalable=no', $html);
        $this->assertStringNotContainsString('maximum-scale=1', $html);
        $this->assertStringContainsString('window.enableWebPushNotifications', $html);
        $beforeOptIn = strstr($html, 'window.enableWebPushNotifications', true) ?: $html;
        $this->assertStringNotContainsString('Notification.requestPermission()', $beforeOptIn);
    }

    public function test_mentor_and_supervisor_show_standardized_indonesian_status_ui_while_db_stays_english(): void
    {
        $data = $this->createScenario();

        // Pastikan status di database tetap berbahasa Inggris terintegrasi
        $this->assertSame('active', $data['appActive']->fresh()->getRawOriginal('status'));
        $this->assertSame('pending', $data['logbookPending']->fresh()->getRawOriginal('status'));
        $this->assertSame('revision', $data['reportRevision']->fresh()->getRawOriginal('status'));

        // Mentor dashboard, logbooks, & detail mahasiswa memakai Bahasa Indonesia seragam
        $dashboard = $this->actingAs($data['mentor'])->get(route('mentor.dashboard'))->assertOk()->getContent();
        $this->assertStringContainsString('data-status="active"', $dashboard);
        $this->assertStringContainsString(ApplicationStatus::ACTIVE->localizedLabel('mentor'), $dashboard);
        $this->assertStringContainsString(ReviewStatus::REVISION->localizedLabel('mentor'), $dashboard);
        $this->assertStringNotContainsString('Menunggu Review (Pending)', $dashboard);
        $this->assertStringNotContainsString('Disetujui (Approved)', $dashboard);

        $logbooks = $this->actingAs($data['mentor'])->get(route('mentor.logbooks.index'))->assertOk()->getContent();
        $this->assertStringContainsString(ReviewStatus::PENDING->localizedLabel('mentor'), $logbooks);
        $this->assertStringNotContainsString('Menunggu ACC', $logbooks);

        // Supervisor dashboard, laporan, & persetujuan aplikasi
        $supDash = $this->actingAs($data['supervisor'])->get(route('supervisor.dashboard'))->assertOk()->getContent();
        $this->assertStringContainsString(ApplicationStatus::WAITING_APPROVAL->localizedLabel('supervisor'), $supDash);

        $supReports = $this->actingAs($data['supervisor'])->get(route('supervisor.reports.index'))->assertOk()->getContent();
        $this->assertStringContainsString(ReviewStatus::REVISION->localizedLabel('supervisor'), $supReports);
        $this->assertStringNotContainsString('Menunggu Review (Pending)', $supReports);

        $supApps = $this->actingAs($data['supervisor'])->get(route('supervisor.applications.index'))->assertOk()->getContent();
        $this->assertStringContainsString(ApplicationStatus::WAITING_APPROVAL->localizedLabel('supervisor'), $supApps);
    }

    public function test_no_user_facing_dpl_jargon_and_no_emojis_in_elderly_workflows(): void
    {
        $data = $this->createScenario();

        $urls = [
            route('mentor.dashboard'),
            route('mentor.students.show', $data['placementActive']->id),
            route('mentor.evaluations.create', $data['placementActive']->id),
            route('mentor.logbooks.index'),
            route('mentor.logbooks.show', $data['logbookPending']->id),
            route('profile.edit'),
        ];

        $forbiddenEmojis = ['⚠', '✅', '📊', '🔍', '📌', '🎉', '💡'];

        foreach ($urls as $url) {
            $html = $this->actingAs($data['mentor'])->get($url)->assertOk()->getContent();
            $visibleText = strip_tags(preg_replace('#<(script|style)\b[^>]*>.*?</\1>#is', '', $html));

            $this->assertDoesNotMatchRegularExpression('/\bDPL\b/', $visibleText, "Masih ditemukan singkatan DPL pada: {$url}");
            foreach ($forbiddenEmojis as $emoji) {
                $this->assertStringNotContainsString($emoji, $visibleText, "Ditemukan emoji {$emoji} pada: {$url}");
            }
        }

        $detailText = strip_tags($this->actingAs($data['mentor'])->get(route('mentor.students.show', $data['placementActive']->id))->getContent());
        $this->assertStringContainsString('Dosen Pembimbing', $detailText);
    }

    public function test_profile_and_password_reset_views_are_friendly_and_indonesian(): void
    {
        $data = $this->createScenario();

        $profile = $this->actingAs($data['mentor'])->get(route('profile.edit'))->assertOk()->getContent();
        $this->assertStringContainsString('Informasi Akun Anda', $profile);
        $this->assertStringContainsString('Pengaturan Lanjutan Akun', $profile);
        $this->assertStringContainsString('Tampilkan kata sandi', $profile);

        auth()->logout();
        $forgot = $this->get(route('password.request'))->assertOk()->getContent();
        $this->assertStringNotContainsString('Forgot your password?', $forgot);
        $this->assertStringContainsString('Kirim Tautan Atur Ulang', $forgot);
    }

    public function test_mentor_dashboard_action_alerts_scoped_universities_and_accurate_eval_count(): void
    {
        $data = $this->createScenario();

        $response = $this->actingAs($data['mentor'])->get(route('mentor.dashboard'))->assertOk();
        $response->assertSeeText('Perlu Tindakan Segera')
            ->assertSeeText('Budi Santoso')
            ->assertSeeText('Universitas Negeri Surabaya')
            ->assertDontSeeText('Universitas Luar Scope');

        // Pastikan mahasiswa upcoming yang terkunci tidak terhitung ke total_pending_eval
        $response->assertViewHas('stats', function (array $stats) {
            return $stats['total_pending_eval'] === 1
                && $stats['active_students'] === 1;
        });

        $response->assertViewHas('actionAlerts', function (array $alerts) {
            return $alerts['pending_logbooks_count'] === 1;
        });
    }

    public function test_logbook_search_is_case_insensitive_and_views_include_lightbox_and_confirmations(): void
    {
        $data = $this->createScenario();

        // Pencarian huruf kecil tetap menemukan "Budi Santoso" dan tombol Reset tersedia
        $this->actingAs($data['mentor'])
            ->get(route('mentor.logbooks.index', ['search' => 'budi']))
            ->assertOk()
            ->assertSeeText('Budi Santoso')
            ->assertSeeText('Reset');

        // Halaman detail logbook memakai lightbox in-page + modal konfirmasi Setujui & Minta Revisi
        $showHtml = $this->actingAs($data['mentor'])
            ->get(route('mentor.logbooks.show', $data['logbookPending']->id))
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('lightboxOpen', $showHtml);
        $this->assertStringContainsString('open-confirm-modal', $showHtml);

        // Detail mahasiswa memiliki navigasi cepat 3-tahap kelulusan + konfirmasi logbook mingguan
        $studentHtml = $this->actingAs($data['mentor'])
            ->get(route('mentor.students.show', $data['placementActive']->id))
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('#bagian-logbook', $studentHtml);
        $this->assertStringContainsString('#bagian-laporan', $studentHtml);
        $this->assertStringContainsString('#bagian-nilai', $studentHtml);
        $this->assertStringContainsString('Setujui yang Dipilih', $studentHtml);
    }

    public function test_evaluation_form_shows_simplified_mode_grade_guide_early_notice_and_saves_decimals(): void
    {
        $data = $this->createScenario();

        $formHtml = $this->actingAs($data['mentor'])
            ->get(route('mentor.evaluations.create', $data['placementActive']->id))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Sesuaikan Aspek Penilaian', $formHtml);
        $this->assertStringContainsString('Panduan Nilai &amp; Predikat', $formHtml);
        $this->assertStringContainsString('Catatan Jadwal Penilaian', $formHtml);
        $this->assertStringContainsString('step="0.01"', $formHtml);
        $this->assertStringContainsString('Bobot 40% nilai Mentor Lapangan dan 60% nilai Dosen Pembimbing', $formHtml);

        // Simpan nilai desimal
        $this->actingAs($data['mentor'])
            ->post(route('mentor.evaluations.store', $data['placementActive']->id), [
                'aspects' => [
                    ['name' => 'Kedisiplinan', 'score' => '85.5'],
                    ['name' => 'Kinerja Teknis', 'score' => '90.25'],
                    ['name' => 'Etika Kerja', 'score' => '88'],
                ],
                'catatan' => 'Sangat rajin dan rapi dalam bekerja.',
            ])
            ->assertRedirect(route('mentor.students.show', $data['placementActive']->id));

        $evaluation = Evaluation::where('placement_id', $data['placementActive']->id)->firstOrFail();
        $this->assertEquals(87.92, (float) $evaluation->nilai_pembimbing);
    }
}
