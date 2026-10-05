<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Logbook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogbookController extends Controller
{
    /**
     * Daftar logbook yang menjadi wewenang mentor:
     *  - mahasiswa bimbingan langsung (mentor_id / pembimbing_id), dan
     *  - FALLBACK: mahasiswa ACTIVE di unit yang ia pimpin (Kepala Unit) yang belum memiliki mentor teknis.
     */
    public function index(Request $request)
    {
        $mentor = Auth::user();

        $query = Logbook::with([
            'placement.application.user.studentProfile',
            'placement.application.unit.agencyProfile',
        ])->whereHas('placement', function ($q) use ($mentor) {
            $q->where(function ($own) use ($mentor) {
                $own->where('mentor_id', $mentor->id)
                    ->orWhere('pembimbing_id', $mentor->id);
            })->orWhere(function ($fallback) use ($mentor) {
                $fallback->whereNull('mentor_id')
                    ->whereNull('pembimbing_id')
                    ->whereHas('application', function ($aq) use ($mentor) {
                        $aq->where('status', 'active')
                            ->whereHas('unit', fn ($uq) => $uq->where('head_user_id', $mentor->id));
                    });
            });

            if ($mentor->agency_profile_id !== null) {
                $q->whereHas('application.unit', function ($uq) use ($mentor) {
                    $uq->where('agency_profile_id', $mentor->agency_profile_id);
                });
            }
        });

        // Filter status (pending, approved, rejected)
        if ($request->filled('status')) {
            $status = strtolower($request->status);
            $query->where('status', $status);
        }

        // Pencarian nama mahasiswa
        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $query->whereHas('placement.application.user', function ($uq) use ($search) {
                $uq->where('name', 'like', "%{$search}%");
            });
        }

        $logbooks = $query->orderBy('date', 'desc')->paginate(15)->withQueryString();

        return view('mentor.logbooks.index', compact('logbooks'));
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
     * Proses Verifikasi & Feedback Logbook oleh Pembimbing Lapangan / Mentor
     * (atau Kepala Unit sebagai verifikator sementara selama mentor teknis belum ditunjuk).
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

        return redirect()->back()->with('success', $isFallback
            ? 'Status logbook diperbarui sebagai Kepala Unit (validasi sementara sebelum mentor ditugaskan).'
            : 'Status logbook mahasiswa berhasil diperbarui!');
    }
}
