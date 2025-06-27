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
        $rules = [
            'price_type' => 'required|string|in:day_use,night,full_day',
            'dates'      => 'required|array|min:1',
            'dates.*'    => 'required|date|after_or_equal:today',
        ];

        // Add specific date count validation based on price type
        $priceType = $this->input('price_type');
        
        if (in_array($priceType, ['day_use', 'night'])) {
            // day_use and night must have exactly 1 date
            $rules['dates'] = 'required|array|size:1';
        } elseif ($priceType === 'full_day') {
            // full_day can have 1 or 2 dates (single day or date range)
            $rules['dates'] = 'required|array|min:1|max:2';
            
            // If 2 dates provided, add validation for date range
            if (count($this->input('dates', [])) === 2) {
                $rules['dates'] = 'required|array|size:2';
                // You could add custom validation here to ensure first date <= second date
            }
        }

        return $rules;
    }

    public function messages(): array
    {
        return array_merge(__('farm.validation'), [
            'dates.size' => $this->getDatesSizeMessage(),
            'dates.max' => __('farm.validation.dates.max'),
        ]);
    }

    public function attributes(): array
    {
        return __('farm.attributes');
    }

    /**
     * Get appropriate message for dates.size validation
     */
    private function getDatesSizeMessage(): string
    {
        $priceType = $this->input('price_type');
        
        if ($priceType === 'day_use') {
            return __('farm.validation.dates.day_use_single');
        }
        
        if ($priceType === 'night') {
            return __('farm.validation.dates.night_single');
        }
        
        return __('farm.validation.dates.size');
    }

    /**
     * Custom validation logic
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $dates = $this->input('dates', []);
            $priceType = $this->input('price_type');

            // If full_day with 2 dates, ensure first date <= second date
            if ($priceType === 'full_day' && count($dates) === 2) {
                $startDate = $dates[0] ?? null;
                $endDate = $dates[1] ?? null;

                if ($startDate && $endDate && $startDate > $endDate) {
                    $validator->errors()->add('dates', __('farm.validation.dates.date_range_invalid'));
                }
            }
        });
    }
}