<?php

return [
    'validation' => [
        // Card number validation
        'card_number_required' => 'رقم البطاقة مطلوب',
        'card_number_min' => 'رقم البطاقة يجب أن يكون 13 رقم على الأقل',
        'card_number_max' => 'رقم البطاقة يجب ألا يزيد عن 19 رقم',
        'card_number_format' => 'رقم البطاقة يجب أن يحتوي على أرقام فقط',
        'card_number_invalid' => 'رقم البطاقة غير صحيح',

        // Expiration validation
        'exp_month_required' => 'شهر انتهاء الصلاحية مطلوب',
        'exp_month_range' => 'الشهر يجب أن يكون بين 1 و 12',
        'exp_year_required' => 'سنة انتهاء الصلاحية مطلوبة',
        'exp_year_past' => 'السنة لا يمكن أن تكون في الماضي',
        'exp_year_future' => 'السنة بعيدة جداً في المستقبل',
        'card_expired' => 'البطاقة منتهية الصلاحية',

        // CVC validation
        'cvc_required' => 'رمز الأمان مطلوب',
        'cvc_length' => 'رمز الأمان يجب أن يكون 3 أو 4 أرقام',
        'cvc_format' => 'رمز الأمان يجب أن يحتوي على أرقام فقط',
        'cvc_amex_length' => 'رمز أمان أمريكان إكسبريس يجب أن يكون 4 أرقام',
        'cvc_standard_length' => 'رمز الأمان يجب أن يكون 3 أرقام',

        // Cardholder info validation
        'name_required' => 'اسم حامل البطاقة مطلوب',
        'name_format' => 'الاسم يجب أن يحتوي على حروف ومسافات فقط',
        'street_required' => 'عنوان الشارع مطلوب',
        'city_required' => 'المدينة مطلوبة',
        'city_format' => 'اسم المدينة يحتوي على أحرف غير صالحة',
        'state_required' => 'الولاية مطلوبة',
        'postal_code_required' => 'الرمز البريدي مطلوب',
        'postal_code_format' => 'تنسيق الرمز البريدي غير صحيح',
        'country_required' => 'البلد مطلوب',
        'country_format' => 'البلد يجب أن يكون رمز من حرفين (مثال: AE، SA)',

        // Card management validation
        'card_id_required' => 'معرف البطاقة مطلوب',
        'card_id_string' => 'معرف البطاقة يجب أن يكون نص صحيح',
        'card_id_max' => 'معرف البطاقة طويل جداً',
        'card_id_format' => 'تنسيق معرف البطاقة غير صحيح',
        'card_not_found' => 'البطاقة غير موجودة أو لا تنتمي لك',

        // Payment method validation
        'payment_method_id_invalid' => 'معرف طريقة الدفع غير صحيح',
        'save_card_boolean' => 'خيار حفظ البطاقة يجب أن يكون صحيح أو خطأ',
        'cannot_save_existing_card' => 'لا يمكن حفظ بطاقة محفوظة مسبقاً',

        // UPDATED: Contact info validation (more flexible)
        'contact_info_required' => 'البريد الإلكتروني أو رقم الهاتف مطلوب لحفظ طرق الدفع. يرجى تحديث ملفك الشخصي.',
        'email_recommended' => 'البريد الإلكتروني مُوصى به لإيصالات الدفع والإشعارات',
        'phone_recommended' => 'رقم الهاتف مُوصى به لتأكيد المدفوعات',

        // General card errors
        'already_exists' => 'هذه البطاقة محفوظة بالفعل في حسابك',
        'card_declined' => 'تم رفض بطاقتك',
        'insufficient_funds' => 'رصيد غير كافي',
        'card_not_supported' => 'نوع البطاقة غير مدعوم',
    ],

    'attributes' => [
        'card_number' => 'رقم البطاقة',
        'exp_month' => 'شهر انتهاء الصلاحية',
        'exp_year' => 'سنة انتهاء الصلاحية',
        'cvc' => 'رمز الأمان',
        'name' => 'اسم حامل البطاقة',
        'street' => 'عنوان الشارع',
        'city' => 'المدينة',
        'state' => 'الولاية',
        'postal_code' => 'الرمز البريدي',
        'country' => 'البلد',
        'card_id' => 'معرف البطاقة',
        'payment_method_id' => 'طريقة الدفع',
        'save_card' => 'خيار حفظ البطاقة',
    ],

    'messages' => [
        'added_successfully' => 'تم إضافة البطاقة بنجاح',
        'deleted_successfully' => 'تم حذف البطاقة بنجاح',
        'payment_successful' => 'تم معالجة الدفع بنجاح',
        'payment_failed' => 'فشل في الدفع. يرجى المحاولة مرة أخرى',
        
        // UPDATED: More flexible messages
        'no_cards_found' => 'لم يتم العثور على بطاقات محفوظة',
        'contact_info_required_for_cards' => 'يرجى إضافة بريد إلكتروني أو رقم هاتف لملفك الشخصي لحفظ بطاقات الدفع',
        'profile_update_recommended' => 'حدث ملفك الشخصي بمعلومات الاتصال للحصول على تجربة دفع أفضل',
    ],

    // NEW: Profile recommendations
    'recommendations' => [
        'add_email' => 'أضف عنوان بريد إلكتروني لإيصالات الدفع وأمان الحساب',
        'add_phone' => 'أضف رقم هاتف لتأكيد المدفوعات والإشعارات',
        'add_name' => 'أضف اسمك للحصول على تجربة دفع شخصية',
    ],

    // NEW: Contact method labels
    'contact_methods' => [
        'email' => 'البريد الإلكتروني',
        'phone' => 'الهاتف',
        'both' => 'البريد الإلكتروني والهاتف',
        'none' => 'لا توجد معلومات اتصال',
    ],
];