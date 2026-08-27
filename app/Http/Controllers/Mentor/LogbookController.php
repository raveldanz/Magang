<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Models\Logbook;
use App\Models\Placement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogbookController extends Controller
{
    /**
     * Menampilkan daftar seluruh kegiatan logbook mahasiswa bimbingan mentor
     */
    public function index(Request $request)
    {
        $mentor = Auth::user();

        $query = Logbook::with([
            'placement.application.user.studentProfile',
            'placement.application.unit.agencyProfile',
        ])->whereHas('placement', function ($q) use ($mentor) {
            $q->where('mentor_id', $mentor->id)
              ->orWhere('pembimbing_id', $mentor->id);
            
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
     * Proses Verifikasi & Feedback Logbook oleh Pembimbing Lapangan / Mentor
     */
    public function updateStatus(Request $request, $logbookId)
    {
        $mentor = Auth::user();

        $request->validate([
            'status' => 'required|in:approved,rejected,pending',
            'feedback' => 'nullable|string|max:1000',
        ]);

        $logbook = Logbook::with(['placement.application.unit'])->findOrFail($logbookId);
        $placement = $logbook->placement;

        // Authorization Check
        $isAssignedMentor = ($placement->mentor_id === $mentor->id || $placement->pembimbing_id === $mentor->id);
        $isAgencyStaff = ($mentor->agency_profile_id !== null && optional($placement->application?->unit)->agency_profile_id === $mentor->agency_profile_id);

        if (!$isAssignedMentor && !$isAgencyStaff) {
            abort(403, 'Anda tidak memiliki hak akses untuk memverifikasi logbook mahasiswa ini.');
        }

        $logbook->update([
            'status' => strtolower($request->status),
            'feedback' => $request->feedback,
        ]);

        \App\Models\AuditLog::record('MENTOR_LOGBOOK_REVIEW', 'Logbook', $logbook->id, [
            'status' => $logbook->status,
            'feedback' => $logbook->feedback,
            'student_name' => optional($placement->application?->user)->name,
        ]);

        return redirect()->back()->with('success', 'Status logbook mahasiswa berhasil diperbarui!');
    }
}
