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
                    $validator->errors()->add('name', __('farm.validation.at_least_one_name_required'));
                }
            }
            
            // Step 3 specific validation
            if ($step === 3) {
                $this->validateImageOwnership($validator);
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
        return [
            // Use localized messages from language files
            'city_id.exists' => __('farm.validation.city_id.*.exists'),
            'area_id.exists' => __('farm.validation.area_id.*.exists'),
            'name_ar.string' => __('farm.validation.name_ar.string'),
            'name_ar.max' => __('farm.validation.name_ar.max'),
            'name_en.string' => __('farm.validation.name_en.string'),
            'name_en.max' => __('farm.validation.name_en.max'),
            'description_ar.string' => __('farm.validation.description_ar.string'),
            'description_en.string' => __('farm.validation.description_en.string'),
            'deposit_rate.numeric' => __('farm.validation.deposit_rate.numeric'),
            'deposit_rate.min' => __('farm.validation.deposit_rate.min'),
            'guest_count.integer' => __('farm.validation.guest_count.integer'),
            'guest_count.min' => __('farm.validation.guest_count.min'),
            'features.*.exists' => __('farm.validation.features.*.exists'),
            
            // Image validation messages
            'main_image_id.integer' => __('farm.validation.main_image_id.integer'),
            'main_image_id.exists' => __('farm.validation.main_image_id.exists'),
            'gallery_image_ids.array' => __('farm.validation.gallery_image_ids.array'),
            'gallery_image_ids.*.integer' => __('farm.validation.gallery_image_ids.*.integer'),
            'gallery_image_ids.*.exists' => __('farm.validation.gallery_image_ids.*.exists'),
            
            // Pricing validation messages
            '*.*.numeric' => __('farm.validation.*.*.numeric'),
            '*.*.min' => __('farm.validation.*.*.min'),
            'start_time.date_format' => __('farm.validation.start_time.date_format'),
            'end_time.date_format' => __('farm.validation.end_time.date_format'),
            
            // Offer validation messages
            'offer.array' => __('farm.validation.offer.array'),
            'offer.percentage.required_with' => __('farm.validation.offer.percentage.required_with'),
            'offer.percentage.numeric' => __('farm.validation.offer.percentage.numeric'),
            'offer.percentage.min' => __('farm.validation.offer.percentage.min'),
            'offer.percentage.max' => __('farm.validation.offer.percentage.max'),
            'offer.start_date.required_with' => __('farm.validation.offer.start_date.required_with'),
            'offer.start_date.date' => __('farm.validation.offer.start_date.date'),
            'offer.start_date.after_or_equal' => __('farm.validation.offer.start_date.after_or_equal'),
            'offer.end_date.required_with' => __('farm.validation.offer.end_date.required_with'),
            'offer.end_date.date' => __('farm.validation.offer.end_date.date'),
            'offer.end_date.after' => __('farm.validation.offer.end_date.after'),
            'offer.is_active.boolean' => __('farm.validation.offer.is_active.boolean'),
            
            // Not available dates validation messages
            'not_available_dates.array' => __('farm.validation.not_available_dates.array'),
            'not_available_dates.*.date' => __('farm.validation.not_available_dates.*.date'),
            'not_available_dates.*.after_or_equal' => __('farm.validation.not_available_dates.*.after_or_equal'),
        ];
    }
}