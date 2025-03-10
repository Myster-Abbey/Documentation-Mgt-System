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
// Auth routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/verify-account', [AuthController::class, 'verifyAccount']);
Route::post('/create', [AuthController::class, 'create']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/logout', [AuthController::class, 'logout']);

// Document routes - all users
Route::post('/documents', [DocumentManager::class, 'index']);
Route::post('/documents/{id}', [DocumentManager::class, 'show']);

// Document routes - admin only
Route::post('/store-documents', [DocumentManager::class, 'upload']);
// Route::get('/documents/{id}/print', [DocumentManager::class, 'print']);
// });

// Download request routes
Route::post('get-download-requests/', [DownloadRequestManager::class, 'index']);
Route::post('store-download-requests/', [DownloadRequestManager::class, 'store']);
Route::post('show-download-requests/{id}', [DownloadRequestManager::class, 'show']);
Route::post('update-download-requests/{id}', [DownloadRequestManager::class, 'update']);
Route::post('download-requests/{id}/archive', [DownloadRequestManager::class, 'archive']);
Route::post('download-requests/{id}', [DownloadRequestManager::class, 'cancel']);
// // });



// Fallback for undefined routes
Route::fallback(function () {
    return General::apiFailureResponse('Route not found', 404);
});
