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
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'city_id.exists' => 'Selected city does not exist',
            'name_ar.string' => 'Arabic name must be text',
            'name_ar.max' => 'Arabic name cannot exceed 255 characters',
            'name_en.string' => 'English name must be text',
            'name_en.max' => 'English name cannot exceed 255 characters',
            'description_ar.string' => 'Arabic description must be text',
            'description_en.string' => 'English description must be text',
            'passengers_count.integer' => 'Passengers count must be a number',
            'passengers_count.min' => 'Passengers count must be at least 1',
            'features.*.exists' => 'One or more selected features do not exist',
            'main_image.image' => 'Main image must be a valid image file',
            'main_image.mimes' => 'Main image must be jpeg, png, jpg, or gif format',
            'main_image.max' => 'Main image size cannot exceed 2MB',
            'images.*.image' => 'Uploaded files must be images',
            'images.*.mimes' => 'Images must be jpeg, png, jpg, or gif format',
            'images.*.max' => 'Images may not be larger than 2MB',
            'delete_image_ids.*.exists' => 'One or more image IDs are invalid',
            
            // Pricing validation messages
            '*.*.numeric' => 'Price must be a valid number',
            '*.*.min' => 'Price cannot be negative',
        ];
    }
}