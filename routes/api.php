<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DocumentManager;
use App\Http\Controllers\DownloadRequestManager;
use App\helper\General;
use App\Http\Controllers\apiCaller;
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

    // Download request routes
    // Route::prefix('download-requests')->group(function () {
        Route::get('download-requests/', [DownloadRequestManager::class, 'index']);
        Route::post('download-requests/', [DownloadRequestManager::class, 'store']);
        Route::get('download-requests/{id}', [DownloadRequestManager::class, 'show']);
        Route::put('download-requests/{id}', [DownloadRequestManager::class, 'update']);
        Route::put('download-requests/{id}/archive', [DownloadRequestManager::class, 'archive']);
        Route::delete('download-requests/{id}', [DownloadRequestManager::class, 'cancel']);
    // });
// });


// Fallback for undefined routes
Route::fallback(function () {
    return General::apiFailureResponse('Route not found', 404);
});

