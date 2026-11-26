<?php

namespace App\Http\Controllers\Admin;

use App\Console\Commands\SyncExternalInstitutions;
use App\Exceptions\SyncAlreadyRunning;
use App\Http\Controllers\Controller;
use App\Jobs\RunInstitutionSyncStep;
use App\Models\AuditLog;
use App\Models\InstitutionChangeProposal;
use App\Models\InstitutionSyncRun;
use App\Services\Directory\CollegeDirectoryClient;
use App\Services\Directory\SchoolDirectoryClient;
use App\Services\Institutions\ChangeProposalService;
use App\Services\Institutions\InstitutionSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Sinkron direktori sekolah/kampus dan keputusan atas usul perubahannya. Khusus Super Administrator. */
class InstitutionSyncController extends Controller
{
    private const DENIED = 'Hanya Super Administrator yang dapat memperbarui data sekolah/kampus.';

    /** Proses yang masih menunggu selama ini berarti pekerja antrean tidak berjalan. */
    private const QUEUE_WAIT_SECONDS = 60;

    public function start(Request $request, InstitutionSyncService $sync, SchoolDirectoryClient $directory, CollegeDirectoryClient $colleges): RedirectResponse
    {
        $this->ensureSuperAdmin(self::DENIED);

        $kind = $request->validate(['kind' => 'sometimes|in:school,college'])['kind'] ?? 'school';
        if (! ($kind === 'college' ? $colleges : $directory)->enabled()) {
            return back()->with('error', 'Direktori yang dipilih sedang dinonaktifkan pada pengaturan server.');
        }

        try {
            $run = $sync->start('manual', auth()->id(), kind: $kind);
        } catch (SyncAlreadyRunning $e) {
            return back()->with('error', $e->getMessage());
        }

        AuditLog::record('INSTITUTION_SYNC_START', 'InstitutionSyncRun', $run->id);
        $first = $sync->firstStep($run);
        RunInstitutionSyncStep::dispatch($run->id, $first['step'], $first['page']);

        return back()->with('success', 'Sinkron dimulai. Progresnya tampil di panel data sekolah & kampus.');
    }

    public function status(Request $request): JsonResponse
    {
        $this->ensureSuperAdmin(self::DENIED);

        $kind = $request->validate(['kind' => 'sometimes|in:school,college'])['kind'] ?? 'school';
        $run = InstitutionSyncRun::where('kind', $kind)->latest('id')->first();

        return response()->json([
            'run' => $run ? [
                'status' => $run->status,
                'trigger' => $run->trigger,
                'step' => $run->current_step,
                'page' => $run->current_page,
                'last_page' => $run->last_page,
                'created' => $run->created_count,
                'updated' => $run->updated_count,
                'proposed' => $run->proposed_count,
                'skipped' => $run->skipped_count,
                'message' => $run->message,
                'finished_at' => $run->finished_at?->format('d/m/Y H:i'),
            ] : null,
            'active' => (bool) $run?->isActive(),
            'stalled' => (bool) $run?->isStalled(),
            'queue_waiting' => $run?->status === 'queued' && $run->created_at->lt(now()->subSeconds(self::QUEUE_WAIT_SECONDS)),
            'last_full_sync' => SyncExternalInstitutions::lastFullSync($kind)?->format('d/m/Y H:i'),
            'pending_proposals' => InstitutionChangeProposal::where('status', 'pending')
                ->where('kind', $kind === 'college' ? '=' : '!=', InstitutionChangeProposal::KIND_NEW_COLLEGE)
                ->count(),
        ]);
    }

    public function proposals(Request $request): View
    {
        $this->ensureSuperAdmin(self::DENIED);

        $kinds = [
            InstitutionChangeProposal::KIND_NAME => 'Perubahan nama',
            InstitutionChangeProposal::KIND_ADDRESS => 'Perubahan alamat',
            InstitutionChangeProposal::KIND_NEW_COLLEGE => 'Kampus baru',
        ];
        $kind = array_key_exists((string) $request->query('kind'), $kinds) ? (string) $request->query('kind') : null;

        $proposals = InstitutionChangeProposal::with('university')
            ->where('status', 'pending')
            ->when($kind, fn ($q) => $q->where('kind', $kind))
            ->orderBy('id')
            ->paginate(50)
            ->withQueryString();

        return view('admin.universities.proposals', compact('proposals', 'kinds', 'kind'));
    }

    public function decide(Request $request, ChangeProposalService $proposals): RedirectResponse
    {
        $this->ensureSuperAdmin(self::DENIED);

        $validated = $request->validate([
            'ids' => ['required', 'array', 'max:200'],
            'ids.*' => ['integer'],
            'decision' => ['required', 'in:accept,reject'],
        ], ['ids.required' => 'Pilih minimal satu usul.']);

        $count = $proposals->decide($validated['ids'], $validated['decision'], (int) auth()->id());
        $accepted = $validated['decision'] === 'accept';

        AuditLog::record($accepted ? 'INSTITUTION_PROPOSAL_ACCEPT' : 'INSTITUTION_PROPOSAL_REJECT', 'InstitutionChangeProposal', null, [
            'ids' => array_map('intval', $validated['ids']),
            'count' => $count,
        ]);

        return back()->with('success', "{$count} usul ".($accepted ? 'diterima.' : 'ditolak.'));
    }
}
