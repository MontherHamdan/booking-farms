<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFarmRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; 
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $farm = $this->route('farm');
        
        return [
            'city_id' => 'nullable|exists:cities,id',
            'name_ar' => 'nullable|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'passengers_count' => 'nullable|integer|min:1',
            'features' => 'nullable|array',
            'features.*' => 'exists:features,id',
            'main_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'delete_image_ids' => 'nullable|array',
            'delete_image_ids.*' => [
                'integer',
                Rule::exists('farm_images', 'id')->where(function ($query) use ($farm) {
                    $query->where('farm_id', $farm->id);
                }),
            ],
            
            // Not available dates validation
            'not_available_dates' => 'nullable|array',
            'not_available_dates.*' => 'date|after_or_equal:today',
            
            // Pricing validation - all optional
            'day_use_pricing' => 'nullable|array',
            'day_use_pricing.saturday_price' => 'nullable|numeric|min:0',
            'day_use_pricing.sunday_price' => 'nullable|numeric|min:0',
            'day_use_pricing.monday_price' => 'nullable|numeric|min:0',
            'day_use_pricing.tuesday_price' => 'nullable|numeric|min:0',
            'day_use_pricing.wednesday_price' => 'nullable|numeric|min:0',
            'day_use_pricing.thursday_price' => 'nullable|numeric|min:0',
            'day_use_pricing.friday_price' => 'nullable|numeric|min:0',
            
            'night_pricing' => 'nullable|array',
            'night_pricing.saturday_price' => 'nullable|numeric|min:0',
            'night_pricing.sunday_price' => 'nullable|numeric|min:0',
            'night_pricing.monday_price' => 'nullable|numeric|min:0',
            'night_pricing.tuesday_price' => 'nullable|numeric|min:0',
            'night_pricing.wednesday_price' => 'nullable|numeric|min:0',
            'night_pricing.thursday_price' => 'nullable|numeric|min:0',
            'night_pricing.friday_price' => 'nullable|numeric|min:0',
            
            'full_day_pricing' => 'nullable|array',
            'full_day_pricing.saturday_price' => 'nullable|numeric|min:0',
            'full_day_pricing.sunday_price' => 'nullable|numeric|min:0',
            'full_day_pricing.monday_price' => 'nullable|numeric|min:0',
            'full_day_pricing.tuesday_price' => 'nullable|numeric|min:0',
            'full_day_pricing.wednesday_price' => 'nullable|numeric|min:0',
            'full_day_pricing.thursday_price' => 'nullable|numeric|min:0',
            'full_day_pricing.friday_price' => 'nullable|numeric|min:0',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validate not available dates for duplicates
            if ($this->filled('not_available_dates')) {
                $dates = $this->not_available_dates;
                if (count($dates) !== count(array_unique($dates))) {
                    $validator->errors()->add('not_available_dates', 'Duplicate dates are not allowed in not available dates.');
                }

                // Check for valid date format and future dates
                foreach ($dates as $index => $date) {
                    try {
                        $carbonDate = \Carbon\Carbon::parse($date);
                        if ($carbonDate->isPast()) {
                            $validator->errors()->add("not_available_dates.{$index}", "Date {$date} cannot be in the past.");
                        }
                    } catch (\Exception $e) {
                        $validator->errors()->add("not_available_dates.{$index}", "Date {$date} is not a valid date format.");
                    }
                }
            }
        });
    }
}