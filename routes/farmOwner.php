<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FarmOwner\ApiFarmController;
use App\Http\Controllers\Api\FarmOwner\ApiFarmOwnerBookingController;
use App\Http\Controllers\Api\FarmOwner\ApiFarmOwnerWalletController;
use App\Http\Controllers\Api\FarmOwner\ApiFarmOwnerBankAccountController;

/*
|--------------------------------------------------------------------------
| Farm Owner API Routes
|--------------------------------------------------------------------------
*/

# Protected routes
Route::middleware('auth:sanctum')->group(function () {

    // ──────────────────────────────────Farms────────────────────────────────────────

    // Farm management (CRUD)
    Route::prefix('farms')->controller(ApiFarmController::class)->group(function () {
        // ──────────────────────────────────Main CRUD──────────────────────────────────────── 
        Route::get('/', 'index');                           // List farms by status
        Route::get('/{farm_id}', 'show');                  // Show specific farm
        Route::post('/', 'store');                          // Step-based store
        Route::delete('/{farm_id}', 'destroy');             // Delete farm

        // ──────────────────────────────────Image Management──────────────────────────────────────── 
        Route::post('/{farm_id}/images/main', 'uploadMainImage');           // Upload main image
        Route::post('/{farm_id}/images/gallery', 'uploadGalleryImages');    // Upload gallery images
        Route::get('/{farm_id}/images', 'getImages');                       // Get all farm images
        Route::delete('/{farm_id}/images/{image_id}', 'deleteImage');       // Delete specific image
    });

    // ──────────────────────────────────Bookings────────────────────────────────────────

    // Booking management for farm owners
    Route::prefix('bookings')->controller(ApiFarmOwnerBookingController::class)->group(function () {
        Route::get('/', 'index');                           // List all bookings for farm owner's farms
        Route::get('/statistics', 'statistics');            // Booking statistics
        Route::get('/recent', 'recent');                    // Recent bookings for dashboard
        Route::get('/{booking_id}', 'show');               // Show specific booking details
    });

    // ──────────────────────────────────Wallet & Earnings────────────────────────────────────────

    // Wallet management for farm owners
    Route::prefix('wallet')->controller(ApiFarmOwnerWalletController::class)->group(function () {
        // Wallet overview
        Route::get('/dashboard', 'dashboard');               // Complete wallet dashboard
        Route::get('/balance', 'balance');                   // Current balance info
        Route::get('/statistics', 'statistics');             // Wallet statistics

        // Transactions
        Route::get('/transactions', 'transactions');         // List wallet transactions
        Route::get('/transactionTypes', 'transactionTypes');         // List wallet transactions

        // Payment History (NEW)
        Route::get('/payment-history', 'paymentHistory');    // Get manual payment history
    });

    Route::prefix('bank-account')->controller(ApiFarmOwnerBankAccountController::class)->group(function () {
        Route::get('/', 'show');                    // Get current bank account
        Route::post('/', 'store');                  // Save/Update bank account
        Route::delete('/', 'destroy');              // Delete bank account
        Route::get('/types', 'accountTypes');       // Get available account types
    });

});