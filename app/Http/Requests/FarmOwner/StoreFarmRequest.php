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
                    'deposit_rate' => 'nullable|numeric|min:0|max:100',
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
                    'offer.percentage' => 'required_with:offer|numeric|min:1|max:100',
                    'offer.start_date' => 'required_with:offer|date|after_or_equal:today',
                    'offer.end_date' => 'required_with:offer|date|after:offer.start_date',
                    'offer.is_active' => 'nullable|boolean',
                    
                    // NEW: Price-type specific unavailable dates validation
                    'not_available_dates' => 'nullable|array',
                    
                    // Support both old format (simple array) and new format (price-type specific)
                    // Old format: ["2025-08-15", "2025-08-20"] 
                    'not_available_dates.*' => 'sometimes|date_format:Y-m-d|after_or_equal:today',
                    
                    // New format: {"day_use": [...], "night": [...], "full_day": [...]}
                    'not_available_dates.day_use' => 'nullable|array',
                    'not_available_dates.day_use.*' => 'date_format:Y-m-d|after_or_equal:today',
                    'not_available_dates.night' => 'nullable|array', 
                    'not_available_dates.night.*' => 'date_format:Y-m-d|after_or_equal:today',
                    'not_available_dates.full_day' => 'nullable|array',
                    'not_available_dates.full_day.*' => 'date_format:Y-m-d|after_or_equal:today',
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
                
                // Validate deposit rate range
                if ($this->filled('deposit_rate') && $this->deposit_rate > 100) {
                    $validator->errors()->add('deposit_rate', __('farm.validation.deposit_rate.max'));
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
     * Validate not available dates for Step 5 (UPDATED for price-type specific)
     */
    private function validateNotAvailableDates($validator): void
    {
        if (!$this->filled('not_available_dates')) {
            return;
        }
        
        $dates = $this->not_available_dates;
        
        // Detect format: old (simple array) vs new (price-type specific)
        if ($this->isOldFormatUnavailableDates($dates)) {
            $this->validateOldFormatDates($validator, $dates);
        } else {
            $this->validateNewFormatDates($validator, $dates);
        }
    }

    /**
     * Check if dates format is old (simple array) or new (price-type specific)
     */
    private function isOldFormatUnavailableDates($data): bool
    {
        if (empty($data) || !is_array($data)) {
            return false;
        }

        // If it's a sequential array of dates, it's old format
        return array_keys($data) === range(0, count($data) - 1);
    }

    /**
     * Validate old format unavailable dates
     */
    private function validateOldFormatDates($validator, array $dates): void
    {
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
     * Validate new format unavailable dates (price-type specific)
     */
    private function validateNewFormatDates($validator, array $dates): void
    {
        $validPriceTypes = ['day_use', 'night', 'full_day'];
        
        foreach ($dates as $priceType => $priceTypeDates) {
            // Validate price type
            if (!in_array($priceType, $validPriceTypes)) {
                $validator->errors()->add("not_available_dates.{$priceType}", __('farm.validation.invalid_price_type', ['type' => $priceType]));
                continue;
            }
            
            if (!is_array($priceTypeDates)) {
                $validator->errors()->add("not_available_dates.{$priceType}", __('farm.validation.dates.must_be_array'));
                continue;
            }
            
            // Check for duplicates within this price type
            if (count($priceTypeDates) !== count(array_unique($priceTypeDates))) {
                $validator->errors()->add("not_available_dates.{$priceType}", __('farm.validation.dates.duplicates_not_allowed'));
            }
            
            // Validate each date
            foreach ($priceTypeDates as $index => $date) {
                try {
                    $carbonDate = \Carbon\Carbon::parse($date);
                    if ($carbonDate->isPast()) {
                        $validator->errors()->add("not_available_dates.{$priceType}.{$index}", __('farm.validation.dates.cannot_be_past', ['date' => $date]));
                    }
                } catch (\Exception $e) {
                    $validator->errors()->add("not_available_dates.{$priceType}.{$index}", __('farm.validation.dates.invalid_format', ['date' => $date]));
                }
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
        return array_merge(__('farm.validation'), [
            // Price-type specific messages
            'not_available_dates.day_use.*.date_format' => __('farm.validation.dates.day_use_invalid_format'),
            'not_available_dates.day_use.*.after_or_equal' => __('farm.validation.dates.day_use_future_only'),
            'not_available_dates.night.*.date_format' => __('farm.validation.dates.night_invalid_format'),
            'not_available_dates.night.*.after_or_equal' => __('farm.validation.dates.night_future_only'),
            'not_available_dates.full_day.*.date_format' => __('farm.validation.dates.full_day_invalid_format'),
            'not_available_dates.full_day.*.after_or_equal' => __('farm.validation.dates.full_day_future_only'),
            
            // Offer messages
            'offer.percentage.min' => __('farm.validation.offer.percentage_min'),
            'offer.percentage.max' => __('farm.validation.offer.percentage_max'),
            'offer.start_date.after_or_equal' => __('farm.validation.offer.start_date_future'),
            'offer.end_date.after' => __('farm.validation.offer.end_date_after_start'),
        ]);
    }

    /**
     * Get custom attribute names for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return array_merge(__('farm.attributes'), [
            'not_available_dates.day_use' => __('farm.attributes.day_use_unavailable_dates'),
            'not_available_dates.night' => __('farm.attributes.night_unavailable_dates'),
            'not_available_dates.full_day' => __('farm.attributes.full_day_unavailable_dates'),
        ]);
    }
}