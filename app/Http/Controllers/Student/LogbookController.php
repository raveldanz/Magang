<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Logbook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogbookController extends Controller
{
    public function index()
    {
        // Ambil pengajuan mahasiswa yang statusnya ACCEPTED
        $application = Application::where('user_id', Auth::id())
            ->where('status', 'accepted')
            ->with('placement.logbooks')
            ->first();

        if (!$application || !$application->placement) {
            return redirect()->route('dashboard')
                ->with('error', 'Anda belum dapat mengakses Logbook karena belum diterima magang.');
        }

        $logbooks = $application->placement->logbooks()->latest()->get();

        return view('student.logbook.index', compact('application', 'logbooks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'activity' => 'required|string',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $application = Application::where('user_id', Auth::id())
            ->where('status', 'accepted')
            ->firstOrFail();

        $filePath = null;
        if ($request->hasFile('attachment')) {
            $filePath = $request->file('attachment')->store('documents/logbooks', 'public');
        }

        Logbook::create([
            'placement_id' => $application->placement->id,
            'date' => $request->date,
            'activity' => $request->activity,
            'attachment' => $filePath,
            'status' => 'pending',
        ]);

        return redirect()->back()->with('success', 'Logbook kegiatan berhasil diisi!');
    }
}