<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MasterUniversityApiController;
use Illuminate\Support\Facades\Route;

// Endpoint Publik
// Rate limit 'api-login' (lihat AppServiceProvider): 5x/menit per email+IP, 300x/menit per IP
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:api-login');

// API Pencarian Master Perguruan Tinggi Nasional (Kemdikbud / PDDikti)
Route::get('/master-universities/search', [MasterUniversityApiController::class, 'search'])->name('api.master-universities.search');

// Endpoint Terproteksi Token (Bearer Token)
Route::middleware(['auth:sanctum', 'active'])->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});
