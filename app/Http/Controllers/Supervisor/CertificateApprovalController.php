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
        $revisionCount = Certificate::where('approval_status', 'revision')->count();

        return view('supervisor.certificates.index', compact(
            'certificates',
            'statusFilter',
            'waitingCount',
            'approvedCount',
            'revisionCount'
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
     * Sahkan Sertifikat Kelulusan
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
            ->with('success', "Sertifikat kelulusan atas nama {$certificate->placement?->application?->user?->name} berhasil disahkan dan siap diunduh oleh mahasiswa.");
    }

    /**
     * Halaman Khusus Pemilihan Aspek & Pengisian Catatan Revisi Sertifikat
     */
    public function revision($placementId)
    {
        $placement = Placement::with([
            'application.user.studentProfile',
            'application.unit.agencyProfile',
            'evaluation',
            'finalreport',
            'logbooks',
            'certificate',
            'mentor',
            'pembimbing',
            'academicAdvisor',
        ])->where('id', $placementId)->orWhereHas('certificate', function ($q) use ($placementId) {
            $q->where('id', $placementId);
        })->firstOrFail();

        $certificate = $placement->certificate;
        if (! $certificate) {
            $year = date('Y');
            $paddedId = str_pad($placement->id, 3, '0', STR_PAD_LEFT);
            $certificate = Certificate::firstOrCreate(
                ['placement_id' => $placement->id],
                [
                    'certificate_number' => $placement->certificate_number ?: "SERT/{$paddedId}/DISPUSIP-SBY/{$year}",
                    'file_path' => 'certificates/' . $placement->id . '.pdf',
                    'approval_status' => 'waiting_approval',
                    'issued_at' => now()->toDateString(),
                ]
            );
        }

        $application = $placement->application;
        $student = $application?->user;
        $profile = $student?->studentProfile;
        $evaluation = $placement->evaluation;
        $unit = $application?->unit;
        $mentor = $placement->mentor ?? $placement->pembimbing;
        $advisor = $placement->academicAdvisor;

        $logbookTotal = $placement->logbooks()->count();
        $logbookApproved = $placement->logbooks()->where('status', 'approved')->count();
        $finalReport = $placement->finalreport;

        return view('supervisor.certificates.revision', compact(
            'placement',
            'certificate',
            'application',
            'student',
            'profile',
            'evaluation',
            'unit',
            'mentor',
            'advisor',
            'logbookTotal',
            'logbookApproved',
            'finalReport'
        ));
    }

    /**
     * Simpan Pengembalian Revisi Sertifikat dengan Aspek & Catatan Spesifik
     */
    public function submitRevision(Request $request, $placementId)
    {
        $request->validate([
            'aspects' => 'required|array|min:1',
            'aspects.*' => 'string|in:nilai,identitas,laporan,lainnya',
            'notes' => 'required|string|min:5|max:1000',
            'aspect_notes' => 'nullable|array',
        ], [
            'aspects.required' => 'Pilih minimal satu aspek yang perlu direvisi.',
            'aspects.min' => 'Pilih minimal satu aspek yang perlu direvisi.',
            'notes.required' => 'Catatan instruksi revisi wajib diisi agar Admin mengetahui detail perbaikan.',
            'notes.min' => 'Catatan instruksi revisi minimal 5 karakter.',
        ]);

        $placement = Placement::with(['application.user', 'certificate'])
            ->where('id', $placementId)
            ->orWhereHas('certificate', function ($q) use ($placementId) {
                $q->where('id', $placementId);
            })->firstOrFail();

        $certificate = $placement->certificate ?: Certificate::where('placement_id', $placement->id)->firstOrFail();
        $user = Auth::user();

        $aspectLabels = [
            'nilai' => 'Nilai Evaluasi & Transkrip',
            'identitas' => 'Data Mahasiswa & Magang',
            'laporan' => 'Validasi Logbook & Laporan Akhir',
            'lainnya' => 'Aspek Lainnya',
        ];

        $selectedAspectNames = array_map(fn ($a) => $aspectLabels[$a] ?? $a, $request->aspects);
        $aspectSummary = implode(', ', $selectedAspectNames);

        // Susun catatan komprehensif berstandar kedinasan
        $compiledNotes = "Aspek Revisi: {$aspectSummary}\n" . trim($request->notes);

        if (! empty($request->aspect_notes) && is_array($request->aspect_notes)) {
            foreach ($request->aspect_notes as $key => $aspekNote) {
                if (! empty(trim($aspekNote)) && in_array($key, $request->aspects, true)) {
                    $lbl = $aspectLabels[$key] ?? ucfirst($key);
                    $compiledNotes .= "\n• [{$lbl}]: " . trim($aspekNote);
                }
            }
        }

        $certificate->update([
            'approval_status' => 'revision',
            'notes' => $compiledNotes,
            'approved_by' => null,
            'approved_at' => null,
        ]);

        AuditLog::record('SUPERVISOR_CERTIFICATE_REVISED', 'Certificate', $certificate->id, [
            'placement_id' => $placement->id,
            'certificate_number' => $certificate->certificate_number,
            'student_name' => $placement->application?->user?->name,
            'aspects' => $request->aspects,
            'notes' => $compiledNotes,
            'revised_by' => $user->name,
            'revised_by_id' => $user->id,
        ]);

        return redirect()->route('supervisor.certificates.index')
            ->with('success', "Sertifikat magang atas nama {$placement->application?->user?->name} berhasil dikembalikan untuk revisi pada aspek: {$aspectSummary}.");
    }

    /**
     * Kembalikan Sertifikat untuk Ditinjau / Direvisi (Fallback API/Action)
     */
    public function revise(Request $request, $id)
    {
        $request->validate([
            'notes' => 'required|string|min:5',
        ], [
            'notes.required' => 'Catatan revisi wajib diisi agar Admin Dinas mengetahui aspek nilai atau data yang perlu diperbaiki.',
            'notes.min' => 'Catatan revisi minimal 5 karakter.',
        ]);

        $certificate = Certificate::with(['placement.application.user'])->findOrFail($id);
        $user = Auth::user();

        $certificate->update([
            'approval_status' => 'revision',
            'notes' => $request->notes,
            'approved_by' => null,
            'approved_at' => null,
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
