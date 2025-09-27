<?php

namespace App\Http\Requests\FrontEnd;

use Illuminate\Foundation\Http\FormRequest;

class GetCheckoutPageDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'price_type' => ['required', 'string', 'in:day_use,night,full_day'],
            'dates' => ['required', 'array', 'min:1'],
            'dates.*' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'payment_option' => ['required', 'string', 'in:full,deposit'],
            'guest_count' => ['required', 'integer', 'min:1'],
            'coupon_code' => ['nullable', 'string', 'max:20', 'regex:/^[A-Z0-9]+$/'],
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
        $farmValidation = __('farm.validation');
        $bookingValidation = __('booking.validation');
        
        return [
            // Access flat structure from your language files
            'dates.size' => $this->getDatesSizeMessage(),
            'dates.max' => $farmValidation['dates.max'] ?? 'Full day price type can have maximum 2 dates for date range',
            'dates.min' => $farmValidation['dates.min'] ?? 'At least one date must be selected',
            'dates.required' => $farmValidation['dates.required'] ?? 'Dates are required',
            'dates.array' => $farmValidation['dates.array'] ?? 'Dates must be an array',
            'dates.*.required' => $farmValidation['dates.*.required'] ?? 'Date is required',
            'dates.*.date_format' => $farmValidation['dates.*.date_format'] ?? 'Date must be in YYYY-MM-DD format',
            'dates.*.after_or_equal' => $farmValidation['dates.*.after_or_equal'] ?? 'Date must be today or in the future',
            
            'price_type.required' => $farmValidation['price_type.required'] ?? 'Price type is required',
            'price_type.string' => $farmValidation['price_type.string'] ?? 'Price type must be a string',
            'price_type.in' => $farmValidation['price_type.in'] ?? 'The selected price type is invalid',
            
            'payment_option.required' => $bookingValidation['payment_option.required'] ?? 'Payment option is required',
            'payment_option.string' => $bookingValidation['payment_option.string'] ?? 'Payment option must be a string',
            'payment_option.in' => $bookingValidation['payment_option.in'] ?? 'The selected payment option is invalid',
            
            'guest_count.required' => $bookingValidation['guest_count.required'] ?? 'Guest count is required',
            'guest_count.integer' => $bookingValidation['guest_count.integer'] ?? 'Guest count must be an integer',
            'guest_count.min' => $bookingValidation['guest_count.min'] ?? 'Guest count must be at least 1',
            
            'coupon_code.string' => $farmValidation['coupon_code.string'] ?? 'Coupon code must be a valid text',
            'coupon_code.max' => $farmValidation['coupon_code.max'] ?? 'Coupon code cannot exceed 20 characters',
            'coupon_code.regex' => $farmValidation['coupon_code.regex'] ?? 'Coupon code must contain only uppercase letters and numbers',
        ];
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

        // Convert coupon code to uppercase
        if ($this->has('coupon_code') && !empty($this->coupon_code)) {
            $this->merge([
                'coupon_code' => strtoupper($this->coupon_code),
            ]);
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