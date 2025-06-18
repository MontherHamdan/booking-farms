<?php

return [
    'not_found' => 'The farm with ID :id was not found.',
    
    'price_calculated_successfully' => 'Price calculated successfully',
    'pricing_not_available' => 'Pricing not available for :price_type',
    'unavailable_dates' => 'Unavailable dates: :dates',
    
    'fields_retrieved_successfully' => 'Farm form fields retrieved successfully',
    'farms_filtered_successfully' => 'Farms filtered successfully',

    // keys for rating-related errors/successes:
    'already_rated'           => 'You have already rated this farm.',
    'no_existing_rating'      => 'No existing rating found for this farm.',
    'not_yet_rated'           => 'You have not rated this farm.',
    'rating_deleted_success'  => 'Rating deleted successfully.',
    'rating_created_success' => 'Rating created successfully.',
    'rating_updated_success' => 'Rating updated successfully.',
    
    'validation' => [
        // Filter validation messages
        'city_id.array' => 'Cities must be an array',
        'city_id.*.integer' => 'City ID must be an integer',
        'city_id.*.exists' => 'The selected city does not exist',
        
        // Area validation messages
        'area_id.array' => 'Areas must be an array',
        'area_id.*.integer' => 'Area ID must be an integer',
        'area_id.*.exists' => 'The selected area does not exist',
        
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
        
        // Additional validation messages for features, ratings, etc.
        'features.array' => 'Features must be an array of feature IDs',
        'features.*.integer' => 'Each feature must be a valid integer',
        'features.*.exists' => 'Selected feature does not exist',
        'ratings.array' => 'Ratings must be an array of rating values',
        'ratings.*.integer' => 'Each rating must be an integer',
        'ratings.*.in' => 'Rating must be between 1 and 5',
        'sort_by.in' => 'Invalid sorting field',
        'sort_order.in' => 'Sort order must be either asc or desc',
        'passenger_count.integer' => 'Passenger count must be an integer',
        'passenger_count.min' => 'Passenger count must be at least 1',
        
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

        'rating' => [
            'required'     => 'Please provide a rating.',
            'numeric'      => 'Rating must be a valid number.',
            'min'          => 'Rating must be at least :min.',
            'max'          => 'Rating cannot exceed :max.',
            'increments'   => 'Rating must be between 1.0 and 5.0 in 0.5 increments (e.g., 1.0, 1.5, 2.0, etc.).',
        ],

        'review' => [
            'string'      => 'Review must be a valid text.',
            'max'         => 'Review cannot exceed :max characters.',
        ],

        'ratings' => [
            'per_page.integer'   => 'Per page must be a number.',
            'per_page.min'       => 'Per page must be at least :min.',
            'per_page.max'       => 'Per page cannot exceed :max.',

            'sort_by.in'         => 'Sort by must be one of: :values (newest, oldest, highest_rating).',

            'star_filter.integer'=> 'Star filter must be a number.',
            'star_filter.min'    => 'Star filter must be at least :min.',
            'star_filter.max'    => 'Star filter cannot exceed :max.',
        ],
    ],

    'filter_placeholders' => [
        'city_id' => 'Select cities',
        'area_id' => 'Select areas',  // NEW
        'min_price' => 'Enter minimum price',
        'max_price' => 'Enter maximum price',
        'has_offer' => 'Select offer status',
        'available_time' => 'Select available times',
        'date' => 'Select date',
        'start_date' => 'Start date',
        'end_date' => 'End date',
        'features' => 'Select features',
        'ratings' => 'Select ratings',
        'passenger_count' => 'Guests',
        'sort_by' => 'Sort by',
        'per_page' => 'Items per page',
    ],

    'filter_options' => [
        'yes' => 'Yes',
        'no' => 'No',
        'rating_1' => '1',
        'rating_2' => '2',
        'rating_3' => '3',
        'rating_4' => '4',
        'rating_5' => '5',
    ],

    'sort_options' => [
        'lowest_price' => 'Price: Low to High',
        'highest_price' => 'Price: High to Low',
        'highest_rating' => 'Rating: High to Low',
        'lowest_rating' => 'Rating: Low to High',
    ],
    
    'attributes' => [
        'city_id' => 'City',
        'area_id' => 'Area', 
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
        'features' => 'Features',
        'ratings' => 'Ratings',
        'sort_by' => 'Sort By',
        'sort_order' => 'Sort Order',
        'passenger_count' => 'Passenger Count',
        'rating'  => 'Rating',
        'review'  => 'Review',
        'ratings' => [
            'per_page'   => 'Per Page',
            'sort_by'    => 'Sort By',
            'star_filter'=> 'Star Filter',
        ],
    ],
    
    'price_types' => [
        'day_use' => 'Day Use',
        'night' => 'Night',
        'full_day' => 'Full Day',
    ],
];