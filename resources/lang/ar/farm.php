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
        // Step 1 - Basic Information
        'name_ar' => [
            'string' => 'الاسم العربي يجب أن يكون نصًا صالحًا',
            'max' => 'الاسم العربي لا يمكن أن يتجاوز 255 حرفًا',
        ],
        'name_en' => [
            'string' => 'الاسم الإنجليزي يجب أن يكون نصًا صالحًا',
            'max' => 'الاسم الإنجليزي لا يمكن أن يتجاوز 255 حرفًا',
        ],
        'description_ar' => [
            'string' => 'الوصف العربي يجب أن يكون نصًا صالحًا',
        ],
        'description_en' => [
            'string' => 'الوصف الإنجليزي يجب أن يكون نصًا صالحًا',
        ],
        'deposit_rate' => [
            'numeric' => 'مبلغ العربون يجب أن يكون رقمًا',
            'min' => 'مبلغ العربون لا يمكن أن يكون سالبًا',
        ],
        'guest_count' => [
            'integer' => 'عدد الضيوف يجب أن يكون رقمًا صحيحًا',
            'min' => 'عدد الضيوف يجب أن يكون على الأقل 1',
        ],
        
        // Step 2 - Features
        'features' => [
            'array' => 'الميزات يجب أن تكون قائمة من معرفات الميزات',
            '*' => [
                'exists' => 'الميزة المحددة غير موجودة',
            ],
        ],
        
        // Step 3 - Location & Images
        'city_id' => [
            '*' => [
                'exists' => 'المدينة المحددة غير موجودة',
            ],
        ],
        'area_id' => [
            '*' => [
                'exists' => 'المنطقة المحددة غير موجودة',
            ],
        ],
        'latitude' => [
            'numeric' => 'خط العرض يجب أن يكون رقمًا',
            'between' => 'خط العرض يجب أن يكون بين -90 و 90 درجة',
            'required_with_longitude' => 'خط العرض مطلوب عند تقديم خط الطول',
        ],
        'longitude' => [
            'numeric' => 'خط الطول يجب أن يكون رقمًا',
            'between' => 'خط الطول يجب أن يكون بين -180 و 180 درجة',
            'required_with_latitude' => 'خط الطول مطلوب عند تقديم خط العرض',
        ],
        'main_image_id' => [
            'integer' => 'معرف الصورة الرئيسية يجب أن يكون رقمًا صحيحًا',
            'exists' => 'الصورة الرئيسية المحددة غير موجودة',
        ],
        'gallery_image_ids' => [
            'array' => 'معرفات صور المعرض يجب أن تكون قائمة',
            '*' => [
                'integer' => 'معرف صورة المعرض يجب أن يكون رقمًا صحيحًا',
                'exists' => 'إحدى صور المعرض المحددة غير موجودة',
            ],
        ],
        'coordinates' => [
            'too_far_from_city' => 'الإحداثيات المقدمة تبدو بعيدة جداً عن المدينة المحددة',
        ],
        
        // Step 4 - Pricing
        'start_time' => [
            'date_format' => 'وقت البداية يجب أن يكون بتنسيق HH:MM',
        ],
        'end_time' => [
            'date_format' => 'وقت النهاية يجب أن يكون بتنسيق HH:MM',
        ],
        'saturday_price' => [
            'numeric' => 'سعر السبت يجب أن يكون رقمًا',
        ],
        'sunday_price' => [
            'numeric' => 'سعر الأحد يجب أن يكون رقمًا',
        ],
        'monday_price' => [
            'numeric' => 'سعر الاثنين يجب أن يكون رقمًا',
        ],
        'tuesday_price' => [
            'numeric' => 'سعر الثلاثاء يجب أن يكون رقمًا',
        ],
        'wednesday_price' => [
            'numeric' => 'سعر الأربعاء يجب أن يكون رقمًا',
        ],
        'thursday_price' => [
            'numeric' => 'سعر الخميس يجب أن يكون رقمًا',
        ],
        'friday_price' => [
            'numeric' => 'سعر الجمعة يجب أن يكون رقمًا',
        ],
        '*' => [
            '*' => [
                'min' => 'السعر لا يمكن أن يكون سالبًا',
                'numeric' => 'السعر يجب أن يكون رقمًا',
            ],
        ],
        
        // Step 5 - Offers & Dates
        'offer' => [
            'array' => 'العرض يجب أن يكون كائنًا',
            'percentage' => [
                'required_with' => 'نسبة العرض مطلوبة عند تقديم عرض',
                'numeric' => 'نسبة العرض يجب أن تكون رقمًا',
                'min' => 'نسبة العرض لا يمكن أن تكون سالبة',
                'max' => 'نسبة العرض لا يمكن أن تتجاوز 100%',
            ],
            'start_date' => [
                'required_with' => 'تاريخ بداية العرض مطلوب عند تقديم عرض',
                'date' => 'تاريخ بداية العرض يجب أن يكون تاريخًا صالحًا',
                'after_or_equal' => 'تاريخ بداية العرض يجب أن يكون اليوم أو في المستقبل',
            ],
            'end_date' => [
                'required_with' => 'تاريخ نهاية العرض مطلوب عند تقديم عرض',
                'date' => 'تاريخ نهاية العرض يجب أن يكون تاريخًا صالحًا',
                'after' => 'تاريخ نهاية العرض يجب أن يكون بعد تاريخ البداية',
            ],
            'is_active' => [
                'boolean' => 'حالة تفعيل العرض يجب أن تكون صحيح أو خطأ',
            ],
            'end_date_after_start' => 'تاريخ نهاية العرض يجب أن يكون بعد تاريخ البداية',
            'invalid_date_format' => 'تنسيق تواريخ العرض غير صالح',
        ],
        'not_available_dates' => [
            'array' => 'التواريخ غير المتاحة يجب أن تكون قائمة',
            '*' => [
                'date' => 'التاريخ يجب أن يكون تاريخًا صالحًا',
                'after_or_equal' => 'التاريخ يجب أن يكون اليوم أو في المستقبل',
            ],
        ],
        
        // Additional date validation messages
        'dates' => [
            'size' => 'عدد التواريخ غير صالح لنوع السعر المحدد',
            'max' => 'نوع اليوم الكامل يمكن أن يحتوي على حد أقصى تاريخين للفترة الزمنية',
            'date_range_invalid' => 'تاريخ البداية يجب أن يكون قبل أو يساوي تاريخ النهاية',
            'day_use_single' => 'نوع الاستخدام النهاري يجب أن يحتوي على تاريخ واحد فقط',
            'night_single' => 'النوع الليلي يجب أن يحتوي على تاريخ واحد فقط',
            'full_day_range' => 'نوع اليوم الكامل يمكن أن يحتوي على تاريخ واحد (يوم واحد) أو تاريخين (فترة زمنية)',
            'duplicates_not_allowed' => 'التواريخ المكررة غير مسموحة في التواريخ غير المتاحة',
            'cannot_be_past' => 'التاريخ :date لا يمكن أن يكون في الماضي',
            'invalid_format' => 'التاريخ :date ليس بتنسيق صالح',
        ],

        // Additional validation messages for features, ratings, etc.
        'ratings' => [
            'array' => 'التقييمات يجب أن تكون قائمة من قيم التقييم',
            '*' => [
                'integer' => 'كل تقييم يجب أن يكون رقمًا صحيحًا',
                'in' => 'التقييم يجب أن يكون بين 1 و 5',
            ],
        ],
        'sort_by' => [
            'in' => 'حقل الترتيب غير صالح',
        ],
        'sort_order' => [
            'in' => 'ترتيب الفرز يجب أن يكون تصاعدي أو تنازلي',
        ],
        'passenger_count' => [
            'integer' => 'عدد الزوار يجب أن يكون رقمًا صحيحًا',
            'min' => 'عدد الزوار يجب أن يكون على الأقل 1',
        ],

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
    ],
    
    'price_types' => [
        'day_use' => 'صباحي',
        'night' => 'ليلي',
        'full_day' => 'يوم كامل',
    ],
];