<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\FarmRating;

class StoreFarmRatingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'rating' => [
                'required',
                'numeric',
                'min:1',
                'max:5',
                function ($attribute, $value, $fail) {
                    if (!FarmRating::isValidRating($value)) {
                        $fail(__('farm.validation.rating.increments'));
                    }
                },
            ],
            'review' => [
                'nullable',
                'string',
                'max:500',
            ],
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return __('farm.validation.rating');
    }

    public function attributes(): array
    {
        return [
            'rating' => __('farm.attributes.rating'),
            'review' => __('farm.attributes.review'),
        ];
    }
}