<?php

return [
    'not_found' => 'The farm with ID :id was not found.',
    
    'price_calculated_successfully' => 'Price calculated successfully',
    'pricing_not_available' => 'Pricing not available for :price_type',
    'unavailable_dates' => 'Unavailable dates: :dates',
    
    'fields_retrieved_successfully' => 'Farm form fields retrieved successfully',
    'farms_filtered_successfully' => 'Farms filtered successfully',
    
    'validation' => [
        // Filter validation messages
        'city_id.array' => 'Cities must be an array',
        'city_id.*.integer' => 'City ID must be an integer',
        'city_id.*.exists' => 'The selected city does not exist',
        'min_price.numeric' => 'Minimum price must be a number',
        'min_price.min' => 'Minimum price cannot be less than zero',
        'max_price.numeric' => 'Maximum price must be a number',
        'max_price.min' => 'Maximum price cannot be less than zero',
        'has_offer.boolean' => 'Has offer field must be true or false',
        'available_time.array' => 'Available times must be an array',
        'available_time.*.string' => 'Available time must be a string',
        'available_time.*.in' => 'The selected available time is invalid. Allowed values: day_use, night, full_day',
        'date.date_format' => 'Invalid date format. Must be YYYY-MM-DD',
        'date.after_or_equal' => 'Date must be today or in the future',
        'start_date.date_format' => 'Invalid start date format. Must be YYYY-MM-DD',
        'start_date.after_or_equal' => 'Start date must be today or in the future',
        'end_date.date_format' => 'Invalid end date format. Must be YYYY-MM-DD',
        'end_date.after_or_equal' => 'End date must be after or equal to start date',
        'per_page.integer' => 'Per page must be an integer',
        'per_page.min' => 'Per page must be at least 1',
        'per_page.max' => 'Per page cannot exceed 100',
        
        // Calculate price validation messages
        'dates.required' => 'Dates are required',
        'dates.array' => 'Dates must be an array',
        'dates.min' => 'At least one date must be selected',
        'dates.*.required' => 'Date is required',
        'dates.*.date' => 'Date must be a valid date',
        'dates.*.after_or_equal' => 'Date must be today or in the future',
        'price_type.required' => 'Price type is required',
        'price_type.string' => 'Price type must be a string',
        'price_type.in' => 'The selected price type is invalid. Allowed values: day_use, night, full_day',
    ],
    
    'attributes' => [
        'city_id' => 'City',
        'min_price' => 'Minimum Price',
        'max_price' => 'Maximum Price',
        'has_offer' => 'Has Offer',
        'available_time' => 'Available Time',
        'date' => 'Date',
        'start_date' => 'Start Date',
        'end_date' => 'End Date',
        'per_page' => 'Per Page',
        'dates' => 'Dates',
        'price_type' => 'Price Type',
    ],
    
    'price_types' => [
        'day_use' => 'Day Use',
        'night' => 'Night',
        'full_day' => 'Full Day',
    ],
];