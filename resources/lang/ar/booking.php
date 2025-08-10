<?php

return [

    // Payment status messages
    'not_found' => 'الحجز غير موجود',
    'payment_successful' => 'تم الدفع بنجاح',
    'additional_authentication_required' => 'يتطلب مصادقة إضافية',
    
    // Payment status variations
    'payment_succeeded' => 'تم الدفع بنجاح',
    'payment_pending' => 'الدفع قيد المعالجة',
    'payment_requires_payment_method' => 'يتطلب الدفع طريقة دفع صالحة',
    'payment_requires_confirmation' => 'يتطلب الدفع التأكيد',
    'payment_requires_action' => 'يتطلب الدفع مصادقة إضافية',
    'payment_processing' => 'الدفع قيد المعالجة حالياً',
    'payment_requires_capture' => 'يتطلب الدفع الحصول على المبلغ',
    'payment_canceled' => 'تم إلغاء الدفع',
    'payment_failed' => 'فشل الدفع',
    'payment_intent_created' => 'تم إنشاء نية الدفع بنجاح',
    'deposit_not_available' => 'خيار دفع العربون غير متوفر لهذه المزرعة',

    'validation' => [
        'payment_option.required' => 'خيار الدفع مطلوب',
        'payment_option.in' => 'خيار الدفع المحدد غير صالح. القيم المسموحة: كامل، عربون',
        'guest_count.required' => 'عدد الضيوف مطلوب',
        'guest_count.integer' => 'عدد الضيوف يجب أن يكون رقمًا صحيحًا',
        'guest_count.min' => 'عدد الضيوف يجب أن يكون على الأقل 1',

        'customer_name.required' => 'اسم العميل مطلوب',
        'customer_email.required' => 'البريد الإلكتروني مطلوب',
        'customer_email.email' => 'البريد الإلكتروني غير صالح',
        'customer_phone.required' => 'رقم الهاتف مطلوب',
        'notes.max' => 'الملاحظات لا يمكن أن تتجاوز :max حرفًا',
    ],

    'attributes' => [
        'payment_option' => 'خيار الدفع',
        'guest_count' => 'عدد الضيوف',
        'customer_name' => 'اسم العميل',
        'customer_email' => 'البريد الإلكتروني للعميل',
        'customer_phone' => 'رقم هاتف العميل',
        'notes' => 'الملاحظات',
    ],
];