<?php

namespace App\Http\Requests;

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
        return Auth::check();
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
            'status' => ['required', Rule::in([City::STATUS_PUBLISHED, City::STATUS_UNPUBLISHED])],
            'image' => 'nullable|image|max:2048',
            'order' => [
                'nullable',
                'integer',
                Rule::unique('cities', 'order')->ignore($cityId)
            ],
        ];
    }
}