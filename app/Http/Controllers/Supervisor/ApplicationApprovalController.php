<?php

namespace App\Http\Controllers\Supervisor;

use App\Enums\ApplicationStatus;
use App\Http\Controllers\Controller;
use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\AuditLog;
use App\Models\Placement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ApplicationApprovalController extends Controller
{
    /**
     * Kotak Masuk Persetujuan Pengajuan Magang (Double ACC)
     */
    public function index(Request $request)
    {
        $statusFilter = $request->query('status', 'waiting_approval');

        $query = Application::with([
            'user.studentProfile',
            'unit.agencyProfile',
            'placement.mentor',
            'placement.pembimbing',
        ]);

        if ($statusFilter === 'waiting_approval') {
            $query->where('status', ApplicationStatus::WAITING_APPROVAL->value);
        } elseif ($statusFilter === 'approved') {
            $query->whereIn('status', [ApplicationStatus::ACCEPTED->value, ApplicationStatus::ACTIVE->value, ApplicationStatus::COMPLETED->value]);
        } elseif ($statusFilter === 'all') {
            // all
        } else {
            $query->where('status', $statusFilter);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $like = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
            $query->whereHas('user', function ($uq) use ($s, $like) {
                $uq->where('name', $like, "%{$s}%")
                    ->orWhereHas('studentProfile', fn ($sp) => $sp->where('universitas', $like, "%{$s}%")->orWhere('nim', $like, "%{$s}%"));
            });
        }

        if ($request->filled('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        $applications = $query->latest()->paginate(10)->withQueryString();

        $units = \App\Models\Unit::orderBy('name')->get();

        $waitingCount = Application::where('status', ApplicationStatus::WAITING_APPROVAL->value)->count();
        $approvedCount = Application::whereIn('status', [ApplicationStatus::ACCEPTED->value, ApplicationStatus::ACTIVE->value, ApplicationStatus::COMPLETED->value])->count();

        return view('supervisor.applications.index', compact(
            'applications',
            'statusFilter',
            'units',
            'waitingCount',
            'approvedCount'
        ));
    }

    /**
     * Pratinjau Detail Pengajuan Magang untuk Verifikasi Atasan
     */
    public function show($id)
    {
        $application = Application::with([
            'user.studentProfile',
            'unit.agencyProfile',
            'documents',
            'placement.mentor',
            'placement.pembimbing',
            'placement.academicAdvisor',
        ])->findOrFail($id);

        return view('supervisor.applications.show', compact('application'));
    }

    /**
     * Aksi ACC: Setujui & Terbitkan Surat Balasan Resmi
     */
    public function approve(Request $request, $id)
    {
        $application = Application::with(['unit', 'placement', 'user'])->findOrFail($id);
        $user = Auth::user();

        // Status baru: jika sudah melewati start_date, langsung 'active'; jika belum, 'accepted'
        $now = now()->toDateString();
        $newStatus = ($application->start_date && $application->start_date <= $now && (! $application->end_date || $application->end_date >= $now))
            ? ApplicationStatus::ACTIVE->value
            : ApplicationStatus::ACCEPTED->value;

        $year = date('Y');
        $paddedId = str_pad($application->id, 3, '0', STR_PAD_LEFT);
        $autoLetterNumber = $application->letter_number ?: "500.12.1/{$paddedId}/436.7.14/{$year}";
        $letterToken = $application->letter_token ?: Str::random(32);

        DB::transaction(function () use ($application, $newStatus, $autoLetterNumber, $letterToken, $paddedId, $year, $user) {
            $oldStatus = $application->status instanceof ApplicationStatus ? $application->status->value : (string) $application->status;

            $application->update([
                'status' => $newStatus,
                'letter_number' => $autoLetterNumber,
                'letter_date' => $application->letter_date ?: date('Y-m-d'),
                'letter_token' => $letterToken,
                'rejection_note' => null,
                'rejection_reason' => null,
            ]);

            // Pastikan Placement aktif dan memiliki nomor sertifikat & token verifikasi
            $existingPlacement = Placement::where('application_id', $application->id)->first();
            $placementData = [
                'certificate_hash' => $existingPlacement?->certificate_hash ?: Str::random(32),
                'certificate_number' => $existingPlacement?->certificate_number ?: "SERT/{$paddedId}/DISPUSIP-SBY/{$year}",
            ];

            Placement::updateOrCreate(
                ['application_id' => $application->id],
                $placementData
            );

            // Audit Trail
            AuditLog::record('SUPERVISOR_APPLICATION_APPROVED', 'Application', $application->id, [
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'student_name' => $application->user?->name,
                'unit_name' => $application->unit?->name,
                'approved_by' => $user->name,
                'approved_by_id' => $user->id,
            ]);
        });

        return redirect()->route('supervisor.applications.index')
            ->with('success', "Pengajuan magang atas nama {$application->user?->name} berhasil disetujui (ACC) dan Surat Balasan resmi telah diterbitkan!");
    }

    /**
     * Aksi Revisi: Kembalikan Berkas ke Admin Dinas dengan Catatan
     */
    public function revise(Request $request, $id)
    {
        $request->validate([
            'notes' => 'required|string|min:5',
        ], [
            'notes.required' => 'Catatan revisi wajib diisi agar Admin Dinas mengetahui bagian yang perlu diperbaiki.',
            'notes.min' => 'Catatan revisi minimal 5 karakter.',
        ]);

        $application = Application::with(['user'])->findOrFail($id);
        $user = Auth::user();

        $oldStatus = $application->status instanceof ApplicationStatus ? $application->status->value : (string) $application->status;

        $application->update([
            'status' => ApplicationStatus::PENDING->value,
            'rejection_note' => "Catatan Revisi Atasan: " . $request->notes,
            'rejection_reason' => "Catatan Revisi Atasan: " . $request->notes,
        ]);

        AuditLog::record('SUPERVISOR_APPLICATION_REVISED', 'Application', $application->id, [
            'old_status' => $oldStatus,
            'new_status' => ApplicationStatus::PENDING->value,
            'student_name' => $application->user?->name,
            'notes' => $request->notes,
            'revised_by' => $user->name,
            'revised_by_id' => $user->id,
        ]);

        return redirect()->route('supervisor.applications.index')
            ->with('success', "Pengajuan magang atas nama {$application->user?->name} telah dikembalikan ke Admin Dinas untuk direvisi.");
    }
}
