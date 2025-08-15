<?php

namespace App\Http\Requests\FrontEnd;

use Illuminate\Foundation\Http\FormRequest;

class ValidateCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'coupon_code' => ['required', 'string', 'max:20', 'regex:/^[A-Z0-9]+$/'],
            'dates' => ['nullable', 'array', 'min:1'],
            'dates.*' => ['date_format:Y-m-d', 'after_or_equal:today'],
            'price_type' => ['nullable', 'string', 'in:day_use,night,full_day'],
        ];

        // Add specific date count validation based on price type if provided
        $priceType = $this->input('price_type');
        $dates = $this->input('dates', []);
        
        if ($priceType && !empty($dates)) {
            if (in_array($priceType, ['day_use', 'night'])) {
                // day_use and night must have exactly 1 date
                $rules['dates'] = ['nullable', 'array', 'size:1'];
            } elseif ($priceType === 'full_day') {
                // full_day can have 1 or 2 dates (single day or date range)
                $rules['dates'] = ['nullable', 'array', 'min:1', 'max:2'];
                
                // If 2 dates provided, add validation for date range
                if (count($dates) === 2) {
                    $rules['dates'] = ['nullable', 'array', 'size:2'];
                }
            }
        }

        return $rules;
    }

    public function messages(): array
    {
        return array_merge(
            __('farm.validation'), 
            __('booking.validation'), 
            [
                'coupon_code.required' => __('coupon.validation.code_required'),
                'coupon_code.regex' => __('coupon.validation.invalid_format'),
                'dates.size' => $this->getDatesSizeMessage(),
                'dates.max' => __('farm.validation.dates.max'),
            ]
        );
    }

    public function attributes(): array
    {
        return array_merge(__('farm.attributes'), __('booking.attributes'));
    }

    protected function prepareForValidation()
    {
        // Convert coupon code to uppercase
        if ($this->has('coupon_code')) {
            $this->merge([
                'coupon_code' => strtoupper($this->coupon_code),
            ]);
        }

        // Sort dates to ensure consistency
        if ($this->has('dates') && is_array($this->dates)) {
            $sortedDates = $this->dates;
            sort($sortedDates);
            $this->merge(['dates' => $sortedDates]);
        }
    }

    /**
     * Get appropriate message for dates.size validation
     */
    private function getDatesSizeMessage(): string
    {
        $priceType = $this->input('price_type');
        $validationMessages = __('farm.validation');
        
        if ($priceType === 'day_use') {
            return $validationMessages['dates.day_use_single'];
        }
        
        if ($priceType === 'night') {
            return $validationMessages['dates.night_single'];
        }
        
        return $validationMessages['dates.size'];
    }

    /**
     * Custom validation logic
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $dates = $this->input('dates', []);
            $priceType = $this->input('price_type');

            // Skip validation if no dates or price_type provided
            if (empty($dates) || !$priceType) {
                return;
            }

            // If full_day with 2 dates, ensure first date <= second date
            if ($priceType === 'full_day' && count($dates) === 2) {
                $startDate = $dates[0] ?? null;
                $endDate = $dates[1] ?? null;

                if ($startDate && $endDate && $startDate > $endDate) {
                    $validator->errors()->add('dates', __('farm.validation.dates.date_range_invalid'));
                }
            }

            // Validate dates are not duplicated
            if (count($dates) !== count(array_unique($dates))) {
                $validator->errors()->add('dates', __('farm.validation.dates.duplicates_not_allowed'));
            }
        });
    }
}