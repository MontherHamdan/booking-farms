<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FrontEnd\ApiFarmController;
use App\Http\Controllers\Api\Users\ApiFavoriteFarmController;
use App\Http\Controllers\Api\Users\ApiRatingFarmController;
use App\Http\Controllers\Api\FrontEnd\ApiFeatureController;
use App\Http\Controllers\Api\Auth\ApiAuthController;
use App\Http\Controllers\Api\FrontEnd\ApiCityController;
use App\Http\Controllers\Api\Users\ApiUserProfileController;

/*
|--------------------------------------------------------------------------
| User API Routes
|--------------------------------------------------------------------------
*/
Route::post('/ratings/farms/{farmId}', [ApiRatingFarmController::class, 'getRatings']);

// Authentication (public)
Route::controller(ApiAuthController::class)->group(function () {
    Route::post('/login',        'login')->middleware('throttle:login');
    Route::post('/register',     'register');
    Route::post('/verify-otp',   'verifyOtp');
    Route::post('/resend-otp',   'resendOtp');
});

// public cities and areas 
Route::prefix('cities')->controller(ApiCityController::class)->group(function () {
    Route::get('/', 'index'); // Cities with images
    Route::get('/basic', 'basic'); // Cities without images
    Route::get('/{cityId}/areas', 'getAreasByCity');
});

// Public farm listing / detail / filter / calculate‐price / search
Route::prefix('farms')->controller(ApiFarmController::class)->group(function () {
    Route::get('/',                    'index');
    Route::get('/filter-fields',       'getFilterFields');
    Route::get('/{farm_id}',           'show');
    Route::post('/filter',             'filter');
    Route::post('/search',             'search');  
    Route::post('/{farm}/calculate-price', 'calculatePrice');
});

// Features listing
Route::get('/features', [ApiFeatureController::class, 'index']);

// ★ Public Rating Farms - Get Ratings
Route::get('/ratings/farms/{farmId}', [ApiRatingFarmController::class, 'getRatings']);

# Protected routes
Route::middleware('auth:sanctum')->group(function () {

    // ───────────────────────────────User───────────────────────────────────────────
    // User‐related (under /users)
    Route::prefix('users')->controller(ApiUserProfileController::class)->group(function () {
        Route::get('/profile',        'profile');
        Route::get('/avatar',        'getAvatar');
        Route::put('/update-profile', 'updateProfile');
        Route::post('/update-avatar', 'updateAvatar');
        Route::delete('/delete-avatar','deleteAvatar');
    });

    // Search History - for authenticated users only
    Route::get('/search-history', [ApiFarmController::class, 'getSearchHistory']);
    Route::delete('search-history/{historyId}', [ApiFarmController::class, 'deleteSearchHistoryItem']);
    Route::delete('search-history', [ApiFarmController::class, 'clearSearchHistory']);

    // Logout
    Route::post('/logout', [ApiAuthController::class, 'logout']);

    // ──────────────────────────────────Farms────────────────────────────────────────

    // ★ Favorite Farms
    Route::prefix('favorites')->controller(ApiFavoriteFarmController::class)->group(function () {
        Route::get('/', 'index');                           
        Route::post('/farms/{farm_id}/toggle', 'toggle');      
    });

    // ★ Rating Farms   
    Route::prefix('ratings')->controller(ApiRatingFarmController::class)->group(function () {
        Route::post('/farms/{farmId}', 'storeRating');
        Route::put('/farms/{farmId}',  'updateRating');
        Route::delete('/farms/{farmId}', 'deleteRating');
        Route::get('/farms/{farmId}/user', 'getUserRating');
    });
});