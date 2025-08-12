<?php

return [
    // Booking status messages
    'not_found' => 'الحجز غير موجود',
    'cannot_be_cancelled' => 'لا يمكن إلغاء هذا الحجز',
    'cancelled_successfully' => 'تم إلغاء الحجز بنجاح',
    
    // Payment status messages
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

    // Booking statuses
    'status' => [
        'pending' => 'في انتظار الدفع',
        'confirmed' => 'مؤكد',
        'failed' => 'فشل الدفع',
        'expired' => 'انتهت صلاحية الدفع',
        'cancelled' => 'ملغي',
        'completed' => 'مكتمل',
    ],

    // Status descriptions
    'status_description' => [
        'pending' => 'في انتظار إتمام الدفع',
        'confirmed' => 'حجزك مؤكد وجاهز',
        'failed' => 'فشل الدفع أثناء عملية الشراء',
        'expired' => 'انتهت صلاحية نافذة الدفع (30 دقيقة)',
        'cancelled' => 'تم إلغاء الحجز من قبل المستخدم',
        'completed' => 'تم إكمال الخدمة بنجاح',
    ],

    // Payment statuses
    'payment_status' => [
        'pending' => 'الدفع معلق',
        'paid' => 'مدفوع',
        'failed' => 'فشل الدفع',
        'expired' => 'انتهت صلاحية نافذة الدفع',
        'partially_paid' => 'مدفوع جزئياً',
        'refunded' => 'مسترد',
    ],

    // Payment types
    'payment_type' => [
        'full' => 'دفع كامل',
        'deposit' => 'دفع عربون',
    ],

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