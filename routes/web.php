<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Route::get('/otp', function () {
//     return view('emails.otp');
// });

// Add this route for testing
// Route::get('/email/preview', function () {
//     $user = new stdClass();
//     $user->name = 'Test User';
//     $otp = '123456';

//     return view('emails.otp', compact('user', 'otp'));
// });
