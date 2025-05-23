<?php

use App\Http\Controllers\Api\ApiFarmController;
use App\Http\Controllers\Api\ApiFeatureController;
use App\Http\Controllers\Api\Auth\ApiAuthController;
use App\Http\Controllers\Api\ApiCityController;
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

# Authentication routes
Route::controller(ApiAuthController::class)->group(function () {
    Route::post('/login', 'login')->middleware('throttle:login');
    Route::post('/register', 'register');
    Route::post('/verify-otp', 'verifyOtp');
    Route::post('/resend-otp', 'resendOtp');
});

# Public city routes
Route::get('/cities', [ApiCityController::class, 'index']);

# Public farm routes
Route::get('/farms', [ApiFarmController::class, 'index']);
Route::get('/farms/{farm}', [ApiFarmController::class, 'show']);

# Protected routes
Route::middleware('auth:sanctum')->group(function () {
    # User related routes
    Route::prefix('user')->group(function () {
        Route::post('/logout', [ApiAuthController::class, 'logout']);
    });
    
    # Feature management
    Route::get('/features', [ApiFeatureController::class, 'index']);

    # Farm management
    Route::prefix('farms')->controller(ApiFarmController::class)->group(function () {
        Route::post('/', 'store');
        Route::put('/{farm}', 'update');
        Route::delete('/{farm}', 'destroy');
    });
});
