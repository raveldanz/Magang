<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class LogbookWeeklyBundler
{
    /**
     * Kelompokkan koleksi logbook ke dalam paket mingguan (ISO Week per penempatan).
     *
     * @param  iterable  $logbooks  Koleksi atau query logbook yang sudah terurut
     * @param  string  $statusColumn  Nama kolom status wewenang pengguna ('status' untuk Mentor, 'lecturer_status' untuk DPL)
     * @param  string  $feedbackColumn  Nama kolom feedback ('feedback' untuk Mentor, 'lecturer_feedback' untuk DPL)
     * @param  string  $otherStatusColumn  Nama kolom status pihak lain ('lecturer_status' untuk Mentor, 'status' untuk DPL)
     * @param  string  $otherFeedbackColumn  Nama kolom feedback pihak lain ('lecturer_feedback' untuk Mentor, 'feedback' untuk DPL)
     * @param  string|null  $statusFilter  Filter opsional ('pending', 'approved', 'rejected')
     * @param  int|null  $perPage  Jumlah paket per halaman (default 10 untuk paginasi, null untuk semua)
     * @param  int|null  $page  Halaman aktif
     * @return LengthAwarePaginator|Collection
     */
    public function bundle(
        iterable $logbooks,
        string $statusColumn = 'status',
        string $feedbackColumn = 'feedback',
        string $otherStatusColumn = 'lecturer_status',
        string $otherFeedbackColumn = 'lecturer_feedback',
        ?string $statusFilter = null,
        ?int $perPage = 10,
        ?int $page = null
    ) {
        $collection = $logbooks instanceof Collection ? $logbooks : collect($logbooks);

        $groups = $collection->groupBy(function ($item) {
            $carbonDate = Carbon::parse($item->date);

            return $item->placement_id.'_'.$carbonDate->year.'-W'.str_pad($carbonDate->isoWeek(), 2, '0', STR_PAD_LEFT);
        });

        $bundles = $groups->map(function ($group, $key) use ($statusColumn, $feedbackColumn, $otherStatusColumn, $otherFeedbackColumn) {
            $first = $group->first();
            $placement = $first->placement;
            $student = $placement?->application?->user;
            $unit = $placement?->application?->unit;
            $agency = $unit?->agencyProfile ?? $placement?->agencyProfile;

            $minDate = $group->min('date');
            $maxDate = $group->max('date');

            $pendingCount = $group->where($statusColumn, 'pending')->count();
            $approvedCount = $group->where($statusColumn, 'approved')->count();
            $rejectedCount = $group->filter(fn ($item) => in_array($item->{$statusColumn}, ['rejected', 'revision']))->count();

            $status = 'approved';
            if ($pendingCount > 0) {
                $status = 'pending';
            } elseif ($rejectedCount > 0) {
                $status = 'rejected';
            }

            $entries = $group->sortBy('date')->map(function ($entry) use ($statusColumn, $feedbackColumn, $otherStatusColumn, $otherFeedbackColumn) {
                return [
                    'id' => $entry->id,
                    'date' => $entry->date,
                    'activity' => $entry->activity,
                    'attachment' => $entry->attachment,
                    'attachment_url' => $entry->attachment_url,
                    'status' => $entry->{$statusColumn} ?? 'pending',
                    'feedback' => $entry->{$feedbackColumn} ?? null,
                    'other_status' => $entry->{$otherStatusColumn} ?? null,
                    'other_feedback' => $entry->{$otherFeedbackColumn} ?? null,
                ];
            })->values()->all();

            $hasFieldMentor = $placement && $placement->hasFieldMentor();

            return [
                'bundle_key' => $key,
                'placement' => $placement,
                'student' => $student,
                'unit' => $unit,
                'agency' => $agency,
                'min_date' => $minDate,
                'max_date' => $maxDate,
                'entries_count' => $group->count(),
                'pending_count' => $pendingCount,
                'approved_count' => $approvedCount,
                'rejected_count' => $rejectedCount,
                'status' => $status,
                'logbook_ids' => $group->pluck('id')->all(),
                'feedback' => $group->pluck($feedbackColumn)->filter()->first() ?? null,
                'entries' => $entries,
                'is_fallback_unit_head' => (! $hasFieldMentor),
            ];
        })->values();

        // Filter status jika diminta
        if ($statusFilter && in_array(strtolower($statusFilter), ['pending', 'approved', 'rejected'])) {
            $bundles = $bundles->filter(fn ($b) => $b['status'] === strtolower($statusFilter))->values();
        }

        if ($perPage === null) {
            return $bundles;
        }

        $currentPage = $page ?? LengthAwarePaginator::resolveCurrentPage();
        $itemsForPage = $bundles->slice(($currentPage - 1) * $perPage, $perPage)->values();

        return new LengthAwarePaginator(
            $itemsForPage,
            $bundles->count(),
            $perPage,
            $currentPage,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'query' => request()->query(),
            ]
        );
    }

    /**
     * Hitung ringkasan statistik (Menunggu, Disetujui, Perlu Revisi).
     */
    public function calculateCounts(iterable $logbooks, string $statusColumn = 'status'): array
    {
        $collection = $logbooks instanceof Collection ? $logbooks : collect($logbooks);

        $pending = $collection->where($statusColumn, 'pending')->count();
        $approved = $collection->where($statusColumn, 'approved')->count();
        $rejected = $collection->filter(fn ($item) => in_array($item->{$statusColumn}, ['rejected', 'revision']))->count();

        return [
            'pending' => $pending,
            'approved' => $approved,
            'rejected' => $rejected,
            'total' => $collection->count(),
        ];
    }
}
