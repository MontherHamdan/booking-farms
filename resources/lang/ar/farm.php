<?php

return [
    'not_found' => 'المزرعة بالمعرف :id غير موجودة.',
    
    'price_calculated_successfully' => 'تم حساب السعر بنجاح',
    'pricing_not_available' => 'السعر غير متوفر لـ :price_type',
    'unavailable_dates' => 'التواريخ غير المتاحة: :dates',
    
    'fields_retrieved_successfully' => 'تم استرداد حقول نموذج المزرعة بنجاح',
    'farms_filtered_successfully' => 'تم تصفية المزارع بنجاح',
    
    'validation' => [
        // Filter validation messages
        'city_id.array' => 'المدن يجب أن تكون قائمة',
        'city_id.*.integer' => 'معرف المدينة يجب أن يكون رقمًا صحيحًا',
        'city_id.*.exists' => 'المدينة المحددة غير موجودة',
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
    ],
    
    'attributes' => [
        'city_id' => 'المدينة',
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
    ],
    
    'price_types' => [
        'day_use' => 'اومي',
        'night' => 'ليلي',
        'full_day' => 'يوم كامل',
    ],
];