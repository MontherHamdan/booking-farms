<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class AddCardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('sanctum')->check();
    }

    public function rules(): array
    {
        return [
            'payment_method_id' => [
                'required', 
                'string',
                'regex:/^pm_[a-zA-Z0-9_]+$/' // Stripe PaymentMethod ID format (allows underscores)
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'payment_method_id.required' => 'Payment method ID is required',
            'payment_method_id.string' => 'Payment method ID must be a valid string',
            'payment_method_id.regex' => 'Invalid payment method ID format',
        ];
    }

    public function attributes(): array
    {
        return [
            'payment_method_id' => 'payment method',
        ];
    }

    protected function prepareForValidation()
    {
        // Trim payment_method_id
        if ($this->has('payment_method_id')) {
            $paymentMethodId = trim($this->payment_method_id);
            $this->merge(['payment_method_id' => $paymentMethodId]);
        }
    }
}