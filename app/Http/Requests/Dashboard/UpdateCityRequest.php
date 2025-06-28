<?php

namespace App\Http\Requests\Dashboard;

use App\Models\City;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class UpdateCityRequest extends FormRequest
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
        $cityId = $this->route()->parameter('city') ?? $this->route()->parameter('city_id');
        
        return [
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'description_ar' => 'nullable|string|max:1000',
            'description_en' => 'nullable|string|max:1000',
            'status' => ['required', Rule::in([City::STATUS_PUBLISHED, City::STATUS_UNPUBLISHED])],
            'image' => 'nullable|image|max:2048',
            'order' => [
                'nullable',
                'integer',
                Rule::unique('cities', 'order')->ignore($cityId)
            ],
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
            'image.image' => 'The file must be an image.',
            'image.max' => 'Image size cannot exceed 2MB.',
        ];
    }
}