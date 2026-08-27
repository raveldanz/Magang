<?php

namespace App\Http\Controllers\University;

use App\Http\Controllers\Controller;
use App\Models\University;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LecturerController extends Controller
{
    /**
     * Menampilkan daftar dosen pembimbing lapangan kampus (Tenant Scoped per Universitas)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $universityId = $user->university_id;
        $university = $universityId 
            ? University::find($universityId) 
            : University::where('name', $user->university)->orWhere('code', $user->university)->first();
        $univName = $university?->name ?? $user->university;

        $query = User::whereIn('role', ['dosen', 'academic_advisor'])
            ->where(function ($q) use ($universityId, $univName) {
                if ($universityId) {
                    $q->where('university_id', $universityId);
                } elseif ($univName) {
                    $q->where('university', $univName);
                }
            })
            ->with(['academicPlacements.application.user', 'academicPlacements.finalreport', 'academicPlacements.evaluation']);

        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $lecturers = $query->orderBy('name', 'asc')->get();

        // Hitung statistik ringkasan
        $totalLecturers = $lecturers->count();
        $totalActiveStudents = 0;
        $totalCompletedStudents = 0;

        foreach ($lecturers as $lecturer) {
            $activeCount = $lecturer->academicPlacements->filter(function ($p) {
                $isAccepted = optional($p->application)->status === 'accepted';
                $isPassed = optional($p->finalreport)->status === 'approved' && optional($p->evaluation)->nilai_akademik > 0;
                return $isAccepted && !$isPassed;
            })->count();

            $completedCount = $lecturer->academicPlacements->filter(function ($p) {
                $isAccepted = optional($p->application)->status === 'accepted';
                $isPassed = optional($p->finalreport)->status === 'approved' && optional($p->evaluation)->nilai_akademik > 0;
                return $isAccepted && $isPassed;
            })->count();

            $lecturer->active_students_count = $activeCount;
            $lecturer->completed_students_count = $completedCount;
            $lecturer->total_students_count = $activeCount + $completedCount;

            $totalActiveStudents += $activeCount;
            $totalCompletedStudents += $completedCount;
        }

        $stats = [
            'total_lecturers' => $totalLecturers,
            'total_active_students' => $totalActiveStudents,
            'total_completed_students' => $totalCompletedStudents,
            'total_assigned_students' => $totalActiveStudents + $totalCompletedStudents,
        ];

        return view('university.lecturers.index', compact(
            'user',
            'university',
            'univName',
            'lecturers',
            'stats'
        ));
    }

    /**
     * Tambah Dosen Pembimbing Baru oleh Admin Kampus
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $universityId = $user->university_id;
        $university = $universityId 
            ? University::find($universityId) 
            : University::where('name', $user->university)->orWhere('code', $user->university)->first();
        $univName = $university?->name ?? $user->university ?? 'Perguruan Tinggi';

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'nidn' => 'nullable|string|max:50',
        ], [
            'name.required' => 'Nama lengkap beserta gelar dosen wajib diisi.',
            'email.required' => 'Email resmi dosen wajib diisi.',
            'email.unique' => 'Email ini sudah terdaftar di dalam sistem.',
        ]);

        $dosenName = trim($request->name);
        if ($request->filled('nidn') && !str_contains($dosenName, 'NIDN')) {
            $dosenName .= ' (NIDN: ' . trim($request->nidn) . ')';
        }

        $lecturer = User::create([
            'name' => $dosenName,
            'email' => strtolower(trim($request->email)),
            'password' => Hash::make('password'),
            'role' => 'dosen',
            'university_id' => $universityId,
            'university' => $univName,
            'email_verified_at' => now(),
        ]);

        return redirect()->route('university.lecturers.index')
            ->with('success', "Dosen Pembimbing Baru '{$lecturer->name}' berhasil ditambahkan ke daftar dosen kampus!");
    }

    /**
     * Update Data Dosen Pembimbing oleh Admin Kampus
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $universityId = $user->university_id;
        $university = $universityId 
            ? University::find($universityId) 
            : University::where('name', $user->university)->orWhere('code', $user->university)->first();
        $univName = $university?->name ?? $user->university;

        $lecturer = User::whereIn('role', ['dosen', 'academic_advisor'])->findOrFail($id);

        // Tenant Scoping Authorization Check
        $isSameUniv = ($universityId && $lecturer->university_id === $universityId);
        if (!$isSameUniv && $univName) {
            $isSameUniv = ($lecturer->university === $univName);
        }

        if (!$isSameUniv) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengedit data dosen kampus lain.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $lecturer->id,
            'nidn' => 'nullable|string|max:50',
            'status' => 'nullable|string|in:active,on_leave,inactive',
        ], [
            'name.required' => 'Nama lengkap dosen wajib diisi.',
            'email.required' => 'Email resmi dosen wajib diisi.',
            'email.unique' => 'Email ini sudah digunakan oleh akun lain.',
        ]);

        $dosenName = trim($request->name);
        if ($request->filled('nidn') && !str_contains($dosenName, 'NIDN')) {
            $dosenName .= ' (NIDN: ' . trim($request->nidn) . ')';
        }

        $lecturer->update([
            'name' => $dosenName,
            'email' => strtolower(trim($request->email)),
            'status' => $request->status ?? 'active',
        ]);

        return redirect()->route('university.lecturers.index')
            ->with('success', "Data & Status Dosen Pembimbing '{$dosenName}' berhasil diperbarui!");
    }

    /**
     * Reset password dosen ke default ('password')
     */
    public function resetPassword(Request $request, $id)
    {
        $user = Auth::user();
        $universityId = $user->university_id;
        $university = $universityId 
            ? University::find($universityId) 
            : University::where('name', $user->university)->orWhere('code', $user->university)->first();
        $univName = $university?->name ?? $user->university;

        $lecturer = User::whereIn('role', ['dosen', 'academic_advisor'])->findOrFail($id);

        // Tenant Scoping Authorization Check
        $isSameUniv = ($universityId && $lecturer->university_id === $universityId);
        if (!$isSameUniv && $univName) {
            $isSameUniv = ($lecturer->university === $univName);
        }

        if (!$isSameUniv) {
            abort(403, 'Anda tidak memiliki hak akses untuk mereset password dosen kampus lain.');
        }

        $lecturer->update([
            'password' => Hash::make('password'),
        ]);

        return redirect()->route('university.lecturers.index')
            ->with('success', "Password untuk Dosen '{$lecturer->name}' ({$lecturer->email}) berhasil direset ke default ('password')!");
    }

    /**
     * Hapus Dosen Pembimbing oleh Admin Kampus
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $universityId = $user->university_id;
        $university = $universityId 
            ? University::find($universityId) 
            : University::where('name', $user->university)->orWhere('code', $user->university)->first();
        $univName = $university?->name ?? $user->university;

        $lecturer = User::whereIn('role', ['dosen', 'academic_advisor'])->findOrFail($id);

        // Tenant Scoping Authorization Check
        $isSameUniv = ($universityId && $lecturer->university_id === $universityId);
        if (!$isSameUniv && $univName) {
            $isSameUniv = ($lecturer->university === $univName);
        }

        if (!$isSameUniv) {
            abort(403, 'Anda tidak memiliki hak akses untuk menghapus dosen kampus lain.');
        }

        // Periksa apakah dosen masih membimbing mahasiswa aktif
        $activePlacementsCount = $lecturer->academicPlacements()
            ->whereHas('application', function ($q) {
                $q->whereIn('status', ['accepted', 'pending']);
            })
            ->count();

        if ($activePlacementsCount > 0) {
            return redirect()->route('university.lecturers.index')
                ->with('error', "Dosen '{$lecturer->name}' tidak dapat dihapus karena masih membimbing {$activePlacementsCount} mahasiswa aktif. Silakan alihkan bimbingan ke dosen lain terlebih dahulu.");
        }

        $lecturerName = $lecturer->name;
        $lecturer->delete();

        return redirect()->route('university.lecturers.index')
            ->with('success', "Dosen Pembimbing '{$lecturerName}' berhasil dihapus dari daftar kampus.");
    }
}
