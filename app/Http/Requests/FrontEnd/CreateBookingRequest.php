<?php

namespace App\Http\Requests\FrontEnd;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class CreateBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('sanctum')->check();
    }

    public function rules(): array
    {
        $rules = [
            'price_type' => ['required', 'string', 'in:day_use,night,full_day'],
            'dates' => ['required', 'array', 'min:1'],
            'dates.*' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'guest_count' => ['required', 'integer', 'min:1'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:20'],
            'payment_option' => ['required', 'string', 'in:full,deposit'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];

        // Add specific date count validation based on price type
        $priceType = $this->input('price_type');
        
        if (in_array($priceType, ['day_use', 'night'])) {
            // day_use and night must have exactly 1 date
            $rules['dates'] = ['required', 'array', 'size:1'];
        } elseif ($priceType === 'full_day') {
            // full_day can have 1 or 2 dates (single day or date range)
            $rules['dates'] = ['required', 'array', 'min:1', 'max:2'];
            
            // If 2 dates provided, add validation for date range
            if (count($this->input('dates', [])) === 2) {
                $rules['dates'] = ['required', 'array', 'size:2'];
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
        // Sort dates to ensure consistency
        if ($this->has('dates') && is_array($this->dates)) {
            $sortedDates = $this->dates;
            sort($sortedDates);
            $this->merge(['dates' => $sortedDates]);
        }

        // Default payment option to 'full' if not provided
        if (!$this->has('payment_option')) {
            $this->merge(['payment_option' => 'full']);
        }
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

            // Validate dates are not duplicated
            if (count($dates) !== count(array_unique($dates))) {
                $validator->errors()->add('dates', __('farm.validation.dates.duplicates_not_allowed'));
            }

            // Additional validation can be added here for farm-specific rules
            // like deposit availability, farm capacity, etc.
        });
    }
}