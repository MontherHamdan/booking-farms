<?php

return [
    'validation' => [
        // Card number validation
        'card_number_required' => 'Card number is required',
        'card_number_min' => 'Card number must be at least 13 digits',
        'card_number_max' => 'Card number must not exceed 19 digits',
        'card_number_format' => 'Card number must contain only numbers',
        'card_number_invalid' => 'Invalid card number',

        // Expiration validation
        'exp_month_required' => 'Expiration month is required',
        'exp_month_range' => 'Month must be between 1 and 12',
        'exp_year_required' => 'Expiration year is required',
        'exp_year_past' => 'Year cannot be in the past',
        'exp_year_future' => 'Year is too far in the future',
        'card_expired' => 'Card has expired',

        // CVC validation
        'cvc_required' => 'CVC is required',
        'cvc_length' => 'CVC must be 3 or 4 digits',
        'cvc_format' => 'CVC must contain only numbers',
        'cvc_amex_length' => 'American Express CVC must be 4 digits',
        'cvc_standard_length' => 'CVC must be 3 digits',

        // Cardholder info validation
        'name_required' => 'Cardholder name is required',
        'name_format' => 'Name must contain only letters and spaces',
        'street_required' => 'Street address is required',
        'city_required' => 'City is required',
        'city_format' => 'City name contains invalid characters',
        'state_required' => 'State is required',
        'postal_code_required' => 'Postal code is required',
        'postal_code_format' => 'Invalid postal code format',
        'country_required' => 'Country is required',
        'country_format' => 'Country must be a 2-letter code (e.g., US, CA)',

        // Card management validation
        'card_id_required' => 'Card ID is required',
        'card_id_string' => 'Card ID must be a valid string',
        'card_id_max' => 'Card ID is too long',
        'card_id_format' => 'Invalid card ID format',
        'card_not_found' => 'Card not found or does not belong to you',

        // Payment method validation
        'payment_method_id_invalid' => 'Invalid payment method ID',
        'save_card_boolean' => 'Save card option must be true or false',
        'cannot_save_existing_card' => 'Cannot save an already saved card',

        // UPDATED: Contact info validation (more flexible)
        'contact_info_required' => 'Either email or phone number is required to save payment methods. Please update your profile.',
        'email_recommended' => 'Email address is recommended for payment receipts and notifications',
        'phone_recommended' => 'Phone number is recommended for payment verification',

        // General card errors
        'already_exists' => 'This card is already saved to your account',
        'card_declined' => 'Your card was declined',
        'insufficient_funds' => 'Insufficient funds',
        'card_not_supported' => 'This card type is not supported',
    ],

    'attributes' => [
        'card_number' => 'card number',
        'exp_month' => 'expiration month',
        'exp_year' => 'expiration year',
        'cvc' => 'CVC',
        'name' => 'cardholder name',
        'street' => 'street address',
        'city' => 'city',
        'state' => 'state',
        'postal_code' => 'postal code',
        'country' => 'country',
        'card_id' => 'card ID',
        'payment_method_id' => 'payment method',
        'save_card' => 'save card option',
    ],

    'messages' => [
        'added_successfully' => 'Card added successfully',
        'deleted_successfully' => 'Card removed successfully',
        'payment_successful' => 'Payment processed successfully',
        'payment_failed' => 'Payment failed. Please try again',
        
        // UPDATED: More flexible messages
        'no_cards_found' => 'No saved cards found',
        'contact_info_required_for_cards' => 'Please add an email or phone number to your profile to save payment cards',
        'profile_update_recommended' => 'Update your profile with contact information for a better payment experience',
    ],

    // NEW: Profile recommendations
    'recommendations' => [
        'add_email' => 'Add email address for payment receipts and account security',
        'add_phone' => 'Add phone number for payment verification and notifications',
        'add_name' => 'Add your name for personalized payment experience',
    ],

    // NEW: Contact method labels
    'contact_methods' => [
        'email' => 'Email',
        'phone' => 'Phone',
        'both' => 'Email & Phone',
        'none' => 'No contact info',
    ],
];