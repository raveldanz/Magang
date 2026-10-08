<?php

use App\Http\Controllers\Lecturer\DashboardController as LecturerDashboardController;
use App\Http\Controllers\Lecturer\EvaluationController as LecturerEvaluationController;
use App\Http\Controllers\Lecturer\LogbookController as LecturerLogbookController;
use App\Http\Controllers\Lecturer\MonitoringController as LecturerMonitoringController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Route Khusus Dosen Pembimbing Lapangan (DPL Kampus)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:dosen,academic_advisor'])->group(function () {
    Route::get('/lecturer/dashboard', [LecturerDashboardController::class, 'index'])->name('lecturer.dashboard');
    Route::get('/lecturer/students/{placementId}', [LecturerDashboardController::class, 'showStudent'])->name('lecturer.students.show');
    Route::get('/lecturer/monitoring', [LecturerMonitoringController::class, 'index'])->name('lecturer.monitoring.index');
    Route::get('/lecturer/monitoring/export', [LecturerMonitoringController::class, 'export'])->name('lecturer.monitoring.export');
    Route::get('/lecturer/logbooks', [LecturerLogbookController::class, 'index'])->name('lecturer.logbooks.index');
    Route::get('/lecturer/logbooks/{id}', [LecturerLogbookController::class, 'show'])->name('lecturer.logbooks.show');
    Route::put('/lecturer/logbooks/{id}', [LecturerLogbookController::class, 'updateStatus'])->name('lecturer.logbooks.updateStatus');
    Route::post('/lecturer/logbooks/bulk-approve', [LecturerLogbookController::class, 'bulkApprove'])->name('lecturer.logbooks.bulk_approve');
    Route::get('/lecturer/students/{placementId}/evaluation', [LecturerEvaluationController::class, 'create'])->name('lecturer.evaluations.create');
    Route::post('/lecturer/students/{placementId}/evaluation', [LecturerEvaluationController::class, 'store'])->name('lecturer.evaluations.store');
    Route::post('/lecturer/students/{placementId}/evaluate', [LecturerEvaluationController::class, 'store'])->name('lecturer.students.evaluate');
    Route::post('/lecturer/students/{placementId}/report-approval', [LecturerEvaluationController::class, 'updateFinalReportStatus'])->name('lecturer.final_report.updateStatus');
    Route::get('/lecturer/students/{placementId}/grade-sheet', [LecturerEvaluationController::class, 'printGradeSheet'])->name('lecturer.students.grade_sheet');
    Route::get('/lecturer/evaluations/{placementId}/grade-sheet', [LecturerEvaluationController::class, 'printGradeSheet'])->name('lecturer.evaluations.grade_sheet');
    Route::post('/lecturer/students/{placementId}/consultations', [LecturerDashboardController::class, 'storeConsultation'])->name('lecturer.consultations.store');
    Route::delete('/lecturer/consultations/{id}', [LecturerDashboardController::class, 'destroyConsultation'])->name('lecturer.consultations.destroy');
});
