<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Logbook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogbookController extends Controller
{
    /**
     * Daftar semua logbook dari seluruh mahasiswa (Multi-Tenant Scoped)
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Logbook::with(['placement.application.user.studentProfile', 'placement.application.unit.agencyProfile']);

        // Multi-Tenant Isolation
        if ($user && $user->agency_profile_id !== null) {
            $query->whereHas('placement.application.unit', function ($q) use ($user) {
                $q->where('agency_profile_id', $user->agency_profile_id);
            });
        }

        // Filter berdasarkan status (mengantisipasi huruf besar/kecil)
        if ($request->filled('status')) {
            $status = strtolower($request->status);
            $query->whereIn('status', [$status, strtoupper($status)]);
        }

        $logbooks = $query->orderBy('date', 'desc')->get();

        return view('admin.logbooks.index', compact('logbooks'));
    }

    /**
     * Detail logbook + form review (approve/reject)
     */
    public function show($id)
    {
        $user = Auth::user();

        $logbook = Logbook::with([
            'placement.application.user.studentProfile', 
            'placement.application.unit.agencyProfile', 
            'placement.pembimbing'
        ])->findOrFail($id);

        // Multi-Tenant Authorization Check
        if ($user && $user->agency_profile_id !== null && optional($logbook->placement?->application?->unit)->agency_profile_id !== $user->agency_profile_id) {
            abort(403, 'Anda tidak memiliki hak akses ke logbook instansi lain.');
        }

        return view('admin.logbooks.show', compact('logbook'));
    }

    /**
     * Review logbook: approve atau reject dengan feedback
     */
    public function review(Request $request, $id)
    {
        $user = Auth::user();

        $request->validate([
            'status'   => 'required|in:approved,rejected,APPROVED,REJECTED',
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

        return redirect()->route('admin.logbooks.index')
            ->with('success', 'Logbook berhasil di-review!');
    }
}