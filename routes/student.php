<?php

use App\Http\Controllers\Student\ApplicationController as StudentApplicationController;
use App\Http\Controllers\Student\CertificateController as StudentCertificateController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\FinalReportController as StudentFinalReportController;
use App\Http\Controllers\Student\LogbookController as StudentLogbookController;
use App\Http\Controllers\Student\ProfileController as StudentProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Route Khusus Mahasiswa
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:mahasiswa'])->group(function () {
    // Profil Mahasiswa
    Route::get('/student/profile', [StudentProfileController::class, 'edit'])->name('student.profile.edit');
    Route::post('/student/profile', [StudentProfileController::class, 'update'])->name('student.profile.update');

    // Pengajuan Magang
    Route::get('/student/application', [StudentApplicationController::class, 'create'])->name('student.application.create');
    Route::post('/student/application', [StudentApplicationController::class, 'store'])->name('student.application.store');
    Route::get('/student/application/{id}/letter', [StudentApplicationController::class, 'downloadLetter'])->name('student.application.letter');

    // Kelompok Magang
    Route::post('/student/group/create', [StudentApplicationController::class, 'createGroup'])->name('student.group.create');
    Route::post('/student/group/join', [StudentApplicationController::class, 'joinGroup'])->name('student.group.join');
    Route::post('/student/group/leave', [StudentApplicationController::class, 'leaveGroup'])->name('student.group.leave');
    Route::post('/student/group/remove-member', [StudentApplicationController::class, 'removeMember'])->name('student.group.remove_member');
    Route::post('/student/group/cancel', [StudentApplicationController::class, 'cancelGroup'])->name('student.group.cancel');
    Route::post('/student/group/submit', [StudentApplicationController::class, 'submitGroup'])->name('student.group.submit');

    // Logbook Magang
    Route::get('/student/logbook', [StudentLogbookController::class, 'index'])->name('student.logbook.index');
    Route::get('/student/logbook/create', [StudentLogbookController::class, 'create'])->name('student.logbook.create');
    Route::post('/student/logbook', [StudentLogbookController::class, 'store'])->name('student.logbook.store');
    Route::get('/student/logbook/{id}/edit', [StudentLogbookController::class, 'edit'])->name('student.logbook.edit');
    Route::put('/student/logbook/{id}', [StudentLogbookController::class, 'update'])->name('student.logbook.update');
    Route::delete('/student/logbook/{id}', [StudentLogbookController::class, 'destroy'])->name('student.logbook.destroy');

    // Laporan Akhir & E-Sertifikat
    Route::get('/student/final-report', [StudentFinalReportController::class, 'index'])->name('student.final_report.index');
    Route::post('/student/final-report', [StudentFinalReportController::class, 'store'])->name('student.final_report.store');
    Route::get('/student/projects/create', [\App\Http\Controllers\Student\InternshipProjectController::class, 'create'])->name('student.projects.create');
    Route::post('/student/projects', [\App\Http\Controllers\Student\InternshipProjectController::class, 'store'])->name('student.projects.store');
    Route::get('/student/projects/{id}/edit', [\App\Http\Controllers\Student\InternshipProjectController::class, 'edit'])->name('student.projects.edit');
    Route::put('/student/projects/{id}', [\App\Http\Controllers\Student\InternshipProjectController::class, 'update'])->name('student.projects.update');
    Route::delete('/student/projects/{id}', [\App\Http\Controllers\Student\InternshipProjectController::class, 'destroy'])->name('student.projects.destroy');
    Route::get('/student/certificate/{placementId}/download', [StudentCertificateController::class, 'download'])->name('student.certificate.download');
    Route::get('/student/certificate/{id}', [StudentCertificateController::class, 'show'])->name('student.certificate.show');

    // Pemilihan & Input Dosen Pembimbing Lapangan (DPL Kampus)
    Route::post('/student/select-advisor', [StudentDashboardController::class, 'selectAdvisor'])->name('student.select_advisor');
    Route::post('/student/create-advisor', [StudentDashboardController::class, 'storeNewAdvisor'])->name('student.create_advisor');
});
