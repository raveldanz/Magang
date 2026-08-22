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
        $query = University::withCount(['users', 'dosens', 'students']);

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

        return view('admin.universities.index', compact('universities'));
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
        ]);

        $univ->update([
            'name' => $request->name,
            'code' => strtoupper(trim($request->code)),
            'email' => $request->email ? strtolower(trim($request->email)) : null,
            'phone' => $request->phone,
            'address' => $request->address,
            'pic_name' => $request->pic_name,
            'pic_nip' => $request->pic_nip,
            'pic_position' => $request->pic_position,
        ]);

        AuditLog::record('UNIVERSITY_UPDATE', 'University', $univ->id, [
            'name' => $univ->name,
            'code' => $univ->code,
        ]);

        return redirect()->route('admin.universities.index')
            ->with('success', "Data Universitas '{$univ->name}' berhasil diperbarui!");
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
