<?php

use App\Http\Controllers\Api\Auth\ApiAuthController;
use App\Http\Controllers\Api\CityController;
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
Route::get('/cities', [CityController::class, 'index']);

# Protected routes
Route::middleware('auth:sanctum')->group(function () {
    # User related routes
    Route::prefix('user')->group(function () {
        Route::post('/logout', [ApiAuthController::class, 'logout']);
    });
    
    # City management
    Route::prefix('cities')->controller(CityController::class)->group(function () {
        Route::put('/{city_id}', 'update');
        Route::post('/', 'store');
        Route::delete('/{city_id}', 'destroy'); 
        Route::get('/{city}', 'show');
    });
});