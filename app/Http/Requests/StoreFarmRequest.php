<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFarmRequest extends FormRequest
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
        return [
            'city_id' => 'nullable|exists:cities,id',
            'name_ar' => 'nullable|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'passengers_count' => 'nullable|integer|min:1',
            'features' => 'nullable|array',
            'features.*' => 'exists:features,id',
            'features_string' => 'nullable|string',
            'main_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            
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
            // Ensure at least one name is provided
            if (empty($this->name_ar) && empty($this->name_en)) {
                $validator->errors()->add('name', 'At least one name (Arabic or English) is required.');
            }

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

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'city_id.exists' => 'Selected city does not exist.',
            'name_ar.string' => 'Arabic name must be text.',
            'name_ar.max' => 'Arabic name cannot exceed 255 characters.',
            'name_en.string' => 'English name must be text.',
            'name_en.max' => 'English name cannot exceed 255 characters.',
            'description_ar.string' => 'Arabic description must be text.',
            'description_en.string' => 'English description must be text.',
            'passengers_count.integer' => 'Passengers count must be a number.',
            'passengers_count.min' => 'Passengers count must be at least 1.',
            'features.*.exists' => 'One or more selected features do not exist.',
            'main_image.image' => 'Main image must be a valid image file.',
            'main_image.mimes' => 'Main image must be jpeg, png, jpg, or gif format.',
            'main_image.max' => 'Main image size cannot exceed 2MB.',
            'images.*.image' => 'All gallery images must be valid image files.',
            'images.*.mimes' => 'Gallery images must be jpeg, png, jpg, or gif format.',
            'images.*.max' => 'Gallery image size cannot exceed 2MB.',
            
            // Not available dates validation messages
            'not_available_dates.array' => 'Not available dates must be an array.',
            'not_available_dates.*.date' => 'Each not available date must be a valid date.',
            'not_available_dates.*.after_or_equal' => 'Not available dates must be today or in the future.',
            
            // Pricing validation messages
            '*.*.numeric' => 'Price must be a valid number.',
            '*.*.min' => 'Price cannot be negative.',
        ];
    }
}