<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AgencyProfile;
use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\Unit;
use App\Models\User;
use App\Services\PrivateDocumentStorage;
use App\Support\UploadRules;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ApplicationController extends Controller
{
    // Menampilkan halaman form pengajuan magang
    public function create()
    {
        $units = Unit::with('agencyProfile')
            ->withCount(['applications as occupied_count_db' => function ($query) {
                $query->occupyingQuota();
            }])->get();
        $groupedUnits = $units->groupBy(function ($unit) {
            return $unit->agencyProfile->agency_name ?? 'Pemerintah Kota Surabaya';
        });

        $user = Auth::user();

        // 1. Cek apakah mahasiswa sudah punya profil
        if (! $user->studentProfile) {
            return redirect()->route('student.profile.edit')
                ->with('error', 'Silakan lengkapi profil Anda terlebih dahulu sebelum mengajukan magang.');
        }

        // 2. Cek apakah ada pengajuan yang sedang berjalan atau aktif (pending, verified, accepted, active, completed)
        $activeApplication = Application::with(['unit.agencyProfile', 'placement'])
            ->where('user_id', $user->id)
            ->whereIn('status', ['pending', 'verified', 'accepted', 'active', 'completed'])
            ->latest()
            ->first();

        // 3. Ambil seluruh riwayat pengajuan mahasiswa
        $applicationHistory = Application::with('unit.agencyProfile')
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $agencies = AgencyProfile::orderBy('agency_name')->get();

        return view('student.application.create', compact('units', 'groupedUnits', 'agencies', 'activeApplication', 'applicationHistory'));
    }

    // Menyimpan data pengajuan magang & upload dokumen
    public function store(Request $request)
    {
        $user = Auth::user();

        // 1. Cek kelengkapan profil mahasiswa
        if (! $user->studentProfile) {
            return redirect()->route('student.profile.edit')
                ->with('error', 'Silakan lengkapi profil Anda terlebih dahulu sebelum mengajukan magang.');
        }

        // 2. Cegah pengajuan ganda jika sudah ada pengajuan aktif / diterima / selesai
        $existingActive = Application::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'verified', 'accepted', 'active', 'completed'])
            ->first();

        if ($existingActive) {
            $existingStatusVal = $existingActive->status instanceof \BackedEnum ? $existingActive->status->value : (string) $existingActive->status;
            $msg = match ($existingStatusVal) {
                'pending', 'verified' => 'Anda masih memiliki berkas pengajuan magang yang sedang diproses. Mohon tunggu proses verifikasi admin dinas.',
                'accepted', 'active' => 'Akses ditolak: Anda sudah memiliki penempatan magang aktif yang sedang berjalan.',
                'completed' => 'Anda telah menyelesaikan program magang MBKM pada instansi sebelumnya.',
                default => 'Anda sudah memiliki pengajuan magang aktif.'
            };

            return redirect()->route('student.application.create')->with('error', $msg);
        }

        $request->validate([
            'unit_id' => 'required|exists:units,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'surat_pengantar' => 'required|'.UploadRules::APPLICATION_DOCUMENT,
            'cv' => 'required|'.UploadRules::APPLICATION_DOCUMENT,
            'transkrip' => 'required|'.UploadRules::APPLICATION_DOCUMENT,
            'id_card' => 'required|'.UploadRules::APPLICATION_ID_CARD,
        ], [
            'start_date.required' => 'Tanggal mulai magang wajib diisi.',
            'start_date.date' => 'Tanggal mulai magang tidak valid.',
            'end_date.required' => 'Tanggal selesai magang wajib diisi.',
            'end_date.after_or_equal' => 'Tanggal selesai magang harus setelah atau sama dengan tanggal mulai.',
            'surat_pengantar.required' => 'Surat Pengantar / Proposal magang wajib diunggah.',
            'surat_pengantar.mimes' => 'File Surat Pengantar harus berformat PDF.',
            'surat_pengantar.max' => 'Ukuran file Surat Pengantar maksimal 2MB (2048 KB).',
            'cv.required' => 'Berkas CV (Curriculum Vitae) wajib diunggah.',
            'cv.mimes' => 'File CV harus berformat PDF.',
            'cv.max' => 'Ukuran file CV maksimal 2MB (2048 KB).',
            'transkrip.required' => 'Transkrip Nilai akademik wajib diunggah.',
            'transkrip.mimes' => 'File Transkrip Nilai harus berformat PDF.',
            'transkrip.max' => 'Ukuran file Transkrip Nilai maksimal 2MB (2048 KB).',
            'id_card.required' => 'KTM / Kartu Identitas wajib diunggah.',
            'id_card.mimes' => 'File KTM / Kartu Identitas harus berformat PDF, JPG, JPEG, atau PNG.',
            'id_card.max' => 'Ukuran file KTM / Kartu Identitas maksimal 2MB (2048 KB).',
        ]);

        // Cek Sisa Kuota Instansi yang Dipilih
        $unit = Unit::findOrFail($request->unit_id);
        if ($unit->remaining_quota <= 0) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['unit_id' => 'Kuota untuk instansi/unit ini sudah penuh. Silakan pilih unit kerja lain.']);
        }

        // Upload Berkas ke disk privat 'local' (storage/app/private/documents/applications), TIDAK bisa
        // diakses publik; dibuka/diunduh lewat route documents.application(.download) yang terotorisasi.
        $storage = app(PrivateDocumentStorage::class);
        $proposalPath = $storage->storeApplicationDocument($request->file('surat_pengantar'));
        $cvPath = $storage->storeApplicationDocument($request->file('cv'));
        $transcriptPath = $storage->storeApplicationDocument($request->file('transkrip'));
        $idCardPath = $storage->storeApplicationDocument($request->file('id_card'));

        // Simpan Data Pengajuan dalam transaksi + kunci baris user, supaya klik ganda /
        // dua tab yang submit bersamaan tidak menghasilkan 2 pengajuan aktif sekaligus.
        $application = DB::transaction(function () use ($request, $user, $proposalPath, $cvPath, $transcriptPath, $idCardPath) {
            User::whereKey($user->id)->lockForUpdate()->first();

            $alreadyActive = Application::where('user_id', $user->id)
                ->whereIn('status', ['pending', 'verified', 'accepted', 'active', 'completed'])
                ->exists();
            if ($alreadyActive) {
                return null;
            }

            return $this->createApplicationRecords($request, $proposalPath, $cvPath, $transcriptPath, $idCardPath);
        });

        if (! $application) {
            // Pengajuan ganda dibatalkan: jangan tinggalkan berkas yatim di disk privat
            $storage->discard([$proposalPath, $cvPath, $transcriptPath, $idCardPath]);

            return redirect()->route('student.application.create')
                ->with('error', 'Anda masih memiliki berkas pengajuan magang yang sedang diproses. Mohon tunggu proses verifikasi admin dinas.');
        }

        return redirect()->back()->with('success', 'Pengajuan magang dan dokumen persyaratan berhasil dikirim!');
    }

    /**
     * Buat record pengajuan + dokumen persyaratan (dipanggil di dalam transaksi).
     */
    private function createApplicationRecords(Request $request, ?string $proposalPath, ?string $cvPath, ?string $transcriptPath, ?string $idCardPath): Application
    {
        $application = Application::create([
            'user_id' => Auth::id(),
            'unit_id' => $request->unit_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'proposal_letter_path' => $proposalPath,
            'cv_path' => $cvPath,
            'transcript_path' => $transcriptPath,
            'id_card_path' => $idCardPath,
            'status' => 'pending',
            'letter_token' => Str::random(32),
        ]);

        // Simpan Dokumen Persyaratan ke tabel application_documents
        $documents = [
            'Surat Pengantar' => $proposalPath,
            'CV' => $cvPath,
            'Transkrip Nilai' => $transcriptPath,
        ];
        if ($idCardPath) {
            $documents['KTM / Kartu Identitas'] = $idCardPath;
        }

        foreach ($documents as $type => $path) {
            if ($path) {
                ApplicationDocument::create([
                    'application_id' => $application->id,
                    'document_type' => $type,
                    'file_path' => $path,
                ]);
            }
        }

        return $application;
    }

    // Download / Print Surat Penerimaan Magang untuk Mahasiswa
    public function downloadLetter($id)
    {
        $application = Application::with(['user.studentProfile', 'unit.agencyProfile', 'placement.pembimbing'])
            ->where('user_id', Auth::id())
            ->whereIn('status', ['accepted', 'active', 'completed'])
            ->findOrFail($id);

        return view('letters.acceptance', compact('application'));
    }
}
