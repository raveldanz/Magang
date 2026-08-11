<?php

use App\Http\Controllers\ProfileController;
// 1. Tambahkan import Controller Mahasiswa di sini
use App\Http\Controllers\Student\ProfileController as StudentProfileController; 
use App\Http\Controllers\Student\ApplicationController as StudentApplicationController;
use App\Http\Controllers\Admin\ApplicationController as AdminApplicationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    //Route Profile Bawaan Breeze
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Route Profil Mahasiswa
    Route::get('/student/profile', [StudentProfileController::class, 'edit'])->name('student.profile.edit');
    Route::post('/student/profile', [StudentProfileController::class, 'update'])->name('student.profile.update');

    // Route Pengajuan Magang Mahasiswa
    Route::get('/student/application', [StudentApplicationController::class, 'create'])->name('student.application.create');
    Route::post('/student/application', [StudentApplicationController::class, 'store'])->name('student.application.store');

    // Route Admin Magang
    Route::get('/admin/applications', [AdminApplicationController::class, 'index'])->name('admin.applications.index');
    Route::get('/admin/applications/{id}', [AdminApplicationController::class, 'show'])->name('admin.applications.show');
    Route::patch('/admin/applications/{id}/status', [AdminApplicationController::class, 'updateStatus'])->name('admin.applications.updateStatus');
});

require __DIR__.'/auth.php';
