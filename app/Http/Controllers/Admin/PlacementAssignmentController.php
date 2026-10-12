<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Services\PlacementAssignmentService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Penugasan / re-assign Mentor Dinas & DPL pada penempatan yang sudah berjalan (ACCEPTED / ACTIVE)
 * tanpa membatalkan atau mengulang verifikasi pengajuan.
 */
class PlacementAssignmentController extends Controller
{
    public function __construct(private PlacementAssignmentService $assignments) {}

    public function update(Request $request, $applicationId)
    {
        $user = $request->user();

        $request->validate([
            'mentor_id' => 'nullable|integer|exists:users,id',
            'academic_advisor_id' => 'nullable|integer|exists:users,id',
            'return_to' => 'nullable|string',
        ]);

        $application = Application::with('unit')->findOrFail($applicationId);

        // Multi-tenant: Admin Dinas hanya boleh menugaskan pembimbing untuk mahasiswa di instansinya
        if (! $this->currentUserIsSuperAdmin() && (int) optional($application->unit)->agency_profile_id !== (int) $user->agency_profile_id) {
            abort(403, 'Anda tidak memiliki hak akses untuk menugaskan pembimbing pada penempatan instansi lain.');
        }

        try {
            $changes = $this->assignments->assign(
                $application,
                $user,
                $request->filled('mentor_id') ? (int) $request->mentor_id : null,
                $request->filled('academic_advisor_id') ? (int) $request->academic_advisor_id : null,
            );
        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->with('error', collect($e->errors())->flatten()->first());
        }

        $message = empty($changes)
            ? 'Tidak ada perubahan penugasan pembimbing.'
            : 'Penugasan pembimbing berhasil diperbarui: '.collect($changes)->map(function ($c, $key) {
                $label = $key === 'mentor' ? 'Mentor Dinas' : 'DPL';

                return "{$label} → {$c['to']}";
            })->implode(', ').'. Histori logbook sebelumnya tetap tersimpan.';

        $target = $this->safeReturnTo($request->input('return_to')) ?? route('admin.applications.show', $application->id);

        return redirect()->to($target)->with('success', $message);
    }
}
