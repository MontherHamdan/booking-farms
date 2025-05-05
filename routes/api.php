<?php

use App\Http\Controllers\Api\Auth\ApiAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::controller(ApiAuthController::class)->group(function () {
    Route::post('/login', 'login')->middleware('throttle:login');
    // Route::post('/register', 'register')->middleware('throttle:register');
    // Route::post('/verify-otp', 'verifyOtp')->middleware('throttle:verify');
    // Route::post('/resend-otp', 'resendOtp')->middleware('throttle:resend');
    Route::post('/register', 'register');
    Route::post('/verify-otp', 'verifyOtp');
    Route::post('/resend-otp', 'resendOtp');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [ApiAuthController::class, 'logout']);
});