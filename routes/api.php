<?php

use App\Http\Controllers\Api\ApiFarmController;
use App\Http\Controllers\Api\ApiFavoriteFarmController;
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
Route::get('/farms/{farm_id}', [ApiFarmController::class, 'show']);
Route::post('/farms/filter', [ApiFarmController::class, 'filter']);
Route::post('/farms/{farm}/calculate-price', [ApiFarmController::class, 'calculatePrice']);

# Protected routes
Route::middleware('auth:sanctum')->group(function () {
    # User related routes
    Route::post('/logout', [ApiAuthController::class, 'logout']);
    
    # Feature management
    Route::get('/features', [ApiFeatureController::class, 'index']);

    # Farm management
    Route::prefix('farms')->controller(ApiFarmController::class)->group(function () {
        Route::post('/', 'store');
        Route::put('/{farm}', 'update');
        Route::delete('/{farm}', 'destroy');
    });

    # Favorite farms management
    Route::prefix('favorites')->controller(ApiFavoriteFarmController::class)->group(function () {
        Route::get('/', 'index');                           
        Route::post('/farms/{farm_id}/toggle', 'toggle');      
    });
});