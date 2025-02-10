<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DocumentManager;
use App\helper\General;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/create', [AuthController::class, 'create']);
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/verify-account', [AuthController::class, 'verifyAccount']);

// Protected routes
// Route::middleware(['auth'])->group(function () {
    // Auth routes
    Route::post('/send-otp', [AuthController::class, 'sendOtp']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Document routes - all users
    Route::get('/documents', [DocumentManager::class, 'index']);
    Route::get('/documents/{id}', [DocumentManager::class, 'show']);

    // Document routes - admin only
    // Route::middleware(['admin'])->group(function () {
        Route::post('/documents', [DocumentManager::class, 'upload']);
        Route::get('/documents/{id}/print', [DocumentManager::class, 'print']);
    // });
// });

// Fallback for undefined routes
Route::fallback(function () {
    return General::apiFailureResponse('Route not found', 404);
});

