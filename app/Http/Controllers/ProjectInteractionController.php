<?php

namespace App\Http\Controllers;

use App\Models\InternshipProject;
use App\Models\ProjectComment;
use App\Models\ProjectCommentLike;
use App\Models\ProjectLike;
use App\Services\ContentModeration\ProfanityFilterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ProjectInteractionController extends Controller
{
    /**
     * Dapatkan atau buat token pengenal unik untuk pengunjung tamu (Session-Based)
     */
    protected function getGuestIdentifier(Request $request): string
    {
        $guestToken = $request->session()->get('guest_project_token');
        if (! $guestToken) {
            $guestToken = 'gst_' . Str::random(32) . '_' . substr(md5($request->ip() . $request->userAgent()), 0, 12);
            $request->session()->put('guest_project_token', $guestToken);
        }

        return $guestToken;
    }

    /**
     * Toggle Suka (Like / Unlike) Proyek Inovasi (Mendukung Login & Tamu)
     */
    public function toggleLike(Request $request, $id)
    {
        $project = InternshipProject::findOrFail($id);
        $user = Auth::user();
        $guestId = $user ? null : $this->getGuestIdentifier($request);

        $existingLike = ProjectLike::where('internship_project_id', $project->id)
            ->when($user, fn ($q) => $q->where('user_id', $user->id))
            ->when(!$user, fn ($q) => $q->where('guest_identifier', $guestId))
            ->first();

        if ($existingLike) {
            $existingLike->delete();
            $project->decrement('likes_count');
            $liked = false;
        } else {
            ProjectLike::create([
                'internship_project_id' => $project->id,
                'user_id' => $user?->id,
                'guest_identifier' => $guestId,
                'ip_address' => $request->ip(),
            ]);
            $project->increment('likes_count');
            $liked = true;
        }

        $project->refresh();

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'liked' => $liked,
                'likes_count' => (int) $project->likes_count,
            ]);
        }

        return back()->with('success', $liked ? 'Terima kasih telah menyukai proyek ini!' : 'Batal menyukai proyek.');
    }

    /**
     * Kirim Komentar pada Proyek Inovasi (Mendukung Login & Tamu dengan Sensor Kata Kasar)
     */
    public function storeComment(Request $request, $id)
    {
        $project = InternshipProject::findOrFail($id);
        $user = Auth::user();

        // 1. Validasi Input
        $rules = [
            'comment' => ['required', 'string', 'min:2', 'max:2000'],
        ];

        if (! $user) {
            $rules['author_name'] = ['required', 'string', 'min:2', 'max:80'];
        }

        $validated = $request->validate($rules, [
            'comment.required' => 'Isi komentar tidak boleh kosong.',
            'comment.min' => 'Komentar terlalu pendek (minimal 2 karakter).',
            'comment.max' => 'Komentar maksimal 2.000 karakter.',
            'author_name.required' => 'Silakan isi nama Anda untuk mengirim komentar.',
            'author_name.min' => 'Nama minimal 2 karakter.',
        ]);

        // 2. Filter Ketat Kata Kotor dan Jorok (Tanpa False Positive)
        $textToCheck = ($validated['author_name'] ?? '') . ' ' . $validated['comment'];
        $profanityResult = ProfanityFilterService::check($textToCheck);

        if (! $profanityResult['is_clean']) {
            $errorMessage = $profanityResult['message'] ?? 'Komentar mengandung kata tidak pantas. Mohon gunakan bahasa yang sopan dan santun.';
            
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'detected' => $profanityResult['detected'],
                ], 422);
            }

            return back()->withInput()->with('error', $errorMessage);
        }

        // 3. Tentukan Atribut Penulis
        if ($user) {
            $authorName = $user->name;
            $authorRole = $user->roleLabel() ?? ucfirst($user->role);
            $isGuest = false;
        } else {
            $authorName = strip_tags(trim($validated['author_name']));
            $authorRole = 'Masyarakat / Tamu';
            $isGuest = true;
        }

        // 4. Simpan Komentar
        $comment = ProjectComment::create([
            'internship_project_id' => $project->id,
            'user_id' => $user?->id,
            'author_name' => $authorName,
            'author_role' => $authorRole,
            'is_guest' => $isGuest,
            'comment' => trim($validated['comment']),
            'likes_count' => 0,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $project->increment('comments_count');

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Komentar berhasil dipublikasikan!',
                'comment' => $comment,
                'comments_count' => (int) $project->refresh()->comments_count,
            ]);
        }

        return back()->with('success', 'Komentar Anda berhasil dipublikasikan!');
    }

    /**
     * Hapus Komentar (Khusus Super Admin, Admin Dinas, atau Pemilik Komentar)
     */
    public function deleteComment(Request $request, $commentId)
    {
        $comment = ProjectComment::findOrFail($commentId);
        $project = $comment->project;
        $user = Auth::user();

        // Otorisasi: Hanya Super Admin, Admin Dinas, atau pemilik komentar yang berhak menghapus
        $isSuperAdmin = $user?->isSuperAdmin() ?? false;
        $isAdminDinas = $user && in_array($user->role, ['admin', 'supervisor']);
        $isOwner = $user && $comment->user_id === $user->id;

        if (! ($isSuperAdmin || $isAdminDinas || $isOwner)) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki hak akses untuk menghapus komentar ini.',
                ], 403);
            }

            return back()->with('error', 'Anda tidak memiliki wewenang untuk menghapus komentar ini.');
        }

        $comment->delete();
        if ($project && $project->comments_count > 0) {
            $project->decrement('comments_count');
        }

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Komentar berhasil dihapus oleh moderator.',
                'comments_count' => (int) ($project?->refresh()->comments_count ?? 0),
            ]);
        }

        return back()->with('success', 'Komentar berhasil dihapus.');
    }

    /**
     * Toggle Suka pada Komentar (Mendukung Login & Tamu)
     */
    public function toggleCommentLike(Request $request, $commentId)
    {
        $comment = ProjectComment::findOrFail($commentId);
        $user = Auth::user();
        $guestId = $user ? null : $this->getGuestIdentifier($request);

        $existingLike = ProjectCommentLike::where('project_comment_id', $comment->id)
            ->when($user, fn ($q) => $q->where('user_id', $user->id))
            ->when(!$user, fn ($q) => $q->where('guest_identifier', $guestId))
            ->first();

        if ($existingLike) {
            $existingLike->delete();
            $comment->decrement('likes_count');
            $liked = false;
        } else {
            ProjectCommentLike::create([
                'project_comment_id' => $comment->id,
                'user_id' => $user?->id,
                'guest_identifier' => $guestId,
            ]);
            $comment->increment('likes_count');
            $liked = true;
        }

        $comment->refresh();

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'liked' => $liked,
                'likes_count' => (int) $comment->likes_count,
            ]);
        }

        return back();
    }

    /**
     * Sembunyikan atau Publikasikan Proyek Inovasi (Khusus Super Admin & Supervisor)
     */
    public function toggleVisibility(Request $request, $id)
    {
        $user = Auth::user();
        if (! $user || (! $user->isSuperAdmin() && ! in_array($user->role, ['super_admin', 'superadmin', 'admin', 'supervisor']))) {
            abort(403, 'Akses terbatas untuk Administrator dan Supervisor.');
        }

        $project = InternshipProject::findOrFail($id);
        $project->is_public = ! $project->is_public;
        $project->save();

        $message = $project->is_public 
            ? "Proyek '{$project->title}' berhasil ditampilkan ke publik." 
            : "Proyek '{$project->title}' berhasil disembunyikan (privat).";

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'is_public' => $project->is_public,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * Hapus Proyek Inovasi Secara Permanen (Khusus Super Admin)
     */
    public function destroyProject(Request $request, $id)
    {
        $user = Auth::user();
        if (! $user || (! $user->isSuperAdmin() && ! in_array($user->role, ['super_admin', 'superadmin']))) {
            abort(403, 'Hanya Super Admin yang berhak menghapus proyek.');
        }

        $project = InternshipProject::findOrFail($id);
        $title = $project->title;
        $project->delete();

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Proyek '{$title}' berhasil dihapus.",
            ]);
        }

        return redirect()->route('public.projects.index')->with('success', "Proyek '{$title}' berhasil dihapus.");
    }
}
