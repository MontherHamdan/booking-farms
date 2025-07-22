<?php

return [
    'not_found' => 'The farm with ID :id was not found.',
    
    'price_calculated_successfully' => 'Price calculated successfully',
    'pricing_not_available' => 'Pricing not available for :price_type',
    'unavailable_dates' => 'Unavailable dates: :dates',
    
    'fields_retrieved_successfully' => 'Farm form fields retrieved successfully',
    'farms_filtered_successfully' => 'Farms filtered successfully',

    // ──────────────────────────────────Farm Owner Messages────────────────────────────────────────
    
    // Step-based creation messages
    'step_saved' => 'Step saved successfully',
    'unauthorized' => 'Unauthorized to access this farm',
    'invalid_step' => 'Invalid step number',
    
    // Image management messages
    'images_uploaded' => 'Images uploaded successfully',
    'image_deleted' => 'Image deleted successfully',
    'image_not_found' => 'Image not found',
    'main_image_uploaded' => 'Main image uploaded successfully',
    'gallery_images_uploaded' => 'Gallery images uploaded successfully',
    
    // Farm management messages
    'incomplete_not_found' => 'Incomplete farm not found',
    'incomplete_deleted' => 'Incomplete farm deleted successfully',
    'deleted' => 'Farm deleted successfully',
    'location_updated' => 'Location updated successfully',
    'updated' => 'Farm updated successfully',
    
    // Farm status messages
    'pending_review' => 'Farm is pending review',
    'approved' => 'Farm has been approved',
    'rejected' => 'Farm has been rejected',
    'disabled' => 'Farm has been disabled',
    
    // Validation messages specific to farm owner
    'at_least_one_name_required' => 'At least one name (Arabic or English) is required',
    'image_ownership_error' => 'The selected image does not belong to your farm',
    'main_image_ownership_error' => 'The selected main image does not belong to your farm',
    'gallery_images_ownership_error' => 'One or more gallery images do not belong to your farm',

    // keys for rating-related errors/successes:
    'already_rated'           => 'You have already rated this farm.',
    'no_existing_rating'      => 'No existing rating found for this farm.',
    'not_yet_rated'           => 'You have not rated this farm.',
    'rating_deleted_success'  => 'Rating deleted successfully.',
    'rating_created_success' => 'Rating created successfully.',
    'rating_updated_success' => 'Rating updated successfully.',

    // search history
    'history_not_found' => 'Search history item not found.',
    'history_item_deleted' => 'Search history item deleted successfully.',
    'history_cleared' => 'All search history cleared successfully.',
    
    'validation' => [
        // Step 1 - Basic Information
        'name_ar' => [
            'string' => 'Arabic name must be a valid text',
            'max' => 'Arabic name cannot exceed 255 characters',
        ],
        'name_en' => [
            'string' => 'English name must be a valid text',
            'max' => 'English name cannot exceed 255 characters',
        ],
        'description_ar' => [
            'string' => 'Arabic description must be a valid text',
        ],
        'description_en' => [
            'string' => 'English description must be a valid text',
        ],
        'deposit_rate' => [
            'numeric' => 'Deposit rate must be a number',
            'min' => 'Deposit rate cannot be negative',
        ],
        'guest_count' => [
            'integer' => 'Guest count must be a whole number',
            'min' => 'Guest count must be at least 1',
        ],
        
        // Step 2 - Features
        'features' => [
            'array' => 'Features must be a list of feature IDs',
            '*' => [
                'exists' => 'Selected feature does not exist',
            ],
        ],
        
        // Step 3 - Location & Images
        'city_id' => [
            '*' => [
                'exists' => 'The selected city does not exist',
            ],
        ],
        'area_id' => [
            '*' => [
                'exists' => 'The selected area does not exist',
            ],
        ],
        'latitude' => [
            'numeric' => 'Latitude must be a number',
            'between' => 'Latitude must be between -90 and 90 degrees',
            'required_with_longitude' => 'Latitude is required when longitude is provided',
        ],
        'longitude' => [
            'numeric' => 'Longitude must be a number',
            'between' => 'Longitude must be between -180 and 180 degrees',
            'required_with_latitude' => 'Longitude is required when latitude is provided',
        ],
        'main_image_id' => [
            'integer' => 'Main image ID must be a whole number',
            'exists' => 'The selected main image does not exist',
        ],
        'gallery_image_ids' => [
            'array' => 'Gallery image IDs must be a list',
            '*' => [
                'integer' => 'Gallery image ID must be a whole number',
                'exists' => 'The selected gallery image does not exist',
            ],
        ],
        'coordinates' => [
            'too_far_from_city' => 'The provided coordinates seem too far from the selected city',
        ],
        
        // Step 4 - Pricing
        'start_time' => [
            'date_format' => 'Start time must be in HH:MM format',
        ],
        'end_time' => [
            'date_format' => 'End time must be in HH:MM format',
        ],
        'saturday_price' => [
            'numeric' => 'Saturday price must be a number',
        ],
        'sunday_price' => [
            'numeric' => 'Sunday price must be a number',
        ],
        'monday_price' => [
            'numeric' => 'Monday price must be a number',
        ],
        'tuesday_price' => [
            'numeric' => 'Tuesday price must be a number',
        ],
        'wednesday_price' => [
            'numeric' => 'Wednesday price must be a number',
        ],
        'thursday_price' => [
            'numeric' => 'Thursday price must be a number',
        ],
        'friday_price' => [
            'numeric' => 'Friday price must be a number',
        ],
        '*' => [
            '*' => [
                'min' => 'Price cannot be negative',
                'numeric' => 'Price must be a number',
            ],
        ],
        
        // Step 5 - Offers & Dates
        'offer' => [
            'array' => 'Offer must be an object',
            'percentage' => [
                'required_with' => 'Offer percentage is required when providing an offer',
                'numeric' => 'Offer percentage must be a number',
                'min' => 'Offer percentage cannot be negative',
                'max' => 'Offer percentage cannot exceed 100%',
            ],
            'start_date' => [
                'required_with' => 'Offer start date is required when providing an offer',
                'date' => 'Offer start date must be a valid date',
                'after_or_equal' => 'Offer start date must be today or in the future',
            ],
            'end_date' => [
                'required_with' => 'Offer end date is required when providing an offer',
                'date' => 'Offer end date must be a valid date',
                'after' => 'Offer end date must be after start date',
            ],
            'is_active' => [
                'boolean' => 'Offer active status must be true or false',
            ],
            'end_date_after_start' => 'Offer end date must be after start date',
            'invalid_date_format' => 'Invalid offer date format',
        ],
        'not_available_dates' => [
            'array' => 'Not available dates must be a list',
            '*' => [
                'date' => 'Date must be a valid date',
                'after_or_equal' => 'Date must be today or in the future',
            ],
        ],
        
        // Additional date validation messages
        'dates' => [
            'size' => 'Invalid number of dates for the selected price type',
            'max' => 'Full day price type can have maximum 2 dates for date range',
            'date_range_invalid' => 'Start date must be before or equal to end date',
            'day_use_single' => 'Day use price type must have exactly 1 date',
            'night_single' => 'Night price type must have exactly 1 date',
            'full_day_range' => 'Full day price type can have 1 date (single day) or 2 dates (date range)',
            'duplicates_not_allowed' => 'Duplicate dates are not allowed in not available dates',
            'cannot_be_past' => 'Date :date cannot be in the past',
            'invalid_format' => 'Date :date is not a valid date format',
        ],

        // Additional validation messages for features, ratings, etc.
        'ratings' => [
            'array' => 'Ratings must be an array of rating values',
            '*' => [
                'integer' => 'Each rating must be an integer',
                'in' => 'Rating must be between 1 and 5',
            ],
        ],
        'sort_by' => [
            'in' => 'Invalid sorting field',
        ],
        'sort_order' => [
            'in' => 'Sort order must be either asc or desc',
        ],
        'passenger_count' => [
            'integer' => 'Passenger count must be an integer',
            'min' => 'Passenger count must be at least 1',
        ],

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
        
        // Farm Owner Attributes
        'name_ar' => 'Arabic Name',
        'name_en' => 'English Name',
        'description_ar' => 'Arabic Description',
        'description_en' => 'English Description',
        'deposit_rate' => 'Deposit Rate',
        'guest_count' => 'Guest Count',
        'main_image_id' => 'Main Image',
        'gallery_image_ids' => 'Gallery Images',
        'latitude' => 'Latitude',
        'longitude' => 'Longitude',
        'coordinates' => 'Coordinates',
    ],
    
    'price_types' => [
        'day_use' => 'Day Use',
        'night' => 'Night',
        'full_day' => 'Full Day',
    ],
];