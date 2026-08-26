<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\Logbook;
use App\Models\Placement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogbookController extends Controller
{
    /**
     * Tampilkan seluruh rekapitulasi & feed logbook mahasiswa bimbingan dosen (Strictly Scoped DPL)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $lecturerId = $user->id;

        // 1. Ambil seluruh ID placement yang dibimbing langsung oleh Dosen ini
        $supervisedPlacements = Placement::with(['application.user.studentProfile', 'application.unit.agencyProfile'])
            ->where('academic_advisor_id', $lecturerId)
            ->whereHas('application', function ($q) {
                $q->whereIn('status', ['accepted', 'completed']);
            })
            ->get();

        $placementIds = $supervisedPlacements->pluck('id')->toArray();

        // 2. Query Logbook HANYA untuk mahasiswa bimbingan DPL ini
        $logbooksQuery = Logbook::with([
            'placement.application.user.studentProfile',
            'placement.application.unit.agencyProfile',
            'placement.mentor',
            'placement.academicAdvisor',
        ])
        ->whereIn('placement_id', $placementIds);

        // Filter Mahasiswa
        if ($request->filled('placement_id')) {
            $logbooksQuery->where('placement_id', $request->placement_id);
        }

        // Filter Status Verifikasi Dosen
        if ($request->filled('lecturer_status')) {
            $logbooksQuery->where('lecturer_status', $request->lecturer_status);
        }

        // Filter Status Verifikasi Mentor
        if ($request->filled('mentor_status')) {
            $logbooksQuery->where('status', $request->mentor_status);
        }

        // Search Keyword (Nama Mahasiswa, NIM, Deskripsi Kegiatan)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $logbooksQuery->where(function ($q) use ($search) {
                $q->where('activity', 'like', "%{$search}%")
                  ->orWhereHas('placement.application.user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhereHas('studentProfile', function ($sq) use ($search) {
                             $sq->where('nim', 'like', "%{$search}%");
                         });
                  });
            });
        }

        // Urutkan dari tanggal logbook terbaru
        $logbooks = $logbooksQuery->orderBy('date', 'desc')->orderBy('id', 'desc')->paginate(15)->withQueryString();

        // Statistik Ringkas
        $totalLogs = Logbook::whereIn('placement_id', $placementIds)->count();
        $pendingDosenLogs = Logbook::whereIn('placement_id', $placementIds)->where('lecturer_status', 'pending')->count();
        $approvedDosenLogs = Logbook::whereIn('placement_id', $placementIds)->where('lecturer_status', 'approved')->count();
        $rejectedDosenLogs = Logbook::whereIn('placement_id', $placementIds)->where('lecturer_status', 'rejected')->count();

        return view('lecturer.logbooks.index', compact(
            'user',
            'logbooks',
            'supervisedPlacements',
            'totalLogs',
            'pendingDosenLogs',
            'approvedDosenLogs',
            'rejectedDosenLogs'
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

        if (!$isSameUniv && $user->university && $student) {
            $isSameUniv = (
                $student->university === $user->university || 
                optional($student->studentProfile)->universitas === $user->university
            );
        }

        if (!$isAssignedAdvisor && !$isSameUniv) {
            abort(403, 'Anda tidak memiliki hak akses untuk memonitor logbook mahasiswa ini.');
        }

        return view('admin.logbooks.show', compact('logbook'));
    }

    /**
     * Simpan status verifikasi & feedback dari Dosen Pembimbing
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

        if (!$isSameUniv && $user->university && $student) {
            $isSameUniv = (
                $student->university === $user->university || 
                optional($student->studentProfile)->universitas === $user->university
            );
        }

        if (!$isAssignedAdvisor && !$isSameUniv) {
            abort(403, 'Anda tidak memiliki hak akses untuk memverifikasi logbook mahasiswa ini.');
        }

        $logbook->update([
            'lecturer_status' => $request->status,
            'lecturer_feedback' => $request->feedback,
            'lecturer_verified_at' => Carbon::now(),
        ]);

        $statusText = $request->status === 'approved' ? 'disetujui (ACC)' : ($request->status === 'rejected' ? 'ditolak / diminta revisi' : 'diperbarui');

        return redirect()->back()->with('success', "Logbook berhasil {$statusText} dan catatan feedback Dosen telah tersimpan!");
    }
}
