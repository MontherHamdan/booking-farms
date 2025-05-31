<?php

return [
    'failed' => 'The provided credentials are incorrect.',
    'logged_out_successfully' => 'Logged out successfully',
    'unauthorized' => 'You are not authorized to access this resource.',
    
    // OTP related messages
    'otp_sent' => 'OTP code has been sent to your phone.',
    'otp_resent' => 'OTP code has been resent to your phone.',
    'invalid_otp' => 'The OTP code is invalid.',
    'otp_expired' => 'The OTP code has expired. Please request a new one.',
    'user_not_found' => 'User not found or already verified.',
    'invalid_verification' => 'Invalid verification code or security token.',
    'invalid_token' => 'Security token is invalid or missing.',
    'registration_complete' => 'Registration completed successfully.',
    'registered_successfully' => 'User registered successfully.',

    // ────────── Validation Messages ──────────
    'validation' => [
        'name.required'             => 'Your name is required.',
        'name.string'               => 'Name must be a valid string.',
        'name.min'                  => 'Name must be at least :min characters.',
        'name.max'                  => 'Name cannot exceed :max characters.',

        'email.required'            => 'Email is required.',
        'email.email'               => 'Email must be a valid email address.',
        'email.max'                 => 'Email cannot exceed :max characters.',
        'email.unique'              => 'This email is already taken.',

        'city.required'             => 'City is required.',
        'city.string'               => 'City must be a valid string.',
        'city.max'                  => 'City cannot exceed :max characters.',

        'phone.required'            => 'Phone number is required.',
        'phone.string'              => 'Phone must be a valid string of digits.',
        'phone.max'                 => 'Phone cannot exceed :max characters.',
        'phone.unique'              => 'This phone number is already in use.',

        'password.required'         => 'Password is required.',
        'password.min'              => 'Password must be at least :min characters.',
        'password.confirmed'        => 'Password confirmation does not match.',

        'current_password.required_with' => 'Your current password is required when changing password.',

        'avatar.required' => 'Please choose an avatar image.',
        'avatar.image'    => 'The avatar must be an image.',
        'avatar.mimes'    => 'Allowed avatar formats: jpg, jpeg, png, gif, webp.',
        'avatar.max'      => 'Avatar cannot be larger than :max kilobytes.',
    ],

    // ────────── Human-readable Attribute Names ──────────
    'attributes' => [
        'name'             => 'Name',
        'email'            => 'Email Address',
        'city'             => 'City',
        'phone'            => 'Phone Number',
        'password'         => 'Password',
        'password_confirmation' => 'Password Confirmation',
        'current_password' => 'Current Password',
        'avatar' => 'Avatar',
    ],

    // Custom success messages
    'profile_updated_successfully' => 'Profile updated successfully.',
    'avatar_updated_successfully' => 'Avatar updated successfully.',
    'avatar_deleted_successfully' => 'Avatar deleted successfully.',

    // Custom error messages
    'no_avatar_to_delete'        => 'No avatar to delete.',

];