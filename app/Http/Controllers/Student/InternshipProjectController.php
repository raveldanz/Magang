<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\AuditLog;
use App\Models\InternshipProject;
use App\Models\InternshipProjectMember;
use App\Models\Placement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InternshipProjectController extends Controller
{
    /**
     * Tampilkan formulir pembuatan proyek inovasi baru di halaman mandiri
     */
    public function create()
    {
        $user = Auth::user();
        $placement = $this->getActivePlacement($user->id);

        if (! $placement) {
            return redirect()->route('student.final_report.index')->with('error', 'Data penempatan magang aktif tidak ditemukan.');
        }

        if ($placement->finalreport && $placement->finalreport->status === 'approved') {
            return redirect()->route('student.final_report.index')->with('error', 'Laporan akhir telah disetujui, proyek tidak dapat ditambah.');
        }

        $peers = $this->getEligiblePeers($placement);

        return view('student.projects.create', [
            'placement' => $placement,
            'user' => $user,
            'peers' => $peers,
            'categories' => InternshipProject::CATEGORIES,
            'roleRecommendations' => InternshipProject::ROLE_RECOMMENDATIONS,
            'categoryKeywords' => InternshipProject::CATEGORY_KEYWORDS,
        ]);
    }

    /**
     * Tampilkan formulir ubah proyek inovasi di halaman mandiri
     */
    public function edit($id)
    {
        $user = Auth::user();
        $placement = $this->getActivePlacement($user->id);

        if (! $placement) {
            return redirect()->route('student.final_report.index')->with('error', 'Akses ditolak.');
        }

        $project = InternshipProject::with(['members.placement.application.user.studentProfile'])
            ->where('id', $id)
            ->where(function ($q) use ($placement) {
                $q->where('placement_id', $placement->id)
                    ->orWhereHas('members', fn ($m) => $m->where('placement_id', $placement->id)->where('is_leader', true));
            })
            ->firstOrFail();

        if ($placement->finalreport && $placement->finalreport->status === 'approved') {
            return redirect()->route('student.final_report.index')->with('error', 'Laporan akhir telah disetujui, proyek tidak dapat diubah.');
        }

        $peers = $this->getEligiblePeers($placement);

        return view('student.projects.edit', [
            'placement' => $placement,
            'project' => $project,
            'user' => $user,
            'peers' => $peers,
            'categories' => InternshipProject::CATEGORIES,
            'roleRecommendations' => InternshipProject::ROLE_RECOMMENDATIONS,
            'categoryKeywords' => InternshipProject::CATEGORY_KEYWORDS,
        ]);
    }

    /**
     * Simpan proyek inovasi magang baru
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $placement = $this->getActivePlacement($user->id);

        if (! $placement) {
            return redirect()->route('student.final_report.index')->with('error', 'Data penempatan magang aktif tidak ditemukan.');
        }

        if ($placement->finalreport && $placement->finalreport->status === 'approved') {
            return redirect()->route('student.final_report.index')->with('error', 'Laporan akhir telah disetujui, proyek tidak dapat ditambah.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'custom_category' => 'nullable|string|max:150',
            'project_type' => 'nullable|in:individual,group',
            'creator_role' => 'nullable|string|max:150',
            'members' => 'nullable|array',
            'members.*.placement_id' => 'required_with:members|exists:placements,id',
            'members.*.role' => 'nullable|string|max:150',
            'is_public' => 'nullable|boolean',
            'description' => 'required|string',
            'community_benefit' => 'required|string',
            'photo' => 'required|image|mimes:jpeg,png,jpg,webp|max:4096',
            'demo_url' => 'nullable|url|max:500',
            'repository_url' => 'nullable|url|max:500',
            'video_url' => 'nullable|url|max:500',
            'source_code' => 'nullable|file|mimes:zip,rar,7z,tar,gz|max:51200',
            'screenshots.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'community_photos.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'documents' => 'nullable|array|max:10',
            'documents.*' => 'nullable|file|mimes:pdf,doc,docx,zip|max:10240',
        ], [
            'title.required' => 'Judul proyek inovasi wajib diisi.',
            'description.required' => 'Deskripsi proyek wajib diisi.',
            'community_benefit.required' => 'Manfaat untuk masyarakat wajib diisi.',
            'photo.required' => 'Foto sampul utama proyek wajib diunggah.',
            'photo.image' => 'Berkas sampul harus berupa gambar.',
            'photo.mimes' => 'Format foto sampul harus JPG, PNG, atau WebP.',
            'photo.max' => 'Ukuran foto maksimal 4 MB.',
            'demo_url.url' => 'Format tautan hasil proyek harus berupa URL valid.',
            'repository_url.url' => 'Format tautan repositori kode harus berupa URL valid.',
            'video_url.url' => 'Format tautan video dokumentasi harus berupa URL valid.',
            'source_code.mimes' => 'Berkas sumber proyek harus berekstensi ZIP, RAR, 7Z, TAR, atau GZ.',
            'source_code.max' => 'Ukuran berkas sumber proyek maksimal 50 MB.',
            'documents.max' => 'Jumlah berkas dokumen pendukung maksimal 10 berkas.',
            'documents.*.max' => 'Ukuran berkas dokumen maksimal 10 MB.',
        ]);

        $category = ! empty($validated['category']) ? $validated['category'] : 'teknologi';
        $projectType = $request->input('project_type', 'individual');

        if ($category === 'lainnya' && ! $request->filled('custom_category')) {
            return back()->withInput()->withErrors(['custom_category' => 'Nama bidang spesifik wajib diisi jika memilih Inovasi Lainnya.']);
        }

        // 1. Simpan Foto Sampul Utama
        $photoFile = $request->file('photo');
        $ext = strtolower($photoFile->getClientOriginalExtension());
        $filename = 'project_'.$placement->id.'_'.time().'_'.Str::random(6).'.'.$ext;
        $photoPath = $photoFile->storeAs('internship_projects', $filename, 'public');

        // 2. Simpan Berkas Arsip / Source Code Proyek (ZIP)
        $sourceCodePath = null;
        $sourceCodeName = null;
        $sourceCodeSize = null;
        if ($request->hasFile('source_code')) {
            $scFile = $request->file('source_code');
            if ($scFile->isValid()) {
                $scExt = strtolower($scFile->getClientOriginalExtension());
                $scName = 'source_'.$placement->id.'_'.time().'_'.Str::random(6).'.'.$scExt;
                $sourceCodePath = $scFile->storeAs('internship_projects/source_codes', $scName, 'public');
                $sourceCodeName = $scFile->getClientOriginalName();
                $sourceCodeSize = number_format($scFile->getSize() / 1048576, 2).' MB';
            }
        }

        // 3. Simpan Galeri Multi-Screenshot
        $screenshots = [];
        if ($request->hasFile('screenshots')) {
            foreach ($request->file('screenshots') as $ssFile) {
                if ($ssFile->isValid()) {
                    $ssExt = strtolower($ssFile->getClientOriginalExtension());
                    $ssName = 'ss_'.$placement->id.'_'.time().'_'.Str::random(6).'.'.$ssExt;
                    $screenshots[] = $ssFile->storeAs('internship_projects/screenshots', $ssName, 'public');
                }
            }
        }

        // 4. Simpan Foto Dokumentasi Lapangan / Masyarakat
        $communityPhotos = [];
        if ($request->hasFile('community_photos')) {
            foreach ($request->file('community_photos') as $cpFile) {
                if ($cpFile->isValid()) {
                    $cpExt = strtolower($cpFile->getClientOriginalExtension());
                    $cpName = 'cp_'.$placement->id.'_'.time().'_'.Str::random(6).'.'.$cpExt;
                    $communityPhotos[] = $cpFile->storeAs('internship_projects/community', $cpName, 'public');
                }
            }
        }

        // 5. Simpan Dokumen Pendukung (PDF, Manual Book, dll)
        $documents = [];
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $docFile) {
                if ($docFile->isValid()) {
                    $docExt = strtolower($docFile->getClientOriginalExtension());
                    $docName = 'doc_'.$placement->id.'_'.time().'_'.Str::random(6).'.'.$docExt;
                    $docPath = $docFile->storeAs('internship_projects/documents', $docName, 'public');
                    $documents[] = [
                        'name' => $docFile->getClientOriginalName(),
                        'path' => $docPath,
                        'size' => number_format($docFile->getSize() / 1024, 1).' KB',
                        'ext' => $docExt,
                    ];
                }
            }
        }

        $project = InternshipProject::create([
            'placement_id' => $placement->id,
            'title' => $validated['title'],
            'category' => $category,
            'custom_category' => $category === 'lainnya' ? trim((string) $request->input('custom_category')) : null,
            'is_public' => $request->boolean('is_public', true),
            'description' => $validated['description'],
            'community_benefit' => $validated['community_benefit'],
            'photo_path' => $photoPath,
            'demo_url' => $validated['demo_url'] ?? null,
            'repository_url' => $validated['repository_url'] ?? null,
            'source_code_path' => $sourceCodePath,
            'source_code_name' => $sourceCodeName,
            'source_code_size' => $sourceCodeSize,
            'video_url' => $validated['video_url'] ?? null,
            'screenshots' => !empty($screenshots) ? $screenshots : null,
            'community_photos' => !empty($communityPhotos) ? $communityPhotos : null,
            'documents' => !empty($documents) ? $documents : null,
        ]);

        // Simpan Anggota Tim Proyek
        $creatorRole = trim((string) $request->input('creator_role'));
        if (empty($creatorRole)) {
            $creatorRole = $projectType === 'group' ? 'Koordinator Proyek' : 'Pengembang Utama';
        }

        InternshipProjectMember::create([
            'internship_project_id' => $project->id,
            'placement_id' => $placement->id,
            'role_in_project' => $creatorRole,
            'is_leader' => true,
        ]);

        if ($projectType === 'group' && $request->has('members') && is_array($request->input('members'))) {
            foreach ($request->input('members') as $memData) {
                $mPlacementId = (int) ($memData['placement_id'] ?? 0);
                if ($mPlacementId && $mPlacementId !== $placement->id) {
                    $mRole = trim((string) ($memData['role'] ?? 'Anggota Tim'));
                    InternshipProjectMember::create([
                        'internship_project_id' => $project->id,
                        'placement_id' => $mPlacementId,
                        'role_in_project' => ! empty($mRole) ? $mRole : 'Anggota Tim',
                        'is_leader' => false,
                    ]);
                }
            }
        }

        AuditLog::record('STUDENT_PROJECT_CREATE', 'InternshipProject', $project->id, [
            'student_name' => $user->name,
            'title' => $project->title,
        ]);

        return redirect()->route('student.final_report.index')->with('success', 'Proyek inovasi magang berhasil ditambahkan.');
    }

    /**
     * Perbarui proyek inovasi magang
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $placement = $this->getActivePlacement($user->id);

        if (! $placement) {
            return redirect()->route('student.final_report.index')->with('error', 'Akses ditolak.');
        }

        $project = InternshipProject::where('id', $id)
            ->where(function ($q) use ($placement) {
                $q->where('placement_id', $placement->id)
                    ->orWhereHas('members', fn ($m) => $m->where('placement_id', $placement->id)->where('is_leader', true));
            })
            ->firstOrFail();

        if ($placement->finalreport && $placement->finalreport->status === 'approved') {
            return redirect()->route('student.final_report.index')->with('error', 'Laporan akhir telah disetujui, proyek tidak dapat diubah.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'custom_category' => 'nullable|string|max:150',
            'project_type' => 'nullable|in:individual,group',
            'creator_role' => 'nullable|string|max:150',
            'members' => 'nullable|array',
            'members.*.placement_id' => 'required_with:members|exists:placements,id',
            'members.*.role' => 'nullable|string|max:150',
            'is_public' => 'nullable|boolean',
            'description' => 'required|string',
            'community_benefit' => 'required|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'demo_url' => 'nullable|url|max:500',
            'repository_url' => 'nullable|url|max:500',
            'video_url' => 'nullable|url|max:500',
            'source_code' => 'nullable|file|mimes:zip,rar,7z,tar,gz|max:51200',
            'delete_source_code' => 'nullable|boolean',
            'screenshots.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'community_photos.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'documents' => 'nullable|array|max:10',
            'documents.*' => 'nullable|file|mimes:pdf,doc,docx,zip|max:10240',
        ], [
            'title.required' => 'Judul proyek inovasi wajib diisi.',
            'description.required' => 'Deskripsi proyek wajib diisi.',
            'community_benefit.required' => 'Manfaat untuk masyarakat wajib diisi.',
            'photo.image' => 'Berkas sampul harus berupa gambar.',
            'photo.mimes' => 'Format foto sampul harus JPG, PNG, atau WebP.',
            'photo.max' => 'Ukuran foto maksimal 4 MB.',
            'demo_url.url' => 'Format tautan eksternal harus berupa URL valid.',
            'repository_url.url' => 'Format tautan repositori kode harus berupa URL valid.',
            'video_url.url' => 'Format tautan video dokumentasi harus berupa URL valid.',
            'source_code.mimes' => 'Berkas sumber proyek harus berekstensi ZIP, RAR, 7Z, TAR, atau GZ.',
            'source_code.max' => 'Ukuran berkas sumber proyek maksimal 50 MB.',
            'documents.max' => 'Jumlah berkas dokumen pendukung maksimal 10 berkas.',
            'documents.*.max' => 'Ukuran berkas dokumen maksimal 10 MB.',
        ]);

        $category = ! empty($validated['category']) ? $validated['category'] : ($project->category ?? 'teknologi');
        $projectType = $request->input('project_type', ($project->members()->where('is_leader', false)->exists() ? 'group' : 'individual'));

        if ($category === 'lainnya' && ! $request->filled('custom_category')) {
            return back()->withInput()->withErrors(['custom_category' => 'Nama bidang spesifik wajib diisi jika memilih Inovasi Lainnya.']);
        }

        // 1. Perbarui Foto Sampul Utama jika diunggah baru
        $photoPath = $project->photo_path;
        if ($request->hasFile('photo')) {
            if ($project->photo_path && Storage::disk('public')->exists($project->photo_path)) {
                Storage::disk('public')->delete($project->photo_path);
            }

            $photoFile = $request->file('photo');
            $ext = strtolower($photoFile->getClientOriginalExtension());
            $filename = 'project_'.$placement->id.'_'.time().'_'.Str::random(6).'.'.$ext;
            $photoPath = $photoFile->storeAs('internship_projects', $filename, 'public');
        }

        // 2. Berkas Arsip / Source Code Proyek (ZIP)
        $sourceCodePath = $project->source_code_path;
        $sourceCodeName = $project->source_code_name;
        $sourceCodeSize = $project->source_code_size;

        if ($request->boolean('delete_source_code')) {
            if ($project->source_code_path && Storage::disk('public')->exists($project->source_code_path)) {
                Storage::disk('public')->delete($project->source_code_path);
            }
            $sourceCodePath = null;
            $sourceCodeName = null;
            $sourceCodeSize = null;
        }

        if ($request->hasFile('source_code')) {
            if ($project->source_code_path && Storage::disk('public')->exists($project->source_code_path)) {
                Storage::disk('public')->delete($project->source_code_path);
            }
            $scFile = $request->file('source_code');
            if ($scFile->isValid()) {
                $scExt = strtolower($scFile->getClientOriginalExtension());
                $scName = 'source_'.$placement->id.'_'.time().'_'.Str::random(6).'.'.$scExt;
                $sourceCodePath = $scFile->storeAs('internship_projects/source_codes', $scName, 'public');
                $sourceCodeName = $scFile->getClientOriginalName();
                $sourceCodeSize = number_format($scFile->getSize() / 1048576, 2).' MB';
            }
        }

        // 3. Tambahkan Screenshots Baru
        $screenshots = is_array($project->screenshots) ? $project->screenshots : [];
        if ($request->hasFile('screenshots')) {
            foreach ($request->file('screenshots') as $ssFile) {
                if ($ssFile->isValid()) {
                    $ssExt = strtolower($ssFile->getClientOriginalExtension());
                    $ssName = 'ss_'.$placement->id.'_'.time().'_'.Str::random(6).'.'.$ssExt;
                    $screenshots[] = $ssFile->storeAs('internship_projects/screenshots', $ssName, 'public');
                }
            }
        }

        // 4. Tambahkan Foto Komunitas / Lapangan Baru
        $communityPhotos = is_array($project->community_photos) ? $project->community_photos : [];
        if ($request->hasFile('community_photos')) {
            foreach ($request->file('community_photos') as $cpFile) {
                if ($cpFile->isValid()) {
                    $cpExt = strtolower($cpFile->getClientOriginalExtension());
                    $cpName = 'cp_'.$placement->id.'_'.time().'_'.Str::random(6).'.'.$cpExt;
                    $communityPhotos[] = $cpFile->storeAs('internship_projects/community', $cpName, 'public');
                }
            }
        }

        // 5. Tambahkan Dokumen Baru
        $documents = is_array($project->documents) ? $project->documents : [];
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $docFile) {
                if ($docFile->isValid()) {
                    $docExt = strtolower($docFile->getClientOriginalExtension());
                    $docName = 'doc_'.$placement->id.'_'.time().'_'.Str::random(6).'.'.$docExt;
                    $docPath = $docFile->storeAs('internship_projects/documents', $docName, 'public');
                    $documents[] = [
                        'name' => $docFile->getClientOriginalName(),
                        'path' => $docPath,
                        'size' => number_format($docFile->getSize() / 1024, 1).' KB',
                        'ext' => $docExt,
                    ];
                }
            }
            $documents = array_slice($documents, 0, 10);
        }

        $project->update([
            'title' => $validated['title'],
            'category' => $category,
            'custom_category' => $category === 'lainnya' ? trim((string) $request->input('custom_category')) : null,
            'is_public' => $request->has('is_public') ? $request->boolean('is_public') : $project->is_public,
            'description' => $validated['description'],
            'community_benefit' => $validated['community_benefit'],
            'photo_path' => $photoPath,
            'demo_url' => $validated['demo_url'] ?? null,
            'repository_url' => $validated['repository_url'] ?? null,
            'source_code_path' => $sourceCodePath,
            'source_code_name' => $sourceCodeName,
            'source_code_size' => $sourceCodeSize,
            'video_url' => $validated['video_url'] ?? null,
            'screenshots' => !empty($screenshots) ? array_values($screenshots) : null,
            'community_photos' => !empty($communityPhotos) ? array_values($communityPhotos) : null,
            'documents' => !empty($documents) ? array_values($documents) : null,
        ]);

        // Sinkronisasi Anggota Tim Proyek
        $project->members()->delete();

        $creatorRole = trim((string) $request->input('creator_role'));
        if (empty($creatorRole)) {
            $creatorRole = $projectType === 'group' ? 'Koordinator Proyek' : 'Pengembang Utama';
        }

        InternshipProjectMember::create([
            'internship_project_id' => $project->id,
            'placement_id' => $project->placement_id,
            'role_in_project' => $creatorRole,
            'is_leader' => true,
        ]);

        if ($projectType === 'group' && $request->has('members') && is_array($request->input('members'))) {
            foreach ($request->input('members') as $memData) {
                $mPlacementId = (int) ($memData['placement_id'] ?? 0);
                if ($mPlacementId && $mPlacementId !== $project->placement_id) {
                    $mRole = trim((string) ($memData['role'] ?? 'Anggota Tim'));
                    InternshipProjectMember::create([
                        'internship_project_id' => $project->id,
                        'placement_id' => $mPlacementId,
                        'role_in_project' => ! empty($mRole) ? $mRole : 'Anggota Tim',
                        'is_leader' => false,
                    ]);
                }
            }
        }

        AuditLog::record('STUDENT_PROJECT_UPDATE', 'InternshipProject', $project->id, [
            'student_name' => $user->name,
            'title' => $project->title,
        ]);

        return redirect()->route('student.final_report.index')->with('success', 'Proyek inovasi magang berhasil diperbarui.');
    }

    /**
     * Hapus proyek inovasi magang beserta seluruh berkas medianya
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $placement = $this->getActivePlacement($user->id);

        if (! $placement) {
            return redirect()->route('student.final_report.index')->with('error', 'Akses ditolak.');
        }

        $project = InternshipProject::where('id', $id)
            ->where('placement_id', $placement->id)
            ->firstOrFail();

        if ($placement->finalreport && $placement->finalreport->status === 'approved') {
            return redirect()->route('student.final_report.index')->with('error', 'Laporan akhir telah disetujui, proyek tidak dapat dihapus.');
        }

        // Hapus Cover
        if ($project->photo_path && Storage::disk('public')->exists($project->photo_path)) {
            Storage::disk('public')->delete($project->photo_path);
        }

        // Hapus Source Code ZIP
        if ($project->source_code_path && Storage::disk('public')->exists($project->source_code_path)) {
            Storage::disk('public')->delete($project->source_code_path);
        }

        // Hapus Screenshots
        if (is_array($project->screenshots)) {
            foreach ($project->screenshots as $ss) {
                if (Storage::disk('public')->exists($ss)) {
                    Storage::disk('public')->delete($ss);
                }
            }
        }

        // Hapus Foto Komunitas
        if (is_array($project->community_photos)) {
            foreach ($project->community_photos as $cp) {
                if (Storage::disk('public')->exists($cp)) {
                    Storage::disk('public')->delete($cp);
                }
            }
        }

        // Hapus Dokumen
        if (is_array($project->documents)) {
            foreach ($project->documents as $doc) {
                $docPath = is_array($doc) ? ($doc['path'] ?? null) : $doc;
                if ($docPath && Storage::disk('public')->exists($docPath)) {
                    Storage::disk('public')->delete($docPath);
                }
            }
        }

        $project->delete();

        AuditLog::record('STUDENT_PROJECT_DELETE', 'InternshipProject', $id, [
            'student_name' => $user->name,
        ]);

        return redirect()->route('student.final_report.index')->with('success', 'Proyek inovasi berhasil dihapus.');
    }

    /**
     * Ambil placement aktif mahasiswa
     */
    protected function getActivePlacement(int $userId)
    {
        $application = Application::with('placement')
            ->where('user_id', $userId)
            ->whereNotIn('status', ['rejected', 'resigned'])
            ->whereHas('placement')
            ->latest()
            ->first();

        return $application?->placement;
    }

    /**
     * Ambil rekan magang yang satu unit kerja atau satu kelompok pendaftaran
     */
    protected function getEligiblePeers(Placement $placement)
    {
        $unitId = $placement->application?->unit_id;
        $peers = Placement::with(['application.user.studentProfile'])
            ->where('id', '!=', $placement->id)
            ->whereHas('application', function ($q) use ($unitId) {
                $q->whereIn('status', ['accepted', 'active', 'completed']);
                if ($unitId) {
                    $q->where('unit_id', $unitId);
                } else {
                    $q->whereRaw('1 = 0');
                }
            })
            ->get();

        if ($placement->application?->group_id) {
            $groupPeers = Placement::with(['application.user.studentProfile'])
                ->where('id', '!=', $placement->id)
                ->whereHas('application', function ($q) use ($placement) {
                    $q->where('group_id', $placement->application->group_id)
                        ->whereIn('status', ['accepted', 'active', 'completed']);
                })
                ->get();

            $peers = $peers->merge($groupPeers)->unique('id')->values();
        }

        return $peers;
    }
}
