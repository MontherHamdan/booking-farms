<?php

return [
    // Coupon status messages
    'not_found' => 'رمز الكوبون غير موجود.',
    'inactive' => 'هذا الكوبون غير نشط حالياً.',
    'not_started' => 'هذا الكوبون لم يصبح صالحاً بعد.',
    'expired' => 'انتهت صلاحية هذا الكوبون.',
    'platform_not_allowed' => 'هذا الكوبون غير صالح لهذه المنصة.',
    'city_not_allowed' => 'هذا الكوبون غير صالح لهذه المدينة.',
    'usage_limit_reached' => 'وصل هذا الكوبون لحد الاستخدام الأقصى.',
    'user_limit_reached' => 'وصلت لحد الاستخدام الأقصى لهذا الكوبون.',
    'valid' => 'رمز الكوبون صالح وتم تطبيقه بنجاح.',
    'applied_successfully' => 'تم تطبيق الكوبون بنجاح.',
    'removed_successfully' => 'تم إزالة الكوبون بنجاح.',
    'invalid_format' => 'تنسيق رمز الكوبون غير صالح.',
    'cannot_be_combined' => 'لا يمكن دمج هذا الكوبون مع العروض الحالية.',
    'minimum_amount_not_met' => 'لم يتم الوصول للحد الأدنى للمبلغ المطلوب لهذا الكوبون.',
    'already_used' => 'لقد استخدمت هذا الكوبون من قبل.',
    
    // Success messages
    'created_successfully' => 'تم إنشاء الكوبون بنجاح.',
    'updated_successfully' => 'تم تحديث الكوبون بنجاح.',
    'deleted_successfully' => 'تم حذف الكوبون بنجاح.',
    'activated_successfully' => 'تم تفعيل الكوبون بنجاح.',
    'deactivated_successfully' => 'تم إلغاء تفعيل الكوبون بنجاح.',
    
    // Error messages
    'creation_failed' => 'فشل في إنشاء الكوبون.',
    'update_failed' => 'فشل في تحديث الكوبون.',
    'deletion_failed' => 'فشل في حذف الكوبون.',
    'code_already_exists' => 'رمز الكوبون هذا موجود بالفعل.',
    
    'validation' => [
        'code_required' => 'رمز الكوبون مطلوب.',
        'code_unique' => 'رمز الكوبون هذا موجود بالفعل.',
        'code_max' => 'رمز الكوبون لا يمكن أن يتجاوز 20 حرفاً.',
        'invalid_format' => 'رمز الكوبون يجب أن يحتوي على أحرف إنجليزية كبيرة وأرقام فقط.',
        'name_required' => 'اسم الكوبون مطلوب.',
        'name_max' => 'اسم الكوبون لا يمكن أن يتجاوز 255 حرفاً.',
        'start_date_required' => 'تاريخ البداية مطلوب.',
        'start_date_date' => 'تاريخ البداية يجب أن يكون تاريخاً صالحاً.',
        'end_date_required' => 'تاريخ النهاية مطلوب.',
        'end_date_date' => 'تاريخ النهاية يجب أن يكون تاريخاً صالحاً.',
        'end_date_after' => 'تاريخ النهاية يجب أن يكون بعد تاريخ البداية.',
        'discount_type_required' => 'نوع الخصم مطلوب.',
        'discount_type_in' => 'نوع الخصم يجب أن يكون نسبة مئوية أو مبلغ ثابت.',
        'discount_value_required' => 'قيمة الخصم مطلوبة.',
        'discount_value_numeric' => 'قيمة الخصم يجب أن تكون رقماً.',
        'discount_value_min' => 'قيمة الخصم يجب أن تكون أكبر من صفر.',
        'discount_value_percentage_max' => 'الخصم بالنسبة المئوية لا يمكن أن يتجاوز 100%.',
        'max_discount_numeric' => 'الحد الأقصى للخصم يجب أن يكون رقماً.',
        'max_discount_min' => 'الحد الأقصى للخصم يجب أن يكون أكبر من صفر.',
        'usage_limit_integer' => 'حد الاستخدام يجب أن يكون رقماً صحيحاً.',
        'usage_limit_min' => 'حد الاستخدام يجب أن يكون على الأقل 1.',
        'platform_required' => 'المنصة مطلوبة.',
        'platform_in' => 'المنصة يجب أن تكون ويب، موبايل، أو كليهما.',
        'cities_array' => 'المدن يجب أن تكون قائمة.',
        'cities_exists' => 'إحدى المدن المحددة أو أكثر غير موجودة.',
        'usage_limit_per_user_type_required' => 'نوع حد الاستخدام لكل مستخدم مطلوب.',
        'usage_limit_per_user_type_in' => 'نوع حد الاستخدام لكل مستخدم يجب أن يكون استخدام واحد، متعدد، أو غير محدود.',
        'usage_limit_per_user_count_required_if' => 'عدد الاستخدامات لكل مستخدم مطلوب عندما يكون النوع متعدد.',
        'usage_limit_per_user_count_integer' => 'عدد الاستخدامات لكل مستخدم يجب أن يكون رقماً صحيحاً.',
        'usage_limit_per_user_count_min' => 'عدد الاستخدامات لكل مستخدم يجب أن يكون على الأقل 1.',
        'is_active_boolean' => 'حالة التفعيل يجب أن تكون صحيح أو خطأ.',
    ],
    
    'discount_types' => [
        'percentage' => 'خصم بالنسبة المئوية',
        'fixed_amount' => 'خصم بمبلغ ثابت',
    ],
    
    'platforms' => [
        'web' => 'الويب فقط',
        'mobile' => 'الموبايل فقط',
        'both' => 'الويب والموبايل',
    ],
    
    'usage_limits' => [
        'single' => 'استخدام واحد',
        'multiple' => 'استخدامات متعددة',
        'unlimited' => 'غير محدود',
    ],
    
    'statuses' => [
        'active' => 'نشط',
        'inactive' => 'غير نشط',
        'expired' => 'منتهي الصلاحية',
        'not_started' => 'لم يبدأ بعد',
        'usage_limit_reached' => 'وصل لحد الاستخدام الأقصى',
    ],
    
    'attributes' => [
        'name' => 'اسم الكوبون',
        'code' => 'رمز الكوبون',
        'start_date' => 'تاريخ البداية',
        'end_date' => 'تاريخ النهاية',
        'discount_type' => 'نوع الخصم',
        'discount_value' => 'قيمة الخصم',
        'max_discount' => 'الحد الأقصى للخصم',
        'usage_limit' => 'حد الاستخدام',
        'platform' => 'المنصة',
        'cities' => 'المدن',
        'usage_limit_per_user_type' => 'حد الاستخدام لكل مستخدم',
        'usage_limit_per_user_count' => 'عدد الاستخدامات لكل مستخدم',
        'is_active' => 'حالة التفعيل',
        'coupon_code' => 'رمز الكوبون',
    ],
    
    'filter_options' => [
        'percentage' => 'نسبة مئوية',
        'fixed_amount' => 'مبلغ ثابت',
    ],
    
    'sort_options' => [
        'newest' => 'الأحدث أولاً',
        'oldest' => 'الأقدم أولاً',
        'expiring_soon' => 'ينتهي قريباً',
    ],
];