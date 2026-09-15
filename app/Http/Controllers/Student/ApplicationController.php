<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\Placement;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ApplicationController extends Controller
{
    // Menampilkan halaman form pengajuan magang
    public function create()
    {
        $units = Unit::with('agencyProfile')->get();
        $groupedUnits = $units->groupBy(function ($unit) {
            return $unit->agencyProfile->agency_name ?? 'Pemerintah Kota Surabaya';
        });

        $user = Auth::user();

        // 1. Cek apakah mahasiswa sudah punya profil
        if (!$user->studentProfile) {
            return redirect()->route('student.profile.edit')
                ->with('error', 'Silakan lengkapi profil Anda terlebih dahulu sebelum mengajukan magang.');
        }

        // 2. Cek apakah ada pengajuan yang sedang berjalan atau aktif (pending, verified, accepted, completed)
        $activeApplication = Application::with(['unit.agencyProfile', 'placement'])
            ->where('user_id', $user->id)
            ->whereIn('status', ['pending', 'verified', 'accepted', 'completed'])
            ->latest()
            ->first();

        // 3. Ambil seluruh riwayat pengajuan mahasiswa
        $applicationHistory = Application::with('unit.agencyProfile')
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        return view('student.application.create', compact('units', 'groupedUnits', 'activeApplication', 'applicationHistory'));
    }

    // Menyimpan data pengajuan magang & upload dokumen
    public function store(Request $request)
    {
        $user = Auth::user();

        // 1. Cek kelengkapan profil mahasiswa
        if (!$user->studentProfile) {
            return redirect()->route('student.profile.edit')
                ->with('error', 'Silakan lengkapi profil Anda terlebih dahulu sebelum mengajukan magang.');
        }

        // 2. Cegah pengajuan ganda jika sudah ada pengajuan aktif / diterima / selesai
        $existingActive = Application::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'verified', 'accepted', 'completed'])
            ->first();

        if ($existingActive) {
            $msg = match ($existingActive->status) {
                'pending', 'verified' => 'Anda masih memiliki berkas pengajuan magang yang sedang diproses. Mohon tunggu proses verifikasi admin dinas.',
                'accepted' => 'Akses ditolak: Anda sudah memiliki penempatan magang aktif yang sedang berjalan.',
                'completed' => 'Anda telah menyelesaikan program magang MBKM pada instansi sebelumnya.',
                default => 'Anda sudah memiliki pengajuan magang aktif.'
            };
            return redirect()->route('student.application.create')->with('error', $msg);
        }

        $request->validate([
            'unit_id'         => 'required|exists:units,id',
            'start_date'      => 'required|date|after_or_equal:today',
            'end_date'        => 'required|date|after_or_equal:start_date',
            'surat_pengantar' => 'required|file|mimes:pdf|max:2048', 
            'cv'              => 'required|file|mimes:pdf|max:2048',
            'transkrip'       => 'required|file|mimes:pdf|max:2048',
            'id_card'         => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ], [
            'start_date.after_or_equal' => 'Tanggal mulai magang tidak boleh sebelum hari ini.',
            'end_date.after_or_equal'   => 'Tanggal selesai magang harus setelah atau sama dengan tanggal mulai.',
            'surat_pengantar.required'  => 'Surat Pengantar / Proposal magang wajib diunggah.',
            'surat_pengantar.mimes'     => 'File Surat Pengantar harus berformat PDF.',
            'surat_pengantar.max'       => 'Ukuran file Surat Pengantar maksimal 2MB (2048 KB).',
            'cv.required'               => 'Berkas CV (Curriculum Vitae) wajib diunggah.',
            'cv.mimes'                  => 'File CV harus berformat PDF.',
            'cv.max'                    => 'Ukuran file CV maksimal 2MB (2048 KB).',
            'transkrip.required'        => 'Transkrip Nilai akademik wajib diunggah.',
            'transkrip.mimes'           => 'File Transkrip Nilai harus berformat PDF.',
            'transkrip.max'             => 'Ukuran file Transkrip Nilai maksimal 2MB (2048 KB).',
            'id_card.required'          => 'KTM / Kartu Identitas wajib diunggah.',
            'id_card.mimes'             => 'File KTM / Kartu Identitas harus berformat PDF, JPG, JPEG, atau PNG.',
            'id_card.max'               => 'Ukuran file KTM / Kartu Identitas maksimal 2MB (2048 KB).',
        ]);

        // Cek Sisa Kuota Instansi yang Dipilih
        $unit = Unit::findOrFail($request->unit_id);
        if ($unit->remaining_quota <= 0) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['unit_id' => 'Kuota untuk instansi/unit ini sudah penuh. Silakan pilih unit kerja lain.']);
        }

        // Upload Berkas
        $proposalPath = $request->file('surat_pengantar') ? $request->file('surat_pengantar')->store('documents/applications', 'public') : null;
        $cvPath = $request->file('cv') ? $request->file('cv')->store('documents/applications', 'public') : null;
        $transcriptPath = $request->file('transkrip') ? $request->file('transkrip')->store('documents/applications', 'public') : null;
        $idCardPath = $request->file('id_card') ? $request->file('id_card')->store('documents/applications', 'public') : null;

        // Simpan Data Pengajuan
        $application = Application::create([
            'user_id'              => Auth::id(),
            'unit_id'              => $request->unit_id,
            'start_date'           => $request->start_date,
            'end_date'             => $request->end_date,
            'proposal_letter_path' => $proposalPath,
            'cv_path'              => $cvPath,
            'transcript_path'      => $transcriptPath,
            'id_card_path'         => $idCardPath,
            'status'               => 'pending',
            'letter_token'         => Str::random(32),
        ]);

        // Simpan Dokumen Persyaratan ke tabel application_documents
        $documents = [
            'Surat Pengantar' => $proposalPath,
            'CV'             => $cvPath,
            'Transkrip Nilai' => $transcriptPath,
        ];
        if ($idCardPath) {
            $documents['KTM / Kartu Identitas'] = $idCardPath;
        }

        foreach ($documents as $type => $path) {
            if ($path) {
                ApplicationDocument::create([
                    'application_id' => $application->id,
                    'document_type'  => $type,
                    'file_path'      => $path,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Pengajuan magang dan dokumen persyaratan berhasil dikirim!');
    }

    // Download / Print Surat Penerimaan Magang untuk Mahasiswa
    public function downloadLetter($id)
    {
        $application = Application::with(['user.studentProfile', 'unit.agencyProfile', 'placement.pembimbing'])
            ->where('user_id', Auth::id())
            ->whereIn('status', ['accepted', 'completed'])
            ->findOrFail($id);

        return view('letters.acceptance', compact('application'));
    }
}