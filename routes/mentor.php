<?php

use App\Http\Controllers\Mentor\DashboardController as MentorDashboardController;
use App\Http\Controllers\Mentor\EvaluationController as MentorEvaluationController;
use App\Http\Controllers\Mentor\LogbookController as MentorLogbookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Route Khusus Pembimbing Lapangan (Mentor)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:mentor,pembimbing'])->group(function () {
    Route::get('/mentor/dashboard', [MentorDashboardController::class, 'index'])->name('mentor.dashboard');
    Route::get('/mentor/students/{placementId}', [MentorDashboardController::class, 'showStudent'])->name('mentor.students.show');
    Route::get('/mentor/logbooks', [MentorLogbookController::class, 'index'])->name('mentor.logbooks.index');
    Route::get('/mentor/logbooks/{id}', [MentorLogbookController::class, 'show'])->name('mentor.logbooks.show');
    Route::put('/mentor/logbooks/{logbookId}', [MentorLogbookController::class, 'updateStatus'])->name('mentor.logbooks.updateStatus');
    Route::post('/mentor/logbooks/bulk-review', [MentorLogbookController::class, 'bulkReview'])->name('mentor.logbooks.bulk_review');
    Route::get('/mentor/students/{placementId}/evaluation', [MentorEvaluationController::class, 'create'])->name('mentor.evaluations.create');
    Route::post('/mentor/students/{placementId}/evaluation', [MentorEvaluationController::class, 'store'])->name('mentor.evaluations.store');
    Route::match(['put', 'patch'], '/mentor/final-report/{reportId}', [MentorDashboardController::class, 'updateFinalReportStatus'])->name('mentor.final_report.updateStatus');

    // Backward compatibility
    Route::get('/pembimbing/dashboard', [MentorDashboardController::class, 'index'])->name('pembimbing.dashboard');
    Route::get('/pembimbing/student/{placementId}', [MentorDashboardController::class, 'showStudent'])->name('pembimbing.student.detail');
    Route::get('/pembimbing/logbook/{id}', [MentorLogbookController::class, 'show'])->name('pembimbing.logbook.show');
    Route::put('/pembimbing/logbook/{logbookId}', [MentorLogbookController::class, 'updateStatus'])->name('pembimbing.logbook.updateStatus');
    Route::get('/pembimbing/student/{placementId}/evaluation', [MentorEvaluationController::class, 'create'])->name('pembimbing.evaluation.create');
    Route::post('/pembimbing/student/{placementId}/evaluation', [MentorEvaluationController::class, 'store'])->name('pembimbing.evaluation.store');
    Route::match(['put', 'patch'], '/pembimbing/final-report/{reportId}', [MentorDashboardController::class, 'updateFinalReportStatus'])->name('pembimbing.final_report.updateStatus');
});
