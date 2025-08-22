<?php

namespace App\Http\Requests\Dashboard;

use App\Models\Coupon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCouponRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $couponId = $this->route('coupon');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'code' => [
                'required',
                'string',
                'max:50',
                'regex:/^[A-Z0-9_-]+$/',
                Rule::unique('coupons', 'code')->ignore($couponId),
            ],
            'start_date' => [
                'required',
                'date',
            ],
            'end_date' => [
                'required',
                'date',
                'after:start_date',
            ],
            'discount_type' => [
                'required',
                Rule::in([Coupon::DISCOUNT_TYPE_PERCENTAGE, Coupon::DISCOUNT_TYPE_FIXED_AMOUNT]),
            ],
            'discount_value' => [
                'required',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) {
                    if ($this->discount_type === Coupon::DISCOUNT_TYPE_PERCENTAGE && $value > 100) {
                        $fail('The discount percentage cannot be greater than 100%.');
                    }
                },
            ],
            'max_discount' => [
                'nullable',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) {
                    if ($this->discount_type === Coupon::DISCOUNT_TYPE_FIXED_AMOUNT && $value) {
                        $fail('Max discount is only applicable for percentage discounts.');
                    }
                },
            ],
            'usage_limit' => [
                'nullable',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) {
                    // Get current coupon to check current usage
                    $coupon = Coupon::find($this->route('coupon'));
                    if ($coupon && $value && $coupon->usage_count > $value) {
                        $fail("Usage limit cannot be less than current usage count ({$coupon->usage_count}).");
                    }
                },
            ],
            'platform' => [
                'required',
                Rule::in([Coupon::PLATFORM_WEB, Coupon::PLATFORM_MOBILE, Coupon::PLATFORM_BOTH]),
            ],
            'cities' => [
                'nullable',
                'array',
            ],
            'cities.*' => [
                'integer',
                'exists:cities,id',
            ],
            'usage_limit_per_user_type' => [
                'required',
                Rule::in([Coupon::USAGE_LIMIT_SINGLE, Coupon::USAGE_LIMIT_MULTIPLE, Coupon::USAGE_LIMIT_UNLIMITED]),
            ],
            'usage_limit_per_user_count' => [
                'nullable',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) {
                    if ($this->usage_limit_per_user_type === Coupon::USAGE_LIMIT_MULTIPLE && !$value) {
                        $fail('Usage count per user is required when usage type is set to multiple.');
                    }
                    if ($this->usage_limit_per_user_type !== Coupon::USAGE_LIMIT_MULTIPLE && $value) {
                        $fail('Usage count per user is only applicable when usage type is set to multiple.');
                    }
                },
            ],
            'is_active' => [
                'boolean',
            ],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'coupon name',
            'code' => 'coupon code',
            'start_date' => 'start date',
            'end_date' => 'end date',
            'discount_type' => 'discount type',
            'discount_value' => 'discount value',
            'max_discount' => 'maximum discount',
            'usage_limit' => 'usage limit',
            'platform' => 'platform',
            'cities' => 'cities',
            'usage_limit_per_user_type' => 'usage limit per user type',
            'usage_limit_per_user_count' => 'usage count per user',
            'is_active' => 'active status',
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.regex' => 'The coupon code may only contain uppercase letters, numbers, underscores, and hyphens.',
            'code.unique' => 'This coupon code is already taken.',
            'end_date.after' => 'The end date must be after the start date.',
            'cities.*.exists' => 'One or more selected cities are invalid.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert code to uppercase
        if ($this->code) {
            $this->merge([
                'code' => strtoupper($this->code),
            ]);
        }

        // Convert is_active to boolean
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);

        // Handle cities - if "all_cities" is selected, set cities to null
        if ($this->all_cities === '1') {
            $this->merge([
                'cities' => null,
            ]);
        }
    }
}