<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Certificate;
use App\Models\Placement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CertificateApprovalController extends Controller
{
    /**
     * Kotak Masuk Pengesahan Sertifikat Magang (Double ACC)
     */
    public function index(Request $request)
    {
        $statusFilter = $request->query('status', 'waiting_approval');

        $query = Certificate::with([
            'placement.application.user.studentProfile',
            'placement.application.unit',
            'placement.evaluation',
            'approver',
        ]);

        if ($statusFilter === 'waiting_approval') {
            $query->where('approval_status', 'waiting_approval');
        } elseif ($statusFilter === 'approved') {
            $query->where('approval_status', 'approved');
        } elseif ($statusFilter === 'revision') {
            $query->where('approval_status', 'revision');
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $like = \DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
            $query->whereHas('placement.application.user', function ($uq) use ($s, $like) {
                $uq->where('name', $like, "%{$s}%")
                    ->orWhereHas('studentProfile', fn ($sp) => $sp->where('universitas', $like, "%{$s}%")->orWhere('nim', $like, "%{$s}%"));
            });
        }

        $certificates = $query->latest()->paginate(10)->withQueryString();

        $waitingCount = Certificate::where('approval_status', 'waiting_approval')->count();
        $approvedCount = Certificate::where('approval_status', 'approved')->count();

        return view('supervisor.certificates.index', compact(
            'certificates',
            'statusFilter',
            'waitingCount',
            'approvedCount'
        ));
    }

    /**
     * Pratinjau Sertifikat & Transkrip Nilai Sebelum Pengesahan
     */
    public function show($placementId)
    {
        $data = \App\Http\Controllers\Student\CertificateController::getCertificateData($placementId, Auth::user());
        $certificate = Certificate::where('placement_id', $placementId)->first();
        $data['certificateRecord'] = $certificate;

        return view('certificates.internship_certificate', $data);
    }

    /**
     * Sahkan Sertifikat Kelulusan (ACC)
     */
    public function approve(Request $request, $id)
    {
        $certificate = Certificate::with(['placement.application.user'])->findOrFail($id);
        $user = Auth::user();

        $certificate->update([
            'approval_status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
            'notes' => null,
        ]);

        AuditLog::record('SUPERVISOR_CERTIFICATE_APPROVED', 'Certificate', $certificate->id, [
            'placement_id' => $certificate->placement_id,
            'certificate_number' => $certificate->certificate_number,
            'student_name' => $certificate->placement?->application?->user?->name,
            'approved_by' => $user->name,
            'approved_by_id' => $user->id,
        ]);

        return redirect()->route('supervisor.certificates.index')
            ->with('success', "Sertifikat kelulusan atas nama {$certificate->placement?->application?->user?->name} berhasil disahkan (ACC) dan siap diunduh oleh mahasiswa!");
    }

    /**
     * Kembalikan Sertifikat untuk Ditinjau / Direvisi
     */
    public function revise(Request $request, $id)
    {
        $request->validate([
            'notes' => 'required|string|min:5',
        ], [
            'notes.required' => 'Catatan revisi wajib diisi agar Admin Dinas mengetahui aspek nilai/data yang perlu diperbaiki.',
            'notes.min' => 'Catatan revisi minimal 5 karakter.',
        ]);

        $certificate = Certificate::with(['placement.application.user'])->findOrFail($id);
        $user = Auth::user();

        $certificate->update([
            'approval_status' => 'revision',
            'notes' => $request->notes,
        ]);

        AuditLog::record('SUPERVISOR_CERTIFICATE_REVISED', 'Certificate', $certificate->id, [
            'placement_id' => $certificate->placement_id,
            'certificate_number' => $certificate->certificate_number,
            'student_name' => $certificate->placement?->application?->user?->name,
            'notes' => $request->notes,
            'revised_by' => $user->name,
            'revised_by_id' => $user->id,
        ]);

        return redirect()->route('supervisor.certificates.index')
            ->with('success', "Sertifikat magang atas nama {$certificate->placement?->application?->user?->name} telah dikembalikan untuk perbaikan.");
    }
}
