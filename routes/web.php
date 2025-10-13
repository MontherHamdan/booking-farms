<?php

use App\Http\Controllers\Dashboard\AreaController;
use App\Http\Controllers\Dashboard\Auth\AuthController;
use App\Http\Controllers\Dashboard\CouponController;
use App\Http\Controllers\Dashboard\WalletController;
use App\Http\Controllers\Dashboard\SettingsController;
use App\Http\Controllers\Dashboard\FarmController;
use App\Http\Controllers\Dashboard\BookingController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\CityController;
use App\Http\Controllers\Dashboard\FeatureController;
use App\Http\Controllers\Dashboard\FarmOwnerApplicationController;

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

Route::prefix('dashboard')->name('dashboard.')->middleware(['auth', 'admin'])->group(function () {
    
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
    //                              FARM MANAGEMENT
    // ═══════════════════════════════════════════════════════════════════════════════════
    
    Route::prefix('farms')->name('farms.')->controller(FarmController::class)->group(function () {
        // Main CRUD
        Route::get('/', 'index')->name('index');
        Route::get('/{farm}', 'show')->name('show');
        Route::get('/{farm}/edit', 'edit')->name('edit');
        Route::put('/{farm}', 'update')->name('update');
        
        // Status management
        Route::post('/{farm}/status', 'updateStatus')->name('update-status');
        Route::post('/bulk-status', 'bulkStatusUpdate')->name('bulk-status');
        
        // Image management
        Route::delete('/{farm}/images/{image}', 'deleteImage')->name('delete-image');
        
        // AJAX endpoints
        Route::get('/cities/{city}/areas', 'getAreasByCity')->name('areas-by-city');
    });
    
    // ═══════════════════════════════════════════════════════════════════════════════════
    //                              BOOKING MANAGEMENT (Enhanced)
    // ═══════════════════════════════════════════════════════════════════════════════════
    
    Route::prefix('bookings')->name('bookings.')->controller(BookingController::class)->group(function () {
        // Main CRUD (Enhanced with Edit/Update)
        Route::get('/', 'index')->name('index');
        Route::get('/{booking}', 'show')->name('show');
        Route::get('/{booking}/edit', 'edit')->name('edit');              // NEW: Edit form
        Route::put('/{booking}', 'update')->name('update');               // NEW: Update booking
        
        // Status management (Enhanced)
        Route::post('/{booking}/status', 'updateStatus')->name('update-status');
        
        // Reports and analytics
        Route::get('/reports/analytics', 'reports')->name('reports');
        Route::get('/statistics', 'statistics')->name('statistics');
        
        // Export
        Route::get('/export/csv', 'export')->name('export');
        
        // Additional booking management endpoints
        Route::post('/{booking}/payment-status', 'updatePaymentStatus')->name('update-payment-status'); // NEW: Separate payment status update
        Route::post('/bulk-update', 'bulkUpdate')->name('bulk-update');                                    // NEW: Bulk operations
    });
    
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

    // ═══════════════════════════════════════════════════════════════════════════════════
    //                              FARM OWNER APPLICATION
    // ═══════════════════════════════════════════════════════════════════════════════════
    Route::prefix('farm-owner-applications')->name('farm-owner-applications.')->controller(FarmOwnerApplicationController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{id}', 'show')->name('show');
        Route::post('/{id}/verify', 'verify')->name('verify');
        Route::get('/statistics', 'statistics')->name('statistics');
    });
});