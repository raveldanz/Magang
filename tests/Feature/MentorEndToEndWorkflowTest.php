<?php

namespace Tests\Feature;

use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\Evaluation;
use App\Models\FinalReport;
use App\Models\Logbook;
use App\Models\Placement;
use App\Models\SystemNotification;
use App\Models\Unit;
use App\Models\University;
use App\Models\User;
use App\Services\MentorTaskList;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

/**
 * Alur kerja Mentor Lapangan dari awal sampai akhir, seperti yang dijalani pegawai di dinas:
 * mahasiswa diterima → magang berjalan → logbook diperiksa per hari → laporan akhir diperiksa →
 * nilai diisi → magang selesai. Setiap tahap memeriksa bahwa Daftar Tugas, kartu angka, dan
 * lonceng notifikasi menunjukkan hal yang sama sehingga tidak ada tugas yang terlewat.
 */
class MentorEndToEndWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $mentor;

    private User $dosen;

    private User $student;

    private Application $application;

    private Placement $placement;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-08-01 09:00:00'));

        $agency = AgencyProfile::create(['agency_name' => 'Dinas Perpustakaan dan Kearsipan']);
        $unit = Unit::create(['agency_profile_id' => $agency->id, 'name' => 'Bidang Layanan', 'quota' => 10]);
        $university = University::create([
            'name' => 'Universitas Negeri Surabaya',
            'code' => 'UNESA',
            'evaluation_scheme' => 'dual_evaluation',
            'weight_mentor' => 40,
            'weight_lecturer' => 60,
            'require_dpl' => true,
        ]);

        $this->mentor = User::factory()->create(['role' => 'mentor', 'name' => 'Ibu Mentor Senior', 'agency_profile_id' => $agency->id]);
        $this->dosen = User::factory()->create(['role' => 'dosen', 'name' => 'Dr. Dosen', 'university_id' => $university->id]);
        $this->student = User::factory()->create(['role' => 'mahasiswa', 'name' => 'Rina Mahasiswi', 'university_id' => $university->id]);
        $this->student->studentProfile()->create([
            'nim' => '22081010777',
            'universitas' => $university->name,
            'jurusan' => 'Ilmu Perpustakaan',
            'phone' => '081234567000',
        ]);

        $this->application = Application::create([
            'user_id' => $this->student->id,
            'unit_id' => $unit->id,
            'status' => 'accepted',
            'start_date' => '2026-08-10',
            'end_date' => '2026-09-30',
        ]);
        $this->placement = Placement::create([
            'application_id' => $this->application->id,
            'mentor_id' => $this->mentor->id,
            'academic_advisor_id' => $this->dosen->id,
        ]);

        // Mahasiswa lain yang masih menunggu ACC Atasan: tetap terhitung di tab "Belum Mulai Magang"
        $waitingStudent = User::factory()->create(['role' => 'mahasiswa', 'name' => 'Dodi Menunggu', 'university_id' => $university->id]);
        $waitingApp = Application::create([
            'user_id' => $waitingStudent->id,
            'unit_id' => $unit->id,
            'status' => 'waiting_approval',
            'start_date' => '2026-09-01',
            'end_date' => '2026-10-31',
        ]);
        Placement::create(['application_id' => $waitingApp->id, 'mentor_id' => $this->mentor->id]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function tasks(): Collection
    {
        return app(MentorTaskList::class)->forMentor($this->mentor->fresh())['tasks'];
    }

    private function logbook(string $date, string $status = 'pending', ?string $feedback = null): Logbook
    {
        return Logbook::create([
            'placement_id' => $this->placement->id,
            'date' => $date,
            'activity' => "Kegiatan tanggal {$date} di meja layanan",
            'status' => $status,
            'feedback' => $feedback,
            'lecturer_status' => 'pending',
        ]);
    }

    public function test_alur_mentor_lengkap_dari_diterima_sampai_selesai(): void
    {
        // ── Tahap 0: sebelum magang dimulai ─────────────────────────────────────
        $dashboard = $this->actingAs($this->mentor)->get(route('mentor.dashboard'))->assertOk();
        $dashboard->assertSeeText('Daftar Tugas Anda')
            ->assertSeeText('Tidak ada tugas yang menunggu');
        $dashboard->assertViewHas('stats', fn (array $s) => $s['total_students'] === 2
            && $s['upcoming_students'] === 2
            && $s['total_students'] === $s['active_students'] + $s['upcoming_students'] + $s['completed_students']);

        $this->actingAs($this->mentor)->get(route('mentor.students.show', $this->placement->id))
            ->assertOk()
            ->assertSeeText('Formulir nilai terbuka setelah masa magang dimulai.')
            ->assertSeeText('Dimulai dalam 9 hari.');

        // Nilai belum boleh diisi sebelum magang berjalan
        $this->actingAs($this->mentor)->post(route('mentor.evaluations.store', $this->placement->id), [
            'aspects' => [['name' => 'Disiplin', 'score' => 90]],
        ])->assertRedirect(route('mentor.students.show', $this->placement->id));
        $this->assertNull($this->placement->fresh()->evaluation);

        // ── Tahap 1: magang berjalan, logbook masuk ─────────────────────────────
        Carbon::setTestNow(Carbon::parse('2026-08-14 09:00:00'));
        $this->application->update(['status' => 'active']);
        $monday = $this->logbook('2026-08-10');
        $tuesday = $this->logbook('2026-08-11');
        $wednesday = $this->logbook('2026-08-12');

        $tasks = $this->tasks();
        $this->assertSame(['logbook'], $tasks->pluck('type')->all());
        $this->assertStringContainsString('3 hari kegiatan', $tasks->first()['description']);

        $bell = collect(NotificationService::getNotificationsForUser($this->mentor->fresh()));
        $this->assertStringStartsWith('3 Logbook', $bell->firstWhere('id', 'mentor_logbook_pending')['title']);
        $this->assertNull($bell->firstWhere('id', 'mentor_active_students'), 'Notifikasi umum tanpa tindakan tidak boleh memenuhi lonceng.');

        // Halaman logbook langsung menampilkan yang perlu diperiksa, minggu pertama terbuka
        $this->actingAs($this->mentor)->get(route('mentor.logbooks.index'))
            ->assertOk()
            ->assertViewHas('statusFilter', 'pending')
            ->assertSeeText('Ada 3 hari kegiatan (1 minggu) yang menunggu pemeriksaan Anda.')
            ->assertSeeText('Perlu Diperbaiki');

        // Kembalikan 1 hari tanpa catatan → ditolak dengan pesan jelas, tidak ada yang berubah
        $this->actingAs($this->mentor)
            ->from(route('mentor.logbooks.index'))
            ->post(route('mentor.logbooks.bulk_review'), [
                'logbook_ids' => [$monday->id, $tuesday->id, $wednesday->id],
                'status' => 'per_day',
                'revise_ids' => [$wednesday->id],
                'bundle_key' => 'x',
            ])
            ->assertRedirect(route('mentor.logbooks.index'))
            ->assertSessionHasErrors(['feedback' => 'Catatan wajib diisi saat menolak atau meminta revisi, agar peserta magang tahu apa yang perlu diperbaiki.']);
        $this->assertSame('pending', $wednesday->fresh()->status);

        // Halaman yang dibuka setelah gagal menampilkan pesan galat yang jelas (bukan diam-diam)
        $this->actingAs($this->mentor)
            ->from(route('mentor.logbooks.index'))
            ->followingRedirects()
            ->post(route('mentor.logbooks.bulk_review'), [
                'logbook_ids' => [$monday->id, $tuesday->id, $wednesday->id],
                'status' => 'per_day',
                'revise_ids' => [$wednesday->id],
            ])
            ->assertOk()
            ->assertSeeText('Pemeriksaan belum tersimpan.')
            ->assertSee('Data belum tersimpan. Periksa tulisan berwarna merah pada halaman ini.', false);

        // Keputusan per hari: 2 hari sesuai, 1 hari diperbaiki
        $this->actingAs($this->mentor)->post(route('mentor.logbooks.bulk_review'), [
            'logbook_ids' => [$monday->id, $tuesday->id, $wednesday->id],
            'status' => 'per_day',
            'revise_ids' => [$wednesday->id],
            'feedback' => 'Uraikan judul buku yang dikatalogkan.',
        ])->assertRedirect()->assertSessionHas('success', 'Tersimpan: 2 hari disetujui dan 1 hari dikembalikan ke peserta magang untuk diperbaiki.');

        $this->assertSame('approved', $monday->fresh()->status);
        $this->assertNull($monday->fresh()->feedback, 'Catatan revisi tidak boleh menempel di hari yang disetujui.');
        $this->assertSame('rejected', $wednesday->fresh()->status);
        $this->assertSame('Uraikan judul buku yang dikatalogkan.', $wednesday->fresh()->feedback);

        $revisionNotes = SystemNotification::where('user_id', $this->student->id)->where('title', 'Logbook Perlu Diperbaiki')->get();
        $this->assertCount(1, $revisionNotes);
        $this->assertStringContainsString('1 logbook', $revisionNotes->first()->message);
        $this->assertSame([], $this->tasks()->pluck('type')->all(), 'Hari yang sedang diperbaiki mahasiswa bukan tugas mentor.');

        // Mahasiswa memperbaiki → menunggu lagi; mentor menyetujui tanpa catatan → catatan lama hilang
        $wednesday->refresh()->update(['status' => 'pending', 'activity' => 'Mengatalogkan 25 judul buku anak.']);
        $this->assertSame(['logbook'], $this->tasks()->pluck('type')->all());
        $this->actingAs($this->mentor)->post(route('mentor.logbooks.bulk_review'), [
            'logbook_ids' => [$wednesday->id],
            'status' => 'per_day',
        ])->assertSessionHas('success', 'Tersimpan: 1 hari kegiatan disetujui.');
        $this->assertSame('approved', $wednesday->fresh()->status);
        $this->assertNull($wednesday->fresh()->feedback);

        // ── Tahap 2: laporan akhir ──────────────────────────────────────────────
        Carbon::setTestNow(Carbon::parse('2026-09-20 09:00:00'));
        $report = FinalReport::create(['placement_id' => $this->placement->id, 'file_path' => 'final_reports/rina.pdf', 'status' => 'pending']);

        $types = $this->tasks()->pluck('type')->all();
        $this->assertSame(['report', 'evaluation'], $types, 'Laporan masuk dan masa magang tinggal 10 hari: dua tugas muncul berurutan.');
        $bell = collect(NotificationService::getNotificationsForUser($this->mentor->fresh()));
        $this->assertNotNull($bell->firstWhere('id', 'mentor_report_pending_'.$this->placement->id));
        $this->assertNotNull($bell->firstWhere('id', 'mentor_evaluation_due_'.$this->placement->id));

        $this->actingAs($this->mentor)->get(route('mentor.dashboard'))
            ->assertOk()
            ->assertSeeTextInOrder(['Periksa laporan akhir Rina Mahasiswi', 'Beri nilai akhir Rina Mahasiswi'])
            ->assertViewHas('stats', fn (array $s) => $s['total_reports_pending'] === 1 && $s['evaluation_due'] === 1);

        // Kembalikan laporan tanpa catatan ditolak
        $this->actingAs($this->mentor)->put(route('mentor.final_report.updateStatus', $report->id), ['status' => 'revision'])
            ->assertSessionHasErrors('feedback');

        $this->actingAs($this->mentor)->put(route('mentor.final_report.updateStatus', $report->id), [
            'status' => 'revision',
            'feedback' => 'Lengkapi daftar pustaka.',
        ])->assertRedirect();
        $this->assertSame('revision', $report->fresh()->status);
        $this->assertNotContains('report', $this->tasks()->pluck('type')->all());

        // Halaman detail tidak menampilkan tombol pemeriksaan selama mahasiswa memperbaiki
        $this->actingAs($this->mentor)->get(route('mentor.students.show', $this->placement->id))
            ->assertSeeText('Laporan sedang diperbaiki peserta magang.')
            ->assertSeeText('Lengkapi daftar pustaka.');

        // Mahasiswa unggah ulang, mentor setujui tanpa catatan → catatan revisi lama dihapus
        $report->refresh()->update(['status' => 'pending']);
        $this->actingAs($this->mentor)->put(route('mentor.final_report.updateStatus', $report->id), ['status' => 'approved'])
            ->assertRedirect();
        $this->assertSame('approved', $report->fresh()->status);
        $this->assertNull($report->fresh()->feedback);

        // ── Tahap 3: nilai akhir, terlambat bila melewati tanggal akhir ─────────
        Carbon::setTestNow(Carbon::parse('2026-10-03 09:00:00'));
        $evaluationTask = $this->tasks()->firstWhere('type', 'evaluation');
        $this->assertTrue($evaluationTask['urgent']);
        $this->assertSame('Terlambat 3 hari', $evaluationTask['badge']);

        $this->actingAs($this->mentor)->get(route('mentor.students.show', $this->placement->id))
            ->assertSeeText('Masa magang sudah berakhir 3 hari lalu.')
            ->assertSeeText('Langkah berikutnya');

        // Lembar nilai belum bisa dicetak sebelum diisi
        $this->actingAs($this->mentor)->get(route('mentor.evaluations.print', $this->placement->id))
            ->assertRedirect(route('mentor.students.show', $this->placement->id));

        // Dosen sudah memberi nilai lebih dulu
        Evaluation::create(['placement_id' => $this->placement->id, 'nilai_dosen' => 80]);

        $this->actingAs($this->mentor)->post(route('mentor.evaluations.store', $this->placement->id), [
            'aspects' => [
                ['name' => 'Disiplin & Kehadiran', 'score' => 90],
                ['name' => 'Kinerja Teknis', 'score' => 85],
                ['name' => 'Inisiatif & Komunikasi', 'score' => 88],
            ],
            'catatan' => 'Rajin dan teliti.',
        ])->assertRedirect(route('mentor.students.show', $this->placement->id));

        $this->assertSame([], $this->tasks()->pluck('type')->all(), 'Setelah nilai diisi tidak ada tugas tersisa.');
        $this->assertSame('completed', $this->application->fresh()->statusValue(), 'Laporan disetujui + nilai lengkap + masa berakhir = magang selesai.');

        $this->actingAs($this->mentor)->get(route('mentor.evaluations.print', $this->placement->id))
            ->assertOk()
            ->assertSeeText('Lembar Penilaian Mentor Lapangan')
            ->assertSeeText('Rina Mahasiswi')
            ->assertSeeText('87,67')
            ->assertSeeText('Rajin dan teliti.');

        $this->actingAs($this->mentor)->get(route('mentor.dashboard'))
            ->assertSeeText('Tidak ada tugas yang menunggu')
            ->assertViewHas('stats', fn (array $s) => $s['completed_students'] === 1 && $s['total_pending_eval'] === 0);

        // Laporan & nilai terkunci setelah selesai
        $this->actingAs($this->mentor)->get(route('mentor.students.show', $this->placement->id))
            ->assertSeeText('Nilai sudah terkunci karena magang dinyatakan selesai.')
            ->assertDontSeeText('sertifikat kelulusan telah diterbitkan');
    }

    public function test_dosen_juga_bisa_memeriksa_logbook_per_hari(): void
    {
        $this->application->update(['status' => 'active', 'start_date' => '2026-07-20']);
        $first = $this->logbook('2026-07-27', 'approved');
        $second = $this->logbook('2026-07-28', 'approved');

        $this->actingAs($this->dosen)->post(route('lecturer.logbooks.bulk_approve'), [
            'logbook_ids' => [$first->id, $second->id],
            'status' => 'per_day',
            'revise_ids' => [$second->id],
        ])->assertSessionHasErrors('bulk_feedback');
        $this->assertSame('pending', $second->fresh()->lecturer_status);

        $this->actingAs($this->dosen)->post(route('lecturer.logbooks.bulk_approve'), [
            'logbook_ids' => [$first->id, $second->id],
            'status' => 'per_day',
            'revise_ids' => [$second->id],
            'feedback' => 'Kaitkan dengan mata kuliah katalogisasi.',
        ])->assertSessionHas('success');

        $this->assertSame('approved', $first->fresh()->lecturer_status);
        $this->assertNull($first->fresh()->lecturer_feedback);
        $this->assertSame('rejected', $second->fresh()->lecturer_status);
        $this->assertSame('Kaitkan dengan mata kuliah katalogisasi.', $second->fresh()->lecturer_feedback);
    }

    public function test_mentor_lain_tidak_bisa_mencetak_lembar_nilai(): void
    {
        $other = User::factory()->create(['role' => 'mentor', 'agency_profile_id' => $this->mentor->agency_profile_id]);

        $this->actingAs($other)->get(route('mentor.evaluations.print', $this->placement->id))->assertNotFound();
    }
}
