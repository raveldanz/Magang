<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\Logbook;
use App\Models\Placement;
use App\Services\LogbookReviewDecision;
use App\Services\LogbookWeeklyBundler;
use App\Services\StudentNotifier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LogbookController extends Controller
{
    /**
     * Tampilkan rekapitulasi logbook mahasiswa bimbingan dosen (dikelompokkan per paket mingguan)
     */
    public function index(Request $request, LogbookWeeklyBundler $bundler)
    {
        $user = Auth::user();
        $lecturerId = $user->id;

        // 1. Ambil seluruh ID placement yang dibimbing langsung oleh Dosen ini
        $supervisedPlacements = Placement::with(['application.user.studentProfile', 'application.unit.agencyProfile'])
            ->where('academic_advisor_id', $lecturerId)
            ->whereHas('application', function ($q) {
                $q->whereIn('status', ['accepted', 'active', 'completed']);
            })
            ->get();

        $placementIds = $supervisedPlacements->pluck('id')->toArray();

        // 2. Query Logbook HANYA untuk mahasiswa bimbingan DPL ini
        $baseLogbooksQuery = Logbook::with([
            'placement.application.user.studentProfile',
            'placement.application.unit.agencyProfile',
            'placement.mentor',
            'placement.academicAdvisor',
        ])->whereIn('placement_id', $placementIds);

        // Hitung 3 angka statistik global wewenang DPL (Menunggu, Disetujui, Perlu Revisi)
        $allSupervisedLogs = (clone $baseLogbooksQuery)->get();
        $stats = $bundler->calculateCounts($allSupervisedLogs, 'lecturer_status');
        $pendingCount = $stats['pending'];
        $approvedCount = $stats['approved'];
        $rejectedCount = $stats['rejected'];
        $mentorApprovedPendingCount = $allSupervisedLogs->where('status', 'approved')->where('lecturer_status', 'pending')->count();

        // 3. Terapkan filter pencarian & mahasiswa jika dipilih
        $filteredQuery = clone $baseLogbooksQuery;

        if ($request->filled('placement_id')) {
            $filteredQuery->where('placement_id', $request->placement_id);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $like = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
            $filteredQuery->where(function ($q) use ($search, $like) {
                $q->where('activity', $like, "%{$search}%")
                    ->orWhereHas('placement.application.user', function ($uq) use ($search, $like) {
                        $uq->where('name', $like, "%{$search}%")
                            ->orWhereHas('studentProfile', function ($sq) use ($search, $like) {
                                $sq->where('nim', $like, "%{$search}%");
                            });
                    });
            });
        }

        // Status filter: terima 'status' maupun 'lecturer_status'
        $statusFilter = $request->input('status', $request->input('lecturer_status'));

        if ($statusFilter === 'mentor_approved') {
            $filteredQuery->where('status', 'approved')->where('lecturer_status', 'pending');
            $statusFilter = null;
        }

        $logsForBundling = $filteredQuery->orderBy('date', 'desc')->orderBy('id', 'desc')->get();

        // 4. Kelompokkan menjadi paket mingguan (10 paket per halaman)
        $weeklyBundles = $bundler->bundle(
            logbooks: $logsForBundling,
            statusColumn: 'lecturer_status',
            feedbackColumn: 'lecturer_feedback',
            otherStatusColumn: 'status',
            otherFeedbackColumn: 'feedback',
            statusFilter: $statusFilter,
            perPage: 10
        );

        return view('lecturer.logbooks.index', compact(
            'weeklyBundles',
            'supervisedPlacements',
            'pendingCount',
            'approvedCount',
            'rejectedCount',
            'mentorApprovedPendingCount'
        ));
    }

    /**
     * Tampilkan detail & formulir verifikasi interaktif Dosen Kampus
     */
    public function show($id)
    {
        $user = Auth::user();

        $logbook = Logbook::with([
            'placement.application.user.studentProfile',
            'placement.application.unit.agencyProfile',
            'placement.mentor',
            'placement.pembimbing',
            'placement.academicAdvisor',
        ])->findOrFail($id);

        $placement = $logbook->placement;
        $student = $placement?->application?->user;

        // Otorisasi: Dosen adalah pembimbing yang diplot (academic_advisor_id) atau satu kampus
        $isAssignedAdvisor = ($placement && ($placement->academic_advisor_id === $user->id || $placement->mentor_id === $user->id));
        $isSameUniv = ($user->university_id !== null && $student?->university_id === $user->university_id);

        if (! $isSameUniv && $user->university && $student) {
            $isSameUniv = (
                $student->university === $user->university ||
                optional($student->studentProfile)->universitas === $user->university
            );
        }

        if (! $isAssignedAdvisor && ! $isSameUniv) {
            abort(403, 'Anda tidak memiliki hak akses untuk memonitor logbook peserta magang ini.');
        }

        return view('admin.logbooks.show', compact('logbook'));
    }

    /**
     * Simpan status verifikasi & feedback dari Dosen Pembimbing (per-item)
     */
    public function updateStatus(Request $request, $id)
    {
        $user = Auth::user();

        $request->validate([
            'status' => 'required|in:approved,rejected,pending',
            'feedback' => 'nullable|required_if:status,rejected|string|max:1500',
        ]);

        $logbook = Logbook::with('placement.application.user')->findOrFail($id);
        $placement = $logbook->placement;
        $student = $placement?->application?->user;

        // Otorisasi: memverifikasi logbook adalah tugas DPL yang ditetapkan pada penempatan,
        // sama seperti bulkApprove dan penilaian. Dosen lain di kampus yang sama hanya boleh melihat.
        $isAssignedAdvisor = ($placement && ((int) $placement->academic_advisor_id === (int) $user->id || (int) $placement->mentor_id === (int) $user->id));

        if (! $isAssignedAdvisor) {
            abort(403, 'Anda tidak memiliki hak akses untuk memverifikasi logbook peserta magang ini.');
        }

        $logbook->update([
            'lecturer_status' => $request->status,
            'lecturer_feedback' => $request->feedback,
            'lecturer_verified_at' => Carbon::now(),
        ]);

        if ($request->status === 'rejected') {
            StudentNotifier::logbookRevision($student, 'lecturer', 1, $request->feedback);
        }

        $statusText = $request->status === 'approved' ? 'disetujui (ACC)' : ($request->status === 'rejected' ? 'ditolak / diminta revisi' : 'diperbarui');

        return redirect()->back()->with('success', "Logbook berhasil {$statusText} dan catatan feedback Pembimbing telah tersimpan!");
    }

    /**
     * Setujui / Minta Revisi secara massal (Bulk Review) paket logbook mahasiswa bimbingan Dosen
     */
    public function bulkApprove(Request $request)
    {
        $user = Auth::user();

        // Dukung baik field baru (status, feedback) maupun legacy (action, bulk_feedback)
        $action = $request->input('status', $request->input('action'));
        $feedback = $request->input('feedback', $request->input('bulk_feedback'));

        $request->merge([
            'action' => $action,
            'bulk_feedback' => $feedback,
        ]);

        $request->validate([
            'logbook_ids' => 'required|array|min:1',
            'logbook_ids.*' => 'integer|exists:logbooks,id',
            // per_day: hari yang dicentang "Perlu Diperbaiki" (revise_ids) dikembalikan, sisanya disetujui.
            'action' => 'required|in:approved,rejected,per_day',
            'revise_ids' => 'nullable|array',
            'revise_ids.*' => ['integer', Rule::in(array_map('intval', (array) $request->input('logbook_ids', [])))],
            'bulk_feedback' => 'nullable|required_if:action,rejected|required_with:revise_ids|string|max:1000',
        ], [
            'bulk_feedback.required_if' => 'Catatan wajib diisi saat menolak atau meminta revisi, agar peserta magang tahu apa yang perlu diperbaiki.',
            'bulk_feedback.required_with' => 'Catatan wajib diisi saat menolak atau meminta revisi, agar peserta magang tahu apa yang perlu diperbaiki.',
        ]);

        // Ambil ID placement yang dibimbing langsung oleh Dosen ini
        $supervisedPlacementIds = Placement::where('academic_advisor_id', $user->id)
            ->pluck('id')
            ->toArray();

        $logbooks = Logbook::whereIn('id', $request->logbook_ids)
            ->whereIn('placement_id', $supervisedPlacementIds)
            ->get();

        $reviseIds = collect($request->input('revise_ids', []))->map(fn ($id) => (int) $id)->all();
        $decisions = LogbookReviewDecision::resolve($logbooks, $action, $reviseIds);
        $note = filled($feedback) ? (string) $feedback : null;
        $noteTargetsRejected = in_array('rejected', $decisions, true);

        // Catatan lama dipertahankan bila tidak ada catatan baru, kecuali catatan revisi yang
        // sudah ditangani mahasiswa (lihat LogbookReviewDecision::feedbackFor).
        DB::transaction(function () use ($logbooks, $decisions, $note, $noteTargetsRejected) {
            foreach ($logbooks as $logbook) {
                $newStatus = $decisions[$logbook->id];
                $logbook->update([
                    'lecturer_status' => $newStatus,
                    'lecturer_verified_at' => Carbon::now(),
                    'lecturer_feedback' => LogbookReviewDecision::feedbackFor($logbook->lecturer_status, $logbook->lecturer_feedback, $newStatus, $note, $noteTargetsRejected),
                ]);
            }
        });

        // Satu notifikasi per mahasiswa saat ada hari yang dikembalikan.
        $rejected = $logbooks->filter(fn ($logbook) => $decisions[$logbook->id] === 'rejected');
        if ($rejected->isNotEmpty()) {
            $perPlacement = $rejected->countBy('placement_id');
            Placement::with('application.user')->whereIn('id', $perPlacement->keys())->get()
                ->each(fn ($pl) => StudentNotifier::logbookRevision($pl->application?->user, 'lecturer', (int) $perPlacement[$pl->id], $note));
        }

        return redirect()->back()->with('success', LogbookReviewDecision::summaryMessage(
            $logbooks->count() - $rejected->count(),
            $rejected->count()
        ));
    }

    /**
     * One-Click Approval: Setujui semua logbook mahasiswa pada penempatan ini
     * yang telah di-ACC oleh Mentor Lapangan namun masih pending di sisi DPL.
     */
    public function approveMentorReviewedForPlacement($placementId)
    {
        $user = Auth::user();

        $placement = Placement::with(['application.user'])->find($placementId)
            ?? Placement::with(['application.user'])->where('application_id', $placementId)->firstOrFail();

        $isAssignedAdvisor = ($placement->academic_advisor_id === $user->id);
        $isSuperAdmin = $user->isSuperAdmin();

        if (! $isAssignedAdvisor && ! $isSuperAdmin) {
            abort(403, 'Akses Ditolak: Anda bukan Pembimbing yang ditugaskan untuk peserta magang ini.');
        }

        $now = Carbon::now();
        $updatedCount = Logbook::where('placement_id', $placement->id)
            ->where('status', 'approved')
            ->where('lecturer_status', 'pending')
            ->update([
                'lecturer_status' => 'approved',
                'lecturer_verified_at' => $now,
            ]);

        // Cek sinkronisasi status kelulusan jika seluruh prasyarat magang sudah selesai
        $placement->syncCompletionStatus();

        if ($updatedCount > 0) {
            return redirect()->back()->with('success', "Berhasil menyetujui {$updatedCount} logbook yang telah di-ACC mentor dalam satu klik!");
        }

        return redirect()->back()->with('info', 'Tidak ada logbook menunggu yang telah di-ACC oleh mentor.');
    }

    /**
     * One-Click Approval Global: Setujui seluruh logbook bimbingan DPL
     * yang telah di-ACC oleh Mentor Lapangan.
     */
    public function approveAllMentorReviewed(Request $request)
    {
        $user = Auth::user();

        $supervisedPlacementIds = Placement::where('academic_advisor_id', $user->id)
            ->pluck('id')
            ->toArray();

        $query = Logbook::whereIn('placement_id', $supervisedPlacementIds)
            ->where('status', 'approved')
            ->where('lecturer_status', 'pending');

        if ($request->filled('placement_id')) {
            $query->where('placement_id', $request->placement_id);
        }

        $now = Carbon::now();
        $count = $query->update([
            'lecturer_status' => 'approved',
            'lecturer_verified_at' => $now,
        ]);

        if ($count > 0) {
            return redirect()->back()->with('success', "Sebanyak {$count} logbook peserta magang yang telah disetujui mentor berhasil di-ACC sekaligus!");
        }

        return redirect()->back()->with('info', 'Tidak ada logbook menunggu yang telah di-ACC oleh mentor.');
    }
}
