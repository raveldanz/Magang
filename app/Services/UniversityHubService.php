<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\University;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Data "Pusat Kendali Perguruan Tinggi" (Admin\UniversityController@show):
 * daftar dosen + beban bimbingan, mahasiswa kampus (urut prioritas tindakan, bisa difilter),
 * dan statistik ringkas. Dipisah dari controller agar controller tetap tipis.
 */
class UniversityHubService
{
    /**
     * @return array{0: Collection, 1: Collection, 2: array, 3: bool}
     */
    public function build(University $university, ?string $statusFilter = '', ?string $search = null): array
    {
        // 1. Query Seluruh Dosen Pembimbing Kampus Ini
        $dosens = User::whereIn('role', ['dosen', 'academic_advisor'])
            ->where(function ($q) use ($university) {
                $q->where('university_id', $university->id)
                    ->orWhere('university', $university->name)
                    ->orWhere('university', $university->code);
            })
            ->with(['academicPlacements.application.user', 'academicPlacements.finalreport', 'academicPlacements.evaluation'])
            ->orderBy('name', 'asc')
            ->get();

        $totalDosenActive = 0;
        $totalDosenCompleted = 0;

        // Lulus = status completed, atau laporan disetujui + lembar nilai lengkap (accessor is_complete,
        // bukan kolom lama nilai_akademik yang kosong bila DPL menilai lewat aspek score_*).
        $isPassed = function ($p, string $val) use ($university) {
            return $val === 'completed'
                || (optional($p->finalreport)->status === 'approved' && (bool) $p->evaluation?->useUniversity($university)->is_complete);
        };

        foreach ($dosens as $dosen) {
            $activeCount = $dosen->academicPlacements->filter(function ($p) use ($isPassed) {
                $val = $p->application?->statusValue() ?? '';

                return in_array($val, ['accepted', 'active']) && ! $isPassed($p, $val);
            })->count();

            $completedCount = $dosen->academicPlacements->filter(function ($p) use ($isPassed) {
                $val = $p->application?->statusValue() ?? '';

                return $val === 'completed' || (in_array($val, ['accepted', 'active']) && $isPassed($p, $val));
            })->count();

            $dosen->active_students_count = $activeCount;
            $dosen->completed_students_count = $completedCount;
            $dosen->total_students_count = $activeCount + $completedCount;

            $totalDosenActive += $activeCount;
            $totalDosenCompleted += $completedCount;
        }

        // 2. Query Seluruh Mahasiswa Asal Kampus Ini
        $studentsQuery = User::where('role', 'mahasiswa')
            ->where(function ($q) use ($university) {
                $q->where('university_id', $university->id)
                    ->orWhere('university', $university->name)
                    ->orWhereHas('studentProfile', function ($sp) use ($university) {
                        $sp->where('university_id', $university->id)
                            ->orWhereRaw('LOWER(universitas) = ?', [mb_strtolower($university->name)]);
                    });
            })
            ->with([
                'studentProfile',
                'applications' => function ($q) {
                    $q->latest();
                },
                'applications.unit.agencyProfile',
                'applications.placement.mentor',
                'applications.placement.pembimbing',
                'applications.placement.academicAdvisor',
                'applications.placement.evaluation',
                'applications.placement.finalreport',
                'applications.placement.logbooks',
            ]);

        // Filter status: 'no_application' di query; 'action' & kode status disaring di bawah
        // terhadap pengajuan terbaru — sama dengan badge yang tampil di tabel.
        $statusFilter = $statusFilter ?? '';
        if ($statusFilter === 'no_application') {
            $studentsQuery->doesntHave('applications');
        }

        // Search Mahasiswa
        if ($search !== null && trim($search) !== '') {
            $sSearch = strtolower($search);
            $studentsQuery->where(function ($q) use ($sSearch) {
                $q->where('name', 'like', "%{$sSearch}%")
                    ->orWhere('email', 'like', "%{$sSearch}%")
                    ->orWhereHas('studentProfile', fn ($sp) => $sp->where('nim', 'like', "%{$sSearch}%")->orWhere('jurusan', 'like', "%{$sSearch}%"));
            });
        }

        $students = $studentsQuery->get();

        // Semua mahasiswa di daftar ini berasal dari kampus ini: tetapkan langsung agar accessor
        // nilai (nilai_akhir, is_complete) memakai kebijakan kampus tanpa query berantai per baris.
        foreach ($students as $s) {
            $s->applications->each(fn ($a) => $a->placement?->evaluation?->useUniversity($university));
        }

        // Urutan standar prioritas tindakan (sama dengan halaman Pengajuan & pusat kendali dinas):
        // tingkat 1–3 antrean terlama dulu, tingkat lain terbaru dulu. Mahasiswa tanpa pengajuan = tingkat 7.
        $requireAdvisor = (bool) ($university->require_dpl ?? true);
        $priorities = $students->mapWithKeys(fn ($s) => [
            $s->id => $s->applications->first()?->actionPriority($requireAdvisor) ?? 7,
        ]);
        $sortKey = function ($s) use ($priorities) {
            $app = $s->applications->first();

            return Application::actionSortKey($priorities[$s->id], $app?->created_at ?? $s->created_at, $app?->id ?? 0);
        };
        $students = $students->sort(fn ($a, $b) => $sortKey($a) <=> $sortKey($b))->values();

        if ($statusFilter === 'action') {
            $students = $students->filter(fn ($s) => $priorities[$s->id] <= Application::ACTION_THRESHOLD)->values();
        } elseif (ApplicationStatus::tryFrom($statusFilter)) {
            $students = $students->filter(fn ($s) => $s->applications->first()?->statusValue() === $statusFilter)->values();
        }

        // 3. Hitung Metrik Statistik Kampus (status dibandingkan sebagai string, bukan enum vs string)
        $latestStatus = fn ($s) => $s->applications->first()?->statusValue();

        $scores = $students
            ->map(fn ($s) => $s->applications->first()?->placement?->evaluation)
            ->filter()
            ->map(fn ($eval) => (float) $eval->nilai_akhir)
            ->filter(fn ($score) => $score > 0);

        $stats = [
            'total_students' => $students->count(),
            'total_dosens' => $dosens->count(),
            'active_interns' => $students->filter(fn ($s) => $latestStatus($s) === 'active')->count(),
            'completed_interns' => $students->filter(fn ($s) => $latestStatus($s) === 'completed')->count(),
            'pending_applications' => $students->filter(fn ($s) => in_array($latestStatus($s), ['pending', 'verified'], true))->count(),
            'needs_action' => $students->filter(fn ($s) => $priorities[$s->id] <= Application::ACTION_THRESHOLD)->count(),
            'average_score' => $scores->isNotEmpty() ? round($scores->avg(), 1) : null,
        ];

        return [$dosens, $students, $stats, $requireAdvisor];
    }
}
