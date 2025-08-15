<?php

return [
    // Coupon status messages
    'not_found' => 'Coupon code not found.',
    'inactive' => 'This coupon is currently inactive.',
    'not_started' => 'This coupon is not yet valid.',
    'expired' => 'This coupon has expired.',
    'platform_not_allowed' => 'This coupon is not valid for this platform.',
    'city_not_allowed' => 'This coupon is not valid for this city.',
    'usage_limit_reached' => 'This coupon has reached its usage limit.',
    'user_limit_reached' => 'You have reached the usage limit for this coupon.',
    'valid' => 'Coupon code is valid and applied successfully.',
    'applied_successfully' => 'Coupon applied successfully.',
    'removed_successfully' => 'Coupon removed successfully.',
    'invalid_format' => 'Invalid coupon code format.',
    'cannot_be_combined' => 'This coupon cannot be combined with current offers.',
    'minimum_amount_not_met' => 'Minimum purchase amount not met for this coupon.',
    'already_used' => 'You have already used this coupon.',
    
    // Success messages
    'created_successfully' => 'Coupon created successfully.',
    'updated_successfully' => 'Coupon updated successfully.',
    'deleted_successfully' => 'Coupon deleted successfully.',
    'activated_successfully' => 'Coupon activated successfully.',
    'deactivated_successfully' => 'Coupon deactivated successfully.',
    
    // Error messages
    'creation_failed' => 'Failed to create coupon.',
    'update_failed' => 'Failed to update coupon.',
    'deletion_failed' => 'Failed to delete coupon.',
    'code_already_exists' => 'This coupon code already exists.',
    
    'validation' => [
        'code_required' => 'Coupon code is required.',
        'code_unique' => 'This coupon code already exists.',
        'code_max' => 'Coupon code cannot exceed 20 characters.',
        'invalid_format' => 'Coupon code must contain only uppercase letters and numbers.',
        'name_required' => 'Coupon name is required.',
        'name_max' => 'Coupon name cannot exceed 255 characters.',
        'start_date_required' => 'Start date is required.',
        'start_date_date' => 'Start date must be a valid date.',
        'end_date_required' => 'End date is required.',
        'end_date_date' => 'End date must be a valid date.',
        'end_date_after' => 'End date must be after start date.',
        'discount_type_required' => 'Discount type is required.',
        'discount_type_in' => 'Discount type must be either percentage or fixed amount.',
        'discount_value_required' => 'Discount value is required.',
        'discount_value_numeric' => 'Discount value must be a number.',
        'discount_value_min' => 'Discount value must be greater than 0.',
        'discount_value_percentage_max' => 'Percentage discount cannot exceed 100%.',
        'max_discount_numeric' => 'Maximum discount must be a number.',
        'max_discount_min' => 'Maximum discount must be greater than 0.',
        'usage_limit_integer' => 'Usage limit must be an integer.',
        'usage_limit_min' => 'Usage limit must be at least 1.',
        'platform_required' => 'Platform is required.',
        'platform_in' => 'Platform must be web, mobile, or both.',
        'cities_array' => 'Cities must be an array.',
        'cities_exists' => 'One or more selected cities do not exist.',
        'usage_limit_per_user_type_required' => 'Usage limit per user type is required.',
        'usage_limit_per_user_type_in' => 'Usage limit per user type must be single, multiple, or unlimited.',
        'usage_limit_per_user_count_required_if' => 'Usage count per user is required when usage type is multiple.',
        'usage_limit_per_user_count_integer' => 'Usage count per user must be an integer.',
        'usage_limit_per_user_count_min' => 'Usage count per user must be at least 1.',
        'is_active_boolean' => 'Active status must be true or false.',
    ],
    
    'discount_types' => [
        'percentage' => 'Percentage Discount',
        'fixed_amount' => 'Fixed Amount Discount',
    ],
    
    'platforms' => [
        'web' => 'Web Only',
        'mobile' => 'Mobile Only',
        'both' => 'Web & Mobile',
    ],
    
    'usage_limits' => [
        'single' => 'Single Use',
        'multiple' => 'Multiple Uses',
        'unlimited' => 'Unlimited Uses',
    ],
    
    'statuses' => [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'expired' => 'Expired',
        'not_started' => 'Not Started',
        'usage_limit_reached' => 'Usage Limit Reached',
    ],
    
    'attributes' => [
        'name' => 'Coupon Name',
        'code' => 'Coupon Code',
        'start_date' => 'Start Date',
        'end_date' => 'End Date',
        'discount_type' => 'Discount Type',
        'discount_value' => 'Discount Value',
        'max_discount' => 'Maximum Discount',
        'usage_limit' => 'Usage Limit',
        'platform' => 'Platform',
        'cities' => 'Cities',
        'usage_limit_per_user_type' => 'Usage Limit Per User',
        'usage_limit_per_user_count' => 'Usage Count Per User',
        'is_active' => 'Active Status',
        'coupon_code' => 'Coupon Code',
    ],
    
    'filter_options' => [
        'percentage' => 'Percentage',
        'fixed_amount' => 'Fixed Amount',
    ],
    
    'sort_options' => [
        'newest' => 'Newest First',
        'oldest' => 'Oldest First',
        'expiring_soon' => 'Expiring Soon',
    ],
];