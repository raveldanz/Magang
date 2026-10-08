<?php

use App\Http\Controllers\University\DashboardController as UniversityDashboardController;
use App\Http\Controllers\University\LecturerController as UniversityLecturerController;
use App\Http\Controllers\University\LetterController as UniversityLetterController;
use App\Http\Controllers\University\ProfileController as UniversityProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Route Khusus Resmi Perguruan Tinggi (Universitas)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:universitas'])->group(function () {
    Route::get('/university/dashboard', [UniversityDashboardController::class, 'index'])->name('university.dashboard');
    Route::get('/university/export-students', [UniversityDashboardController::class, 'export'])->name('university.students.export');
    Route::get('/university/students/{placementId}', [UniversityDashboardController::class, 'showStudent'])->name('university.students.show');
    Route::post('/university/students/{application}/assign-advisor', [UniversityDashboardController::class, 'assignAdvisor'])->name('university.students.assign_advisor');
    Route::get('/university/students/{application}/letter', [UniversityLetterController::class, 'generateLetter'])->name('university.students.letter');

    // Profil & Kop Surat Kampus
    Route::get('/university/profile', [UniversityProfileController::class, 'index'])->name('university.profile.index');
    Route::match(['put', 'patch', 'post'], '/university/profile', [UniversityProfileController::class, 'update'])->name('university.profile.update');

    // Manajemen Dosen Pembimbing
    Route::get('/university/lecturers', [UniversityLecturerController::class, 'index'])->name('university.lecturers.index');
    Route::post('/university/lecturers', [UniversityLecturerController::class, 'store'])->name('university.lecturers.store');
    Route::match(['put', 'patch'], '/university/lecturers/{id}', [UniversityLecturerController::class, 'update'])->name('university.lecturers.update');
    Route::delete('/university/lecturers/{id}', [UniversityLecturerController::class, 'destroy'])->name('university.lecturers.destroy');
    Route::post('/university/lecturers/{id}/reset-password', [UniversityLecturerController::class, 'resetPassword'])->name('university.lecturers.reset_password');
});
