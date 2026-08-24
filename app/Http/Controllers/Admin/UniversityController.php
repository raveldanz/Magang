<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\University;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UniversityController extends Controller
{
    /**
     * Master Perguruan Tinggi / Universitas
     */
    public function index(Request $request)
    {
        $query = University::with(['universityAdmin'])
            ->withCount(['users', 'dosens', 'students'])
            ->withExists(['users as has_admin_account' => function ($q) {
                $q->where('role', 'universitas');
            }])
            ->orderBy('has_admin_account', 'asc')
            ->orderBy('updated_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('pic_name', 'like', "%{$search}%");
            });
        }

        $universities = $query->get();

        // Cari kampus baru yang belum punya akun
        $unregisteredCount = $universities->filter(fn($u) => !$u->universityAdmin)->count();

        return view('admin.universities.index', compact('universities', 'unregisteredCount'));
    }

    public function create()
    {
        return view('admin.universities.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:universities,code',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'pic_name' => 'nullable|string|max:255',
            'pic_nip' => 'nullable|string|max:50',
            'pic_position' => 'nullable|string|max:255',
        ]);

        $univ = University::create([
            'name' => $request->name,
            'code' => strtoupper(trim($request->code)),
            'email' => $request->email ? strtolower(trim($request->email)) : null,
            'phone' => $request->phone,
            'address' => $request->address,
            'pic_name' => $request->pic_name,
            'pic_nip' => $request->pic_nip,
            'pic_position' => $request->pic_position,
        ]);

        AuditLog::record('UNIVERSITY_CREATE', 'University', $univ->id, [
            'name' => $univ->name,
            'code' => $univ->code,
        ]);

        return redirect()->route('admin.universities.index')
            ->with('success', "Universitas '{$univ->name}' ({$univ->code}) berhasil didaftarkan!");
    }

    public function edit($id)
    {
        $university = University::findOrFail($id);
        return view('admin.universities.edit', compact('university'));
    }

    public function update(Request $request, $id)
    {
        $univ = University::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:universities,code,' . $univ->id,
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'pic_name' => 'nullable|string|max:255',
            'pic_nip' => 'nullable|string|max:50',
            'pic_position' => 'nullable|string|max:255',
            'evaluation_scheme' => 'nullable|in:dual_evaluation,mentor_only',
            'weight_mentor' => 'nullable|integer|min:0|max:100',
            'weight_lecturer' => 'nullable|integer|min:0|max:100',
            'require_dpl' => 'nullable',
        ]);

        $evaluationScheme = $request->input('evaluation_scheme', $univ->evaluation_scheme ?? 'dual_evaluation');
        if ($evaluationScheme === 'mentor_only') {
            $weightMentor = 100;
            $weightLecturer = 0;
            $requireDpl = false;
        } else {
            $weightMentor = (int) $request->input('weight_mentor', $univ->weight_mentor ?? 40);
            $weightLecturer = (int) $request->input('weight_lecturer', $univ->weight_lecturer ?? 60);

            if (($weightMentor + $weightLecturer) !== 100) {
                return back()->withInput()->with('error', 'Total bobot penilaian Mentor Dinas (' . $weightMentor . '%) dan DPL Kampus (' . $weightLecturer . '%) harus berjumlah tepat 100%.');
            }

            $requireDpl = $request->boolean('require_dpl', true);
        }

        $univ->update([
            'name' => $request->name,
            'code' => strtoupper(trim($request->code)),
            'email' => $request->email ? strtolower(trim($request->email)) : null,
            'phone' => $request->phone,
            'address' => $request->address,
            'pic_name' => $request->pic_name,
            'pic_nip' => $request->pic_nip,
            'pic_position' => $request->pic_position,
            'evaluation_scheme' => $evaluationScheme,
            'weight_mentor' => $weightMentor,
            'weight_lecturer' => $weightLecturer,
            'require_dpl' => $requireDpl,
        ]);

        AuditLog::record('UNIVERSITY_UPDATE', 'University', $univ->id, [
            'name' => $univ->name,
            'code' => $univ->code,
        ]);

        return redirect()->route('admin.universities.index')
            ->with('success', "Data Universitas '{$univ->name}' berhasil diperbarui!");
    }

    /**
     * Buat Akun PIC / Admin Kampus untuk Universitas tertentu
     */
    public function createAccount(Request $request, $id)
    {
        $univ = University::findOrFail($id);

        $existingAccount = User::where('role', 'universitas')
            ->where('university_id', $univ->id)
            ->first();

        if ($existingAccount) {
            return redirect()->back()->with('error', "Universitas '{$univ->name}' sudah memiliki akun Admin Kampus aktif ({$existingAccount->email}).");
        }

        $cleanCode = strtolower(preg_replace('/[^A-Za-z0-9]/', '', $univ->code ?: 'univ'));
        $defaultEmail = $univ->email ?: ($cleanCode . '@' . $cleanCode . '.ac.id');

        $email = $defaultEmail;
        $counter = 1;
        while (User::where('email', $email)->exists()) {
            $email = $cleanCode . $counter . '@magang.surabaya.go.id';
            $counter++;
        }

        $password = 'password';

        $user = User::create([
            'name' => 'Admin Portal ' . $univ->name,
            'email' => $email,
            'password' => \Illuminate\Support\Facades\Hash::make($password),
            'role' => 'universitas',
            'university_id' => $univ->id,
            'university' => $univ->name,
            'email_verified_at' => now(),
        ]);

        AuditLog::record('UNIVERSITY_ACCOUNT_CREATE', 'User', $user->id, [
            'university_name' => $univ->name,
            'email' => $email,
        ]);

        session()->flash('new_university_credential', [
            'univ_name' => $univ->name,
            'name' => $user->name,
            'email' => $email,
            'password' => $password,
            'login_url' => url('/login'),
        ]);

        return redirect()->route('admin.universities.index')
            ->with('success', "Akun Admin Portal untuk '{$univ->name}' berhasil dibuat! Email: {$email} | Password: {$password}");
    }

    public function destroy($id)
    {
        $univ = University::withCount(['users', 'students'])->findOrFail($id);

        if ($univ->users_count > 0 || $univ->students_count > 0) {
            return redirect()->back()->with('error', "Gagal menghapus: Masih ada {$univ->users_count} user dan {$univ->students_count} mahasiswa terdaftar di universitas ini.");
        }

        $name = $univ->name;
        $univ->delete();

        AuditLog::record('UNIVERSITY_DELETE', 'University', $id, ['name' => $name]);

        return redirect()->route('admin.universities.index')
            ->with('success', "Universitas '{$name}' berhasil dihapus.");
    }
}
