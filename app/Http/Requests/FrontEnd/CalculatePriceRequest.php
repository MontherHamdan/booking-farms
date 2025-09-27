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
            }
        }

        return $rules;
    }

    public function messages(): array
    {
        $farmValidation = __('farm.validation');
        
        return [
            // Use the flat structure from your language files
            'dates.size' => $this->getDatesSizeMessage(),
            'dates.max' => $farmValidation['dates.max'] ?? 'Full day price type can have maximum 2 dates for date range',
            'dates.min' => $farmValidation['dates.min'] ?? 'At least one date must be selected',
            'dates.required' => $farmValidation['dates.required'] ?? 'Dates are required',
            'dates.array' => $farmValidation['dates.array'] ?? 'Dates must be an array',
            'dates.*.required' => $farmValidation['dates.*.required'] ?? 'Date is required',
            'dates.*.date' => $farmValidation['dates.*.date'] ?? 'Date must be a valid date',
            'dates.*.after_or_equal' => $farmValidation['dates.*.after_or_equal'] ?? 'Date must be today or in the future',
            'price_type.required' => $farmValidation['price_type.required'] ?? 'Price type is required',
            'price_type.string' => $farmValidation['price_type.string'] ?? 'Price type must be a string',
            'price_type.in' => $farmValidation['price_type.in'] ?? 'The selected price type is invalid',
        ];
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
        $validationMessages = __('farm.validation');
        
        if ($priceType === 'day_use') {
            return $validationMessages['dates.day_use_single'] ?? 'Day use price type must have exactly 1 date';
        }
        
        if ($priceType === 'night') {
            return $validationMessages['dates.night_single'] ?? 'Night price type must have exactly 1 date';
        }
        
        return $validationMessages['dates.size'] ?? 'Invalid number of dates for the selected price type';
    }

    /**
     * Custom validation logic
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $dates = $this->input('dates', []);
            $priceType = $this->input('price_type');
            $validationMessages = __('farm.validation');

            // If full_day with 2 dates, ensure first date <= second date
            if ($priceType === 'full_day' && count($dates) === 2) {
                $startDate = $dates[0] ?? null;
                $endDate = $dates[1] ?? null;

                if ($startDate && $endDate && $startDate > $endDate) {
                    $message = $validationMessages['dates.date_range_invalid'] ?? 'Start date must be before or equal to end date';
                    $validator->errors()->add('dates', $message);
                }
            }

            // Validate dates are not duplicated
            if (count($dates) !== count(array_unique($dates))) {
                $message = $validationMessages['dates.duplicates_not_allowed'] ?? 'Duplicate dates are not allowed';
                $validator->errors()->add('dates', $message);
            }
        });
    }
}