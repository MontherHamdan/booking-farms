<?php

return [
    'not_found' => 'المزرعة بالمعرف :id غير موجودة.',
    
    'price_calculated_successfully' => 'تم حساب السعر بنجاح',
    'pricing_not_available' => 'السعر غير متوفر لـ :price_type',
    'unavailable_dates' => 'التواريخ غير المتاحة: :dates',
    
    'fields_retrieved_successfully' => 'تم استرداد حقول نموذج المزرعة بنجاح',
    'farms_filtered_successfully' => 'تم تصفية المزارع بنجاح',

    // ──────────────────────────────────Farm Owner Messages────────────────────────────────────────
    
    // Step-based creation messages
    'step_saved' => 'تم حفظ الخطوة بنجاح',
    'unauthorized' => 'غير مخول للوصول لهذه المزرعة',
    'invalid_step' => 'رقم الخطوة غير صالح',
    
    // Image management messages
    'images_uploaded' => 'تم رفع الصور بنجاح',
    'image_deleted' => 'تم حذف الصورة بنجاح',
    'image_not_found' => 'الصورة غير موجودة',
    'main_image_uploaded' => 'تم رفع الصورة الرئيسية بنجاح',
    'gallery_images_uploaded' => 'تم رفع صور المعرض بنجاح',
    
    // Farm management messages
    'incomplete_not_found' => 'المزرعة غير المكتملة غير موجودة',
    'incomplete_deleted' => 'تم حذف المزرعة غير المكتملة بنجاح',
    'deleted' => 'تم حذف المزرعة بنجاح',
    'location_updated' => 'تم تحديث الموقع بنجاح',
    'updated' => 'تم تحديث المزرعة بنجاح',
    
    // Farm status messages
    'pending_review' => 'المزرعة في انتظار المراجعة',
    'approved' => 'تم قبول المزرعة',
    'rejected' => 'تم رفض المزرعة',
    'disabled' => 'تم تعطيل المزرعة',
    
    // Validation messages specific to farm owner
    'at_least_one_name_required' => 'يجب تقديم اسم واحد على الأقل (عربي أو إنجليزي)',
    'image_ownership_error' => 'الصورة المحددة لا تنتمي لمزرعتك',
    'main_image_ownership_error' => 'الصورة الرئيسية المحددة لا تنتمي لمزرعتك',
    'gallery_images_ownership_error' => 'إحدى صور المعرض أو أكثر لا تنتمي لمزرعتك',

    // keys for rating-related errors/successes:
    'already_rated'           => 'لقد قمت بتقييم هذه المزرعة بالفعل.',
    'no_existing_rating'      => 'لا يوجد تقييم حالي لهذه المزرعة.',
    'not_yet_rated'           => 'لم تقم بتقييم هذه المزرعة بعد.',
    'rating_deleted_success'  => 'تم حذف التقييم بنجاح.',
    'rating_created_success' => 'تم إنشاء التقييم بنجاح.',
    'rating_updated_success' => 'تم تحديث التقييم بنجاح.',

    // search history
    'history_not_found' => 'عنصر تاريخ البحث غير موجود.',
    'history_item_deleted' => 'تم حذف عنصر تاريخ البحث بنجاح.',
    'history_cleared' => 'تم مسح كل تاريخ البحث بنجاح.',
    
    'validation' => [
        // Filter validation messages (flat structure for FilterFarmRequest)
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
        
        // Farm Owner Step Validation Messages
        'name_ar.string' => 'الاسم العربي يجب أن يكون نصًا صالحًا',
        'name_ar.max' => 'الاسم العربي لا يمكن أن يتجاوز 255 حرفًا',
        'name_en.string' => 'الاسم الإنجليزي يجب أن يكون نصًا صالحًا',
        'name_en.max' => 'الاسم الإنجليزي لا يمكن أن يتجاوز 255 حرفًا',
        'description_ar.string' => 'الوصف العربي يجب أن يكون نصًا صالحًا',
        'description_en.string' => 'الوصف الإنجليزي يجب أن يكون نصًا صالحًا',
        'deposit_rate.numeric' => 'مبلغ العربون يجب أن يكون رقمًا',
        'deposit_rate.min' => 'مبلغ العربون لا يمكن أن يكون سالبًا',
        'guest_count.integer' => 'عدد الضيوف يجب أن يكون رقمًا صحيحًا',
        'guest_count.min' => 'عدد الضيوف يجب أن يكون على الأقل 1',
        
        // Features validation messages
        'features.array' => 'الميزات يجب أن تكون قائمة من معرفات الميزات',
        'features.*.exists' => 'الميزة المحددة غير موجودة',
        
        // Image validation messages
        'main_image_id.integer' => 'معرف الصورة الرئيسية يجب أن يكون رقمًا صحيحًا',
        'main_image_id.exists' => 'الصورة الرئيسية المحددة غير موجودة',
        'gallery_image_ids.array' => 'معرفات صور المعرض يجب أن تكون قائمة',
        'gallery_image_ids.*.integer' => 'معرف صورة المعرض يجب أن يكون رقمًا صحيحًا',
        'gallery_image_ids.*.exists' => 'إحدى صور المعرض المحددة غير موجودة',

        // Coordinates validation messages
        'latitude.numeric' => 'خط العرض يجب أن يكون رقمًا',
        'latitude.between' => 'خط العرض يجب أن يكون بين -90 و 90 درجة',
        'latitude.required_with_longitude' => 'خط العرض مطلوب عند تقديم خط الطول',
        'longitude.numeric' => 'خط الطول يجب أن يكون رقمًا',
        'longitude.between' => 'خط الطول يجب أن يكون بين -180 و 180 درجة',
        'longitude.required_with_latitude' => 'خط الطول مطلوب عند تقديم خط العرض',
        
        // Coordinates validation messages (for custom validations)
        'coordinates.too_far_from_city' => 'الإحداثيات المقدمة تبدو بعيدة جداً عن المدينة المحددة',
        
        // Pricing validation messages
        'day_use_pricing.array' => 'تسعير الاستخدام النهاري يجب أن يكون كائنًا',
        'night_pricing.array' => 'تسعير الاستخدام الليلي يجب أن يكون كائنًا',
        'full_day_pricing.array' => 'تسعير اليوم الكامل يجب أن يكون كائنًا',
        'start_time.date_format' => 'وقت البداية يجب أن يكون بتنسيق HH:MM',
        'end_time.date_format' => 'وقت النهاية يجب أن يكون بتنسيق HH:MM',
        'saturday_price.numeric' => 'سعر السبت يجب أن يكون رقمًا',
        'sunday_price.numeric' => 'سعر الأحد يجب أن يكون رقمًا',
        'monday_price.numeric' => 'سعر الاثنين يجب أن يكون رقمًا',
        'tuesday_price.numeric' => 'سعر الثلاثاء يجب أن يكون رقمًا',
        'wednesday_price.numeric' => 'سعر الأربعاء يجب أن يكون رقمًا',
        'thursday_price.numeric' => 'سعر الخميس يجب أن يكون رقمًا',
        'friday_price.numeric' => 'سعر الجمعة يجب أن يكون رقمًا',
        '*.*.min' => 'السعر لا يمكن أن يكون سالبًا',
        '*.*.numeric' => 'السعر يجب أن يكون رقمًا',
        
        // Offer validation messages
        'offer.array' => 'العرض يجب أن يكون كائنًا',
        'offer.percentage.required_with' => 'نسبة العرض مطلوبة عند تقديم عرض',
        'offer.percentage.numeric' => 'نسبة العرض يجب أن تكون رقمًا',
        'offer.percentage.min' => 'نسبة العرض لا يمكن أن تكون سالبة',
        'offer.percentage.max' => 'نسبة العرض لا يمكن أن تتجاوز 100%',
        'offer.start_date.required_with' => 'تاريخ بداية العرض مطلوب عند تقديم عرض',
        'offer.start_date.date' => 'تاريخ بداية العرض يجب أن يكون تاريخًا صالحًا',
        'offer.start_date.after_or_equal' => 'تاريخ بداية العرض يجب أن يكون اليوم أو في المستقبل',
        'offer.end_date.required_with' => 'تاريخ نهاية العرض مطلوب عند تقديم عرض',
        'offer.end_date.date' => 'تاريخ نهاية العرض يجب أن يكون تاريخًا صالحًا',
        'offer.end_date.after' => 'تاريخ نهاية العرض يجب أن يكون بعد تاريخ البداية',
        'offer.is_active.boolean' => 'حالة تفعيل العرض يجب أن تكون صحيح أو خطأ',
        
        // Additional validation messages for features, ratings, etc.
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

        // Additional date validation messages
        'dates.size' => 'عدد التواريخ غير صالح لنوع السعر المحدد',
        'dates.max' => 'نوع اليوم الكامل يمكن أن يحتوي على حد أقصى تاريخين للفترة الزمنية',
        'dates.date_range_invalid' => 'تاريخ البداية يجب أن يكون قبل أو يساوي تاريخ النهاية',
        'dates.day_use_single' => 'نوع الاستخدام النهاري يجب أن يحتوي على تاريخ واحد فقط',
        'dates.night_single' => 'النوع الليلي يجب أن يحتوي على تاريخ واحد فقط',
        'dates.full_day_range' => 'نوع اليوم الكامل يمكن أن يحتوي على تاريخ واحد (يوم واحد) أو تاريخين (فترة زمنية)',
        'dates.duplicates_not_allowed' => 'التواريخ المكررة غير مسموحة في التواريخ غير المتاحة',
        'dates.cannot_be_past' => 'التاريخ :date لا يمكن أن يكون في الماضي',
        'dates.invalid_format' => 'التاريخ :date ليس بتنسيق صالح',
        
        'offer.end_date_after_start' => 'تاريخ نهاية العرض يجب أن يكون بعد تاريخ البداية',
        'offer.invalid_date_format' => 'تنسيق تواريخ العرض غير صالح',

        'rating.required'     => 'الرجاء إدخال التقييم.',
        'rating.numeric'      => 'يجب أن يكون التقييم رقمًا صالحًا.',
        'rating.min'          => 'يجب أن يكون التقييم على الأقل :min.',
        'rating.max'          => 'لا يمكن أن يتجاوز التقييم :max.',
        'rating.increments'   => 'يجب أن يكون التقييم بين 1.0 و 5.0 بفواصل 0.5 (مثلاً 1.0، 1.5، 2.0، إلخ).',

        'review.string'      => 'يجب أن تكون المراجعة نصًا صالحًا.',
        'review.max'         => 'لا يمكن أن تتجاوز المراجعة :max حرفًا.',

        'not_available_dates.array' => 'التواريخ غير المتاحة يجب أن تكون قائمة',
        'not_available_dates.*.date' => 'التاريخ يجب أن يكون تاريخًا صالحًا',
        'not_available_dates.*.after_or_equal' => 'التاريخ يجب أن يكون اليوم أو في المستقبل',
        
        // Rating-specific prefixed messages (for StoreFarmRatingRequest)
        'rating.required' => 'الرجاء إدخال التقييم.',
        'rating.numeric' => 'يجب أن يكون التقييم رقمًا صالحًا.',
        'rating.min' => 'يجب أن يكون التقييم على الأقل :min.',
        'rating.max' => 'لا يمكن أن يتجاوز التقييم :max.',
        'rating.increments' => 'يجب أن يكون التقييم بين 1.0 و 5.0 بفواصل 0.5 (مثلاً 1.0، 1.5، 2.0، إلخ).',
        'review.string' => 'يجب أن تكون المراجعة نصًا صالحًا.',
        'review.max' => 'لا يمكن أن تتجاوز المراجعة :max حرفًا.',
        
        // Ratings list/filter validation messages (for GetFarmRatingsRequest)
        'per_page.integer' => 'حقل عدد العناصر في الصفحة يجب أن يكون رقماً.',
        'per_page.min' => 'يجب أن تكون عدد العناصر في الصفحة على الأقل :min.',
        'per_page.max' => 'لا يمكن أن تتجاوز عدد العناصر في الصفحة :max.',
        'sort_by.in' => 'حقل الفرز يجب أن يكون واحداً من: newest, oldest, highest_rating.',
        'star_filter.integer' => 'حقل تصفية النجوم يجب أن يكون رقماً.',
        'star_filter.min' => 'حقل تصفية النجوم يجب أن يكون على الأقل :min.',
        'star_filter.max' => 'حقل تصفية النجوم لا يمكن أن يتجاوز :max.',
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
        
        // Farm Owner Attributes
        'name_ar' => 'الاسم العربي',
        'name_en' => 'الاسم الإنجليزي',
        'description_ar' => 'الوصف العربي',
        'description_en' => 'الوصف الإنجليزي',
        'deposit_rate' => 'مبلغ العربون',
        'guest_count' => 'عدد الضيوف',
        'main_image_id' => 'الصورة الرئيسية',
        'gallery_image_ids' => 'صور المعرض',
        'latitude' => 'خط العرض',
        'longitude' => 'خط الطول',
        'coordinates' => 'الإحداثيات',
        
        // Rating and filter attributes
        'per_page' => 'عدد العناصر في الصفحة',
        'sort_by' => 'طريقة الفرز',
        'star_filter' => 'تصفية النجوم',
    ],
    
    'price_types' => [
        'day_use' => 'صباحي',
        'night' => 'ليلي',
        'full_day' => 'يوم كامل',
    ],
];