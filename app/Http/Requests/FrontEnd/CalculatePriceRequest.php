<?php

namespace App\Http\Requests\FrontEnd;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CalculatePriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dates'      => 'required|array|min:1',
            'dates.*'    => 'required|date|after_or_equal:today',
            'price_type' => 'required|string|in:day_use,night,full_day',
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
