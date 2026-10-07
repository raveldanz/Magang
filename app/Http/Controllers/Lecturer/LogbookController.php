<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\Logbook;
use App\Models\Placement;
use App\Services\LogbookWeeklyBundler;
use App\Services\StudentNotifier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        // Status filter: terima 'status' maupun 'lecturer_status'
        $statusFilter = $request->input('status', $request->input('lecturer_status'));

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
            'rejectedCount'
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
            abort(403, 'Anda tidak memiliki hak akses untuk memonitor logbook mahasiswa ini.');
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
            'feedback' => 'nullable|string',
        ]);

        $logbook = Logbook::with('placement.application.user')->findOrFail($id);
        $placement = $logbook->placement;
        $student = $placement?->application?->user;

        // Otorisasi
        $isAssignedAdvisor = ($placement && ($placement->academic_advisor_id === $user->id || $placement->mentor_id === $user->id));
        $isSameUniv = ($user->university_id !== null && $student?->university_id === $user->university_id);

        if (! $isSameUniv && $user->university && $student) {
            $isSameUniv = (
                $student->university === $user->university ||
                optional($student->studentProfile)->universitas === $user->university
            );
        }

        if (! $isAssignedAdvisor && ! $isSameUniv) {
            abort(403, 'Anda tidak memiliki hak akses untuk memverifikasi logbook mahasiswa ini.');
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

        return redirect()->back()->with('success', "Logbook berhasil {$statusText} dan catatan feedback Dosen telah tersimpan!");
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
            'action' => 'required|in:approved,rejected',
            'bulk_feedback' => 'nullable|string|max:1000',
        ]);

        // Ambil ID placement yang dibimbing langsung oleh Dosen ini
        $supervisedPlacementIds = Placement::where('academic_advisor_id', $user->id)
            ->pluck('id')
            ->toArray();

        $updates = [
            'lecturer_status' => $action,
            'lecturer_verified_at' => Carbon::now(),
        ];
        // Catatan hanya ditimpa bila DPL menulis catatan baru; setujui banyak sekaligus
        // tanpa catatan tidak menghapus catatan lama per logbook.
        if (filled($feedback)) {
            $updates['lecturer_feedback'] = $feedback;
        }

        $targetQuery = Logbook::whereIn('id', $request->logbook_ids)
            ->whereIn('placement_id', $supervisedPlacementIds);

        // Hitung per penempatan sebelum update, untuk satu notifikasi per mahasiswa saat minta revisi.
        $perPlacement = $action === 'rejected'
            ? (clone $targetQuery)->selectRaw('placement_id, count(*) as total')->groupBy('placement_id')->pluck('total', 'placement_id')
            : collect();

        $count = $targetQuery->update($updates);

        if ($perPlacement->isNotEmpty()) {
            Placement::with('application.user')->whereIn('id', $perPlacement->keys())->get()
                ->each(fn ($pl) => StudentNotifier::logbookRevision($pl->application?->user, 'lecturer', (int) $perPlacement[$pl->id], $feedback));
        }

        $statusMsg = $action === 'approved' ? 'disetujui (ACC)' : 'diminta revisi';

        return redirect()->back()->with('success', "Sebanyak {$count} logbook mahasiswa bimbingan berhasil {$statusMsg} sekaligus!");
    }
}
