<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgencyProfile;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UnitController extends Controller
{
    /**
     * Menampilkan daftar seluruh divisi/unit magang beserta sisa kuota (Multi-Tenant Scoped)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $agencyId = $user ? ($user->agency_profile_id ?? $user->agency_id ?? optional($user->agencyProfile)->id) : null;

        $query = Unit::with(['agencyProfile', 'applications']);

        // Multi-Tenant Isolation: Admin Instansi hanya melihat unit di bawah dinasnya
        if ($agencyId) {
            $query->where(function ($q) use ($agencyId) {
                $q->where('agency_profile_id', $agencyId);
            });
            $agencies = AgencyProfile::where('id', $agencyId)->get();
        } else {
            // Superadmin dapat memfilter berdasarkan instansi
            if ($request->filled('agency_id')) {
                $query->where('agency_profile_id', $request->agency_id);
            }
            $agencies = AgencyProfile::all();
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        $units = $query->orderBy('name', 'asc')->get();

        // Hitung statistik kuota
        $totalUnits = $units->count();
        $totalQuota = $units->sum('quota');
        $totalFilled = $units->sum(function ($u) {
            return $u->applications->where('status', 'accepted')->count();
        });
        $totalRemaining = max(0, $totalQuota - $totalFilled);

        $stats = [
            'total_units' => $totalUnits,
            'total_quota' => $totalQuota,
            'total_filled' => $totalFilled,
            'total_remaining' => $totalRemaining,
        ];

        return view('admin.units.index', compact('units', 'stats', 'agencies'));
    }

    /**
     * Tampilkan formulir tambah divisi / lowongan magang baru
     */
    public function create()
    {
        $user = Auth::user();
        $agencyId = $user ? ($user->agency_profile_id ?? $user->agency_id ?? optional($user->agencyProfile)->id) : null;

        if ($agencyId) {
            $agencies = AgencyProfile::where('id', $agencyId)->get();
            $defaultAgencyId = $agencyId;
        } else {
            $agencies = AgencyProfile::all();
            $defaultAgencyId = null;
        }

        return view('admin.units.create', compact('agencies', 'defaultAgencyId'));
    }

    /**
     * Simpan divisi/unit magang baru ke database
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $userAgencyId = $user ? ($user->agency_profile_id ?? $user->agency_id ?? optional($user->agencyProfile)->id) : null;

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'quota' => 'required|integer|min:0|max:500',
            'agency_profile_id' => 'nullable|exists:agency_profiles,id',
        ], [
            'name.required' => 'Nama bidang / divisi wajib diisi.',
            'quota.required' => 'Jumlah kuota wajib diisi.',
            'quota.min' => 'Kuota minimal adalah 0.',
        ]);

        $agencyId = $userAgencyId ?? ($request->agency_profile_id ?? AgencyProfile::first()?->id);

        Unit::create([
            'name' => $request->name,
            'description' => $request->description,
            'quota' => $request->quota,
            'agency_profile_id' => $agencyId,
        ]);

        return redirect()->route('admin.units.index')->with('success', 'Divisi / unit magang baru berhasil ditambahkan!');
    }

    /**
     * Tampilkan formulir edit divisi / unit magang
     */
    public function edit($id)
    {
        $user = Auth::user();
        $agencyId = $user ? ($user->agency_profile_id ?? $user->agency_id ?? optional($user->agencyProfile)->id) : null;
        $unit = Unit::with('agencyProfile')->findOrFail($id);

        // Multi-Tenant Authorization Check
        if ($agencyId && $unit->agency_profile_id !== $agencyId) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah unit instansi lain.');
        }

        if ($agencyId) {
            $agencies = AgencyProfile::where('id', $agencyId)->get();
        } else {
            $agencies = AgencyProfile::all();
        }

        return view('admin.units.edit', compact('unit', 'agencies'));
    }

    /**
     * Update data divisi / unit magang
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $agencyId = $user ? ($user->agency_profile_id ?? $user->agency_id ?? optional($user->agencyProfile)->id) : null;
        $unit = Unit::findOrFail($id);

        // Multi-Tenant Authorization Check
        if ($agencyId && $unit->agency_profile_id !== $agencyId) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah unit instansi lain.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'quota' => 'required|integer|min:0|max:500',
            'agency_profile_id' => 'nullable|exists:agency_profiles,id',
        ]);

        $updateData = [
            'name' => $request->name,
            'description' => $request->description,
            'quota' => $request->quota,
        ];

        // Superadmin can reassign agency
        if (!$agencyId && $request->filled('agency_profile_id')) {
            $updateData['agency_profile_id'] = $request->agency_profile_id;
        }

        $unit->update($updateData);

        return redirect()->route('admin.units.index')->with('success', 'Data divisi / unit magang berhasil diperbarui!');
    }

    /**
<<<<<<< HEAD
     * Quick action penyesuaian kuota (+1 / -1 / adjust)
=======
     * Quick action penyesuaian kuota (+1 / -1 / inline editable custom)
>>>>>>> main
     */
    public function updateQuota(Request $request, $id)
    {
        $user = Auth::user();
        $agencyId = $user ? ($user->agency_profile_id ?? $user->agency_id ?? optional($user->agencyProfile)->id) : null;
        $unit = Unit::findOrFail($id);

        // Multi-Tenant Authorization Check
        if ($agencyId && $unit->agency_profile_id !== $agencyId) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Anda tidak memiliki hak akses ke unit instansi lain.'], 403);
            }
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah kuota unit instansi lain.');
        }

        $action = $request->input('action');

        if ($action === 'increment') {
            $unit->increment('quota', 1);
            $msg = "Kuota divisi '{$unit->name}' berhasil ditambah (+1)! Total kuota sekarang: {$unit->quota}";
        } elseif ($action === 'decrement') {
            if ($unit->quota > 0) {
                $unit->decrement('quota', 1);
                $msg = "Kuota divisi '{$unit->name}' berhasil dikurangi (-1)! Total kuota sekarang: {$unit->quota}";
            } else {
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Kuota sudah 0, tidak dapat dikurangi lagi.'], 422);
                }
                return redirect()->back()->with('error', 'Kuota sudah 0, tidak dapat dikurangi lagi.');
            }
        } elseif ($request->filled('quota') || $request->filled('custom_quota')) {
            $newQuota = (int) ($request->input('quota') ?? $request->input('custom_quota'));
            if ($newQuota < 0 || $newQuota > 500) {
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Jumlah kuota harus antara 0 dan 500.'], 422);
                }
                return redirect()->back()->with('error', 'Jumlah kuota harus antara 0 dan 500.');
            }
            $unit->update(['quota' => $newQuota]);
            $msg = "Kuota divisi '{$unit->name}' berhasil diubah menjadi {$unit->quota}";
        } else {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Aksi penyesuaian kuota tidak valid.'], 422);
            }
            return redirect()->back()->with('error', 'Aksi penyesuaian kuota tidak valid.');
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'unit_id' => $unit->id,
                'quota' => $unit->quota,
            ]);
        }

        return redirect()->back()->with('success', $msg);
    }

    /**
     * Hapus divisi / unit magang jika belum ada mahasiswa aktif
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $agencyId = $user ? ($user->agency_profile_id ?? $user->agency_id ?? optional($user->agencyProfile)->id) : null;
        $unit = Unit::withCount(['applications' => function ($q) {
            $q->where('status', 'accepted');
        }])->findOrFail($id);

        // Multi-Tenant Authorization Check
        if ($agencyId && $unit->agency_profile_id !== $agencyId) {
            abort(403, 'Anda tidak memiliki hak akses untuk menghapus unit instansi lain.');
        }

        if ($unit->applications_count > 0) {
            return redirect()->back()->with('error', "Divisi '{$unit->name}' tidak dapat dihapus karena masih memiliki {$unit->applications_count} mahasiswa yang aktif magang.");
        }

        $unit->delete();

        return redirect()->route('admin.units.index')->with('success', "Divisi / unit magang '{$unit->name}' berhasil dihapus.");
    }
}
