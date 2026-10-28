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
            'comments' => fn ($q) => $q->orderBy('created_at', 'desc')->with('user'),
        ])->findOrFail($id);

        $user = Auth::user();
        $placement = $project->placement;
        $application = $placement?->application;
        $student = $application?->user;

        // Cek Otorisasi Akses Jika Proyek Berstatus Privat
        $isPrivileged = $user && ($user->isSuperAdmin() || in_array($user->role, ['admin', 'supervisor']));
        $isOwner = $user && $student && $user->id === $student->id;
        $isAssignedMentorOrAdvisor = $user && $placement && ($user->id === $placement->mentor_id || $user->id === $placement->academic_advisor_id);

        if (! $project->is_public && ! ($isPrivileged || $isOwner || $isAssignedMentorOrAdvisor)) {
            abort(403, 'Proyek inovasi ini berstatus privat atau memuat data sensitif kedinasan.');
        }

        // Tentukan status Suka (Like) oleh pengguna aktif atau tamu
        $guestId = $user ? null : $request->session()->get('guest_project_token');
        $isLikedByMe = $project->isLikedBy($user, $guestId);

        // Hak moderasi komentar (Hapus komentar buruk / spam)
        $canModerate = $user && ($user->isSuperAdmin() || in_array($user->role, ['admin', 'supervisor']));

        // Tentukan tautan kembali (Back Link) yang kontekstual dan aman
        $backUrl = url()->previous();
        if (! $backUrl || $backUrl === url()->current()) {
            if ($user?->role === 'supervisor') {
                $backUrl = route('supervisor.reports.show', $placement->id);
            } elseif (in_array($user?->role, ['mentor', 'pembimbing'])) {
                $backUrl = route('mentor.students.show', $placement->id);
            } elseif (in_array($user?->role, ['dosen', 'academic_advisor'])) {
                $backUrl = route('lecturer.students.show', $placement->id);
            } elseif ($user?->role === 'mahasiswa') {
                $backUrl = route('student.final_report.index');
            } elseif ($user) {
                $backUrl = route('supervisor.reports.index');
            } else {
                $backUrl = route('public.projects.index');
            }
        }

        return view('projects.show', compact('project', 'placement', 'application', 'student', 'backUrl', 'isLikedByMe', 'canModerate', 'guestId', 'user'));
    }
}
