<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Logbook;
use App\Models\Placement;
use App\Services\LogbookWeeklyBundler;
use App\Services\StudentNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogbookController extends Controller
{
    /**
     * Daftar logbook yang menjadi wewenang mentor (dikelompokkan per paket mingguan):
     *  - mahasiswa bimbingan langsung (mentor_id / pembimbing_id), dan
     *  - FALLBACK: mahasiswa ACTIVE di unit yang ia pimpin (Kepala Unit) yang belum memiliki mentor teknis.
     */
    public function index(Request $request, LogbookWeeklyBundler $bundler)
    {
        $mentor = Auth::user();

        // 1. Ambil seluruh penempatan yang menjadi wewenang mentor ini untuk dropdown filter
        // (lingkup yang sama dipakai notifikasi lonceng mentor, lihat Placement::scopeFieldReviewableBy)
        $supervisedPlacements = Placement::with(['application.user.studentProfile', 'application.unit.agencyProfile'])
            ->fieldReviewableBy($mentor)
            ->get();
        $placementIds = $supervisedPlacements->pluck('id')->toArray();

        // 2. Query logbook yang berhak ditinjau mentor
        $baseLogbooksQuery = Logbook::with([
            'placement.application.user.studentProfile',
            'placement.application.unit.agencyProfile',
            'placement.mentor',
            'placement.academicAdvisor',
        ])->whereIn('placement_id', $placementIds);

        // Hitung 3 angka statistik global wewenang mentor (Menunggu, Disetujui, Perlu Revisi)
        $allMentorLogs = (clone $baseLogbooksQuery)->get();
        $stats = $bundler->calculateCounts($allMentorLogs, 'status');
        $pendingCount = $stats['pending'];
        $approvedCount = $stats['approved'];
        $rejectedCount = $stats['rejected'];

        // 3. Terapkan filter pencarian & mahasiswa jika dipilih
        $filteredQuery = clone $baseLogbooksQuery;

        if ($request->filled('placement_id')) {
            $filteredQuery->where('placement_id', $request->placement_id);
        }

        if ($request->filled('search')) {
            $search = strtolower(trim($request->search));
            $filteredQuery->where(function ($q) use ($search) {
                $q->where('activity', 'like', "%{$search}%")
                    ->orWhereHas('placement.application.user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhereHas('studentProfile', function ($sq) use ($search) {
                                $sq->where('nim', 'like', "%{$search}%");
                            });
                    });
            });
        }

        $logsForBundling = $filteredQuery->orderBy('date', 'desc')->orderBy('id', 'desc')->get();

        // 4. Kelompokkan menjadi paket mingguan (10 paket per halaman)
        $weeklyBundles = $bundler->bundle(
            logbooks: $logsForBundling,
            statusColumn: 'status',
            feedbackColumn: 'feedback',
            otherStatusColumn: 'lecturer_status',
            otherFeedbackColumn: 'lecturer_feedback',
            statusFilter: $request->status,
            perPage: 10
        );

        return view('mentor.logbooks.index', compact(
            'weeklyBundles',
            'supervisedPlacements',
            'pendingCount',
            'approvedCount',
            'rejectedCount'
        ));
    }

    /**
     * Detail logbook untuk mentor yang ditugaskan, atau Kepala Unit (fallback mentor belum ditunjuk).
     */
    public function show($id)
    {
        $logbook = Logbook::with([
            'placement.application.user.studentProfile',
            'placement.application.unit.agencyProfile',
            'placement.mentor',
            'placement.pembimbing',
            'placement.academicAdvisor',
        ])->findOrFail($id);

        abort_unless(
            $logbook->placement?->canFieldReviewLogbook(Auth::user()),
            403,
            'Anda tidak memiliki hak akses ke logbook mahasiswa ini.'
        );

        return view('admin.logbooks.show', compact('logbook'));
    }

    /**
     * Proses Verifikasi & Feedback Logbook per-item (dipertahankan untuk kompatibilitas test).
     */
    public function updateStatus(Request $request, $logbookId)
    {
        $mentor = Auth::user();

        $request->validate([
            'status' => 'required|in:approved,rejected,pending',
            'feedback' => 'nullable|string|max:1000',
        ]);

        $logbook = Logbook::with(['placement.application.unit', 'placement.application.user'])->findOrFail($logbookId);
        $placement = $logbook->placement;

        if (! $placement || ! $placement->canFieldReviewLogbook($mentor)) {
            abort(403, 'Anda tidak memiliki hak akses untuk memverifikasi logbook mahasiswa ini.');
        }

        $isFallback = ! $placement->isAssignedFieldMentor($mentor);

        $logbook->update([
            'status' => strtolower($request->status),
            'feedback' => $request->feedback,
        ]);

        AuditLog::record($isFallback ? 'UNIT_HEAD_LOGBOOK_FALLBACK_REVIEW' : 'MENTOR_LOGBOOK_REVIEW', 'Logbook', $logbook->id, [
            'status' => $logbook->status,
            'feedback' => $logbook->feedback,
            'student_name' => optional($placement->application?->user)->name,
        ]);

        if ($logbook->status === 'rejected') {
            StudentNotifier::logbookRevision($placement->application?->user, 'mentor', 1, $logbook->feedback);
        }

        return redirect()->back()->with('success', $isFallback
            ? 'Status logbook diperbarui sebagai Kepala Unit (validasi sementara sebelum mentor ditugaskan).'
            : 'Status logbook mahasiswa berhasil diperbarui!');
    }

    /**
     * Verifikasi & Ulasan Massal Paket Logbook Mingguan oleh Mentor / Kepala Unit.
     */
    public function bulkReview(Request $request)
    {
        $mentor = Auth::user();

        $request->validate([
            'logbook_ids' => 'required|array|min:1',
            'logbook_ids.*' => 'integer|exists:logbooks,id',
            'status' => 'required|in:approved,rejected',
            'feedback' => 'nullable|string|max:1000',
        ]);

        $logbooks = Logbook::with(['placement.application.unit', 'placement.application.user'])
            ->whereIn('id', $request->logbook_ids)
            ->get();

        $authorizedLogbooks = $logbooks->filter(function ($logbook) use ($mentor) {
            return $logbook->placement && $logbook->placement->canFieldReviewLogbook($mentor);
        });

        if ($authorizedLogbooks->isEmpty()) {
            abort(403, 'Anda tidak memiliki hak akses untuk memverifikasi logbook ini.');
        }

        $count = 0;
        foreach ($authorizedLogbooks as $logbook) {
            $isFallback = ! $logbook->placement->isAssignedFieldMentor($mentor);

            // Setujui banyak sekaligus tanpa catatan: catatan lama per logbook tetap dipertahankan.
            $logbook->update([
                'status' => $request->status,
                'feedback' => $request->filled('feedback') ? $request->feedback : $logbook->feedback,
            ]);

            AuditLog::record($isFallback ? 'UNIT_HEAD_LOGBOOK_FALLBACK_REVIEW' : 'MENTOR_LOGBOOK_REVIEW', 'Logbook', $logbook->id, [
                'status' => $logbook->status,
                'feedback' => $logbook->feedback,
                'student_name' => optional($logbook->placement->application?->user)->name,
                'bulk' => true,
            ]);

            $count++;
        }

        // Satu notifikasi per mahasiswa (bukan per logbook) agar lonceng mahasiswa tidak penuh.
        if ($request->status === 'rejected') {
            $authorizedLogbooks->groupBy('placement_id')->each(function ($group) use ($request) {
                StudentNotifier::logbookRevision($group->first()->placement->application?->user, 'mentor', $group->count(), $request->feedback);
            });
        }

        $statusMsg = $request->status === 'approved' ? 'disetujui' : 'diminta revisi';

        return redirect()->back()->with('success', "Sebanyak {$count} logbook kegiatan mahasiswa berhasil {$statusMsg}!");
    }
}
