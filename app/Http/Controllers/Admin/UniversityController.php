<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\University;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

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
            $search = strtolower($request->search);
            $like = \DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
            $query->where(function ($q) use ($search, $like) {
                $q->where('name', $like, "%{$search}%")
                  ->orWhere('code', $like, "%{$search}%")
                  ->orWhere('email', $like, "%{$search}%")
                  ->orWhere('pic_name', $like, "%{$search}%");
            });
        }

        $universities = $query->paginate(12)->withQueryString();

        // Count un-provisioned university accounts
        $unregisteredCount = University::doesntHave('universityAdmin')->count();

        $user = Auth::user();
        $isSuperAdmin = $user && ($user->role === 'super_admin' || ($user->role === 'admin' && is_null($user->agency_profile_id)));

        return view('admin.universities.index', compact('universities', 'unregisteredCount', 'isSuperAdmin'));
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
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
        ]);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $cleanCode = strtolower(preg_replace('/[^A-Za-z0-9]/', '', $request->code ?? 'univ'));
            $filename = $cleanCode . '_' . time() . '.' . $file->getClientOriginalExtension();
            $targetDir = public_path('images/logos');
            if (!File::exists($targetDir)) {
                File::makeDirectory($targetDir, 0755, true);
            }
            $file->move($targetDir, $filename);
            $logoPath = 'images/logos/' . $filename;
        }

        $univ = University::create([
            'name' => $request->name,
            'code' => strtoupper(trim($request->code)),
            'email' => $request->email ? strtolower(trim($request->email)) : null,
            'phone' => $request->phone,
            'address' => $request->address,
            'pic_name' => $request->pic_name,
            'pic_nip' => $request->pic_nip,
            'pic_position' => $request->pic_position,
            'logo' => $logoPath,
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
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
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

        $data = [
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
        ];

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $cleanCode = strtolower(preg_replace('/[^A-Za-z0-9]/', '', $request->code ?? 'univ'));
            $filename = $cleanCode . '_' . time() . '.' . $file->getClientOriginalExtension();
            $targetDir = public_path('images/logos');
            if (!File::exists($targetDir)) {
                File::makeDirectory($targetDir, 0755, true);
            }
            $file->move($targetDir, $filename);
            $data['logo'] = 'images/logos/' . $filename;
        }

        $univ->update($data);

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
            'user_id' => $user->id,
            'email' => $email,
            'password' => $password,
            'login_url' => url('/login'),
        ]);

        return redirect()->route('admin.universities.index')
            ->with('success', "Akun Admin Portal untuk '{$univ->name}' berhasil dibuat! Email: {$email} | Password: {$password}");
    }

    public function destroy($id)
    {
        $univ = University::withCount(['students', 'dosens'])->findOrFail($id);

        // 1. Proteksi Mahasiswa: Jika ada mahasiswa terdaftar, jangan hapus
        if ($univ->students_count > 0) {
            return redirect()->back()->with('error', "Gagal menghapus: Masih ada {$univ->students_count} mahasiswa terdaftar di {$univ->name}. Pindahkan atau hapus data mahasiswa terlebih dahulu.");
        }

        // 2. Proteksi Dosen dengan Bimbingan Aktif
        $activeDosenCount = $univ->dosens()
            ->whereHas('academicPlacements', function ($q) {
                $q->whereHas('application', function ($aq) {
                    $aq->whereIn('status', ['accepted', 'verified']);
                });
            })->count();

        if ($activeDosenCount > 0) {
            return redirect()->back()->with('error', "Gagal menghapus: Terdapat {$activeDosenCount} dosen pembimbing dari {$univ->name} yang sedang membimbing mahasiswa aktif.");
        }

        $name = $univ->name;

        \DB::transaction(function () use ($univ) {
            // Hapus akun admin kampus yang terafiliasi dengan universitas ini
            User::where('university_id', $univ->id)
                ->where('role', 'universitas')
                ->delete();

            // Lepaskan relasi dosen non-aktif jika ada
            User::where('university_id', $univ->id)
                ->whereIn('role', ['dosen', 'academic_advisor'])
                ->update(['university_id' => null, 'university' => null]);

            // Hapus data universitas
            $univ->delete();
        });

        AuditLog::record('UNIVERSITY_DELETE', 'University', $id, ['name' => $name]);

        return redirect()->route('admin.universities.index')
            ->with('success', "Universitas '{$name}' dan akun admin kampusnya berhasil dihapus.");
    }
}
