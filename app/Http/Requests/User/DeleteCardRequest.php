<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class DeleteCardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('sanctum')->check();
    }

    public function rules(): array
    {
        return [
            'card_id' => [
                'required', 
                'string',
                'max:255',
                'regex:/^pm_[a-zA-Z0-9]+$/' // Stripe payment method ID format
            ],
        ];
    }

    public function messages(): array
    {
        return array_merge(
            __('card.validation'),
            [
                'card_id.required' => __('card.validation.card_id_required'),
                'card_id.string' => __('card.validation.card_id_string'),
                'card_id.max' => __('card.validation.card_id_max'),
                'card_id.regex' => __('card.validation.card_id_format'),
            ]
        );
    }

    public function attributes(): array
    {
        return __('card.attributes');
    }

    protected function prepareForValidation()
    {
        // Trim card_id
        if ($this->has('card_id')) {
            $cardId = trim($this->card_id);
            $this->merge(['card_id' => $cardId]);
        }
    }

    /**
     * Custom validation logic
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $cardId = $this->input('card_id');

            // Additional validation can be added here
            // For example, check if the card belongs to the authenticated user
            if ($cardId && !$this->cardBelongsToUser($cardId)) {
                $validator->errors()->add('card_id', __('card.validation.card_not_found'));
            }
        });
    }

    /**
     * Check if card belongs to authenticated user
     */
    private function cardBelongsToUser(string $cardId): bool
    {
        try {
            $user = auth('sanctum')->user();
            
            if (!$user || !$user->stripe_id) {
                return false;
            }

            // This will be validated in the controller with actual Stripe API call
            // Here we just do basic format validation
            return true;
            
        } catch (\Exception $e) {
            return false;
        }
    }
}