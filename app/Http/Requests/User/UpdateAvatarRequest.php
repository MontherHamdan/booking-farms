<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateAvatarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'avatar' => 'required|image|mimes:jpg,jpeg,png,gif,webp|max:50000',
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
