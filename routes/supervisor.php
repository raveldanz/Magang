<?php

use App\Http\Controllers\Supervisor\ApplicationApprovalController;
use App\Http\Controllers\Supervisor\CertificateApprovalController;
use App\Http\Controllers\Supervisor\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Supervisor (Atasan - Tim Kerja Umum & Kepegawaian Dispusip) Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:supervisor,super_admin'])->prefix('supervisor')->name('supervisor.')->group(function () {
    // 1. Monitoring Tim Kerja & Kepegawaian Dispusip
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // 2. Persetujuan Pengajuan Magang (Double ACC)
    Route::get('/applications', [ApplicationApprovalController::class, 'index'])->name('applications.index');
    Route::get('/applications/{id}', [ApplicationApprovalController::class, 'show'])->name('applications.show');
    Route::post('/applications/{id}/approve', [ApplicationApprovalController::class, 'approve'])->name('applications.approve');
    Route::post('/applications/{id}/revise', [ApplicationApprovalController::class, 'revise'])->name('applications.revise');

    // 3. Pengesahan Sertifikat Kelulusan (Double ACC)
    Route::get('/certificates', [CertificateApprovalController::class, 'index'])->name('certificates.index');
    Route::get('/certificates/{placementId}/preview', [CertificateApprovalController::class, 'show'])->name('certificates.show');
    Route::post('/certificates/{id}/approve', [CertificateApprovalController::class, 'approve'])->name('certificates.approve');
    Route::post('/certificates/{id}/revise', [CertificateApprovalController::class, 'revise'])->name('certificates.revise');
});
