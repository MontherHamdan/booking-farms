<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\City;

class CityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // You can add your own authorization logic here.
        // Returning `true` means "any authenticated user can make this request."
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Grab the raw route parameter
        $raw = $this->route('city');

        // If Laravel gave us a City instance, use its ID; otherwise cast the raw string to int
        $cityId = $raw instanceof City
            ? $raw->id
            : (int) $raw;

        return [
            'name'   => 'sometimes|required|string|max:255',
            'status' => [
                'sometimes',
                'required',
                Rule::in([City::STATUS_PUBLISHED, City::STATUS_UNPUBLISHED]),
            ],
            'image'  => 'sometimes|required|image|max:2048',
            'order'  => [
                'sometimes',
                'required',
                'integer',
                Rule::unique('cities', 'order')->ignore($cityId),
            ],
        ];
    }
}
