<?php

use App\Http\Controllers\Admin\ImpersonationController;
use App\Http\Controllers\Chat\ChatApiController;
use App\Http\Controllers\Chat\ChatGroupController;
use App\Http\Controllers\Chat\ChatMessageController;
use App\Http\Controllers\Chat\ChatModerationController;
use App\Http\Controllers\Chat\ChatPageController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PrivateFileController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PushController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\FinalReportController as StudentFinalReportController;
use App\Http\Middleware\EnsureNotImpersonating;
use App\Models\Application;
use App\Models\Placement;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

// Dashboard Utama Berdasarkan Role
Route::get('/dashboard', [StudentDashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Route Publik Verifikasi QR Code Surat Balasan (Hash Token Unik; fallback ID angka opsional via VERIFY_NUMERIC_FALLBACK)
Route::get('/verify-letter/{token}', function ($token) {
    $application = Application::with(['user.studentProfile', 'unit.agencyProfile', 'placement.pembimbing', 'placement.mentor'])
        ->whereIn('status', ['accepted', 'active', 'completed'])
        ->where(function ($q) use ($token) {
            $q->where('letter_token', $token);
            // Fallback ID angka hanya untuk dokumen lama & harus diaktifkan eksplisit (VERIFY_NUMERIC_FALLBACK=true),
            // karena ID berurutan memungkinkan siapa pun menebak & melihat data mahasiswa lain.
            if (config('app.verify_numeric_fallback') && ctype_digit((string) $token)) {
                $q->orWhere('id', (int) $token);
            }
        })
        ->firstOrFail();

    return view('verify_letter', compact('application'));
})->middleware('throttle:verify-qr')->name('verify.letter');

// Route Publik Verifikasi QR Code Sertifikat Magang (Hash Token Unik; fallback ID angka opsional via VERIFY_NUMERIC_FALLBACK)
Route::get('/verify-certificate/{token}', function ($token) {
    $placement = Placement::with([
        'application.user.studentProfile',
        'application.unit.agencyProfile',
        'evaluation',
        'pembimbing',
        'mentor',
        'academicAdvisor',
    ])
        ->where(function ($q) use ($token) {
            $q->where('certificate_hash', $token);
            // Fallback ID angka hanya untuk dokumen lama & harus diaktifkan eksplisit (VERIFY_NUMERIC_FALLBACK=true)
            if (config('app.verify_numeric_fallback') && ctype_digit((string) $token)) {
                $q->orWhere('id', (int) $token)
                    ->orWhere('application_id', (int) $token);
            }
        })
        ->firstOrFail();

    return view('verify_certificate', compact('placement'));
})->middleware('throttle:verify-qr')->name('verify.certificate');

/*
|--------------------------------------------------------------------------
| Authenticated Shared Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    // Route Profile Akun (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/phone', [ProfileController::class, 'updatePhone'])->name('profile.phone.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Web Push Notifications
    Route::post('/push-subscriptions', [PushController::class, 'store'])->name('push.store');

    // Route Impersonation (Login As & Kembali ke Super Admin)
    Route::post('/admin/impersonate/leave', [ImpersonationController::class, 'leave'])->name('admin.impersonate.leave');
    Route::post('/admin/impersonate/{userId}', [ImpersonationController::class, 'impersonate'])->name('admin.impersonate');

    // Notifikasi & Pemberitahuan Sistem
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark_all_read');

    // Chat antar pengguna (semua role; aturan kontak di App\Services\Chat\ChatContactDirectory)
    Route::prefix('chat')->name('chat.')->group(function () {
        Route::get('/', [ChatPageController::class, 'index'])->name('index');
        Route::post('/start', [ChatPageController::class, 'start'])->middleware('throttle:chat-send')->name('start');
        Route::get('/placement/{placement}', [ChatPageController::class, 'placement'])->name('placement');
        Route::get('/attachments/{attachment}', [ChatMessageController::class, 'attachment'])->name('attachment');
        Route::delete('/moderation/messages/{message}', [ChatModerationController::class, 'destroy'])
            ->middleware('role:super_admin')->name('moderation.destroy');
        Route::get('/{conversation}', [ChatPageController::class, 'show'])->whereNumber('conversation')->name('show');

        Route::prefix('api')->name('api.')->group(function () {
            // Baca & polling (tetap boleh saat "Login As")
            Route::middleware('throttle:chat-poll')->group(function () {
                Route::get('/summary', [ChatApiController::class, 'summary'])->name('summary');
                Route::get('/conversations', [ChatApiController::class, 'conversations'])->name('conversations');
                Route::get('/contacts', [ChatApiController::class, 'contacts'])->name('contacts');
                Route::get('/conversations/{conversation}', [ChatApiController::class, 'show'])->name('conversations.show');
                Route::get('/conversations/{conversation}/media', [ChatApiController::class, 'media'])->name('conversations.media');
                Route::get('/conversations/{conversation}/messages', [ChatMessageController::class, 'index'])->name('messages.index');
                Route::get('/messages/{message}/comments', [ChatMessageController::class, 'comments'])->name('messages.comments.index');
                Route::post('/conversations/{conversation}/read', [ChatMessageController::class, 'read'])->name('messages.read');
                Route::post('/conversations/{conversation}/typing', [ChatMessageController::class, 'typing'])->name('messages.typing');
            });

            // Aksi tulis (diblokir saat "Login As")
            Route::middleware(['throttle:chat-send', EnsureNotImpersonating::class])->group(function () {
                Route::post('/conversations/{conversation}/messages', [ChatMessageController::class, 'store'])->name('messages.store');
                Route::post('/messages/{message}/comments', [ChatMessageController::class, 'storeComment'])->name('messages.comments.store');
                Route::delete('/messages/{message}', [ChatMessageController::class, 'destroy'])->name('messages.destroy');
                Route::post('/messages/{message}/report', [ChatMessageController::class, 'report'])->name('messages.report');
                Route::post('/groups', [ChatGroupController::class, 'store'])->name('groups.store');
                Route::patch('/conversations/{conversation}', [ChatGroupController::class, 'update'])->name('groups.update');
                Route::post('/conversations/{conversation}/members', [ChatGroupController::class, 'addMembers'])->name('groups.members.store');
                Route::delete('/conversations/{conversation}/members/{user}', [ChatGroupController::class, 'removeMember'])->name('groups.members.destroy');
                Route::post('/conversations/{conversation}/leave', [ChatGroupController::class, 'leave'])->name('groups.leave');
                Route::post('/conversations/{conversation}/settings', [ChatGroupController::class, 'settings'])->name('conversations.settings');
            });
        });
    });

    // Masukan & Laporan Kendala (Feedback / Support Ticket)
    Route::get('/feedbacks/create', [FeedbackController::class, 'create'])->name('feedbacks.create');
    Route::post('/feedbacks', [FeedbackController::class, 'store'])->name('feedbacks.store');
    Route::get('/feedbacks/my', [FeedbackController::class, 'myFeedbacks'])->name('feedbacks.my');
    Route::get('/feedbacks/{id}', [FeedbackController::class, 'show'])->name('feedbacks.show');

    // Naskah Laporan Akhir (Akses Terpusat & Unduhan Multi-Role dengan Format Nama Baku)
    Route::get('/final-reports/{id}/file', [StudentFinalReportController::class, 'showFile'])->name('final_reports.show');

    // Dokumen Persyaratan Pengajuan (CV, Transkrip, KTM, Surat Pengantar) — akses terotorisasi, bukan URL publik
    Route::get('/documents/applications/{id}', [DocumentController::class, 'showApplicationDocument'])->name('documents.application');
    Route::get('/documents/applications/{id}/download', [DocumentController::class, 'downloadApplicationDocument'])->name('documents.application.download');

    // Berkas pribadi di disk privat (lampiran logbook, foto profil, lampiran tiket) — akses terotorisasi
    Route::get('/files/logbooks/{logbook}/attachment', [PrivateFileController::class, 'logbookAttachment'])->name('logbooks.attachment');
    Route::get('/files/students/{userId}/photo', [PrivateFileController::class, 'studentPhoto'])->whereNumber('userId')->name('student.photo');
    Route::get('/files/feedbacks/{id}/attachment', [PrivateFileController::class, 'feedbackAttachment'])->name('feedbacks.attachment');

});

/*
|--------------------------------------------------------------------------
| Role-Specific Route Files
|--------------------------------------------------------------------------
| Setiap peran memiliki file route terpisah agar web.php tetap ringkas.
| Masing-masing file sudah membungkus route dengan middleware auth + role.
*/

require __DIR__.'/student.php';
require __DIR__.'/admin.php';
require __DIR__.'/mentor.php';
require __DIR__.'/lecturer.php';
require __DIR__.'/university.php';
require __DIR__.'/auth.php';
