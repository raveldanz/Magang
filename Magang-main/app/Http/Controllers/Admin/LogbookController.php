<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Logbook;
use Illuminate\Http\Request;

class LogbookController extends Controller
{
    /**
     * Daftar semua logbook dari seluruh mahasiswa
     */
    public function index(Request $request)
    {
        $query = Logbook::with(['placement.application.user.studentProfile', 'placement.application.unit']);

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
        $logbook = Logbook::with([
            'placement.application.user.studentProfile', 
            'placement.application.unit', 
            'placement.pembimbing'
        ])->findOrFail($id);

        return view('admin.logbooks.show', compact('logbook'));
    }

    /**
     * Review logbook: approve atau reject dengan feedback
     */
    public function review(Request $request, $id)
    {
        $request->validate([
            'status'   => 'required|in:approved,rejected,APPROVED,REJECTED',
            'feedback' => 'nullable|string',
        ]);

        $logbook = Logbook::findOrFail($id);
        
        $logbook->update([
            'status'   => strtolower($request->status),
            'feedback' => $request->feedback,
        ]);

        return redirect()->route('admin.logbooks.index')
            ->with('success', 'Logbook berhasil di-review!');
    }
}