<?php

namespace App\Http\Requests\FrontEnd;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class FilterFarmRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'city_id'          => 'nullable|array',
            'city_id.*'        => 'integer|exists:cities,id',
            'min_price'        => 'nullable|numeric|min:0',
            'max_price'        => 'nullable|numeric|min:0',
            'has_offer'        => 'nullable|boolean',
            'available_time'   => 'nullable|array',
            'available_time.*' => 'string|in:day_use,night,full_day',
            'date'             => 'nullable|date_format:Y-m-d|after_or_equal:today',
            'start_date'       => 'nullable|date_format:Y-m-d|after_or_equal:today',
            'end_date'         => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
            'features'         => 'nullable|array',
            'features.*'       => 'integer|exists:features,id',
            'ratings'          => 'nullable|array',
            'ratings.*'        => 'integer|in:1,2,3,4,5',
            'passenger_count'  => 'nullable|integer|min:1',
            'sort_by'          => 'nullable|string|in:lowest_price,highest_price,highest_rating,lowest_rating',
            'per_page'         => 'nullable|integer|min:1|max:100',
        ];
    }

    public function messages(): array
    {
        return __('farm.validation');
    }

    public function attributes(): array
    {
        return __('farm.attributes');
    }
}