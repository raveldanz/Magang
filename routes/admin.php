<?php

use App\Http\Controllers\Admin\AgencyController as AdminAgencyController;
use App\Http\Controllers\Admin\AgencyProfileController as AdminAgencyProfileController;
use App\Http\Controllers\Admin\ApplicationController as AdminApplicationController;
use App\Http\Controllers\Admin\AuditLogController as AdminAuditLogController;
use App\Http\Controllers\Admin\CertificateController as AdminCertificateController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\LogbookController as AdminLogbookController;
use App\Http\Controllers\Admin\MentorController as AdminMentorController;
use App\Http\Controllers\Admin\PlacementAssignmentController;
use App\Http\Controllers\Admin\UnitController;
use App\Http\Controllers\Admin\UniversityController as AdminUniversityController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Route Khusus Admin (Super Admin & Admin Dinas)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin,super_admin'])->group(function () {
    // Executive Dashboard
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    // Verifikasi Pengajuan Magang
    Route::get('/admin/applications', [AdminApplicationController::class, 'index'])->name('admin.applications.index');
    Route::post('/admin/applications/bulk-update', [AdminApplicationController::class, 'bulkUpdateStatus'])->name('admin.applications.bulk_update');
    Route::get('/admin/applications/{id}', [AdminApplicationController::class, 'show'])->name('admin.applications.show');
    Route::match(['put', 'patch'], '/admin/applications/{id}', [AdminApplicationController::class, 'updateStatus'])->name('admin.applications.updateStatus');
    Route::get('/admin/applications/{id}/letter', [AdminApplicationController::class, 'downloadLetter'])->name('admin.applications.letter');
    Route::patch('/admin/applications/{id}/assignment', [PlacementAssignmentController::class, 'update'])->name('admin.applications.assignment');

    // Review Logbook Mahasiswa
    Route::get('/admin/logbooks', [AdminLogbookController::class, 'index'])->name('admin.logbooks.index');
    Route::get('/admin/logbooks/{id}', [AdminLogbookController::class, 'show'])->name('admin.logbooks.show');
    Route::match(['put', 'patch'], '/admin/logbooks/{id}/review', [AdminLogbookController::class, 'review'])->name('admin.logbooks.review');

    // Penerbitan Sertifikat
    Route::get('/admin/certificates', [AdminCertificateController::class, 'index'])->name('admin.certificates.index');
    Route::get('/admin/certificates/{placementId}/preview', [AdminCertificateController::class, 'show'])->name('admin.certificates.show');
    Route::get('/admin/certificates/{placementId}/generate', [AdminCertificateController::class, 'generate'])->name('admin.certificates.generate');

    // Pengaturan Profil Instansi & TTD Surat
    Route::get('/admin/agency-profile', [AdminAgencyProfileController::class, 'edit'])->name('admin.agency_profile.edit');
    Route::match(['put', 'patch', 'post'], '/admin/agency-profile', [AdminAgencyProfileController::class, 'update'])->name('admin.agency_profile.update');

    // Manajemen Master Unit & Kuota Magang
    Route::resource('/admin/units', UnitController::class)->names('admin.units');
    Route::patch('/admin/units/{id}/quota', [UnitController::class, 'updateQuota'])->name('admin.units.updateQuota');

    // Master Instansi Dinas
    Route::post('/admin/agencies/{id}/create-account', [AdminAgencyController::class, 'createAccount'])->name('admin.agencies.create_account');
    Route::resource('/admin/agencies', AdminAgencyController::class)->names('admin.agencies');

    // Master Pengguna Sistem
    Route::post('/admin/users/bulk-reset-password', [AdminUserController::class, 'bulkResetPassword'])->name('admin.users.bulk_reset_password');
    Route::post('/admin/users/bulk-delete', [AdminUserController::class, 'bulkDelete'])->name('admin.users.bulk_delete');
    Route::post('/admin/users/{id}/reset-password', [AdminUserController::class, 'resetPassword'])->name('admin.users.reset_password');
    Route::resource('/admin/users', AdminUserController::class)->names('admin.users');

    // Master Perguruan Tinggi (Universitas)
    Route::post('/admin/universities/{id}/create-account', [AdminUniversityController::class, 'createAccount'])->name('admin.universities.create_account');
    Route::post('/admin/universities/{id}/verify', [AdminUniversityController::class, 'verify'])->name('admin.universities.verify');
    Route::post('/admin/universities/{id}/merge', [AdminUniversityController::class, 'merge'])->name('admin.universities.merge');
    Route::post('/admin/universities/{id}/dosens', [AdminUniversityController::class, 'storeDosen'])->name('admin.universities.dosens.store');
    Route::post('/admin/universities/{id}/dosens/{dosenId}/reset-password', [AdminUniversityController::class, 'resetDosenPassword'])->name('admin.universities.dosens.reset_password');
    Route::delete('/admin/universities/{id}/dosens/{dosenId}', [AdminUniversityController::class, 'destroyDosen'])->name('admin.universities.dosens.destroy');
    Route::post('/admin/universities/{id}/assign-advisor', [AdminUniversityController::class, 'assignAdvisor'])->name('admin.universities.assign_advisor');
    Route::get('/admin/universities/{id}/export-students', [AdminUniversityController::class, 'exportStudents'])->name('admin.universities.export_students');
    Route::resource('/admin/universities', AdminUniversityController::class)->names('admin.universities');

    // Manajemen Mentor Internal Dinas
    Route::resource('/admin/mentors', AdminMentorController::class)->names('admin.mentors');
    Route::post('/admin/mentors/{id}/reset-password', [AdminMentorController::class, 'resetPassword'])->name('admin.mentors.reset_password');

    // Log Audit Aktivitas Sistem
    Route::get('/admin/audit-logs', [AdminAuditLogController::class, 'index'])->name('admin.audit_logs.index');
    Route::get('/admin/audit-trail', [AdminAuditLogController::class, 'index'])->name('admin.audit-logs.index');

    // Pusat Pemberitahuan Super Admin
    Route::get('/admin/notifications', [NotificationController::class, 'index'])->name('admin.notifications.index');

    // Manajemen Feedback & Tiket Masukan (Admin)
    Route::get('/admin/feedbacks', [FeedbackController::class, 'index'])->name('admin.feedbacks.index');
    Route::get('/admin/feedbacks/{id}', [FeedbackController::class, 'show'])->name('admin.feedbacks.show');
    Route::post('/admin/feedbacks/{id}/respond', [FeedbackController::class, 'respond'])->name('admin.feedbacks.respond');
});
