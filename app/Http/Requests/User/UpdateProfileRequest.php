<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Only allow authenticated users
        return Auth::check();
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'name'             => 'sometimes|required|string|min:3|max:255',
            'email'            => "sometimes|required|email|max:255|unique:users,email,{$userId}",
            'city'             => 'sometimes|required|string|max:100',
            'phone'            => "sometimes|required|string|min:10|max:10|unique:users,phone,{$userId}",
            'password'         => 'sometimes|required|string|min:8|confirmed',
            'current_password' => 'required_with:password|string',
        ];
    }

    public function messages(): array
    {
        return __('auth.validation');
    }

    public function attributes(): array
    {
        return __('auth.attributes');
    }
}
