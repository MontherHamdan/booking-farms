<?php

return [
    'not_found' => 'المزرعة بالمعرف :id غير موجودة.',
    
    'price_calculated_successfully' => 'تم حساب السعر بنجاح',
    'pricing_not_available' => 'السعر غير متوفر لـ :price_type',
    'unavailable_dates' => 'التواريخ غير المتاحة: :dates',
    
    'fields_retrieved_successfully' => 'تم استرداد حقول نموذج المزرعة بنجاح',
    'farms_filtered_successfully' => 'تم تصفية المزارع بنجاح',

    // keys for rating-related errors/successes:
    'already_rated'           => 'لقد قمت بتقييم هذه المزرعة بالفعل.',
    'no_existing_rating'      => 'لا يوجد تقييم حالي لهذه المزرعة.',
    'not_yet_rated'           => 'لم تقم بتقييم هذه المزرعة بعد.',
    'rating_deleted_success'  => 'تم حذف التقييم بنجاح.',
    'rating_created_success' => 'تم إنشاء التقييم بنجاح.',
    'rating_updated_success' => 'تم تحديث التقييم بنجاح.',
    
    'validation' => [
        // Filter validation messages
        'city_id.array' => 'المدن يجب أن تكون قائمة',
        'city_id.*.integer' => 'معرف المدينة يجب أن يكون رقمًا صحيحًا',
        'city_id.*.exists' => 'المدينة المحددة غير موجودة',
        
        // Area validation messages 
        'area_id.array' => 'المناطق يجب أن تكون قائمة',
        'area_id.*.integer' => 'معرف المنطقة يجب أن يكون رقمًا صحيحًا',
        'area_id.*.exists' => 'المنطقة المحددة غير موجودة',
        
        'min_price.numeric' => 'الحد الأدنى للسعر يجب أن يكون رقمًا',
        'min_price.min' => 'الحد الأدنى للسعر لا يمكن أن يكون أقل من صفر',
        'max_price.numeric' => 'الحد الأعلى للسعر يجب أن يكون رقمًا',
        'max_price.min' => 'الحد الأعلى للسعر لا يمكن أن يكون أقل من صفر',
        'has_offer.boolean' => 'حقل العرض يجب أن يكون صحيح أو خطأ',
        'available_time.array' => 'الأوقات المتاحة يجب أن تكون قائمة',
        'available_time.*.string' => 'الوقت المتاح يجب أن يكون نصًا',
        'available_time.*.in' => 'الوقت المتاح المحدد غير صالح. القيم المسموحة: استخدام يومي، ليلي، يوم كامل',
        'date.date_format' => 'تنسيق التاريخ غير صالح. يجب أن يكون YYYY-MM-DD',
        'date.after_or_equal' => 'التاريخ يجب أن يكون اليوم أو في المستقبل',
        'start_date.date_format' => 'تنسيق تاريخ البداية غير صالح. يجب أن يكون YYYY-MM-DD',
        'start_date.after_or_equal' => 'تاريخ البداية يجب أن يكون اليوم أو في المستقبل',
        'end_date.date_format' => 'تنسيق تاريخ النهاية غير صالح. يجب أن يكون YYYY-MM-DD',
        'end_date.after_or_equal' => 'تاريخ النهاية يجب أن يكون بعد تاريخ البداية أو يساويه',
        'per_page.integer' => 'عدد العناصر في الصفحة يجب أن يكون رقمًا صحيحًا',
        'per_page.min' => 'عدد العناصر في الصفحة يجب أن يكون على الأقل 1',
        'per_page.max' => 'عدد العناصر في الصفحة لا يمكن أن يتجاوز 100',
        
        // Additional validation messages for features, ratings, etc.
        'features.array' => 'الميزات يجب أن تكون قائمة من معرفات الميزات',
        'features.*.integer' => 'كل ميزة يجب أن تكون رقمًا صحيحًا صالحًا',
        'features.*.exists' => 'الميزة المحددة غير موجودة',
        'ratings.array' => 'التقييمات يجب أن تكون قائمة من قيم التقييم',
        'ratings.*.integer' => 'كل تقييم يجب أن يكون رقمًا صحيحًا',
        'ratings.*.in' => 'التقييم يجب أن يكون بين 1 و 5',
        'sort_by.in' => 'حقل الترتيب غير صالح',
        'sort_order.in' => 'ترتيب الفرز يجب أن يكون تصاعدي أو تنازلي',
        'passenger_count.integer' => 'عدد الزوار يجب أن يكون رقمًا صحيحًا',
        'passenger_count.min' => 'عدد الزوار يجب أن يكون على الأقل 1',
        
        // Calculate price validation messages
        'dates.required' => 'التواريخ مطلوبة',
        'dates.array' => 'التواريخ يجب أن تكون قائمة',
        'dates.min' => 'يجب تحديد تاريخ واحد على الأقل',
        'dates.*.required' => 'التاريخ مطلوب',
        'dates.*.date' => 'التاريخ يجب أن يكون تاريخًا صالحًا',
        'dates.*.after_or_equal' => 'التاريخ يجب أن يكون اليوم أو في المستقبل',
        'price_type.required' => 'نوع السعر مطلوب',
        'price_type.string' => 'نوع السعر يجب أن يكون نصًا',
        'price_type.in' => 'نوع السعر المحدد غير صالح. القيم المسموحة: استخدام يومي، ليلي، يوم كامل',

        'rating' => [
            'required'     => 'الرجاء إدخال التقييم.',
            'numeric'      => 'يجب أن يكون التقييم رقمًا صالحًا.',
            'min'          => 'يجب أن يكون التقييم على الأقل :min.',
            'max'          => 'لا يمكن أن يتجاوز التقييم :max.',
            'increments'   => 'يجب أن يكون التقييم بين 1.0 و 5.0 بفواصل 0.5 (مثلاً 1.0، 1.5، 2.0، إلخ).',
        ],

        'review' => [
            'string'      => 'يجب أن تكون المراجعة نصًا صالحًا.',
            'max'         => 'لا يمكن أن تتجاوز المراجعة :max حرفًا.',
        ],

        'ratings' => [
            'per_page.integer'   => 'حقل عدد العناصر في الصفحة يجب أن يكون رقماً.',
            'per_page.min'       => 'يجب أن تكون عدد العناصر في الصفحة على الأقل :min.',
            'per_page.max'       => 'لا يمكن أن تتجاوز عدد العناصر في الصفحة :max.',

            'sort_by.in'         => 'حقل الفرز يجب أن يكون واحداً من: :values (newest, oldest, highest_rating).',

            'star_filter.integer'=> 'حقل تصفية النجوم يجب أن يكون رقماً.',
            'star_filter.min'    => 'حقل تصفية النجوم يجب أن يكون على الأقل :min.',
            'star_filter.max'    => 'حقل تصفية النجوم لا يمكن أن يتجاوز :max.',
        ],
    ],

    'filter_placeholders' => [
        'city_id' => 'اختر المدن',
        'area_id' => 'اختر المناطق',
        'min_price' => 'أدخل الحد الأدنى للسعر',
        'max_price' => 'أدخل الحد الأعلى للسعر',
        'has_offer' => 'اختر حالة العرض',
        'available_time' => 'اختر الأوقات المتاحة',
        'date' => 'اختر التاريخ',
        'start_date' => 'تاريخ البداية',
        'end_date' => 'تاريخ النهاية',
        'features' => 'اختر الميزات',
        'ratings' => 'اختر التقييمات',
        'passenger_count' => 'عدد الزوار',
        'sort_by' => 'ترتيب حسب',
        'per_page' => 'عدد العناصر في الصفحة',
    ],

    'filter_options' => [
        'yes' => 'نعم',
        'no' => 'لا',
        'rating_1' => '1',
        'rating_2' => '2',
        'rating_3' => '3',
        'rating_4' => '4',
        'rating_5' => '5',
    ],

    'sort_options' => [
        'lowest_price' => 'السعر من الأقل للأعلى',
        'highest_price' => 'السعر من الأعلى للأقل',
        'highest_rating' => 'التقييم من الأعلى للأقل',
        'lowest_rating' => 'التقييم من الأقل للأعلى',
    ],
    
    'attributes' => [
        'city_id' => 'المدينة',
        'area_id' => 'المنطقة',  
        'min_price' => 'الحد الأدنى للسعر',
        'max_price' => 'الحد الأعلى للسعر',
        'has_offer' => 'لديه عرض',
        'available_time' => 'الوقت المتاح',
        'date' => 'التاريخ',
        'start_date' => 'تاريخ البداية',
        'end_date' => 'تاريخ النهاية',
        'per_page' => 'عدد العناصر في الصفحة',
        'dates' => 'التواريخ',
        'price_type' => 'نوع السعر',
        'features' => 'الميزات',
        'ratings' => 'التقييمات',
        'sort_by' => 'ترتيب حسب',
        'sort_order' => 'طريقة الترتيب',
        'passenger_count' => 'عدد الزوار',
        'rating'  => 'التقييم',
        'review'  => 'المراجعة',
        'ratings' => [
            'per_page'   => 'عدد العناصر في الصفحة',
            'sort_by'    => 'طريقة الفرز',
            'star_filter'=> 'تصفية النجوم',
        ],
    ],
    
    'price_types' => [
        'day_use' => 'صباحي',
        'night' => 'ليلي',
        'full_day' => 'يوم كامل',
    ],
];