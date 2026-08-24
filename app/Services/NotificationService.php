<?php

namespace App\Services;

use App\Models\Application;
use App\Models\FinalReport;
use App\Models\Logbook;
use App\Models\Placement;
use App\Models\SystemFeedback;
use App\Models\SystemNotification;
use App\Models\University;
use App\Models\User;
use Illuminate\Support\Carbon;

class NotificationService
{
    /**
     * Get all active, actionable, and database notifications for the given user.
     */
    public static function getNotificationsForUser(User $user): array
    {
        $role = $user->role;
        $agencyId = $user->agency_profile_id;
        $isSuperAdmin = ($role === 'super_admin' || ($role === 'admin' && is_null($agencyId)));
        $isAdminDinas = ($role === 'admin' && !is_null($agencyId));

        $actionable = [];

        // 1. SUPER ADMIN NOTIFICATIONS
        if ($isSuperAdmin) {
            // A. Universitas Baru Tanpa Akun
            $pendingUnivs = University::whereDoesntHave('users', function ($q) {
                $q->where('role', 'universitas');
            })->withCount('students')->get();

            foreach ($pendingUnivs as $u) {
                $actionable[] = [
                    'id' => 'univ_' . $u->id,
                    'type' => 'urgent',
                    'category' => 'university',
                    'icon' => '🎓',
                    'title' => "Perguruan Tinggi Baru: {$u->name}",
                    'message' => "Terdaftar otomatis dari pendaftaran {$u->students_count} mahasiswa. Belum memiliki akun Admin Kampus / PIC.",
                    'time' => $u->created_at ? $u->created_at->diffForHumans() : 'Baru saja',
                    'action_url' => route('admin.universities.index'),
                    'action_label' => '⚡ Buat Akun Kampus',
                    'is_action_required' => true,
                ];
            }

            // B. Pengajuan Magang Baru Menunggu Verifikasi
            $pendingAppsCount = Application::whereIn('status', ['submitted', 'pending'])->count();
            if ($pendingAppsCount > 0) {
                $actionable[] = [
                    'id' => 'apps_pending',
                    'type' => 'warning',
                    'category' => 'application',
                    'icon' => '📋',
                    'title' => "{$pendingAppsCount} Pengajuan Magang Menunggu Verifikasi",
                    'message' => "Terdapat permohonan magang mahasiswa yang menunggu pemeriksaan berkas oleh instansi dinas.",
                    'time' => 'Memerlukan tindakan',
                    'action_url' => route('admin.applications.index'),
                    'action_label' => '🔍 Tinjau Pengajuan',
                    'is_action_required' => true,
                ];
            }

            // C. Tiket Feedback & Laporan Kendala Terbaru
            $pendingFeedbacks = \App\Models\SystemFeedback::where('status', 'pending')->latest()->take(6)->get();
            foreach ($pendingFeedbacks as $fb) {
                $catLabel = match($fb->category) {
                    'error_bug' => 'Kendala / Bug',
                    'saran_fitur' => 'Saran Fitur',
                    'pertanyaan' => 'Pertanyaan',
                    'koordinasi' => 'Koordinasi',
                    default => 'Masukan',
                };
                $actionable[] = [
                    'id' => 'fb_' . $fb->id,
                    'type' => $fb->priority === 'urgent' || $fb->category === 'error_bug' ? 'urgent' : 'info',
                    'category' => 'feedback',
                    'icon' => $fb->category === 'error_bug' ? '⚠️' : '💬',
                    'title' => "Feedback: [{$catLabel}] {$fb->subject}",
                    'message' => "Dari {$fb->sender_name} (" . strtoupper($fb->sender_role) . "): " . \Illuminate\Support\Str::limit($fb->message, 80),
                    'time' => $fb->created_at->diffForHumans(),
                    'action_url' => route('admin.feedbacks.show', $fb->id),
                    'action_label' => '✉️ Buka Tiket',
                    'is_action_required' => true,
                ];
            }

            // D. Log Audit & Aktivitas Sistem Terbaru
            $recentAudits = \App\Models\AuditLog::latest()->take(8)->get();
            foreach ($recentAudits as $log) {
                $actionable[] = [
                    'id' => 'audit_' . $log->id,
                    'type' => 'info',
                    'category' => 'audit',
                    'icon' => '📜',
                    'title' => "Aktivitas: {$log->action}",
                    'message' => "Oleh {$log->user_name} (" . strtoupper($log->user_role) . ") pada " . ($log->target_type ?? 'Sistem') . " #{$log->target_id}. " . \Illuminate\Support\Str::limit($log->details ?? '', 70),
                    'time' => $log->created_at ? $log->created_at->diffForHumans() : 'Baru saja',
                    'action_url' => route('admin.audit_logs.index'),
                    'action_label' => '📜 Buka Log Audit',
                    'is_action_required' => false,
                ];
            }

            // D. Penetapan Pembimbing Belum Lengkap (Mahasiswa Diterima tapi Belum Ada DPL)
            $unassignedDpl = Placement::whereNull('academic_advisor_id')
                ->whereHas('application', fn($q) => $q->where('status', 'accepted'))
                ->count();
            if ($unassignedDpl > 0) {
                $actionable[] = [
                    'id' => 'unassigned_dpl',
                    'type' => 'info',
                    'category' => 'academic',
                    'icon' => '👨‍🏫',
                    'title' => "{$unassignedDpl} Mahasiswa Belum Memiliki DPL",
                    'message' => "Mahasiswa telah diterima di instansi dinas namun data Dosen Pembimbing Lapangan (DPL) belum ditentukan.",
                    'time' => 'Perlu penetapan',
                    'action_url' => route('admin.applications.index'),
                    'action_label' => '👥 Lihat Penempatan',
                    'is_action_required' => false,
                ];
            }
        }

        // 2. ADMIN DINAS NOTIFICATIONS
        elseif ($isAdminDinas) {
            $agencyAppsCount = Application::whereIn('status', ['submitted', 'pending'])
                ->whereHas('unit', fn($q) => $q->where('agency_profile_id', $agencyId))
                ->count();
            if ($agencyAppsCount > 0) {
                $actionable[] = [
                    'id' => 'agency_apps_pending',
                    'type' => 'warning',
                    'category' => 'application',
                    'icon' => '📋',
                    'title' => "{$agencyAppsCount} Berkas Pendaftar Menunggu Verifikasi",
                    'message' => "Terdapat pendaftar magang baru di instansi dinas Anda yang menunggu proses verifikasi dan seleksi.",
                    'time' => 'Memerlukan tindakan',
                    'action_url' => route('admin.applications.index'),
                    'action_label' => '🔍 Verifikasi Berkas',
                    'is_action_required' => true,
                ];
            }

            $pendingLogbooks = Logbook::where('status', 'submitted')
                ->whereHas('placement.application.unit', fn($q) => $q->where('agency_profile_id', $agencyId))
                ->count();
            if ($pendingLogbooks > 0) {
                $actionable[] = [
                    'id' => 'agency_logbooks_pending',
                    'type' => 'info',
                    'category' => 'logbook',
                    'icon' => '📖',
                    'title' => "{$pendingLogbooks} Logbook Mahasiswa Perlu Review",
                    'message' => "Mahasiswa magang telah mengunggah logbook harian aktivitas kerja.",
                    'time' => 'Monitoring dinas',
                    'action_url' => route('admin.logbooks.index'),
                    'action_label' => '📖 Cek Logbook',
                    'is_action_required' => false,
                ];
            }
        }

        // 3. DOSEN (DPL) NOTIFICATIONS
        elseif ($role === 'dosen' || $role === 'academic_advisor') {
            // Mahasiswa Bimbingan Baru
            $placements = Placement::where('academic_advisor_id', $user->id)
                ->with(['application.user.studentProfile', 'application.unit.agencyProfile'])
                ->latest()
                ->get();

            $pendingLogbooksCount = Logbook::whereIn('placement_id', $placements->pluck('id'))
                ->where(function ($q) {
                    $q->whereNull('lecturer_status')->orWhere('lecturer_status', 'pending');
                })
                ->count();

            if ($pendingLogbooksCount > 0) {
                $actionable[] = [
                    'id' => 'dosen_logbook_pending',
                    'type' => 'warning',
                    'category' => 'logbook',
                    'icon' => '📖',
                    'title' => "{$pendingLogbooksCount} Logbook Bimbingan Menunggu Evaluasi",
                    'message' => "Mahasiswa bimbingan Anda telah mengisi logbook aktivitas harian yang perlu diberi catatan/tinjauan.",
                    'time' => 'Menunggu review',
                    'action_url' => route('lecturer.logbooks.index'),
                    'action_label' => '✍️ Review Logbook',
                    'is_action_required' => true,
                ];
            }

            // Laporan Akhir Siap Dinilai
            $pendingReportsCount = FinalReport::whereIn('placement_id', $placements->pluck('id'))
                ->where('status', 'submitted')
                ->count();

            if ($pendingReportsCount > 0) {
                $actionable[] = [
                    'id' => 'dosen_report_pending',
                    'type' => 'urgent',
                    'category' => 'evaluation',
                    'icon' => '📊',
                    'title' => "{$pendingReportsCount} Laporan Akhir Mahasiswa Siap Dinilai",
                    'message' => "Mahasiswa telah menyelesaikan masa magang dan mengunggah laporan akhir magang.",
                    'time' => 'Perlu penilaian',
                    'action_url' => route('lecturer.monitoring.index'),
                    'action_label' => '📊 Beri Nilai Akhir',
                    'is_action_required' => true,
                ];
            }

            if ($placements->count() > 0) {
                $actionable[] = [
                    'id' => 'dosen_students_active',
                    'type' => 'info',
                    'category' => 'academic',
                    'icon' => '🎓',
                    'title' => "Total {$placements->count()} Mahasiswa dalam Bimbingan Anda",
                    'message' => "Pantau kemajuan kegiatan magang mahasiswa bimbingan di instansi Pemkot Surabaya.",
                    'time' => 'Portal DPL',
                    'action_url' => route('lecturer.monitoring.index'),
                    'action_label' => '👥 Buka Bimbingan',
                    'is_action_required' => false,
                ];
            }
        }

        // 4. MENTOR LAPANGAN NOTIFICATIONS
        elseif ($role === 'mentor' || $role === 'pembimbing') {
            $mentorPlacements = Placement::where('mentor_id', $user->id)
                ->orWhere(function ($q) use ($user) {
                    if ($user->agency_profile_id) {
                        $q->whereHas('application.unit', fn($uq) => $uq->where('agency_profile_id', $user->agency_profile_id));
                    }
                })->get();

            $pendingMentorLogbooks = Logbook::where('status', 'submitted')
                ->whereIn('placement_id', $mentorPlacements->pluck('id'))
                ->count();

            if ($pendingMentorLogbooks > 0) {
                $actionable[] = [
                    'id' => 'mentor_logbook_pending',
                    'type' => 'warning',
                    'category' => 'logbook',
                    'icon' => '📖',
                    'title' => "{$pendingMentorLogbooks} Logbook Menunggu Validasi Mentor",
                    'message' => "Mahasiswa di divisi Anda telah mengisi logbook aktivitas magang.",
                    'time' => 'Menunggu persetujuan',
                    'action_url' => route('mentor.logbooks.index'),
                    'action_label' => '✅ Validasi Logbook',
                    'is_action_required' => true,
                ];
            }

            if ($mentorPlacements->count() > 0) {
                $actionable[] = [
                    'id' => 'mentor_active_students',
                    'type' => 'info',
                    'category' => 'mentor',
                    'icon' => '👔',
                    'title' => "Supervisi {$mentorPlacements->count()} Mahasiswa di Unit Kerja",
                    'message' => "Pantau kehadiran, kinerja harian, dan berikan evaluasi lapangan.",
                    'time' => 'Portal Mentor',
                    'action_url' => route('mentor.dashboard'),
                    'action_label' => '🏢 Dashboard Mentor',
                    'is_action_required' => false,
                ];
            }
        }

        // 5. MAHASISWA NOTIFICATIONS
        elseif ($role === 'mahasiswa') {
            $latestApp = Application::where('user_id', $user->id)->latest()->first();
            if ($latestApp) {
                $status = strtolower($latestApp->status);
                if ($status === 'pending' || $status === 'submitted') {
                    $actionable[] = [
                        'id' => 'student_app_pending',
                        'type' => 'warning',
                        'category' => 'application',
                        'icon' => '⏳',
                        'title' => 'Pengajuan Magang Sedang Diverifikasi Dinas',
                        'message' => 'Berkas pendaftaran Anda sedang dalam tahap seleksi oleh pihak instansi kedinasan.',
                        'time' => $latestApp->created_at ? $latestApp->created_at->diffForHumans() : 'Terkirim',
                        'action_url' => route('student.logbook.index'),
                        'action_label' => '📋 Pantau Status',
                        'is_action_required' => false,
                    ];
                } elseif ($status === 'accepted') {
                    $placement = Placement::where('application_id', $latestApp->id)->first();
                    if (!$placement || empty($placement->academic_advisor_id)) {
                        $actionable[] = [
                            'id' => 'student_need_dpl',
                            'type' => 'urgent',
                            'category' => 'academic',
                            'icon' => '👨‍🏫',
                            'title' => 'Selamat! Pengajuan Diterima - Silakan Pilih DPL',
                            'message' => 'Permohonan magang Anda telah disetujui. Lengkapi data Dosen Pembimbing Lapangan (DPL) pada dashboard.',
                            'time' => 'Tindakan diperlukan',
                            'action_url' => route('dashboard'),
                            'action_label' => '👨‍🏫 Pilih DPL',
                            'is_action_required' => true,
                        ];
                    } else {
                        $actionable[] = [
                            'id' => 'student_ready',
                            'type' => 'success',
                            'category' => 'academic',
                            'icon' => '🎉',
                            'title' => 'Data Pembimbing Lengkap & Magang Siap Dilaksanakan',
                            'message' => "Mentor Dinas & DPL telah terhubung. Jangan lupa untuk mengisi logbook harian secara berkala.",
                            'time' => 'Aktif',
                            'action_url' => route('student.logbook.index'),
                            'action_label' => '📖 Buka Logbook',
                            'is_action_required' => false,
                        ];
                    }
                } elseif ($status === 'rejected') {
                    $actionable[] = [
                        'id' => 'student_rejected',
                        'type' => 'urgent',
                        'category' => 'application',
                        'icon' => '❌',
                        'title' => 'Pengajuan Magang Belum Diterima',
                        'message' => 'Catatan: "' . ($latestApp->rejection_reason ?? 'Kuota instansi belum mencukupi') . '". Anda dapat mengajukan ulang.',
                        'time' => 'Pemberitahuan',
                        'action_url' => route('student.application.create'),
                        'action_label' => '📝 Ajukan Ulang',
                        'is_action_required' => true,
                    ];
                }
            } else {
                $actionable[] = [
                    'id' => 'student_no_app',
                    'type' => 'info',
                    'category' => 'application',
                    'icon' => '📝',
                    'title' => 'Selamat Datang di Portal Magang Terpadu',
                    'message' => 'Silakan lengkapi profil Anda dan ajukan permohonan magang pada unit instansi yang tersedia.',
                    'time' => 'Langkah Awal',
                    'action_url' => route('student.application.create'),
                    'action_label' => '🚀 Daftar Magang',
                    'is_action_required' => true,
                ];
            }

            // Balasan Feedback untuk Mahasiswa
            $myAnsweredFeedbacks = SystemFeedback::where('user_id', $user->id)
                ->whereNotNull('admin_response')
                ->latest()
                ->take(3)
                ->get();

            foreach ($myAnsweredFeedbacks as $afb) {
                $actionable[] = [
                    'id' => 'my_fb_' . $afb->id,
                    'type' => 'success',
                    'category' => 'feedback',
                    'icon' => '✅',
                    'title' => "Tanggapan atas Masukan: {$afb->subject}",
                    'message' => "Admin telah membalas masukan Anda: \"" . \Illuminate\Support\Str::limit($afb->admin_response, 80) . "\"",
                    'time' => $afb->responded_at ? $afb->responded_at->diffForHumans() : 'Baru saja',
                    'action_url' => route('feedbacks.show', $afb->id),
                    'action_label' => '✉️ Lihat Tanggapan',
                    'is_action_required' => false,
                ];
            }
        }

        // 6. UNIVERSITAS NOTIFICATIONS
        elseif ($role === 'universitas') {
            $univId = $user->university_id;
            $univ = $univId ? University::find($univId) : null;
            if ($univ) {
                $dosenCount = User::whereIn('role', ['dosen', 'academic_advisor'])->where('university_id', $univ->id)->count();
                $studentCount = User::where('role', 'mahasiswa')->where('university_id', $univ->id)->count();

                if ($dosenCount === 0) {
                    $actionable[] = [
                        'id' => 'univ_no_dosen',
                        'type' => 'warning',
                        'category' => 'university',
                        'icon' => '👨‍🏫',
                        'title' => 'Belum Ada Dosen Pembimbing (DPL) Terdaftar',
                        'message' => 'Daftarkan akun Dosen Pembimbing resmi kampus agar mahasiswa dapat memilih DPL dengan mudah.',
                        'time' => 'Perlu kelengkapan',
                        'action_url' => route('university.lecturers.index'),
                        'action_label' => '➕ Tambah DPL',
                        'is_action_required' => true,
                    ];
                }

                if (empty($univ->pic_name) || empty($univ->address)) {
                    $actionable[] = [
                        'id' => 'univ_profile_incomplete',
                        'type' => 'info',
                        'category' => 'university',
                        'icon' => '🏛️',
                        'title' => 'Lengkapi Data Profil & Kop Surat Kampus',
                        'message' => 'Lengkapi data PIC Rektorat/Dekanat dan format kop surat resmi untuk penerbitan surat pengantar mahasiswa.',
                        'time' => 'Profil instansi',
                        'action_url' => route('university.profile.index'),
                        'action_label' => '✏️ Lengkapi Profil',
                        'is_action_required' => false,
                    ];
                }

                $actionable[] = [
                    'id' => 'univ_overview',
                    'type' => 'info',
                    'category' => 'university',
                    'icon' => '🎓',
                    'title' => "Portal Mitra MBKM: {$univ->name}",
                    'message' => "Tercatat {$studentCount} Mahasiswa dan {$dosenCount} Dosen DPL aktif pada program magang Pemkot Surabaya.",
                    'time' => 'Terhubung',
                    'action_url' => route('university.dashboard'),
                    'action_label' => '🏛️ Buka Portal Kampus',
                    'is_action_required' => false,
                ];
            }
        }

        // 7. DATABASE STORED NOTIFICATIONS
        $dbNotifications = SystemNotification::forUser($user)->latest()->take(10)->get();
        foreach ($dbNotifications as $dn) {
            $actionable[] = [
                'id' => 'db_' . $dn->id,
                'type' => $dn->type,
                'category' => $dn->category,
                'icon' => $dn->icon,
                'title' => $dn->title,
                'message' => $dn->message,
                'time' => $dn->created_at->diffForHumans(),
                'action_url' => $dn->action_url,
                'action_label' => $dn->action_label ?? 'Buka Detail',
                'is_action_required' => $dn->type === 'urgent' || $dn->type === 'warning',
                'is_read' => !is_null($dn->read_at),
            ];
        }

        return $actionable;
    }

    /**
     * Get unread notification count for user
     */
    public static function getUnreadCount(User $user): int
    {
        $notifs = self::getNotificationsForUser($user);
        $count = 0;
        foreach ($notifs as $n) {
            if (!empty($n['is_action_required']) || empty($n['is_read'])) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Determine if the notification red dot badge should be visible
     */
    public static function hasUnreadDot(User $user): bool
    {
        $notifs = self::getNotificationsForUser($user);
        if (empty($notifs)) {
            return false;
        }

        $lastRead = $user->last_notification_read_at;

        // If user has never marked as read, show dot if any notifications exist
        if (is_null($lastRead)) {
            return true;
        }

        // If user already marked read today
        if ($lastRead->isToday()) {
            // Only show red dot if there are unread DB notifications created AFTER last read
            $hasNewAfterRead = SystemNotification::forUser($user)
                ->whereNull('read_at')
                ->where('created_at', '>', $lastRead)
                ->exists();

            return $hasNewAfterRead;
        }

        // If last read was BEFORE today (e.g. yesterday or earlier):
        // Re-trigger red dot if there are unresolved urgent / action-required items
        $hasUrgentPending = false;
        foreach ($notifs as $n) {
            if (!empty($n['is_action_required']) || in_array($n['type'] ?? '', ['urgent', 'warning'])) {
                $hasUrgentPending = true;
                break;
            }
        }

        return $hasUrgentPending;
    }
}
