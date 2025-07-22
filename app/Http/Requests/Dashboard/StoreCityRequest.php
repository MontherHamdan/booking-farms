<?php

namespace App\Http\Requests\Dashboard;

use App\Models\City;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class StoreCityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'description_ar' => 'nullable|string|max:1000',
            'description_en' => 'nullable|string|max:1000',
            'status' => ['required', Rule::in([City::STATUS_PUBLISHED, City::STATUS_UNPUBLISHED])],
            'image' => 'required|image|max:2048',
            'order' => 'nullable|integer|unique:cities,order',
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'name_ar.required' => 'Arabic name is required.',
            'name_en.required' => 'English name is required.',
            'description_ar.max' => 'Arabic description cannot exceed 1000 characters.',
            'description_en.max' => 'English description cannot exceed 1000 characters.',
            'image.required' => 'City image is required.',
            'image.image' => 'The file must be an image.',
            'image.max' => 'Image size cannot exceed 2MB.',
            'latitude.between' => 'The latitude must be between -90 and 90 degrees.',
            'longitude.between' => 'The longitude must be between -180 and 180 degrees.',
        ];
    }
}