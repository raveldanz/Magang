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
        $selectedAgency = null;

        // Multi-Tenant Isolation: Admin Instansi hanya melihat unit di bawah dinasnya
        if ($agencyId) {
            $query->where('agency_profile_id', $agencyId);
            $agencies = AgencyProfile::where('id', $agencyId)->get();
            $selectedAgency = $agencies->first();
        } else {
            // Superadmin dapat memfilter berdasarkan instansi
            if ($request->filled('agency_id')) {
                $query->where('agency_profile_id', $request->agency_id);
                $selectedAgency = AgencyProfile::find($request->agency_id);
            }
            $agencies = AgencyProfile::orderBy('agency_name')->get();
        }

        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $query->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
        }

        $units = $query->orderBy('name', 'asc')->get();

        // Hitung statistik kuota
        $totalUnits = $units->count();
        $totalQuota = $units->sum('quota');
        // Unit::occupied_count membandingkan status sebagai string (Collection::where('status', ...) pada enum selalu 0)
        $totalFilled = $units->sum(fn ($u) => $u->occupied_count);
        $totalRemaining = max(0, $totalQuota - $totalFilled);

        $stats = [
            'total_units' => $totalUnits,
            'total_quota' => $totalQuota,
            'total_filled' => $totalFilled,
            'total_remaining' => $totalRemaining,
        ];

        return view('admin.units.index', compact('units', 'stats', 'agencies', 'selectedAgency'));
    }

    /**
     * Tampilkan formulir tambah divisi / lowongan magang baru
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        $agencyId = $user ? ($user->agency_profile_id ?? $user->agency_id ?? optional($user->agencyProfile)->id) : null;

        if ($agencyId) {
            $agencies = AgencyProfile::where('id', $agencyId)->get();
            $defaultAgencyId = $agencyId;
        } else {
            $agencies = AgencyProfile::orderBy('agency_name')->get();
            $defaultAgencyId = $request->query('agency_id');
        }

        $returnTo = $request->query('return_to', url()->previous());

        return view('admin.units.create', compact('agencies', 'defaultAgencyId', 'returnTo'));
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
            'return_to' => 'nullable|string',
        ], [
            'name.required' => 'Nama bidang / divisi wajib diisi.',
            'quota.required' => 'Jumlah kuota wajib diisi.',
            'quota.min' => 'Kuota minimal adalah 0.',
        ]);

        $agencyId = $userAgencyId ?? ($request->agency_profile_id ?? AgencyProfile::first()?->id);

        $unit = Unit::create([
            'name' => $request->name,
            'description' => $request->description,
            'quota' => $request->quota,
            'agency_profile_id' => $agencyId,
        ]);

        $successMsg = "Divisi '{$unit->name}' berhasil ditambahkan!";

        // 1. Prioritas return_to dari pemanggil
        if ($request->filled('return_to') && !str_contains($request->return_to, 'units/create')) {
            return redirect($request->return_to)->with('success', $successMsg);
        }

        // 2. Balik ke detail instansi jika ada agencyId
        if ($agencyId) {
            return redirect()->route('admin.agencies.show', $agencyId)->with('success', $successMsg);
        }

        return redirect()->route('admin.units.index')->with('success', $successMsg);
    }

    /**
     * Tampilkan formulir edit divisi / unit magang
     */
    public function show($id)
    {
        return redirect()->route('admin.units.edit', $id);
    }

    public function edit(Request $request, $id)
    {
        $user = Auth::user();
        $agencyId = $user ? ($user->agency_profile_id ?? $user->agency_id ?? optional($user->agencyProfile)->id) : null;
        $unit = Unit::with('agencyProfile')->findOrFail($id);

        if ($agencyId && $unit->agency_profile_id !== $agencyId) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah unit instansi lain.');
        }

        $agencies = $agencyId ? AgencyProfile::where('id', $agencyId)->get() : AgencyProfile::all();
        $returnTo = $request->query('return_to', url()->previous());

        return view('admin.units.edit', compact('unit', 'agencies', 'returnTo'));
    }

    /**
     * Update data divisi / unit magang
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $agencyId = $user ? ($user->agency_profile_id ?? $user->agency_id ?? optional($user->agencyProfile)->id) : null;
        $unit = Unit::findOrFail($id);

        if ($agencyId && $unit->agency_profile_id !== $agencyId) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah unit instansi lain.');
        }

        $occupied = $unit->occupied_count;
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'quota' => 'required|integer|min:' . $occupied . '|max:500',
            'agency_profile_id' => 'nullable|exists:agency_profiles,id',
            'return_to' => 'nullable|string',
        ], [
            'quota.min' => "Kuota tidak boleh kurang dari jumlah mahasiswa yang sedang menempati divisi ini ({$occupied} orang).",
        ]);

        $updateData = [
            'name' => $request->name,
            'description' => $request->description,
            'quota' => $request->quota,
        ];

        if (!$agencyId && $request->filled('agency_profile_id')) {
            $updateData['agency_profile_id'] = $request->agency_profile_id;
        }

        $unit->update($updateData);
        $successMsg = 'Data divisi / unit magang berhasil diperbarui!';

        // 1. Prioritas return_to dari pemanggil
        if ($request->filled('return_to') && !str_contains($request->return_to, 'units/' . $id . '/edit')) {
            return redirect($request->return_to)->with('success', $successMsg);
        }

        // 2. Balik ke detail instansi terkait
        if ($unit->agency_profile_id) {
            return redirect()->route('admin.agencies.show', $unit->agency_profile_id)->with('success', $successMsg);
        }

        return redirect()->route('admin.units.index')->with('success', $successMsg);
    }

    /**
     * Quick action penyesuaian kuota (+1 / -1 / inline editable custom)
     */
    public function updateQuota(Request $request, $id)
    {
        $user = Auth::user();
        $agencyId = $user ? ($user->agency_profile_id ?? $user->agency_id ?? optional($user->agencyProfile)->id) : null;
        $unit = Unit::findOrFail($id);

        if ($agencyId && $unit->agency_profile_id !== $agencyId) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Anda tidak memiliki hak akses ke unit instansi lain.'], 403);
            }
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah kuota unit instansi lain.');
        }

        $action = $request->input('action');
        $occupied = $unit->occupied_count;
        $belowOccupied = "Kuota tidak boleh kurang dari jumlah mahasiswa yang sedang menempati divisi ini ({$occupied} orang).";

        if ($action === 'decrement' && $unit->quota - 1 < $occupied) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $belowOccupied], 422);
            }
            return redirect()->back()->with('error', $belowOccupied);
        }

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
            if ($newQuota < $occupied) {
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => $belowOccupied], 422);
                }
                return redirect()->back()->with('error', $belowOccupied);
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
    public function destroy(Request $request, $id)
    {
        $user = Auth::user();
        $agencyId = $user ? ($user->agency_profile_id ?? $user->agency_id ?? optional($user->agencyProfile)->id) : null;
        // applications.unit_id ON DELETE CASCADE: menghapus divisi ikut menghapus seluruh pengajuan,
        // penempatan, logbook, nilai, dan sertifikat di dalamnya. Divisi yang punya riwayat apa pun
        // (termasuk alumni) wajib dipertahankan sebagai arsip.
        $unit = Unit::withCount('applications')->findOrFail($id);

        if ($agencyId && $unit->agency_profile_id !== $agencyId) {
            abort(403, 'Anda tidak memiliki hak akses untuk menghapus unit instansi lain.');
        }

        if ($unit->applications_count > 0) {
            return redirect()->back()->with('error', "Divisi '{$unit->name}' tidak dapat dihapus karena menyimpan {$unit->applications_count} riwayat pengajuan magang (termasuk arsip alumni & sertifikat). Untuk menutup pendaftaran, atur kuota divisi menjadi 0.");
        }

        $unit->delete();
        $successMsg = "Divisi / unit magang '{$unit->name}' berhasil dihapus.";

        if ($request->filled('return_to')) {
            return redirect($request->return_to)->with('success', $successMsg);
        }

        return redirect()->back()->with('success', $successMsg);
    }
}