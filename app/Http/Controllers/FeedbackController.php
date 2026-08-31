<?php

namespace App\Http\Controllers;

use App\Models\AgencyProfile;
use App\Models\AuditLog;
use App\Models\SystemFeedback;
use App\Models\SystemNotification;
use App\Models\University;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FeedbackController extends Controller
{
    /**
     * Display feedback management center (for Super Admin, Admin Dinas, & Universitas)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isSuperAdmin = ($user->role === 'super_admin' || ($user->role === 'admin' && is_null($user->agency_profile_id)));
        $isAdminDinas = ($user->role === 'admin' && !is_null($user->agency_profile_id));
        $isUniversitas = ($user->role === 'universitas');

        $query = SystemFeedback::with(['user', 'responder', 'targetAgency', 'targetUniversity'])->latest();

        if ($isSuperAdmin) {
            // Super Admin sees all feedbacks
        } elseif ($isAdminDinas) {
            $query->where(function ($q) use ($user) {
                $q->where('target_agency_id', $user->agency_profile_id)
                  ->orWhere('user_id', $user->id);
            });
        } elseif ($isUniversitas) {
            $query->where(function ($q) use ($user) {
                $q->where('target_university_id', $user->university_id)
                  ->orWhere('user_id', $user->id);
            });
        } else {
            // Regular user only sees their own
            $query->where('user_id', $user->id);
        }

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%")
                  ->orWhere('sender_name', 'like', "%{$search}%")
                  ->orWhere('sender_email', 'like', "%{$search}%");
            });
        }

        $feedbacks = $query->paginate(12)->withQueryString();

        $stats = [
            'total' => SystemFeedback::count(),
            'pending' => SystemFeedback::where('status', 'pending')->count(),
            'in_progress' => SystemFeedback::where('status', 'in_progress')->count(),
            'resolved' => SystemFeedback::where('status', 'resolved')->count(),
        ];

        return view('feedbacks.index', compact('feedbacks', 'stats', 'isSuperAdmin', 'isAdminDinas', 'isUniversitas'));
    }

    /**
     * Show form to submit feedback
     */
    public function create()
    {
        $user = Auth::user();
        $agencies = AgencyProfile::all();
        $universities = University::all();

        return view('feedbacks.create', compact('user', 'agencies', 'universities'));
    }

    /**
     * Store a new feedback / bug report
     */
    public function store(Request $request)
    {
        $request->validate([
            'category' => 'required|in:error_bug,saran_fitur,pertanyaan,koordinasi,lainnya',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
            'target_role' => 'nullable|string|in:super_admin,admin_dinas,universitas',
            'target_agency_id' => 'nullable|exists:agency_profiles,id',
            'target_university_id' => 'nullable|exists:universities,id',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,zip|max:5120',
        ]);

        $user = Auth::user();
        $attachmentPath = null;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $fileName = time() . '_' . preg_replace('/[^A-Za-z0-9\._-]/', '', $file->getClientOriginalName());
            $attachmentPath = $file->storeAs('attachments/feedbacks', $fileName, 'public');
        }

        $feedback = SystemFeedback::create([
            'user_id' => $user->id,
            'sender_name' => $user->name,
            'sender_email' => $user->email,
            'sender_role' => $user->role,
            'target_role' => $request->target_role ?? 'super_admin',
            'target_agency_id' => $request->target_agency_id,
            'target_university_id' => $request->target_university_id,
            'category' => $request->category,
            'subject' => $request->subject,
            'message' => $request->message,
            'attachment' => $attachmentPath,
            'priority' => $request->priority ?? 'medium',
            'status' => 'pending',
        ]);

        AuditLog::record('FEEDBACK_SUBMITTED', 'SystemFeedback', $feedback->id, [
            'subject' => $feedback->subject,
            'category' => $feedback->category,
            'sender' => $user->name,
        ]);

        // Send Notification to Super Admin
        $catLabel = match($feedback->category) {
            'error_bug' => 'Kendala / Bug Sistem',
            'saran_fitur' => 'Saran Fitur',
            'pertanyaan' => 'Pertanyaan MBKM',
            default => 'Masukan Pengguna',
        };

        SystemNotification::send(
            title: "Masukan Baru: [{$catLabel}] {$feedback->subject}",
            message: "Dari {$feedback->sender_name} (" . strtoupper($feedback->sender_role) . "): " . \Illuminate\Support\Str::limit($feedback->message, 80),
            userId: null,
            targetRole: 'super_admin',
            actionUrl: route('admin.feedbacks.show', $feedback->id),
            actionLabel: '✉️ Buka Tiket',
            type: $feedback->category === 'error_bug' ? 'urgent' : 'info',
            category: 'feedback',
            icon: $feedback->category === 'error_bug' ? '⚠️' : '💬'
        );

        return redirect()->route('feedbacks.my')
            ->with('success', 'Masukan / Laporan kendala Anda berhasil dikirimkan ke tim pengelola. Kami akan segera meninjau dan memberikan tanggapan.');
    }

    /**
     * Show detail of a feedback item and allow admin response
     */
    public function show($id)
    {
        $user = Auth::user();
        $isSuperAdmin = ($user->role === 'super_admin' || ($user->role === 'admin' && is_null($user->agency_profile_id)));
        $isAdminDinas = ($user->role === 'admin' && !is_null($user->agency_profile_id));
        $isUniversitas = ($user->role === 'universitas');

        $feedback = SystemFeedback::with(['user', 'responder', 'targetAgency', 'targetUniversity'])->findOrFail($id);

        // Security check
        if (!$isSuperAdmin) {
            if ($isAdminDinas && $feedback->target_agency_id !== $user->agency_profile_id && $feedback->user_id !== $user->id) {
                abort(403, 'Anda tidak memiliki akses ke tiket masukan ini.');
            }
            if ($isUniversitas && $feedback->target_university_id !== $user->university_id && $feedback->user_id !== $user->id) {
                abort(403, 'Anda tidak memiliki akses ke tiket masukan ini.');
            }
            if (!$isAdminDinas && !$isUniversitas && $feedback->user_id !== $user->id) {
                abort(403, 'Anda tidak memiliki akses ke tiket masukan ini.');
            }
        }

        return view('feedbacks.show', compact('feedback', 'isSuperAdmin', 'isAdminDinas', 'isUniversitas'));
    }

    /**
     * Admin submits response / updates ticket status
     */
    public function respond(Request $request, $id)
    {
        $feedback = SystemFeedback::findOrFail($id);

        $request->validate([
            'status' => 'required|in:pending,in_progress,resolved,closed',
            'admin_response' => 'required|string|max:5000',
        ]);

        $user = Auth::user();

        $feedback->update([
            'status' => $request->status,
            'admin_response' => $request->admin_response,
            'responded_by' => $user->id,
            'responded_at' => now(),
        ]);

        AuditLog::record('FEEDBACK_RESPONDED', 'SystemFeedback', $feedback->id, [
            'status' => $feedback->status,
            'responder' => $user->name,
        ]);

        // Send notification to the original sender
        if ($feedback->user_id) {
            SystemNotification::send(
                title: "Tanggapan Masukan: {$feedback->subject}",
                message: "Pengelola telah menanggapi masukan Anda (Status: " . strtoupper($feedback->status) . "): \"" . \Illuminate\Support\Str::limit($feedback->admin_response, 80) . "\"",
                userId: $feedback->user_id,
                targetRole: null,
                actionUrl: route('feedbacks.show', $feedback->id),
                actionLabel: '✉️ Lihat Tanggapan',
                type: $feedback->status === 'resolved' ? 'success' : 'info',
                category: 'feedback',
                icon: '✅'
            );
        }

        return redirect()->route('admin.feedbacks.show', $feedback->id)
            ->with('success', 'Tanggapan resmi berhasil disimpan dan notifikasi telah dikirimkan ke pengirim.');
    }

    /**
     * Show feedbacks submitted by current user
     */
    public function myFeedbacks()
    {
        $user = Auth::user();
        $feedbacks = SystemFeedback::where('user_id', $user->id)
            ->with(['responder'])
            ->latest()
            ->paginate(10);

        return view('feedbacks.my', compact('feedbacks', 'user'));
    }
}
