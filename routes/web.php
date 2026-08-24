<?php

use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Student\ProfileController as StudentProfileController; 
use App\Http\Controllers\Student\ApplicationController as StudentApplicationController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ApplicationController as AdminApplicationController;
use App\Http\Controllers\Admin\UnitController as AdminUnitController;
use App\Http\Controllers\Admin\AgencyController as AdminAgencyController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\UniversityController as AdminUniversityController;
use App\Http\Controllers\Admin\MentorController as AdminMentorController;
use App\Http\Controllers\Admin\AuditLogController as AdminAuditLogController;
use App\Http\Controllers\Admin\ImpersonationController;
use App\Http\Controllers\Student\LogbookController as StudentLogbookController;
use App\Http\Controllers\Admin\LogbookController as AdminLogbookController;
use App\Http\Controllers\Mentor\DashboardController as MentorDashboardController;
use App\Http\Controllers\Mentor\LogbookController as MentorLogbookController;
use App\Http\Controllers\Mentor\EvaluationController as MentorEvaluationController;
use App\Http\Controllers\Lecturer\DashboardController as LecturerDashboardController;
use App\Http\Controllers\Lecturer\MonitoringController as LecturerMonitoringController;
use App\Http\Controllers\Lecturer\EvaluationController as LecturerEvaluationController;
use App\Http\Controllers\Pembimbing\DashboardController as PembimbingDashboardController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Pembimbing\EvaluationController as PembimbingEvaluationController;
use App\Http\Controllers\Admin\AgencyProfileController as AdminAgencyProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Dashboard Pintar Berdasarkan Role
Route::get('/dashboard', [StudentDashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

// Route Publik Verifikasi QR Code Surat Balasan (Bisa di-scan oleh siapa saja tanpa login)
Route::get('/verify-letter/{id}', function ($id) {
    $application = \App\Models\Application::with(['user.studentProfile', 'unit.agencyProfile', 'placement.pembimbing'])
        ->where('status', 'accepted')
        ->findOrFail($id);
    return view('verify_letter', compact('application'));
})->name('verify.letter');

// Route Publik Verifikasi QR Code Sertifikat Magang (Bisa di-scan oleh siapa saja tanpa login)
Route::get('/verify-certificate/{id}', function ($id) {
    $placement = \App\Models\Placement::with(['application.user.studentProfile', 'application.unit.agencyProfile', 'evaluation', 'pembimbing'])
        ->findOrFail($id);
    return view('verify_certificate', compact('placement'));
})->name('verify.certificate');

Route::middleware('auth')->group(function () {

    // Route Profile Bawaan Breeze
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Route Impersonation (Bisa diakses saat login)
    Route::post('/admin/impersonate/leave', [ImpersonationController::class, 'leave'])->name('admin.impersonate.leave');

    // ==========================================
    // NOTIFIKASI & PEMBERITAHUAN SISTEM (SEMUA ROLE)
    // ==========================================
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.mark_all_read');

    // ==========================================
    // MASUKAN & LAPORAN KENDALA (FEEDBACK / SUPPORT TICKET)
    // ==========================================
    Route::get('/feedbacks/create', [\App\Http\Controllers\FeedbackController::class, 'create'])->name('feedbacks.create');
    Route::post('/feedbacks', [\App\Http\Controllers\FeedbackController::class, 'store'])->name('feedbacks.store');
    Route::get('/feedbacks/my', [\App\Http\Controllers\FeedbackController::class, 'myFeedbacks'])->name('feedbacks.my');
    Route::get('/feedbacks/{id}', [\App\Http\Controllers\FeedbackController::class, 'show'])->name('feedbacks.show');

    // ==========================================
    // 1. ROUTE KHUSUS MAHASISWA
    // ==========================================
    Route::middleware(['role:mahasiswa'])->group(function () {
        // Profil
        Route::get('/student/profile', [StudentProfileController::class, 'edit'])->name('student.profile.edit');
        Route::post('/student/profile', [StudentProfileController::class, 'update'])->name('student.profile.update');
        
        // Pengajuan Magang
        Route::get('/student/application', [StudentApplicationController::class, 'create'])->name('student.application.create');
        Route::post('/student/application', [StudentApplicationController::class, 'store'])->name('student.application.store');
        Route::get('/student/application/{id}/letter', [StudentApplicationController::class, 'downloadLetter'])->name('student.application.letter');

        // Logbook Magang
        Route::get('/student/logbook', [StudentLogbookController::class, 'index'])->name('student.logbook.index');
        Route::get('/student/logbook/create', [StudentLogbookController::class, 'create'])->name('student.logbook.create');
        Route::post('/student/logbook', [StudentLogbookController::class, 'store'])->name('student.logbook.store');
        Route::get('/student/logbook/{id}/edit', [StudentLogbookController::class, 'edit'])->name('student.logbook.edit');
        Route::put('/student/logbook/{id}', [StudentLogbookController::class, 'update'])->name('student.logbook.update');
        Route::delete('/student/logbook/{id}', [StudentLogbookController::class, 'destroy'])->name('student.logbook.destroy');

        // Laporan Akhir & E-Sertifikat
        Route::get('/student/final-report', [\App\Http\Controllers\Student\FinalReportController::class, 'index'])->name('student.final_report.index');
        Route::post('/student/final-report', [\App\Http\Controllers\Student\FinalReportController::class, 'store'])->name('student.final_report.store');
        Route::get('/student/certificate/{placementId}/download', [\App\Http\Controllers\Student\CertificateController::class, 'download'])->name('student.certificate.download');
        Route::get('/student/certificate/{id}', [\App\Http\Controllers\Student\CertificateController::class, 'show'])->name('student.certificate.show');

        // Pemilihan & Input Dosen Pembimbing Lapangan (DPL Kampus)
        Route::post('/student/select-advisor', [StudentDashboardController::class, 'selectAdvisor'])->name('student.select_advisor');
        Route::post('/student/create-advisor', [StudentDashboardController::class, 'storeNewAdvisor'])->name('student.create_advisor');
    });


    // ==========================================
    // 2. ROUTE KHUSUS ADMIN (SUPER ADMIN & ADMIN DINAS)
    // ==========================================
    Route::middleware(['role:admin,super_admin'])->group(function () {
        // Executive Dashboard
        Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

        // Impersonation ("Login As")
        Route::post('/admin/impersonate/{userId}', [ImpersonationController::class, 'impersonate'])->name('admin.impersonate');

        // Verifikasi Pengajuan Magang
        Route::get('/admin/applications', [AdminApplicationController::class, 'index'])->name('admin.applications.index');
        Route::get('/admin/applications/{id}', [AdminApplicationController::class, 'show'])->name('admin.applications.show');
        Route::match(['put', 'patch'], '/admin/applications/{id}', [AdminApplicationController::class, 'updateStatus'])->name('admin.applications.updateStatus');
        Route::get('/admin/applications/{id}/letter', [AdminApplicationController::class, 'downloadLetter'])->name('admin.applications.letter');

        // Review Logbook Mahasiswa
        Route::get('/admin/logbooks', [AdminLogbookController::class, 'index'])->name('admin.logbooks.index');
        Route::get('/admin/logbooks/{id}', [AdminLogbookController::class, 'show'])->name('admin.logbooks.show');
        Route::patch('/admin/logbooks/{id}/review', [AdminLogbookController::class, 'review'])->name('admin.logbooks.review');
        
        // Route Penerbitan Sertifikat
        Route::get('/admin/certificates', [\App\Http\Controllers\Admin\CertificateController::class, 'index'])->name('admin.certificates.index');
        Route::get('/admin/certificates/{placementId}/generate', [\App\Http\Controllers\Admin\CertificateController::class, 'generate'])->name('admin.certificates.generate');

        // Pengaturan Profil Instansi & TTD Surat
        Route::get('/admin/agency-profile', [AdminAgencyProfileController::class, 'edit'])->name('admin.agency_profile.edit');
        Route::match(['put', 'patch', 'post'], '/admin/agency-profile', [AdminAgencyProfileController::class, 'update'])->name('admin.agency_profile.update');

        // Manajemen Master Unit & Kuota Magang
        Route::resource('/admin/units', AdminUnitController::class)->names([
            'index' => 'admin.units.index',
            'create' => 'admin.units.create',
            'store' => 'admin.units.store',
            'edit' => 'admin.units.edit',
            'update' => 'admin.units.update',
            'destroy' => 'admin.units.destroy',
        ]);
        Route::patch('/admin/units/{id}/quota', [AdminUnitController::class, 'updateQuota'])->name('admin.units.updateQuota');

        // Master Instansi Dinas
        Route::resource('/admin/agencies', AdminAgencyController::class)->names('admin.agencies');

        // Master Pengguna Sistem
        Route::resource('/admin/users', AdminUserController::class)->names('admin.users');
        Route::post('/admin/users/{id}/reset-password', [AdminUserController::class, 'resetPassword'])->name('admin.users.reset_password');

        // Master Perguruan Tinggi (Universitas)
        Route::post('/admin/universities/{id}/create-account', [AdminUniversityController::class, 'createAccount'])->name('admin.universities.create_account');
        Route::resource('/admin/universities', AdminUniversityController::class)->names('admin.universities');

        // Manajemen Mentor Internal Dinas
        Route::resource('/admin/mentors', AdminMentorController::class)->names('admin.mentors');
        Route::post('/admin/mentors/{id}/reset-password', [AdminMentorController::class, 'resetPassword'])->name('admin.mentors.reset_password');

        // Log Audit Aktivitas Sistem
        Route::get('/admin/audit-logs', [AdminAuditLogController::class, 'index'])->name('admin.audit_logs.index');
        Route::get('/admin/audit-trail', [AdminAuditLogController::class, 'index'])->name('admin.audit-logs.index');

        // Pusat Pemberitahuan & Tindakan Super Admin
        Route::get('/admin/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('admin.notifications.index');

        // Manajemen Feedback & Tiket Masukan (Admin)
        Route::get('/admin/feedbacks', [\App\Http\Controllers\FeedbackController::class, 'index'])->name('admin.feedbacks.index');
        Route::get('/admin/feedbacks/{id}', [\App\Http\Controllers\FeedbackController::class, 'show'])->name('admin.feedbacks.show');
        Route::post('/admin/feedbacks/{id}/respond', [\App\Http\Controllers\FeedbackController::class, 'respond'])->name('admin.feedbacks.respond');
    });

    // ==========================================
    // 3. ROUTE KHUSUS PEMBIMBING LAPANGAN (MENTOR)
    // ==========================================
    Route::middleware(['role:mentor,pembimbing'])->group(function () {
        Route::get('/mentor/dashboard', [MentorDashboardController::class, 'index'])->name('mentor.dashboard');
        Route::get('/mentor/students/{placementId}', [MentorDashboardController::class, 'showStudent'])->name('mentor.students.show');
        Route::get('/mentor/logbooks', [MentorLogbookController::class, 'index'])->name('mentor.logbooks.index');
        Route::get('/mentor/logbooks/{id}', [AdminLogbookController::class, 'show'])->name('mentor.logbooks.show');
        Route::put('/mentor/logbooks/{logbookId}', [MentorLogbookController::class, 'updateStatus'])->name('mentor.logbooks.updateStatus');
        Route::get('/mentor/students/{placementId}/evaluation', [MentorEvaluationController::class, 'create'])->name('mentor.evaluations.create');
        Route::post('/mentor/students/{placementId}/evaluation', [MentorEvaluationController::class, 'store'])->name('mentor.evaluations.store');
        Route::put('/mentor/final-report/{reportId}', [MentorDashboardController::class, 'updateFinalReportStatus'])->name('mentor.final_report.updateStatus');

        // Backward compatibility routes untuk nama route pembimbing lama
        Route::get('/pembimbing/dashboard', [MentorDashboardController::class, 'index'])->name('pembimbing.dashboard');
        Route::get('/pembimbing/student/{placementId}', [MentorDashboardController::class, 'showStudent'])->name('pembimbing.student.detail');
        Route::get('/pembimbing/logbook/{id}', [AdminLogbookController::class, 'show'])->name('pembimbing.logbook.show');
        Route::put('/pembimbing/logbook/{logbookId}', [MentorLogbookController::class, 'updateStatus'])->name('pembimbing.logbook.updateStatus');
        Route::get('/pembimbing/student/{placementId}/evaluation', [MentorEvaluationController::class, 'create'])->name('pembimbing.evaluation.create');
        Route::post('/pembimbing/student/{placementId}/evaluation', [MentorEvaluationController::class, 'store'])->name('pembimbing.evaluation.store');
        Route::put('/pembimbing/final-report/{reportId}', [MentorDashboardController::class, 'updateFinalReportStatus'])->name('pembimbing.final_report.updateStatus');
    });

    // ==========================================
    // 4. ROUTE KHUSUS DOSEN PEMBIMBING LAPANGAN (DPL KAMPUS)
    // ==========================================
    Route::middleware(['role:dosen,academic_advisor'])->group(function () {
        Route::get('/lecturer/dashboard', [LecturerDashboardController::class, 'index'])->name('lecturer.dashboard');
        Route::get('/lecturer/students/{placementId}', [LecturerDashboardController::class, 'showStudent'])->name('lecturer.students.show');
        Route::get('/lecturer/monitoring', [LecturerMonitoringController::class, 'index'])->name('lecturer.monitoring.index');
        Route::get('/lecturer/logbooks', [\App\Http\Controllers\Lecturer\LogbookController::class, 'index'])->name('lecturer.logbooks.index');
        Route::get('/lecturer/logbooks/{id}', [\App\Http\Controllers\Lecturer\LogbookController::class, 'show'])->name('lecturer.logbooks.show');
        Route::put('/lecturer/logbooks/{id}', [\App\Http\Controllers\Lecturer\LogbookController::class, 'updateStatus'])->name('lecturer.logbooks.updateStatus');
        Route::get('/lecturer/students/{placementId}/evaluation', [LecturerEvaluationController::class, 'create'])->name('lecturer.evaluations.create');
        Route::post('/lecturer/students/{placementId}/evaluation', [LecturerEvaluationController::class, 'store'])->name('lecturer.evaluations.store');
        Route::post('/lecturer/students/{placementId}/evaluate', [LecturerEvaluationController::class, 'store'])->name('lecturer.students.evaluate');
        Route::post('/lecturer/students/{placementId}/report-approval', [LecturerEvaluationController::class, 'updateFinalReportStatus'])->name('lecturer.final_report.updateStatus');
    });

    // ==========================================
    // 5. ROUTE KHUSUS RESMI PERGURUAN TINGGI (UNIVERSITAS)
    // ==========================================
    Route::middleware(['role:universitas'])->group(function () {
        Route::get('/university/dashboard', [\App\Http\Controllers\University\DashboardController::class, 'index'])->name('university.dashboard');
        Route::get('/university/export-students', [\App\Http\Controllers\University\DashboardController::class, 'export'])->name('university.students.export');
        Route::get('/university/students/{placementId}', [\App\Http\Controllers\University\DashboardController::class, 'showStudent'])->name('university.students.show');
        Route::post('/university/students/{application}/assign-advisor', [\App\Http\Controllers\University\DashboardController::class, 'assignAdvisor'])->name('university.students.assign_advisor');
        Route::get('/university/students/{application}/letter', [\App\Http\Controllers\University\LetterController::class, 'generateLetter'])->name('university.students.letter');
        
        // Pengaturan Profil & Kop Surat Kampus
        Route::get('/university/profile', [\App\Http\Controllers\University\ProfileController::class, 'index'])->name('university.profile.index');
        Route::match(['put', 'patch', 'post'], '/university/profile', [\App\Http\Controllers\University\ProfileController::class, 'update'])->name('university.profile.update');

        // Manajemen Daftar Dosen Pembimbing
        Route::get('/university/lecturers', [\App\Http\Controllers\University\LecturerController::class, 'index'])->name('university.lecturers.index');
        Route::post('/university/lecturers', [\App\Http\Controllers\University\LecturerController::class, 'store'])->name('university.lecturers.store');
        Route::match(['put', 'patch'], '/university/lecturers/{id}', [\App\Http\Controllers\University\LecturerController::class, 'update'])->name('university.lecturers.update');
        Route::delete('/university/lecturers/{id}', [\App\Http\Controllers\University\LecturerController::class, 'destroy'])->name('university.lecturers.destroy');
        Route::post('/university/lecturers/{id}/reset-password', [\App\Http\Controllers\University\LecturerController::class, 'resetPassword'])->name('university.lecturers.reset_password');
    });

});

require __DIR__.'/auth.php';