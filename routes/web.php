<?php

use App\Http\Controllers\Dashboard\AreaController;
use App\Http\Controllers\Dashboard\Auth\AuthController;
use App\Http\Controllers\Dashboard\CouponController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\CityController;
use App\Http\Controllers\Dashboard\FeatureController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirect root to admin login
Route::get('/', function () {
    return redirect()->route('dashboard.login');
});

// ═══════════════════════════════════════════════════════════════════════════════════
//                                 AUTH ROUTES (Public)
// ═══════════════════════════════════════════════════════════════════════════════════

Route::prefix('dashboard')->name('dashboard.')->controller(AuthController::class)->group(function () {
    // Login routes
    Route::get('/login', 'showLoginForm')->name('login');
    Route::post('/login', 'login');
    
    // Logout route
    Route::post('/logout', 'logout')->name('logout');
});

// ═══════════════════════════════════════════════════════════════════════════════════
//                              DASHBOARD ROUTES (Protected)
// ═══════════════════════════════════════════════════════════════════════════════════

Route::prefix('dashboard')->name('dashboard.')->middleware(['auth'])->group(function () {
    
    // Dashboard home
    Route::get('/home', [DashboardController::class, 'index'])->name('home');
    
    // Cities management
    Route::resource('cities', CityController::class);
    
    // Areas management  
    Route::resource('areas', AreaController::class);
    
    // Features management
    Route::resource('features', FeatureController::class);
    
    // Coupons management
    Route::resource('coupons', CouponController::class);
    Route::get('coupons/{coupon}/usages', [CouponController::class, 'usages'])->name('coupons.usages');
    Route::patch('coupons/{coupon}/toggle-status', [CouponController::class, 'toggleStatus'])->name('coupons.toggle-status');
});