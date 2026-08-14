<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Student\ProfileController as StudentProfileController; 
use App\Http\Controllers\Student\ApplicationController as StudentApplicationController;
use App\Http\Controllers\Admin\ApplicationController as AdminApplicationController;
use App\Http\Controllers\Student\LogbookController as StudentLogbookController;
use App\Http\Controllers\Admin\LogbookController as AdminLogbookController;
use App\Http\Controllers\Pembimbing\DashboardController as PembimbingDashboardController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Pembimbing\EvaluationController as PembimbingEvaluationController;
use App\Http\Controllers\Admin\AgencyProfileController as AdminAgencyProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Dashboard Pintar Berdasarkan Role
Route::get('/dashboard', function () {
    $user = Auth::user();

    if ($user->role === 'admin') {
        return redirect()->route('admin.applications.index');
    }

    if ($user->role === 'pembimbing') {
        return redirect()->route('pembimbing.dashboard');
    }

    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Route Publik Verifikasi QR Code Surat Balasan (Bisa di-scan oleh siapa saja tanpa login)
Route::get('/verify-letter/{id}', function ($id) {
    $application = \App\Models\Application::with(['user.studentProfile', 'unit', 'placement.pembimbing'])
        ->where('status', 'accepted')
        ->findOrFail($id);
    return view('verify_letter', compact('application'));
})->name('verify.letter');

// Route Publik Verifikasi QR Code Sertifikat Magang
Route::get('/verify-certificate/{id}', function ($id) {
    $placement = \App\Models\Placement::with(['application.user.studentProfile', 'application.unit', 'evaluation', 'pembimbing'])
        ->findOrFail($id);
    $application = $placement->application;
    return view('verify_letter', compact('application', 'placement'));
})->name('verify.certificate');

Route::middleware('auth')->group(function () {

    // Route Profile Bawaan Breeze
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

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

        Route::get('/student/final-report', [\App\Http\Controllers\Student\FinalReportController::class, 'index'])->name('student.final_report.index');
        Route::post('/student/final-report', [\App\Http\Controllers\Student\FinalReportController::class, 'store'])->name('student.final_report.store');
    });

    // ==========================================
    // 2. ROUTE KHUSUS ADMIN
    // ==========================================
    Route::middleware(['role:admin'])->group(function () {
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
    });

    // ==========================================
    // 3. ROUTE KHUSUS PEMBIMBING LAPANGAN
    // ==========================================
    Route::middleware(['role:pembimbing'])->group(function () {
        Route::get('/pembimbing/dashboard', [PembimbingDashboardController::class, 'index'])->name('pembimbing.dashboard');
        Route::get('/pembimbing/student/{placementId}', [PembimbingDashboardController::class, 'showStudent'])->name('pembimbing.student.detail');
        Route::put('/pembimbing/logbook/{logbookId}', [PembimbingDashboardController::class, 'updateLogbookStatus'])->name('pembimbing.logbook.updateStatus');

        // Route Monitoring / Penilaian
        Route::get('/pembimbing/student/{placementId}/evaluation', [PembimbingEvaluationController::class, 'create'])->name('pembimbing.evaluation.create');
        Route::post('/pembimbing/student/{placementId}/evaluation', [PembimbingEvaluationController::class, 'store'])->name('pembimbing.evaluation.store');

        // Route Verifikasi Laporan Akhir
        Route::put('/pembimbing/final-report/{reportId}', [PembimbingDashboardController::class, 'updateFinalReportStatus'])->name('pembimbing.final_report.updateStatus');
    });

});

require __DIR__.'/auth.php';