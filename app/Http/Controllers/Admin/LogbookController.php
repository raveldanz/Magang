<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Logbook;
use App\Models\Placement;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogbookController extends Controller
{
    /**
     * Rekapitulasi agregat aktivitas logbook per mahasiswa (Multi-Tenant Scoped)
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Placement::with([
            'application.user.studentProfile',
            'application.unit.agencyProfile',
            'mentor',
            'pembimbing',
            'logbooks' => function ($q) {
                $q->orderBy('date', 'desc');
            }
        ])->whereHas('application', function ($q) {
            $q->where('status', 'accepted');
        });

        // Multi-Tenant Isolation untuk Admin Dinas
        if ($user && $user->agency_profile_id !== null) {
            $query->whereHas('application.unit', function ($q) use ($user) {
                $q->where('agency_profile_id', $user->agency_profile_id);
            });
        }

        // Filter Berdasarkan Unit
        if ($request->filled('unit_id')) {
            $query->whereHas('application', function ($q) use ($request) {
                $q->where('unit_id', $request->unit_id);
            });
        }

        // Pencarian Mahasiswa (Nama / NIM)
        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $query->whereHas('application.user', function ($uq) use ($search) {
                $uq->where('name', 'like', "%{$search}%")
                   ->orWhereHas('studentProfile', function ($sq) use ($search) {
                       $sq->where('nim', 'like', "%{$search}%");
                   });
            });
        }

        // Filter Status Logbook Mahasiswa
        if ($request->filled('status_filter')) {
            $statusFilter = strtolower($request->status_filter);
            if ($statusFilter === 'pending') {
                $query->whereHas('logbooks', function ($lq) {
                    $lq->where('status', 'pending');
                });
            } elseif ($statusFilter === 'approved') {
                $query->whereHas('logbooks', function ($lq) {
                    $lq->where('status', 'approved');
                });
            } elseif ($statusFilter === 'empty') {
                $query->doesntHave('logbooks');
            }
        }

        $placements = $query->latest()->paginate(15)->withQueryString();

        // Data Unit untuk Dropdown Filter
        $unitsQuery = Unit::query();
        if ($user && $user->agency_profile_id !== null) {
            $unitsQuery->where('agency_profile_id', $user->agency_profile_id);
        }
        $units = $unitsQuery->orderBy('name')->get();

        // Statistik Agregat Keseluruhan untuk Kartu Ringkasan
        $statsQuery = Logbook::query();
        if ($user && $user->agency_profile_id !== null) {
            $statsQuery->whereHas('placement.application.unit', function ($uq) use ($user) {
                $uq->where('agency_profile_id', $user->agency_profile_id);
            });
        }
        $totalLogs = (clone $statsQuery)->count();
        $approvedLogs = (clone $statsQuery)->where('status', 'approved')->count();
        $pendingLogs = (clone $statsQuery)->where('status', 'pending')->count();
        $rejectedLogs = (clone $statsQuery)->where('status', 'rejected')->count();

        // Jika admin memilih untuk melihat riwayat logbook spesifik mahasiswa (placement_id)
        $selectedPlacement = null;
        if ($request->filled('placement_id')) {
            $selectedPlacement = Placement::with([
                'application.user.studentProfile',
                'application.unit.agencyProfile',
                'mentor',
                'pembimbing',
                'logbooks' => function ($q) {
                    $q->orderBy('date', 'desc');
                }
            ])->find($request->placement_id);

            // Validasi kepemilikan instansi
            if ($selectedPlacement && $user && $user->agency_profile_id !== null) {
                if (optional($selectedPlacement->application?->unit)->agency_profile_id !== $user->agency_profile_id) {
                    $selectedPlacement = null;
                }
            }
        }

        return view('admin.logbooks.index', compact(
            'placements', 
            'units', 
            'totalLogs', 
            'approvedLogs', 
            'pendingLogs', 
            'rejectedLogs',
            'selectedPlacement'
        ));
    }

    /**
     * Detail logbook kegiatan per aktivitas
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

        // Multi-Tenant Authorization Check untuk Admin/Mentor Instansi
        if ($user && $user->agency_profile_id !== null && optional($placement?->application?->unit)->agency_profile_id !== $user->agency_profile_id) {
            abort(403, 'Anda tidak memiliki hak akses ke logbook instansi lain.');
        }

        // Authorization Check untuk Dosen Kampus
        if (in_array($user->role, ['dosen', 'academic_advisor'])) {
            $student = $placement?->application?->user;
            $isSameUniv = ($user->university_id !== null && $student?->university_id === $user->university_id);
            $isAssignedAdvisor = ($placement && ($placement->academic_advisor_id === $user->id || $placement->mentor_id === $user->id));

            if (!$isSameUniv && $user->university && $student) {
                $isSameUniv = (
                    $student->university === $user->university || 
                    optional($student->studentProfile)->universitas === $user->university
                );
            }

            if (!$isSameUniv && !$isAssignedAdvisor) {
                abort(403, 'Anda tidak memiliki hak akses untuk memonitor logbook mahasiswa ini.');
            }
        }

        return view('admin.logbooks.show', compact('logbook'));
    }

    /**
     * Review logbook (Opsional / Legacy Support)
     */
    public function review(Request $request, $id)
    {
        $user = Auth::user();

        $request->validate([
            'status'   => 'required|in:approved,rejected,pending,APPROVED,REJECTED,PENDING',
            'feedback' => 'nullable|string',
        ]);

        $logbook = Logbook::with('placement.application.unit')->findOrFail($id);
        
        // Multi-Tenant Authorization Check
        if ($user && $user->agency_profile_id !== null && optional($logbook->placement?->application?->unit)->agency_profile_id !== $user->agency_profile_id) {
            abort(403, 'Anda tidak memiliki hak akses untuk me-review logbook instansi lain.');
        }

        $logbook->update([
            'status'   => strtolower($request->status),
            'feedback' => $request->feedback,
        ]);

        return redirect()->back()->with('success', 'Status logbook berhasil diperbarui!');
    }
}