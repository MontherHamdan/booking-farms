<?php

return [
    // Payment status messages
    'not_found' => 'Booking not found',
    'payment_successful' => 'Payment completed successfully',
    'additional_authentication_required' => 'Additional authentication required',
    
    // Payment status variations
    'payment_succeeded' => 'Payment completed successfully',
    'payment_pending' => 'Payment is being processed',
    'payment_requires_payment_method' => 'Payment requires a valid payment method',
    'payment_requires_confirmation' => 'Payment requires confirmation',
    'payment_requires_action' => 'Payment requires additional authentication',
    'payment_processing' => 'Payment is currently processing',
    'payment_requires_capture' => 'Payment requires capture',
    'payment_canceled' => 'Payment was canceled',
    'payment_failed' => 'Payment failed',
    'payment_intent_created' => 'Payment intent created successfully',
    'deposit_not_available' => 'Deposit payment is not available for this farm',

    'validation' => [
        'payment_option.required' => 'Payment option is required',
        'payment_option.in' => 'The selected payment option is invalid. Allowed values: full, deposit',
        'guest_count.required' => 'Guest count is required',
        'guest_count.integer' => 'Guest count must be an integer',
        'guest_count.min' => 'Guest count must be at least 1',

        'customer_name.required' => 'Customer name is required',
        'customer_email.required' => 'Customer email is required',
        'customer_email.email' => 'Customer email must be valid',
        'customer_phone.required' => 'Customer phone is required',
        'notes.max' => 'Notes cannot exceed :max characters',
    ],

    'attributes' => [
        'payment_option' => 'Payment Option',
        'guest_count' => 'Guest Count',
        'customer_name' => 'Customer Name',
        'customer_email' => 'Customer Email',
        'customer_phone' => 'Customer Phone',
        'notes' => 'Notes',
    ],
];