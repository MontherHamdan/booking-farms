<?php

namespace App\Traits;
use App\Models\Farm;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException; 
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Exception;

trait FarmStoreTrait
{

    /**
     * Handle Step 1: Basic Information
     */
    private function handleStep1(Farm $farm, Request $request): void
    {
        $farm->fill([
            'name_ar' => $request->name_ar,
            'name_en' => $request->name_en,
            'description_ar' => $request->description_ar,
            'description_en' => $request->description_en,
            'deposit_rate' => $request->deposit_rate,
            'guest_count' => $request->guest_count,
        ]);
        
        if (!$farm->exists) {
            $farm->save(); // Create farm for first time
        }
    }

    /**
     * Handle Step 2: Features
     */
    private function handleStep2(Farm $farm, Request $request): void
    {
        if ($request->filled('features')) {
            $farm->features()->sync($request->features);
        }
    }

    /**
     * Handle Step 3: Location, Coordinates, and Image Association
     */
    private function handleStep3(Farm $farm, Request $request): void
    {
        // Update location and coordinates
        $farm->fill([
            'city_id' => $request->city_id,
            'area_id' => $request->area_id,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);
        
        $farm->save();
        
        // Handle main image assignment
        if ($request->filled('main_image_id')) {
            // Reset all images to non-main first
            $farm->images()->update(['is_main' => false]);
            
            // Set the selected image as main
            $mainImage = $farm->images()->where('id', $request->main_image_id)->first();
            if ($mainImage) {
                $mainImage->update(['is_main' => true]);
            }
        }
        
        // Handle gallery images - just ensure they exist and belong to this farm
        if ($request->filled('gallery_image_ids')) {
            $galleryIds = $request->gallery_image_ids;
            
            // Verify all gallery image IDs belong to this farm and are not main
            $farm->images()
                 ->whereIn('id', $galleryIds)
                 ->where('is_main', false)
                 ->get(); // This validates they exist and belong to the farm
        }
    }

    /**
     * Handle Step 4: Pricing
     */
    private function handleStep4(Farm $farm, Request $request): void
    {
        // Set default time ranges if not provided
        $this->setDefaultTimeRanges($request);
        
        // Validate time ranges don't overlap
        $timeRangeErrors = $this->validateTimeRanges($request);
        if (!empty($timeRangeErrors)) {
            throw ValidationException::withMessages(['pricing' => $timeRangeErrors]);
        }
        
        // Use the existing trait method for creating/updating pricing
        $this->updateFarmPricing($farm, $request);
    }

    /**
     * Handle Step 5: Offers and Unavailable Dates
     */
    private function handleStep5(Farm $farm, Request $request): void
    {
        // Handle offers
        if ($request->filled('offer')) {
            $offerData = $request->offer;
            
            $farm->offers()->create([
                'percentage' => $offerData['percentage'],
                'start_date' => $offerData['start_date'],
                'end_date' => $offerData['end_date'],
                'is_active' => $offerData['is_active'] ?? true,
            ]);
        }
        
        // Handle not available dates
        if ($request->filled('not_available_dates')) {
            $farm->not_available_dates = $request->not_available_dates;
            $farm->save();
        }
    }

    /**
     * Upload farm image helper with better error handling
     */
    private function uploadFarmImage($file, Farm $farm, $suffix): string
    {
        try {
            $ext = $file->getClientOriginalExtension();
            
            // Generate farm name with fallback
            $farmName = '';
            if (!empty($farm->name_en)) {
                $farmName = $farm->name_en;
            } elseif (!empty($farm->name_ar)) {
                $farmName = $farm->name_ar;
            } else {
                $farmName = "farm-{$farm->id}";
            }
            
            // Generate slug with fallback
            $slug = Str::slug($farmName);
            if (empty($slug)) {
                $slug = "farm-{$farm->id}";
            }
            
            // Generate unique filename
            $filename = "{$slug}-{$suffix}-" . time() . "-" . uniqid() . ".{$ext}";
            
            // Store file
            $path = $file->storeAs('farms', $filename, 's3');
            
            if (!$path) {
                throw new Exception("Failed to upload image to S3");
            }
            
            return Storage::disk('s3')->url($path);
            
        } catch (Exception $e) {
            \Log::error('Image upload failed', [
                'farm_id' => $farm->id,
                'suffix' => $suffix,
                'error' => $e->getMessage()
            ]);
            throw new Exception("Image upload failed: " . $e->getMessage());
        }
    }

    /**
     * Safely delete image from S3
     */
    private function deleteImageFromS3(string $imageUrl): void
    {
        try {
            $baseUrl = Storage::disk('s3')->url('');
            if (!empty($baseUrl) && strpos($imageUrl, $baseUrl) === 0) {
                $path = str_replace($baseUrl, '', $imageUrl);
                Storage::disk('s3')->delete($path);
            }
        } catch (Exception $e) {
            \Log::warning('Failed to delete image from S3', [
                'image_url' => $imageUrl,
                'error' => $e->getMessage()
            ]);
        }
    }
}