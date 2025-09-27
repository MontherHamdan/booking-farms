<?php

return [
    // Booking status messages
    'not_found' => 'Booking not found',
    'cannot_be_cancelled' => 'This booking cannot be cancelled',
    'cancelled_successfully' => 'Booking cancelled successfully',
    
    // Payment status messages
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

    // Coupon-related booking messages
    'coupon_applied' => 'Coupon applied successfully',
    'coupon_removed' => 'Coupon removed successfully',
    'coupon_invalid' => 'Invalid or expired coupon code',
    'coupon_not_applicable' => 'This coupon is not applicable to your booking',
    'coupon_savings' => 'You saved :amount with coupon :code',

    // Booking statuses
    'status' => [
        'pending' => 'Payment Pending',
        'confirmed' => 'Confirmed',
        'failed' => 'Payment Failed',
        'expired' => 'Payment Expired',
        'cancelled' => 'Cancelled',
        'completed' => 'Completed',
    ],

    // Status descriptions
    'status_description' => [
        'pending' => 'Waiting for payment to be completed',
        'confirmed' => 'Your booking is confirmed and ready',
        'failed' => 'Payment failed during checkout',
        'expired' => 'Payment window expired (30 minutes)',
        'cancelled' => 'Booking was cancelled by user',
        'completed' => 'Service completed successfully',
    ],

    // Payment statuses
    'payment_status' => [
        'pending' => 'Payment Pending',
        'paid' => 'Paid',
        'failed' => 'Payment Failed',
        'expired' => 'Payment Window Expired',
        'partially_paid' => 'Partially Paid',
        'refunded' => 'Refunded',
    ],

    // Payment types
    'payment_type' => [
        'full' => 'Full Payment',
        'deposit' => 'Deposit Payment',
    ],

    'validation' => [
        'payment_option.required' => 'Payment option is required',
        'payment_option.in' => 'The selected payment option is invalid. Allowed values: full, deposit',
        'guest_count' => [
            'required' => 'Guest count is required',
            'integer' => 'Guest count must be an integer', 
            'min' => 'Guest count must be at least 1',
            'exceeds_farm_capacity' => 'The number of guests (:requested) exceeds the farm capacity (:max guests maximum).',
        ],

        'customer_name.required' => 'Customer name is required',
        'customer_email.required' => 'Customer email is required',
        'customer_email.email' => 'Customer email must be valid',
        'customer_phone.required' => 'Customer phone is required',
        'notes.max' => 'Notes cannot exceed :max characters',

        'coupon_code.string' => 'Coupon code must be a valid text.',
        'coupon_code.max' => 'Coupon code cannot exceed 20 characters.',
        'coupon_code.regex' => 'Coupon code must contain only uppercase letters and numbers.',
    ],

    'attributes' => [
        'payment_option' => 'Payment Option',
        'guest_count' => 'Guest Count',
        'customer_name' => 'Customer Name',
        'customer_email' => 'Customer Email',
        'customer_phone' => 'Customer Phone',
        'notes' => 'Notes',
        'coupon_code' => 'Coupon Code',
    ],
];