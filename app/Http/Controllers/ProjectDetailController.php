<?php

namespace App\Http\Controllers;

use App\Models\InternshipProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectDetailController extends Controller
{
    /**
     * Tampilkan halaman detail lengkap proyek inovasi magang
     */
    public function show(Request $request, $id)
    {
        $project = InternshipProject::with([
            'placement.application.user.studentProfile',
            'placement.application.unit.agencyProfile',
            'placement.application.group',
            'placement.mentor',
            'placement.academicAdvisor',
        ])->findOrFail($id);

        $user = Auth::user();
        $placement = $project->placement;
        $application = $placement?->application;
        $student = $application?->user;

        // Tentukan tautan kembali (Back Link) yang kontekstual dan aman
        $backUrl = url()->previous();
        if (! $backUrl || $backUrl === url()->current()) {
            if ($user->role === 'supervisor') {
                $backUrl = route('supervisor.reports.show', $placement->id);
            } elseif (in_array($user->role, ['mentor', 'pembimbing'])) {
                $backUrl = route('mentor.students.show', $placement->id);
            } elseif (in_array($user->role, ['dosen', 'academic_advisor'])) {
                $backUrl = route('lecturer.students.show', $placement->id);
            } elseif ($user->role === 'mahasiswa') {
                $backUrl = route('student.final_report.index');
            } else {
                $backUrl = route('supervisor.reports.index');
            }
        }

        return view('projects.show', compact('project', 'placement', 'application', 'student', 'backUrl'));
    }
}
