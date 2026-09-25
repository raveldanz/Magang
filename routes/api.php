<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

// Endpoint Publik
// Rate limit 'api-login' (lihat AppServiceProvider): 5x/menit per email+IP, 300x/menit per IP
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:api-login');

// Endpoint Terproteksi Token (Bearer Token)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});