<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FarmOwner\ApiFarmController;

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
        Route::get('/', 'index');                          // List farms by status
        Route::get('/{farm_id}', 'show');                  // Show specific farm
        Route::post('/', 'store');                         // Step-based store
        Route::delete('/{farm_id}', 'destroy');            // Delete farm
        
        // ──────────────────────────────────Image Management────────────────────────────────────────
        Route::post('/{farm_id}/images/main', 'uploadMainImage');         // Upload main image
        Route::post('/{farm_id}/images/gallery', 'uploadGalleryImages');  // Upload gallery images
        Route::get('/{farm_id}/images', 'getImages');                     // Get all farm images
        Route::delete('/{farm_id}/images/{image_id}', 'deleteImage');     // Delete specific image
    });

});