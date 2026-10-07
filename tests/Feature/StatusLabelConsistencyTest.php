<?php

namespace Tests\Feature;

use App\Enums\AccountStatus;
use App\Enums\ApplicationStatus;
use App\Enums\FeedbackStatus;
use App\Enums\ReviewStatus;
use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\FinalReport;
use App\Models\Logbook;
use App\Models\Placement;
use App\Models\SystemFeedback;
use App\Models\Unit;
use App\Models\University;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Satu status = satu nama di semua role. Nama resmi = kode sistem (ApplicationStatus::label),
 * dirender lewat <x-status-badge>. Dulu tiap halaman menulis nama sendiri, mis. `active`
 * tampil "ACTIVE", "Sedang Magang", "AKTIF (Sedang Magang)" dan "Magang Aktif".
 */
class StatusLabelConsistencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_status_names_equal_system_codes(): void
    {
        foreach (ApplicationStatus::cases() as $case) {
            $this->assertSame(strtoupper($case->value), $case->label());
            $this->assertNotSame('', $case->description());
            $this->assertSame($case, ApplicationStatus::resolve(' '.strtoupper($case->value)));
        }
        $this->assertNull(ApplicationStatus::resolve('canceled'));
    }

    public function test_same_application_shows_same_status_name_for_every_role(): void
    {
        $univ = University::create(['name' => 'Universitas Selaras', 'code' => 'USL']);
        $agency = AgencyProfile::create(['agency_name' => 'Dinas Selaras']);
        $unit = Unit::create(['agency_profile_id' => $agency->id, 'name' => 'Bidang Selaras', 'quota' => 5]);

        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $mentor = User::factory()->create(['role' => 'mentor', 'agency_profile_id' => $agency->id]);
        $dosen = User::factory()->create(['role' => 'dosen', 'university_id' => $univ->id]);
        $univAdmin = User::factory()->create(['role' => 'universitas', 'university_id' => $univ->id]);
        $student = User::factory()->create(['role' => 'mahasiswa', 'university_id' => $univ->id]);
        $student->studentProfile()->create(['nim' => '22081010777', 'universitas' => $univ->name, 'jurusan' => 'TI', 'phone' => '08']);

        $application = Application::create([
            'user_id' => $student->id,
            'unit_id' => $unit->id,
            'status' => 'active',
            'start_date' => now()->subWeek()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
        ]);
        $placement = Placement::create([
            'application_id' => $application->id,
            'mentor_id' => $mentor->id,
            'academic_advisor_id' => $dosen->id,
        ]);

        $pages = [
            'admin: pengajuan' => [$superAdmin, route('admin.applications.index')],
            'admin: detail pengajuan' => [$superAdmin, route('admin.applications.show', $application->id)],
            'admin: pusat kendali dinas' => [$superAdmin, route('admin.agencies.show', $agency->id)],
            'admin: pusat kendali kampus' => [$superAdmin, route('admin.universities.show', ['university' => $univ->id, 'tab' => 'mahasiswa'])],
            'mentor: dashboard' => [$mentor, route('mentor.dashboard')],
            'dosen: monitoring' => [$dosen, route('lecturer.monitoring.index')],
            'kampus: dashboard' => [$univAdmin, route('university.dashboard')],
            'kampus: detail mahasiswa' => [$univAdmin, route('university.students.show', $placement->id)],
            'mahasiswa: dashboard' => [$student, '/dashboard'],
            'mahasiswa: pengajuan' => [$student, route('student.application.create')],
        ];

        foreach ($pages as $page => [$user, $url]) {
            $response = $this->actingAs($user)->get($url);
            $response->assertOk();
            $html = $response->getContent();

            $this->assertStringContainsString('data-status="active"', $html, "{$page}: badge standar tidak dipakai");
            $this->assertStringContainsString('ACTIVE', $html, "{$page}: nama status bukan kode sistem");
            foreach (['AKTIF (Sedang Magang)', '>Sedang Magang<', 'Magang Aktif'] as $legacy) {
                $this->assertStringNotContainsString($legacy, $html, "{$page}: masih memakai nama lama \"{$legacy}\"");
            }
        }
    }

    public function test_review_status_names_equal_system_codes(): void
    {
        foreach (ReviewStatus::cases() as $case) {
            $this->assertSame(strtoupper($case->value), $case->label());
            $this->assertNotSame('', $case->description());
        }
    }

    public function test_every_status_enum_shares_the_same_display_standard(): void
    {
        // Status alur kerja: nama = kode sistem
        foreach ([ApplicationStatus::class, ReviewStatus::class, FeedbackStatus::class, AccountStatus::class] as $enum) {
            foreach ($enum::cases() as $case) {
                if ($enum !== AccountStatus::class) {
                    $this->assertSame(str_replace('_', ' ', strtoupper($case->value)), $case->label());
                }
                $this->assertNotSame('', $case->description());
                $this->assertSame($case, $enum::resolve(strtoupper($case->value)));
            }
            $this->assertSame(array_column($enum::cases(), 'value'), $enum::values());
        }
        $this->assertSame('IN PROGRESS', FeedbackStatus::IN_PROGRESS->label());

        // Status akun: hanya dua keadaan, label Bahasa Indonesia untuk semua role
        $this->assertSame(['active', 'inactive'], AccountStatus::values());
        $this->assertSame(['Aktif', 'Nonaktif'], array_map(fn ($c) => $c->label(), AccountStatus::cases()));
        $this->assertNull(AccountStatus::resolve('on_leave'));
    }

    public function test_account_and_feedback_status_use_standard_badges(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $agency = AgencyProfile::create(['agency_name' => 'Dinas Akun']);
        // Pembimbing yang cuti disetel Nonaktif; dulu tampil "Non-Aktif" (dan cuti tampil sebagai Non-Aktif)
        $mentor = User::factory()->create(['role' => 'mentor', 'agency_profile_id' => $agency->id, 'status' => 'inactive', 'name' => 'Mentor Cuti']);
        User::factory()->create(['role' => 'mahasiswa', 'status' => 'active']);

        foreach ([route('admin.users.index'), route('admin.agencies.show', $agency->id), route('admin.mentors.index')] as $url) {
            $html = $this->actingAs($superAdmin)->get($url)->assertOk()->getContent();
            $this->assertContains('Nonaktif', $this->badgeTexts($html, 'inactive'), $url);
            $this->assertStringNotContainsString('Non-Aktif', $html, $url);
            $this->assertStringNotContainsString('Cuti</option>', $html, $url);
        }
        $html = $this->actingAs($superAdmin)->get(route('admin.users.index', ['role' => 'mahasiswa']))->getContent();
        $this->assertContains('Aktif', $this->badgeTexts($html, 'active'));
        $this->assertTrue($mentor->isInactive(), 'Nonaktif memblokir login');

        $feedback = SystemFeedback::create([
            'user_id' => $mentor->id, 'sender_name' => $mentor->name, 'sender_email' => $mentor->email,
            'sender_role' => 'mentor', 'category' => 'pertanyaan', 'subject' => 'Tanya', 'message' => 'Isi', 'status' => 'in_progress',
        ]);
        foreach ([route('admin.feedbacks.index'), route('feedbacks.show', $feedback->id)] as $url) {
            $html = $this->actingAs($superAdmin)->get($url)->assertOk()->getContent();
            $this->assertStringContainsString('data-status="in_progress"', $html, $url);
            $this->assertStringContainsString('IN PROGRESS', $html, $url);
        }
    }

    public function test_on_leave_is_no_longer_an_account_status(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $dosen = User::factory()->create(['role' => 'dosen', 'status' => 'active']);

        // Form menolak nilai lama "on_leave"
        $this->actingAs($superAdmin)
            ->put(route('admin.users.update', $dosen->id), ['name' => $dosen->name, 'email' => $dosen->email, 'role' => 'dosen', 'status' => 'on_leave'])
            ->assertSessionHasErrors('status');
        $this->assertSame('active', $dosen->fresh()->status);

        // Data lama "on_leave" dipindahkan menjadi inactive oleh migrasi
        $legacy = User::factory()->create(['role' => 'mentor']);
        DB::table('users')->where('id', $legacy->id)->update(['status' => 'on_leave']);
        (require database_path('migrations/2026_09_30_020000_merge_on_leave_into_inactive_account_status.php'))->up();
        $this->assertSame('inactive', $legacy->fresh()->status);
    }

    public function test_logbook_and_report_show_same_review_status_for_every_role(): void
    {
        $univ = University::create(['name' => 'Universitas Review', 'code' => 'URV']);
        $agency = AgencyProfile::create(['agency_name' => 'Dinas Review']);
        $unit = Unit::create(['agency_profile_id' => $agency->id, 'name' => 'Bidang Review', 'quota' => 5]);

        $superAdmin = User::factory()->create(['role' => 'super_admin']);
        $mentor = User::factory()->create(['role' => 'mentor', 'agency_profile_id' => $agency->id]);
        $dosen = User::factory()->create(['role' => 'dosen', 'university_id' => $univ->id]);
        $univAdmin = User::factory()->create(['role' => 'universitas', 'university_id' => $univ->id]);
        $student = User::factory()->create(['role' => 'mahasiswa', 'university_id' => $univ->id]);
        $student->studentProfile()->create(['nim' => '22081010888', 'universitas' => $univ->name, 'jurusan' => 'TI', 'phone' => '08']);

        $application = Application::create([
            'user_id' => $student->id, 'unit_id' => $unit->id, 'status' => 'active',
            'start_date' => now()->subWeek()->toDateString(), 'end_date' => now()->addMonth()->toDateString(),
        ]);
        $placement = Placement::create(['application_id' => $application->id, 'mentor_id' => $mentor->id, 'academic_advisor_id' => $dosen->id]);
        // Mentor menolak, dosen belum meninjau
        $logbook = Logbook::create([
            'placement_id' => $placement->id, 'date' => now()->subDay()->toDateString(), 'activity' => 'Menyusun arsip',
            'status' => 'rejected', 'lecturer_status' => 'pending',
        ]);
        FinalReport::create(['placement_id' => $placement->id, 'file_path' => 'laporan.pdf', 'status' => 'revision']);

        $pages = [
            'admin: detail logbook' => [$superAdmin, route('admin.logbooks.show', $logbook->id), ['rejected', 'pending']],
            'mentor: logbook' => [$mentor, route('mentor.logbooks.index'), ['rejected']],
            'kampus: detail mahasiswa' => [$univAdmin, route('university.students.show', $placement->id), ['rejected', 'pending']],
            'mahasiswa: logbook' => [$student, route('student.logbook.index'), ['rejected', 'pending']],
            'mahasiswa: laporan akhir' => [$student, route('student.final_report.index'), ['revision']],
            'mentor: dashboard' => [$mentor, route('mentor.dashboard', ['tab' => 'all']), ['revision']],
            'dosen: monitoring' => [$dosen, route('lecturer.monitoring.index'), ['revision']],
            'dosen: dashboard' => [$dosen, route('lecturer.dashboard'), ['revision']],
        ];

        foreach ($pages as $page => [$user, $url, $codes]) {
            $html = $this->actingAs($user)->get($url)->assertOk()->getContent();
            foreach ($codes as $code) {
                $this->assertStringContainsString("data-status=\"{$code}\"", $html, "{$page}: badge {$code} tidak dipakai");
                $this->assertStringContainsString(strtoupper($code), $html, "{$page}: nama {$code} bukan kode sistem");
            }
            foreach (['Mentor: Pending', 'Laporan Disetujui (ACC)', 'Perlu Perbaikan (Revisi)', 'Minta Revisi (Rejected)'] as $legacy) {
                $this->assertStringNotContainsString($legacy, $html, "{$page}: masih memakai nama lama \"{$legacy}\"");
            }
        }
    }

    /**
     * Teks utama (nama status) setiap badge dengan data-status tertentu, tanpa teks elemen anaknya
     * (titik warna & keterangan layar sentuh).
     */
    private function badgeTexts(string $html, string $code): array
    {
        $dom = new \DOMDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>'.$html);
        libxml_clear_errors();

        $texts = [];
        foreach ((new \DOMXPath($dom))->query('//*[@data-status="'.$code.'"]') as $badge) {
            $pill = $badge->nodeName === 'div' ? $badge->getElementsByTagName('span')->item(0) : $badge;
            $own = '';
            foreach ($pill->childNodes as $child) {
                if ($child->nodeType === XML_TEXT_NODE) {
                    $own .= $child->textContent;
                }
            }
            $texts[] = trim($own);
        }

        return $texts;
    }
}
