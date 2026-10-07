<?php

namespace Tests\Feature;

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
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Regresi pusat kendali dinas & kampus:
 * 1. Daftar diurutkan dari yang paling butuh tindakan (Application::actionPriority).
 * 2. Nilai di tabel kampus memakai accessor resmi nilai_akhir (dulu tampil 0.0 karena final_score "0.00").
 * 3. Metrik & tab tidak lagi membandingkan status enum dengan string (dulu selalu 0).
 */
class ActionPriorityDisplayTest extends TestCase
{
    use RefreshDatabase;

    private University $univ;

    private Unit $unit;

    private User $superAdmin;

    private User $mentor;

    private User $dosen;

    protected function setUp(): void
    {
        parent::setUp();

        $this->univ = University::create(['name' => 'Universitas Uji', 'code' => 'UJI']);
        $agency = AgencyProfile::create(['agency_name' => 'Dinas Uji']);
        $this->unit = Unit::create(['agency_profile_id' => $agency->id, 'name' => 'Bidang Uji', 'quota' => 10]);
        $this->superAdmin = User::factory()->create(['role' => 'super_admin']);
        $this->mentor = User::factory()->create(['role' => 'mentor', 'agency_profile_id' => $agency->id]);
        $this->dosen = User::factory()->create(['role' => 'dosen', 'university_id' => $this->univ->id]);
    }

    private function makeApplication(string $status, string $name, int $daysAgo = 1, bool $withMentor = false, bool $withDosen = false, ?University $univ = null): Application
    {
        $univ ??= $this->univ;
        $student = User::factory()->create(['role' => 'mahasiswa', 'name' => $name, 'university_id' => $univ->id]);
        $student->studentProfile()->create(['nim' => (string) random_int(10000000, 99999999), 'universitas' => $univ->name, 'jurusan' => 'TI', 'phone' => '08']);

        $application = Application::create([
            'user_id' => $student->id,
            'unit_id' => $this->unit->id,
            'status' => $status,
            'start_date' => now()->subMonth()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'created_at' => Carbon::now()->subDays($daysAgo),
        ]);

        if ($withMentor || $withDosen) {
            Placement::create([
                'application_id' => $application->id,
                'mentor_id' => $withMentor ? $this->mentor->id : null,
                'academic_advisor_id' => $withDosen ? $this->dosen->id : null,
            ]);
        }

        return $application->fresh();
    }

    /** Nilai lama: mentor rata-rata 92 + nilai_akademik 97, final_score kosong (0.00) → 40%·92 + 60%·97 = 95 */
    private function gradeLegacy(Application $application, bool $approveReport = true, bool $withLogbook = true): void
    {
        $placement = $application->placement;
        if ($approveReport) {
            FinalReport::create(['placement_id' => $placement->id, 'file_path' => 'x.pdf', 'status' => 'approved']);
        }
        Evaluation::create([
            'placement_id' => $placement->id,
            'nilai_disiplin' => 93,
            'nilai_kinerja' => 93,
            'nilai_laporan' => 90,
            'nilai_akademik' => 97,
        ]);
        if ($withLogbook) {
            Logbook::create([
                'placement_id' => $placement->id,
                'date' => now()->toDateString(),
                'activity' => 'Aktivitas magang harian',
                'status' => 'approved',
            ]);
        }
    }

    /**
     * Versi SQL (halaman Pengajuan berpaginasi) wajib identik dengan versi PHP (keterangan per baris &
     * pusat kendali) untuk setiap tingkat, termasuk skema 100% mentor dan kampus tanpa kewajiban DPL.
     */
    public function test_sql_priority_is_identical_to_php_priority_and_queues_oldest_first(): void
    {
        $mentorOnly = University::create(['name' => 'Kampus Mentor Saja', 'code' => 'KMS', 'evaluation_scheme' => 'mentor_only']);
        $noDplUniv = University::create(['name' => 'Kampus Tanpa DPL', 'code' => 'KTD', 'require_dpl' => false]);

        $pendingOld = $this->makeApplication('pending', 'Antre Terlama', daysAgo: 10);
        $verified = $this->makeApplication('verified', 'Lolos Berkas', daysAgo: 7);
        $pendingNew = $this->makeApplication('pending', 'Antre Baru', daysAgo: 5);
        $readyDual = $this->makeApplication('active', 'Siap Lulus Ganda', daysAgo: 15, withMentor: true, withDosen: true);
        $this->gradeLegacy($readyDual);
        $readyAspects = $this->makeApplication('active', 'Siap Lulus Aspek', daysAgo: 14, withMentor: true, withDosen: true);
        FinalReport::create(['placement_id' => $readyAspects->placement->id, 'file_path' => 'x.pdf', 'status' => 'approved']);
        Evaluation::create(['placement_id' => $readyAspects->placement->id, 'nilai_disiplin' => 80, 'nilai_kinerja' => 80, 'nilai_laporan' => 80,
            'score_mastery' => 85, 'score_report' => 85, 'score_attitude' => 85]);
        Logbook::create(['placement_id' => $readyAspects->placement->id, 'date' => now()->toDateString(), 'activity' => 'Logbook aspek', 'status' => 'approved']);

        $readyMentorOnly = $this->makeApplication('active', 'Siap Lulus Mentor', daysAgo: 13, withMentor: true, withDosen: true, univ: $mentorOnly);
        FinalReport::create(['placement_id' => $readyMentorOnly->placement->id, 'file_path' => 'x.pdf', 'status' => 'approved']);
        Evaluation::create(['placement_id' => $readyMentorOnly->placement->id, 'nilai_disiplin' => 90, 'nilai_kinerja' => 88, 'nilai_laporan' => 86]);
        Logbook::create(['placement_id' => $readyMentorOnly->placement->id, 'date' => now()->toDateString(), 'activity' => 'Logbook mentor only', 'status' => 'approved']);

        $partialMentorOnly = $this->makeApplication('active', 'Nilai Mentor Belum Lengkap', daysAgo: 12, withMentor: true, withDosen: true, univ: $mentorOnly);
        FinalReport::create(['placement_id' => $partialMentorOnly->placement->id, 'file_path' => 'x.pdf', 'status' => 'approved']);
        Evaluation::create(['placement_id' => $partialMentorOnly->placement->id, 'nilai_disiplin' => 90, 'nilai_kinerja' => 88]);
        $noMentor = $this->makeApplication('accepted', 'Tanpa Mentor', daysAgo: 3);
        $noDosen = $this->makeApplication('active', 'Tanpa DPL', daysAgo: 20, withMentor: true);
        $noDosenFree = $this->makeApplication('active', 'Tanpa DPL Tidak Wajib', daysAgo: 4, withMentor: true, univ: $noDplUniv);
        $active = $this->makeApplication('active', 'Aktif Lengkap', daysAgo: 2, withMentor: true, withDosen: true);
        $accepted = $this->makeApplication('accepted', 'Diterima Lengkap', daysAgo: 1, withMentor: true, withDosen: true);
        $completed = $this->makeApplication('completed', 'Alumni', daysAgo: 60, withMentor: true, withDosen: true);
        $rejected = $this->makeApplication('rejected', 'Ditolak', daysAgo: 6);
        $resigned = $this->makeApplication('resigned', 'Mundur', daysAgo: 8);

        $loaded = Application::with(['placement.finalreport', 'placement.evaluation', 'placement.logbooks', 'user.universityRelation'])->get();
        $phpTiers = $loaded->mapWithKeys(fn ($a) => [$a->id => $a->actionPriority()])->sortKeys()->all();
        $sqlTiers = Application::query()->selectRaw('id, '.Application::actionPrioritySql().' as tier')->get()
            ->mapWithKeys(fn ($row) => [$row->id => (int) $row->tier])->sortKeys()->all();

        $this->assertSame($phpTiers, $sqlTiers, 'Tingkat prioritas SQL berbeda dengan PHP');
        $this->assertSame(
            [1, 1, 1, 2, 2, 2, 4, 3, 3, 4, 4, 5, 6, 8, 8],
            array_map(fn ($a) => $phpTiers[$a->id], [$pendingOld, $verified, $pendingNew, $readyDual, $readyAspects, $readyMentorOnly,
                $partialMentorOnly, $noMentor, $noDosen, $noDosenFree, $active, $accepted, $completed, $rejected, $resigned])
        );

        $phpOrder = $loaded
            ->sort(fn ($a, $b) => Application::actionSortKey($a->actionPriority(), $a->created_at, $a->id)
                <=> Application::actionSortKey($b->actionPriority(), $b->created_at, $b->id))
            ->pluck('id')->values()->all();
        $sqlOrder = Application::query()->orderByActionPriority()->pluck('id')->all();

        $this->assertSame($phpOrder, $sqlOrder, 'Urutan SQL berbeda dengan PHP');
        // Kelompok perlu tindakan = antrean (terlama dulu); kelompok lain = terbaru dulu
        $this->assertSame([$pendingOld->id, $verified->id, $pendingNew->id], array_slice($sqlOrder, 0, 3));
        $this->assertSame([$noDosen->id, $noMentor->id], array_slice($sqlOrder, 6, 2));
        $this->assertSame([$active->id, $noDosenFree->id, $partialMentorOnly->id], array_slice($sqlOrder, 8, 3));
        $this->assertSame([$rejected->id, $resigned->id], array_slice($sqlOrder, -2));
    }

    public function test_applications_index_uses_standard_order_and_action_filter(): void
    {
        $this->makeApplication('completed', 'Alumni Lama', daysAgo: 1, withMentor: true, withDosen: true);
        $noMentor = $this->makeApplication('accepted', 'Belum Ada Mentor', daysAgo: 2);
        $this->makeApplication('active', 'Magang Lancar', daysAgo: 3, withMentor: true, withDosen: true);
        $pending = $this->makeApplication('pending', 'Menunggu Lama', daysAgo: 9);

        $response = $this->actingAs($this->superAdmin)->get(route('admin.applications.index'))
            ->assertOk()
            // Badge = kode sistem; keterangan hanya untuk hal yang tidak terbaca dari badge
            ->assertSee('data-status="pending"', false)
            ->assertSeeText('PENDING')
            ->assertSeeText('Mentor belum ditetapkan')
            ->assertDontSeeText('Perlu verifikasi berkas');

        $this->assertSame(
            Application::query()->orderByActionPriority()->pluck('id')->all(),
            $response->viewData('applications')->getCollection()->pluck('id')->all()
        );
        $this->assertSame($pending->id, $response->viewData('applications')->first()->id);

        $filtered = $this->actingAs($this->superAdmin)->get(route('admin.applications.index', ['status' => 'action']));
        $this->assertSame([$pending->id, $noMentor->id], $filtered->viewData('applications')->getCollection()->pluck('id')->all());
    }

    public function test_action_priority_and_hints(): void
    {
        $pending = $this->makeApplication('pending', 'A Pending');
        $verified = $this->makeApplication('verified', 'B Verified');
        $noMentor = $this->makeApplication('accepted', 'C Tanpa Mentor');
        $noDosen = $this->makeApplication('active', 'D Tanpa Dosen', withMentor: true);
        $ready = $this->makeApplication('active', 'E Siap Lulus', withMentor: true, withDosen: true);
        $this->gradeLegacy($ready);
        $ready = $ready->fresh();
        $completed = $this->makeApplication('completed', 'F Lulus', withMentor: true, withDosen: true);
        $rejected = $this->makeApplication('rejected', 'G Ditolak');

        // Tingkat 1 tanpa keterangan: badge PENDING/VERIFIED sudah menyatakan tindakannya
        $this->assertSame([1, null], [$pending->actionPriority(), $pending->actionHint()]);
        $this->assertSame([1, null], [$verified->actionPriority(), $verified->actionHint()]);
        $this->assertSame([2, 'Siap diluluskan'], [$ready->actionPriority(true), $ready->actionHint(true)]);

        // Mahasiswa tanpa logbook tidak bisa siap diluluskan (masuk tingkat 4 / magang aktif)
        $noLogbook = $this->makeApplication('active', 'Tanpa Logbook', withMentor: true, withDosen: true);
        $this->gradeLegacy($noLogbook, withLogbook: false);
        $this->assertSame([4, null], [$noLogbook->actionPriority(true), $noLogbook->actionHint(true)]);

        $this->assertSame([3, 'Mentor belum ditetapkan'], [$noMentor->actionPriority(), $noMentor->actionHint()]);
        // Default: mengikuti kebijakan kampus mahasiswa (require_dpl = true)
        $this->assertSame([3, 'Dosen pembimbing belum ditetapkan'], [$noDosen->actionPriority(), $noDosen->actionHint()]);
        $this->assertSame([4, null], [$noDosen->actionPriority(false), $noDosen->actionHint(false)]);
        // Kampus yang tidak mewajibkan DPL: mahasiswa aktif tanpa DPL tidak dianggap perlu tindakan
        $this->univ->update(['require_dpl' => false]);
        $this->assertSame([4, null], [$noDosen->fresh()->actionPriority(), $noDosen->fresh()->actionHint()]);
        $this->assertSame([6, null], [$completed->actionPriority(true), $completed->actionHint(true)]);
        $this->assertSame([8, null], [$rejected->actionPriority(), $rejected->actionHint()]);
    }

    public function test_university_show_orders_students_by_priority_and_uses_official_score(): void
    {
        $completed = $this->makeApplication('completed', 'Zahra Lulus', daysAgo: 1, withMentor: true, withDosen: true);
        $this->gradeLegacy($completed);
        $this->makeApplication('rejected', 'Yudi Ditolak', daysAgo: 2);
        $this->makeApplication('active', 'Xena Aktif', daysAgo: 3, withMentor: true, withDosen: true);
        $this->makeApplication('pending', 'Wawan Baru', daysAgo: 30);

        $response = $this->actingAs($this->superAdmin)
            ->get(route('admin.universities.show', ['university' => $this->univ->id, 'tab' => 'mahasiswa']))
            ->assertOk()
            ->assertSee('95.0')
            ->assertSeeText('Grade A')
            ->assertSee('data-status="pending"', false);

        $this->assertSame(
            ['Wawan Baru', 'Xena Aktif', 'Zahra Lulus', 'Yudi Ditolak'],
            $response->viewData('students')->pluck('name')->all()
        );

        $stats = $response->viewData('stats');
        $this->assertSame(1, $stats['active_interns']);
        $this->assertSame(1, $stats['completed_interns']);
        $this->assertSame(1, $stats['pending_applications']);
        $this->assertSame(1, $stats['needs_action']);
        $this->assertEquals(95.0, $stats['average_score']);

        // Filter "Perlu Tindakan" hanya menyisakan mahasiswa yang butuh tindakan
        $filtered = $this->actingAs($this->superAdmin)
            ->get(route('admin.universities.show', ['university' => $this->univ->id, 'tab' => 'mahasiswa', 'student_status' => 'action']));
        $this->assertSame(['Wawan Baru'], $filtered->viewData('students')->pluck('name')->all());

        // Filter kode status = persis badge yang tampil (bukan gabungan status)
        foreach (['active' => ['Xena Aktif'], 'completed' => ['Zahra Lulus'], 'rejected' => ['Yudi Ditolak'], 'accepted' => []] as $code => $names) {
            $byStatus = $this->actingAs($this->superAdmin)
                ->get(route('admin.universities.show', ['university' => $this->univ->id, 'tab' => 'mahasiswa', 'student_status' => $code]));
            $this->assertSame($names, $byStatus->viewData('students')->pluck('name')->all(), "filter {$code}");
        }
    }

    public function test_agency_show_counts_statuses_and_orders_applications(): void
    {
        $this->makeApplication('completed', 'Lulus Dulu', daysAgo: 1, withMentor: true);
        $active = $this->makeApplication('active', 'Sedang Magang', daysAgo: 2, withMentor: true, withDosen: true);
        $pending = $this->makeApplication('pending', 'Baru Daftar', daysAgo: 40);

        $response = $this->actingAs($this->superAdmin)
            ->get(route('admin.agencies.show', $this->unit->agency_profile_id))
            ->assertOk()
            ->assertSee('data-status="pending"', false);

        $this->assertSame($pending->id, $response->viewData('applications')->first()->id);
        $this->assertSame([$active->id], $response->viewData('activePlacements')->pluck('application_id')->all());

        $stats = $response->viewData('stats');
        $this->assertSame(1, $stats['pending_applications']);
        $this->assertSame(1, $stats['active_students']);
        $this->assertSame(1, $stats['needs_action']);
        $this->assertSame(1, $stats['total_filled']); // kuota terisi = diterima/aktif yang belum berakhir
    }

    /** applications.unit_id ON DELETE CASCADE: dulu hanya status 'accepted' yang dicek, mahasiswa 'active' ikut terhapus */
    public function test_unit_with_active_student_cannot_be_deleted(): void
    {
        $active = $this->makeApplication('active', 'Masih Magang', withMentor: true);

        $this->actingAs($this->superAdmin)
            ->delete(route('admin.units.destroy', $this->unit->id))
            ->assertSessionHas('error');

        $this->assertNotNull($this->unit->fresh());
        $this->assertNotNull($active->fresh());
    }

    public function test_quota_cannot_be_reduced_below_occupied_seats(): void
    {
        $this->makeApplication('active', 'Magang Satu', withMentor: true);
        $this->makeApplication('accepted', 'Magang Dua', withMentor: true);
        $this->unit->update(['quota' => 2]);

        $this->assertSame(2, $this->unit->fresh()->occupied_count);

        $this->actingAs($this->superAdmin)
            ->patch(route('admin.units.updateQuota', $this->unit->id), ['action' => 'decrement'])
            ->assertSessionHas('error');
        $this->actingAs($this->superAdmin)
            ->patch(route('admin.units.updateQuota', $this->unit->id), ['quota' => 1])
            ->assertSessionHas('error');
        $this->actingAs($this->superAdmin)
            ->put(route('admin.units.update', $this->unit->id), ['name' => 'Bidang Uji', 'quota' => 1])
            ->assertSessionHasErrors('quota');

        $this->assertSame(2, (int) $this->unit->fresh()->quota);
    }
}
