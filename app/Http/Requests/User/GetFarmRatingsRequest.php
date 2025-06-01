<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class GetFarmRatingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'per_page'   => 'nullable|integer|min:1|max:100',
            'sort_by'    => 'nullable|in:newest,oldest,highest_rating',
            'star_filter'=> 'nullable|integer|min:1|max:5',
        ];
    }

    public function messages(): array
    {
        return __('farm.validation.ratings');
    }

    public function attributes(): array
    {
        return __('farm.attributes.ratings');
    }
}
