<?php

namespace App\Http\Requests\FarmOwner;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StoreFarmOwnerBankAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = Auth::id();
        
        return [
            'account_type' => 'required|in:iban,cliq',
            'account_holder_name' => 'required|string|max:100',
            
            // IBAN specific fields
            'iban' => [
                'required_if:account_type,iban',
                'nullable',
                'string',
                'max:34',
                'regex:/^[A-Z]{2}[0-9]{2}[A-Z0-9]{4}[0-9]{7}([A-Z0-9]?){0,16}$/',
                Rule::unique('farm_owner_bank_accounts', 'iban')->ignore($userId, 'user_id')
            ],
            'bank_name' => 'required_if:account_type,iban|nullable|string|max:100',
            
            // CLIQ specific fields
            'cliq_alias' => 'nullable|string|max:50',
            'cliq_phone' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[\+]?[1-9][\d]{0,15}$/' // International phone format
            ],
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Custom validation for CLIQ - must have either alias or phone
            if ($this->account_type === 'cliq') {
                if (empty($this->cliq_alias) && empty($this->cliq_phone)) {
                    $validator->errors()->add('cliq_details', __('bank_account.validation.cliq_details_required'));
                }
            }

            // Validate IBAN format more thoroughly if provided
            if ($this->account_type === 'iban' && $this->filled('iban')) {
                $iban = strtoupper(str_replace(' ', '', $this->iban));
                
                // Basic IBAN length validation by country (can be expanded)
                $countryCode = substr($iban, 0, 2);
                $expectedLengths = [
                    'JO' => 30, // Jordan
                    'AE' => 23, // UAE
                    'SA' => 24, // Saudi Arabia
                    'QA' => 29, // Qatar
                    'KW' => 30, // Kuwait
                    'BH' => 22, // Bahrain
                ];

                if (isset($expectedLengths[$countryCode]) && strlen($iban) !== $expectedLengths[$countryCode]) {
                    $validator->errors()->add('iban', "IBAN for {$countryCode} should be {$expectedLengths[$countryCode]} characters long.");
                }
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'account_type.required' => __('bank_account.validation.account_type.required'),
            'account_type.in' => __('bank_account.validation.account_type.in'),
            
            'account_holder_name.required' => __('bank_account.validation.account_holder_name.required'),
            'account_holder_name.string' => __('bank_account.validation.account_holder_name.string'),
            'account_holder_name.max' => __('bank_account.validation.account_holder_name.max'),
            
            'iban.required_if' => __('bank_account.validation.iban.required_if'),
            'iban.string' => __('bank_account.validation.iban.string'),
            'iban.max' => __('bank_account.validation.iban.max'),
            'iban.regex' => __('bank_account.validation.iban.regex'),
            'iban.unique' => __('bank_account.validation.iban.unique'),
            
            'bank_name.required_if' => __('bank_account.validation.bank_name.required_if'),
            'bank_name.string' => __('bank_account.validation.bank_name.string'),
            'bank_name.max' => __('bank_account.validation.bank_name.max'),
            
            'cliq_alias.string' => __('bank_account.validation.cliq_alias.string'),
            'cliq_alias.max' => __('bank_account.validation.cliq_alias.max'),
            
            'cliq_phone.string' => __('bank_account.validation.cliq_phone.string'),
            'cliq_phone.max' => __('bank_account.validation.cliq_phone.max'),
            'cliq_phone.regex' => __('bank_account.validation.cliq_phone.regex'),
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
            'account_type' => __('bank_account.attributes.account_type'),
            'account_holder_name' => __('bank_account.attributes.account_holder_name'),
            'iban' => __('bank_account.attributes.iban'),
            'bank_name' => __('bank_account.attributes.bank_name'),
            'cliq_alias' => __('bank_account.attributes.cliq_alias'),
            'cliq_phone' => __('bank_account.attributes.cliq_phone'),
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Normalize IBAN format
        if ($this->filled('iban')) {
            $this->merge([
                'iban' => strtoupper(str_replace(' ', '', $this->iban))
            ]);
        }

        // Clean phone number format
        if ($this->filled('cliq_phone')) {
            $phone = preg_replace('/[^\d+]/', '', $this->cliq_phone);
            $this->merge(['cliq_phone' => $phone]);
        }
    }
}