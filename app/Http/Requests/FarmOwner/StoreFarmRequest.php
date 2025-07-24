<?php

namespace App\Http\Requests\FarmOwner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StoreFarmRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $step = $this->input('step', 1);
        
        return $this->getStepValidationRules($step);
    }

    /**
     * Get validation rules for specific step
     */
    private function getStepValidationRules(int $step): array
    {
        switch ($step) {
            case 1:
                return [
                    'name_ar' => 'nullable|string|max:255',
                    'name_en' => 'nullable|string|max:255',
                    'description_ar' => 'nullable|string',
                    'description_en' => 'nullable|string',
                    'deposit_rate' => 'nullable|numeric|min:0',
                    'guest_count' => 'nullable|integer|min:1',
                ];
                
            case 2:
                return [
                    'features' => 'nullable|array',
                    'features.*' => 'exists:features,id',
                ];
                
            case 3:
                return [
                    'city_id' => 'nullable|exists:cities,id',
                    'area_id' => 'nullable|exists:areas,id',
                    'latitude' => 'nullable|numeric|between:-90,90',
                    'longitude' => 'nullable|numeric|between:-180,180',
                    'main_image_id' => 'nullable|integer|exists:farm_images,id',
                    'gallery_image_ids' => 'nullable|array',
                    'gallery_image_ids.*' => 'integer|exists:farm_images,id',
                ];
                
            case 4:
                return [
                    'day_use_pricing' => 'nullable|array',
                    'day_use_pricing.start_time' => 'nullable|date_format:H:i',
                    'day_use_pricing.end_time' => 'nullable|date_format:H:i',
                    'day_use_pricing.saturday_price' => 'nullable|numeric|min:0',
                    'day_use_pricing.sunday_price' => 'nullable|numeric|min:0',
                    'day_use_pricing.monday_price' => 'nullable|numeric|min:0',
                    'day_use_pricing.tuesday_price' => 'nullable|numeric|min:0',
                    'day_use_pricing.wednesday_price' => 'nullable|numeric|min:0',
                    'day_use_pricing.thursday_price' => 'nullable|numeric|min:0',
                    'day_use_pricing.friday_price' => 'nullable|numeric|min:0',
                    
                    'night_pricing' => 'nullable|array',
                    'night_pricing.start_time' => 'nullable|date_format:H:i',
                    'night_pricing.end_time' => 'nullable|date_format:H:i',
                    'night_pricing.saturday_price' => 'nullable|numeric|min:0',
                    'night_pricing.sunday_price' => 'nullable|numeric|min:0',
                    'night_pricing.monday_price' => 'nullable|numeric|min:0',
                    'night_pricing.tuesday_price' => 'nullable|numeric|min:0',
                    'night_pricing.wednesday_price' => 'nullable|numeric|min:0',
                    'night_pricing.thursday_price' => 'nullable|numeric|min:0',
                    'night_pricing.friday_price' => 'nullable|numeric|min:0',
                    
                    'full_day_pricing' => 'nullable|array',
                    'full_day_pricing.start_time' => 'nullable|date_format:H:i',
                    'full_day_pricing.end_time' => 'nullable|date_format:H:i',
                    'full_day_pricing.saturday_price' => 'nullable|numeric|min:0',
                    'full_day_pricing.sunday_price' => 'nullable|numeric|min:0',
                    'full_day_pricing.monday_price' => 'nullable|numeric|min:0',
                    'full_day_pricing.tuesday_price' => 'nullable|numeric|min:0',
                    'full_day_pricing.wednesday_price' => 'nullable|numeric|min:0',
                    'full_day_pricing.thursday_price' => 'nullable|numeric|min:0',
                    'full_day_pricing.friday_price' => 'nullable|numeric|min:0',
                ];
                
            case 5:
                return [
                    'offer' => 'nullable|array',
                    'offer.percentage' => 'required_with:offer|numeric|min:0|max:100',
                    'offer.start_date' => 'required_with:offer|date|after_or_equal:today',
                    'offer.end_date' => 'required_with:offer|date|after:offer.start_date',
                    'offer.is_active' => 'nullable|boolean',
                    
                    // Not available dates validation
                    'not_available_dates' => 'nullable|array',
                    'not_available_dates.*' => 'date|after_or_equal:today',
                ];
                
            default:
                return [];
        }
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $step = $this->input('step', 1);
        
        $validator->after(function ($validator) use ($step) {
            // Step 1 specific validation
            if ($step === 1) {
                // Ensure at least one name is provided
                if (empty($this->name_ar) && empty($this->name_en)) {
                    $validator->errors()->add('name', __('farm.at_least_one_name_required'));
                }
            }
            
            // Step 3 specific validation
            if ($step === 3) {
                $this->validateImageOwnership($validator);
                $this->validateCoordinates($validator);
            }

            // Step 5 specific validation
            if ($step === 5) {
                $this->validateNotAvailableDates($validator);
                $this->validateOfferDates($validator);
            }
        });
    }

    /**
     * Validate image ownership for Step 3
     */
    private function validateImageOwnership($validator): void
    {
        $farmId = $this->input('farm_id');
        
        if (!$farmId) {
            return; // No farm ID provided, skip validation
        }
        
        // Validate main image ownership
        if ($this->filled('main_image_id')) {
            $mainImageExists = DB::table('farm_images')
                ->join('farms', 'farm_images.farm_id', '=', 'farms.id')
                ->where('farm_images.id', $this->main_image_id)
                ->where('farms.id', $farmId)
                ->where('farms.user_id', Auth::id())
                ->exists();
                
            if (!$mainImageExists) {
                $validator->errors()->add('main_image_id', __('farm.main_image_ownership_error'));
            }
        }
        
        // Validate gallery images ownership
        if ($this->filled('gallery_image_ids')) {
            $galleryIds = $this->gallery_image_ids;
            
            $validGalleryCount = DB::table('farm_images')
                ->join('farms', 'farm_images.farm_id', '=', 'farms.id')
                ->whereIn('farm_images.id', $galleryIds)
                ->where('farms.id', $farmId)
                ->where('farms.user_id', Auth::id())
                ->count();
                
            if ($validGalleryCount !== count($galleryIds)) {
                $validator->errors()->add('gallery_image_ids', __('farm.gallery_images_ownership_error'));
            }
        }
    }

    /**
     * Validate coordinates - both should be provided together
     */
    private function validateCoordinates($validator): void
    {
        $hasLatitude = $this->filled('latitude');
        $hasLongitude = $this->filled('longitude');
        
        // If one coordinate is provided, both should be provided
        if ($hasLatitude && !$hasLongitude) {
            $validator->errors()->add('longitude', __('farm.validation.longitude.required_with_latitude'));
        }
        
        if ($hasLongitude && !$hasLatitude) {
            $validator->errors()->add('latitude', __('farm.validation.latitude.required_with_longitude'));
        }
        
        // Optional: Validate against city/area coordinates if available
        if ($hasLatitude && $hasLongitude && $this->filled('city_id')) {
            $this->validateCoordinatesAgainstLocation($validator);
        }
    }

    /**
     * Optional: Validate coordinates are reasonable for the selected city/area
     */
    private function validateCoordinatesAgainstLocation($validator): void
    {
        try {
            $city = \App\Models\City::find($this->city_id);
            
            if ($city && $city->hasCoordinates()) {
                $farmLat = (float)$this->latitude;
                $farmLng = (float)$this->longitude;
                $cityLat = (float)$city->latitude;
                $cityLng = (float)$city->longitude;
                
                // Calculate rough distance (simple approximation)
                $latDiff = abs($farmLat - $cityLat);
                $lngDiff = abs($farmLng - $cityLng);
                
                // If farm coordinates are more than ~50km from city center (roughly 0.45 degrees)
                if ($latDiff > 0.45 || $lngDiff > 0.45) {
                    $validator->errors()->add('coordinates', __('farm.validation.coordinates.too_far_from_city'));
                }
            }
        } catch (\Exception $e) {
            // Skip validation if there's any error - don't block the user
            \Log::warning('Coordinate validation against city failed', [
                'error' => $e->getMessage(),
                'city_id' => $this->city_id,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude
            ]);
        }
    }

    /**
     * Validate not available dates for Step 5
     */
    private function validateNotAvailableDates($validator): void
    {
        if (!$this->filled('not_available_dates')) {
            return;
        }
        
        $dates = $this->not_available_dates;
        
        // Check for duplicates
        if (count($dates) !== count(array_unique($dates))) {
            $validator->errors()->add('not_available_dates', __('farm.validation.dates.duplicates_not_allowed'));
        }

        // Check for valid date format and future dates
        foreach ($dates as $index => $date) {
            try {
                $carbonDate = \Carbon\Carbon::parse($date);
                if ($carbonDate->isPast()) {
                    $validator->errors()->add("not_available_dates.{$index}", __('farm.validation.dates.cannot_be_past', ['date' => $date]));
                }
            } catch (\Exception $e) {
                $validator->errors()->add("not_available_dates.{$index}", __('farm.validation.dates.invalid_format', ['date' => $date]));
            }
        }
    }

    /**
     * Validate offer dates for Step 5
     */
    private function validateOfferDates($validator): void
    {
        if (!$this->filled('offer')) {
            return;
        }
        
        $offer = $this->offer;
        if (isset($offer['start_date']) && isset($offer['end_date'])) {
            try {
                $startDate = \Carbon\Carbon::parse($offer['start_date']);
                $endDate = \Carbon\Carbon::parse($offer['end_date']);
                
                if ($endDate->lte($startDate)) {
                    $validator->errors()->add('offer.end_date', __('farm.validation.offer.end_date_after_start'));
                }
            } catch (\Exception $e) {
                $validator->errors()->add('offer', __('farm.validation.offer.invalid_date_format'));
            }
        }
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return __('farm.validation');
    }

    /**
     * Get custom attribute names for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return __('farm.attributes');
    }
}