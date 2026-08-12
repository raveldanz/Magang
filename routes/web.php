<?php

use App\Http\Controllers\ProfileController;
// 1. Tambahkan import Controller Mahasiswa di sini
use App\Http\Controllers\Student\ProfileController as StudentProfileController; 
use App\Http\Controllers\Student\ApplicationController as StudentApplicationController;
use App\Http\Controllers\Admin\ApplicationController as AdminApplicationController;
use App\Http\Controllers\Student\LogbookController as StudentLogbookController;
use App\Http\Controllers\Pembimbing\DashboardController as PembimbingDashboardController;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
});

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

Route::middleware('auth')->group(function () {
    //Route Profile Bawaan Breeze
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Route Profil Mahasiswa
    Route::middleware(['role:mahasiswa'])->group(function () {
        Route::get('/student/profile', [StudentProfileController::class, 'edit'])->name('student.profile.edit');
        Route::post('/student/profile', [StudentProfileController::class, 'update'])->name('student.profile.update');
        
        Route::get('/student/application', [StudentApplicationController::class, 'create'])->name('student.application.create');
        Route::post('/student/application', [StudentApplicationController::class, 'store'])->name('student.application.store');

        Route::get('/student/logbook', [StudentLogbookController::class, 'index'])->name('student.logbook.index');
        Route::post('/student/logbook', [StudentLogbookController::class, 'store'])->name('student.logbook.store');
    });

    // Route Admin
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/admin/applications', [AdminApplicationController::class, 'index'])->name('admin.applications.index');
        Route::get('/admin/applications/{id}', [AdminApplicationController::class, 'show'])->name('admin.applications.show');
        Route::match(['put', 'patch'], '/admin/applications/{id}', [AdminApplicationController::class, 'updateStatus'])->name('admin.applications.updateStatus');    });

    // Route Pembimbing Lapangan
    Route::middleware(['role:pembimbing'])->group(function () {
    Route::get('/pembimbing/dashboard', [PembimbingDashboardController::class, 'index'])->name('pembimbing.dashboard');
    Route::get('/pembimbing/student/{placementId}', [PembimbingDashboardController::class, 'showStudent'])->name('pembimbing.student.detail');
    Route::put('/pembimbing/logbook/{logbookId}', [PembimbingDashboardController::class, 'updateLogbookStatus'])->name('pembimbing.logbook.updateStatus');
    });

    // Route Pengajuan Magang Mahasiswa
    Route::get('/student/application', [StudentApplicationController::class, 'create'])->name('student.application.create');
    Route::post('/student/application', [StudentApplicationController::class, 'store'])->name('student.application.store');

    // Logbook Mahasiswa
    Route::get('/student/logbook', [StudentLogbookController::class, 'index'])->name('student.logbook.index');
    Route::post('/student/logbook', [StudentLogbookController::class, 'store'])->name('student.logbook.store');
});

require __DIR__.'/auth.php';
