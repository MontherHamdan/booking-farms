<?php

use App\Http\Controllers\Dashboard\AreaController;
use App\Http\Controllers\Dashboard\Auth\AuthController;
use App\Http\Controllers\Dashboard\CouponController;
use App\Http\Controllers\Dashboard\WalletController;
use App\Http\Controllers\Dashboard\SettingsController;
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
    Route::get('/login', 'showLoginForm')->name('login');
    Route::post('/login', 'login');
    Route::post('/logout', 'logout')->name('logout');
});

// ═══════════════════════════════════════════════════════════════════════════════════
//                              DASHBOARD ROUTES (Protected)
// ═══════════════════════════════════════════════════════════════════════════════════

Route::prefix('dashboard')->name('dashboard.')->middleware(['auth'])->group(function () {
    
    // Dashboard home
    Route::get('/home', [DashboardController::class, 'index'])->name('home');
    
    // Basic Management
    Route::resource('cities', CityController::class);
    Route::resource('areas', AreaController::class);
    Route::resource('features', FeatureController::class);
    Route::resource('coupons', CouponController::class);
    Route::get('coupons/{coupon}/usages', [CouponController::class, 'usages'])->name('coupons.usages');
    Route::patch('coupons/{coupon}/toggle-status', [CouponController::class, 'toggleStatus'])->name('coupons.toggle-status');
    
    // ═══════════════════════════════════════════════════════════════════════════════════
    //                              WALLET MANAGEMENT
    // ═══════════════════════════════════════════════════════════════════════════════════
    
    Route::prefix('wallet')->name('wallet.')->controller(WalletController::class)->group(function () {
        // Dashboard & Overview
        Route::get('/', 'index')->name('index');
        
        // Wallets
        Route::get('/wallets', 'wallets')->name('wallets');
        Route::get('/wallets/{wallet}', 'show')->name('wallets.show');
        Route::post('/wallets/{wallet}/commission-rate', 'updateCommissionRate')->name('wallets.commission-rate');
        Route::post('/wallets/{wallet}/adjustment', 'addAdjustment')->name('wallets.adjustment');
        
        // Payments
        Route::get('/pending-payments', 'pendingPayments')->name('pending-payments');
        Route::post('/process-payment/{user}', 'processPayment')->name('process-payment');
        
        // Transactions
        Route::get('/transactions', 'transactions')->name('transactions');
        
        // Export
        Route::get('/export/wallets', 'exportWallets')->name('export.wallets');
        Route::get('/export/payments', 'exportPayments')->name('export.payments');
    });
    
    // ═══════════════════════════════════════════════════════════════════════════════════
    //                              PLATFORM SETTINGS
    // ═══════════════════════════════════════════════════════════════════════════════════
    
    Route::prefix('settings')->name('settings.')->controller(SettingsController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/payment-settings', 'updatePaymentSettings')->name('payment-settings.update');
        Route::post('/commission-settings', 'updateCommissionSettings')->name('commission-settings.update');
    });
});