<?php

namespace App\Http\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Area;

class UpdateAreaRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'city_id' => ['required', 'integer', 'exists:cities,id'],
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:' . Area::STATUS_PUBLISHED . ',' . Area::STATUS_UNPUBLISHED],
            'order' => ['nullable', 'integer', 'min:1'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes()
    {
        return [
            'city_id' => 'city',
            'name_ar' => 'Arabic name',
            'name_en' => 'English name',
            'status' => 'status',
            'order' => 'order',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages()
    {
        return [
            'city_id.required' => 'Please select a city.',
            'city_id.exists' => 'The selected city is invalid.',
            'name_ar.required' => 'Arabic name is required.',
            'name_en.required' => 'English name is required.',
            'status.required' => 'Status is required.',
            'status.in' => 'Status must be either published or unpublished.',
        ];
    }
}