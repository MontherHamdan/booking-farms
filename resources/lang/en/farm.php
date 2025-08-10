<?php

return [
    'not_found' => 'The farm with ID :id was not found.',
    
    'price_calculated_successfully' => 'Price calculated successfully',
    'pricing_not_available' => 'Pricing not available for :price_type',
    'unavailable_dates' => 'Unavailable dates: :dates',
    'dates_already_booked' => 'Dates are already booked: :dates',
    'deposit_not_available' => 'Deposit payment is not available for this farm',
    
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
        // Filter validation messages (flat structure for FilterFarmRequest)
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
        
        // Farm Owner Step Validation Messages
        'name_ar.string' => 'Arabic name must be a valid text',
        'name_ar.max' => 'Arabic name cannot exceed 255 characters',
        'name_en.string' => 'English name must be a valid text',
        'name_en.max' => 'English name cannot exceed 255 characters',
        'description_ar.string' => 'Arabic description must be a valid text',
        'description_en.string' => 'English description must be a valid text',
        'deposit_rate.numeric' => 'Deposit rate must be a number',
        'deposit_rate.min' => 'Deposit rate cannot be negative',
        'guest_count.integer' => 'Guest count must be a whole number',
        'guest_count.min' => 'Guest count must be at least 1',
        
        // Features validation messages
        'features.array' => 'Features must be a list of feature IDs',
        'features.*.exists' => 'Selected feature does not exist',
        
        // Image validation messages
        'main_image_id.integer' => 'Main image ID must be a whole number',
        'main_image_id.exists' => 'The selected main image does not exist',
        'gallery_image_ids.array' => 'Gallery image IDs must be a list',
        'gallery_image_ids.*.integer' => 'Gallery image ID must be a whole number',
        'gallery_image_ids.*.exists' => 'The selected gallery image does not exist',

        // Coordinates validation messages
        'latitude.numeric' => 'Latitude must be a number',
        'latitude.between' => 'Latitude must be between -90 and 90 degrees',
        'latitude.required_with_longitude' => 'Latitude is required when longitude is provided',
        'longitude.numeric' => 'Longitude must be a number',
        'longitude.between' => 'Longitude must be between -180 and 180 degrees',
        'longitude.required_with_latitude' => 'Longitude is required when latitude is provided',
        
        // Coordinates validation messages (for custom validations)
        'coordinates.too_far_from_city' => 'The provided coordinates seem too far from the selected city',
        
        // Pricing validation messages
        'day_use_pricing.array' => 'Day use pricing must be an object',
        'night_pricing.array' => 'Night pricing must be an object',
        'full_day_pricing.array' => 'Full day pricing must be an object',
        'start_time.date_format' => 'Start time must be in HH:MM format',
        'end_time.date_format' => 'End time must be in HH:MM format',
        'saturday_price.numeric' => 'Saturday price must be a number',
        'sunday_price.numeric' => 'Sunday price must be a number',
        'monday_price.numeric' => 'Monday price must be a number',
        'tuesday_price.numeric' => 'Tuesday price must be a number',
        'wednesday_price.numeric' => 'Wednesday price must be a number',
        'thursday_price.numeric' => 'Thursday price must be a number',
        'friday_price.numeric' => 'Friday price must be a number',
        '*.*.min' => 'Price cannot be negative',
        '*.*.numeric' => 'Price must be a number',
        
        // Offer validation messages
        'offer.array' => 'Offer must be an object',
        'offer.percentage.required_with' => 'Offer percentage is required when providing an offer',
        'offer.percentage.numeric' => 'Offer percentage must be a number',
        'offer.percentage.min' => 'Offer percentage cannot be negative',
        'offer.percentage.max' => 'Offer percentage cannot exceed 100%',
        'offer.start_date.required_with' => 'Offer start date is required when providing an offer',
        'offer.start_date.date' => 'Offer start date must be a valid date',
        'offer.start_date.after_or_equal' => 'Offer start date must be today or in the future',
        'offer.end_date.required_with' => 'Offer end date is required when providing an offer',
        'offer.end_date.date' => 'Offer end date must be a valid date',
        'offer.end_date.after' => 'Offer end date must be after start date',
        'offer.is_active.boolean' => 'Offer active status must be true or false',
        
        // Additional validation messages for features, ratings, etc.
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

        // Additional date validation messages
        'dates.size' => 'Invalid number of dates for the selected price type',
        'dates.max' => 'Full day price type can have maximum 2 dates for date range',
        'dates.date_range_invalid' => 'Start date must be before or equal to end date',
        'dates.day_use_single' => 'Day use price type must have exactly 1 date',
        'dates.night_single' => 'Night price type must have exactly 1 date',
        'dates.full_day_range' => 'Full day price type can have 1 date (single day) or 2 dates (date range)',
        'dates.duplicates_not_allowed' => 'Duplicate dates are not allowed in not available dates',
        'dates.cannot_be_past' => 'Date :date cannot be in the past',
        'dates.invalid_format' => 'Date :date is not a valid date format',
        
        'offer.end_date_after_start' => 'Offer end date must be after start date',
        'offer.invalid_date_format' => 'Invalid offer date format',

        'rating.required'     => 'Please provide a rating.',
        'rating.numeric'      => 'Rating must be a valid number.',
        'rating.min'          => 'Rating must be at least :min.',
        'rating.max'          => 'Rating cannot exceed :max.',
        'rating.increments'   => 'Rating must be between 1.0 and 5.0 in 0.5 increments (e.g., 1.0, 1.5, 2.0, etc.).',

        'review.string'      => 'Review must be a valid text.',
        'review.max'         => 'Review cannot exceed :max characters.',

        'not_available_dates.array' => 'Not available dates must be a list',
        'not_available_dates.*.date' => 'Date must be a valid date',
        'not_available_dates.*.after_or_equal' => 'Date must be today or in the future',
        
        // Rating-specific prefixed messages (for StoreFarmRatingRequest)
        'rating.required' => 'Please provide a rating.',
        'rating.numeric' => 'Rating must be a valid number.',
        'rating.min' => 'Rating must be at least :min.',
        'rating.max' => 'Rating cannot exceed :max.',
        'rating.increments' => 'Rating must be between 1.0 and 5.0 in 0.5 increments (e.g., 1.0, 1.5, 2.0, etc.).',
        'review.string' => 'Review must be a valid text.',
        'review.max' => 'Review cannot exceed :max characters.',
        
        // Ratings list/filter validation messages (for GetFarmRatingsRequest)
        'per_page.integer' => 'Per page must be a number.',
        'per_page.min' => 'Per page must be at least :min.',
        'per_page.max' => 'Per page cannot exceed :max.',
        'sort_by.in' => 'Sort by must be one of: newest, oldest, highest_rating.',
        'star_filter.integer' => 'Star filter must be a number.',
        'star_filter.min' => 'Star filter must be at least :min.',
        'star_filter.max' => 'Star filter cannot exceed :max.',
    ],

    'filter_placeholders' => [
        'city_id' => 'Select cities',
        'area_id' => 'Select areas',
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
        
        // Rating and filter attributes
        'per_page' => 'Per Page',
        'sort_by' => 'Sort By',
        'star_filter' => 'Star Filter',
    ],
    
    'price_types' => [
        'day_use' => 'Day Use',
        'night' => 'Night',
        'full_day' => 'Full Day',
    ],
];