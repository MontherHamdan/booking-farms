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
            'coupon_code' => ['nullable', 'string', 'max:20', 'regex:/^[A-Z0-9]+$/'],
            
            // NEW: Payment method fields
            'payment_method_id' => ['nullable', 'string', 'max:255'],
            'save_card' => ['nullable', 'boolean'],
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
                'coupon_code.regex' => __('coupon.validation.invalid_format'),
                'payment_method_id.string' => __('card.validation.payment_method_id_invalid'),
                'save_card.boolean' => __('card.validation.save_card_boolean'),
            ]
        );
    }

    public function attributes(): array
    {
        return array_merge(
            __('farm.attributes'), 
            __('booking.attributes'),
            __('card.attributes')
        );
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

        // Convert coupon code to uppercase
        if ($this->has('coupon_code') && !empty($this->coupon_code)) {
            $this->merge([
                'coupon_code' => strtoupper($this->coupon_code),
            ]);
        }

        // Default save_card to false if not provided
        if (!$this->has('save_card')) {
            $this->merge(['save_card' => false]);
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

            // NEW: Validate payment method logic
            $paymentMethodId = $this->input('payment_method_id');
            $saveCard = $this->input('save_card', false);

            // If payment_method_id is provided, user shouldn't request to save card
            if ($paymentMethodId && $saveCard) {
                $validator->errors()->add('save_card', __('card.validation.cannot_save_existing_card'));
            }

            // Additional validation can be added here for farm-specific rules
            // like deposit availability, farm capacity, etc.
        });
    }
}