<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Models\Logbook;
use App\Models\Placement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Satu sumber kebenaran untuk "apa yang harus dikerjakan Mentor Lapangan sekarang".
 *
 * Dasbor, halaman detail mahasiswa, dan lonceng notifikasi memakai hitungan yang sama,
 * sehingga angka di layar tidak pernah saling berbeda. Tugas mentor per mahasiswa:
 *  1. Memeriksa logbook harian (mentor yang ditugaskan atau Kepala Unit sementara),
 *  2. Memeriksa naskah laporan akhir (hanya mentor yang ditugaskan),
 *  3. Mengisi nilai akhir (hanya mentor yang ditugaskan, saat magang berjalan).
 */
class MentorTaskList
{
    /** Nilai mulai diminta saat masa magang tinggal sekian hari atau laporan sudah disetujui. */
    public const EVALUATION_WINDOW_DAYS = 14;

    /** Logbook yang menunggu lebih lama dari ini ditandai "segera". */
    public const LOGBOOK_STALE_DAYS = 7;

    /** Status pengajuan yang dihitung sebagai "Belum Mulai Magang" di dasbor mentor. */
    public const UPCOMING_STATUSES = ['pending', 'waiting_approval', 'accepted'];

    /**
     * @return array{placements: Collection<int, Placement>, tasks: Collection<int, array<string, mixed>>, summary: array<string, int>}
     */
    public function forMentor(User $mentor): array
    {
        $placements = Placement::fieldReviewableBy($mentor)
            ->whereHas('application', fn ($q) => $q->whereNotIn('status', [ApplicationStatus::RESIGNED->value, ApplicationStatus::REJECTED->value]))
            ->with(['application.user.studentProfile', 'application.unit', 'evaluation', 'finalreport'])
            ->get();

        $pendingLogbooks = $this->pendingLogbookStats($placements->pluck('id'));

        $tasks = collect();
        foreach ($placements as $placement) {
            foreach ($this->tasksForPlacement($placement, $mentor, $pendingLogbooks->get($placement->id)) as $task) {
                $tasks->push($task);
            }
        }

        $tasks = $tasks->sortBy([
            ['priority', 'asc'],
            ['sort_date', 'asc'],
        ])->values();

        $statusOf = fn (Placement $p) => $p->application?->statusValue();
        $isAssigned = fn (Placement $p) => $p->isAssignedFieldMentor($mentor);

        $notEvaluated = $placements->filter(fn (Placement $p) => $isAssigned($p)
            && $p->evaluationLockReason() === null
            && ! $this->hasMentorScore($p));

        $summary = [
            'total_students' => $placements->count(),
            'active_students' => $placements->filter(fn ($p) => $statusOf($p) === ApplicationStatus::ACTIVE->value)->count(),
            'upcoming_students' => $placements->filter(fn ($p) => in_array($statusOf($p), self::UPCOMING_STATUSES, true))->count(),
            'completed_students' => $placements->filter(fn ($p) => $statusOf($p) === ApplicationStatus::COMPLETED->value)->count(),
            'pending_logbook_days' => (int) $pendingLogbooks->sum('count'),
            'pending_logbook_students' => $pendingLogbooks->count(),
            'pending_reports' => $tasks->where('type', 'report')->count(),
            'approved_reports' => $placements->filter(fn ($p) => $this->reportStatus($p) === 'approved')->count(),
            'not_evaluated' => $notEvaluated->count(),
            'evaluation_due' => $tasks->where('type', 'evaluation')->count(),
            'evaluation_overdue' => $tasks->where('type', 'evaluation')->where('urgent', true)->count(),
            'evaluated' => $placements->filter(fn ($p) => $this->hasMentorScore($p))->count(),
            'tasks' => $tasks->count(),
        ];

        return compact('placements', 'tasks', 'summary');
    }

    /**
     * Daftar tugas untuk satu penempatan, diurutkan dari yang paling mendesak.
     *
     * @param  array{count: int, oldest: string|null}|null  $pendingLogbook
     * @return list<array<string, mixed>>
     */
    public function tasksForPlacement(Placement $placement, User $mentor, ?array $pendingLogbook = null): array
    {
        $application = $placement->application;
        if (! $application) {
            return [];
        }

        $status = $application->statusValue();
        if (in_array($status, [ApplicationStatus::RESIGNED->value, ApplicationStatus::REJECTED->value, ApplicationStatus::COMPLETED->value], true)) {
            return [];
        }

        $pendingLogbook ??= $this->pendingLogbookStats(collect([$placement->id]))->get($placement->id);
        $studentName = $application->user?->name ?? 'Mahasiswa';
        $today = now()->startOfDay();
        $endDate = $application->end_date ? Carbon::parse($application->end_date)->startOfDay() : null;
        $tasks = [];

        // 1. Logbook harian menunggu pemeriksaan
        if ($pendingLogbook && $pendingLogbook['count'] > 0) {
            $oldest = $pendingLogbook['oldest'] ? Carbon::parse($pendingLogbook['oldest'])->startOfDay() : $today;
            $waitingDays = (int) $oldest->diffInDays($today);
            $stale = $waitingDays > self::LOGBOOK_STALE_DAYS;

            $tasks[] = $this->task(
                type: 'logbook',
                priority: $stale ? 3 : 4,
                placement: $placement,
                studentName: $studentName,
                title: "Periksa logbook harian {$studentName}",
                description: $pendingLogbook['count'].' hari kegiatan belum Anda periksa (paling lama sejak '.$oldest->translatedFormat('d F Y').').',
                buttonLabel: 'Periksa Logbook',
                url: route('mentor.students.show', $placement->id).'#bagian-logbook',
                urgent: $stale,
                badge: $stale ? 'Sudah '.$waitingDays.' hari menunggu' : null,
                sortDate: $oldest,
            );
        }

        // Laporan & nilai hanya untuk mentor yang ditugaskan (bukan Kepala Unit sementara)
        if (! $placement->isAssignedFieldMentor($mentor)) {
            return $tasks;
        }

        $reportStatus = $this->reportStatus($placement);

        // 2. Naskah laporan akhir menunggu persetujuan
        if ($reportStatus === 'pending') {
            $tasks[] = $this->task(
                type: 'report',
                priority: 2,
                placement: $placement,
                studentName: $studentName,
                title: "Periksa laporan akhir {$studentName}",
                description: 'Naskah laporan sudah diunggah dan menunggu persetujuan Anda sebagai syarat kelulusan.',
                buttonLabel: 'Periksa Laporan',
                url: route('mentor.students.show', $placement->id).'#bagian-laporan',
                urgent: $endDate !== null && $endDate->lte($today),
                badge: null,
                sortDate: $placement->finalreport?->updated_at ? Carbon::parse($placement->finalreport->updated_at) : $today,
            );
        }

        // 3. Nilai akhir mentor
        if ($placement->evaluationLockReason() === null && ! $this->hasMentorScore($placement)) {
            if ($this->evaluationIsDue($placement)) {
                $overdue = $endDate !== null && $endDate->lt($today);
                $description = match (true) {
                    $overdue => 'Masa magang sudah berakhir '.$endDate->translatedFormat('d F Y').'. Nilai Anda ditunggu agar sertifikat dapat diproses.',
                    $endDate !== null => 'Masa magang berakhir '.$endDate->translatedFormat('d F Y').'. Isi nilai sebelum tanggal tersebut.',
                    default => 'Laporan akhir sudah disetujui. Silakan isi nilai akhir mahasiswa.',
                };

                $tasks[] = $this->task(
                    type: 'evaluation',
                    priority: $overdue ? 1 : 5,
                    placement: $placement,
                    studentName: $studentName,
                    title: "Beri nilai akhir {$studentName}",
                    description: $description,
                    buttonLabel: 'Beri Nilai',
                    url: route('mentor.evaluations.create', $placement->id),
                    urgent: $overdue,
                    badge: $overdue ? 'Terlambat '.(int) $endDate->diffInDays($today).' hari' : null,
                    sortDate: $endDate ?? $today,
                );
            }
        }

        // 4. Pengingat: magang hampir/sudah berakhir tetapi mahasiswa belum mengunggah laporan
        if ($reportStatus === null && $status === ApplicationStatus::ACTIVE->value
            && $endDate !== null && $endDate->lte($today->copy()->addDays(7))) {
            $tasks[] = $this->task(
                type: 'reminder',
                priority: 6,
                placement: $placement,
                studentName: $studentName,
                title: "Ingatkan {$studentName} mengunggah laporan akhir",
                description: 'Masa magang berakhir '.$endDate->translatedFormat('d F Y').', tetapi naskah laporan akhir belum diunggah. Kirim pesan pengingat lewat Chat.',
                buttonLabel: 'Buka Halaman Mahasiswa',
                url: route('mentor.students.show', $placement->id).'#bagian-laporan',
                urgent: false,
                badge: null,
                sortDate: $endDate,
            );
        }

        return collect($tasks)->sortBy([['priority', 'asc'], ['sort_date', 'asc']])->values()->all();
    }

    /**
     * Tiga langkah wajib mentor untuk satu mahasiswa, beserta keadaannya saat ini.
     * Dipakai sebagai daftar periksa di halaman detail mahasiswa.
     *
     * @return list<array{key: string, title: string, state: string, text: string, anchor: string}>
     */
    public function checklist(Placement $placement, User $mentor): array
    {
        $application = $placement->application;
        $status = $application?->statusValue();
        $logbooks = $placement->relationLoaded('logbooks') ? $placement->logbooks : $placement->logbooks()->get();
        $pending = $logbooks->where('status', 'pending')->count();
        $waitingStudent = $logbooks->whereIn('status', ['rejected', 'revision'])->count();
        $approved = $logbooks->where('status', 'approved')->count();
        $isAssigned = $placement->isAssignedFieldMentor($mentor);
        $lockReason = $placement->evaluationLockReason();
        $reportStatus = $this->reportStatus($placement);

        // 1. Logbook
        if ($pending > 0) {
            $logbookStep = ['state' => 'todo', 'text' => "{$pending} hari kegiatan menunggu pemeriksaan Anda."];
        } elseif ($waitingStudent > 0) {
            $logbookStep = ['state' => 'waiting', 'text' => "{$waitingStudent} hari sedang diperbaiki mahasiswa. Tidak ada yang perlu Anda periksa saat ini."];
        } elseif ($approved > 0) {
            $logbookStep = ['state' => 'done', 'text' => "Semua logbook sudah Anda periksa ({$approved} hari disetujui)."];
        } else {
            $logbookStep = ['state' => 'waiting', 'text' => $status === ApplicationStatus::ACTIVE->value
                ? 'Mahasiswa belum mengisi logbook.'
                : 'Logbook mulai diisi mahasiswa saat masa magang berjalan.'];
        }

        // 2. Laporan akhir
        $reportStep = match ($reportStatus) {
            'pending' => $isAssigned
                ? ['state' => 'todo', 'text' => 'Naskah laporan menunggu persetujuan Anda.']
                : ['state' => 'waiting', 'text' => 'Naskah laporan menunggu persetujuan mentor yang ditugaskan.'],
            'revision', 'rejected' => ['state' => 'waiting', 'text' => 'Mahasiswa sedang memperbaiki laporan sesuai catatan.'],
            'approved' => ['state' => 'done', 'text' => 'Laporan akhir sudah disetujui.'],
            default => ['state' => 'waiting', 'text' => 'Mahasiswa belum mengunggah naskah laporan akhir.'],
        };

        // 3. Nilai akhir
        if ($this->hasMentorScore($placement)) {
            $nilai = $placement->evaluation?->nilai_pembimbing;
            $evalStep = ['state' => 'done', 'text' => 'Nilai Anda sudah tersimpan (rata-rata '.$nilai.').'];
        } elseif (! $isAssigned) {
            $evalStep = ['state' => 'waiting', 'text' => 'Nilai diisi oleh mentor yang nanti ditugaskan.'];
        } elseif ($lockReason !== null) {
            $evalStep = ['state' => 'waiting', 'text' => $status === ApplicationStatus::COMPLETED->value
                ? 'Magang sudah selesai; nilai terkunci.'
                : 'Formulir nilai terbuka setelah masa magang dimulai.'];
        } elseif ($this->evaluationIsDue($placement)) {
            $endDate = $application?->end_date ? Carbon::parse($application->end_date) : null;
            $evalStep = ['state' => 'todo', 'text' => $endDate
                ? 'Isi nilai akhir paling lambat '.$endDate->translatedFormat('d F Y').'.'
                : 'Isi nilai akhir mahasiswa.'];
        } else {
            // Formulir sudah terbuka, tetapi belum jatuh tempo: sama dengan Daftar Tugas di dasbor.
            $endDate = Carbon::parse($application->end_date)->startOfDay();
            $evalStep = ['state' => 'waiting', 'text' => 'Nilai diisi menjelang akhir magang ('.$endDate->translatedFormat('d F Y')
                .'). Tugas ini muncul di Daftar Tugas mulai '.$endDate->copy()->subDays(self::EVALUATION_WINDOW_DAYS)->translatedFormat('d F Y')
                .'; boleh diisi lebih awal.'];
        }

        return [
            ['key' => 'logbook', 'title' => 'Periksa Logbook Harian', 'anchor' => '#bagian-logbook'] + $logbookStep,
            ['key' => 'report', 'title' => 'Periksa Laporan Akhir', 'anchor' => '#bagian-laporan'] + $reportStep,
            ['key' => 'evaluation', 'title' => 'Beri Nilai Akhir', 'anchor' => '#bagian-nilai'] + $evalStep,
        ];
    }

    /**
     * Nilai mulai diminta bila laporan sudah disetujui, magang tidak punya tanggal akhir,
     * atau tanggal akhir tinggal EVALUATION_WINDOW_DAYS hari (atau sudah lewat).
     */
    public function evaluationIsDue(Placement $placement): bool
    {
        $endDate = $placement->application?->end_date
            ? Carbon::parse($placement->application->end_date)->startOfDay()
            : null;

        return $this->reportStatus($placement) === 'approved'
            || $endDate === null
            || $endDate->lte(now()->startOfDay()->addDays(self::EVALUATION_WINDOW_DAYS));
    }

    public function hasMentorScore(Placement $placement): bool
    {
        $evaluation = $placement->evaluation;

        return (bool) ($evaluation && (
            ($evaluation->nilai_disiplin ?? 0) > 0
            || ($evaluation->nilai_kinerja ?? 0) > 0
            || ($evaluation->nilai_laporan ?? 0) > 0
        ));
    }

    public function reportStatus(Placement $placement): ?string
    {
        $report = $placement->finalreport;
        if (! $report || empty($report->file_path)) {
            return null;
        }

        $status = $report->status instanceof \BackedEnum ? $report->status->value : strtolower((string) $report->status);

        return $status !== '' ? $status : 'pending';
    }

    /**
     * Jumlah logbook menunggu & tanggal tertua per penempatan dalam satu kueri.
     *
     * @return Collection<int, array{count: int, oldest: string|null}>
     */
    private function pendingLogbookStats(Collection $placementIds): Collection
    {
        if ($placementIds->isEmpty()) {
            return collect();
        }

        return Logbook::query()
            ->whereIn('placement_id', $placementIds)
            ->where('status', 'pending')
            ->selectRaw('placement_id, COUNT(*) as total, MIN(date) as oldest')
            ->groupBy('placement_id')
            ->get()
            ->mapWithKeys(fn ($row) => [(int) $row->placement_id => ['count' => (int) $row->total, 'oldest' => $row->oldest]]);
    }

    /**
     * @return array<string, mixed>
     */
    private function task(
        string $type,
        int $priority,
        Placement $placement,
        string $studentName,
        string $title,
        string $description,
        string $buttonLabel,
        string $url,
        bool $urgent,
        ?string $badge,
        Carbon $sortDate,
    ): array {
        return [
            'type' => $type,
            'priority' => $priority,
            'placement_id' => $placement->id,
            'student_name' => $studentName,
            'title' => $title,
            'description' => $description,
            'button' => $buttonLabel,
            'url' => $url,
            'urgent' => $urgent,
            'badge' => $badge,
            'sort_date' => $sortDate->timestamp,
        ];
    }
}
