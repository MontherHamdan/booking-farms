<?php

return [
    'transaction_types' => [
        'pending_earning' => 'إيرادات معلقة',
        'earning_confirmed' => 'إيرادات مؤكدة',
        'manual_payment' => 'دفع يدوي',
        'commission' => 'عمولة المنصة',
        'refund' => 'استرداد',
        'adjustment' => 'تعديل الرصيد',
        'bonus' => 'مكافأة', 
    ],

    'transaction_descriptions' => [
        'pending_earning' => 'إيرادات معلقة من الحجوزات المؤكدة',
        'earning_confirmed' => 'إيرادات مؤكدة من الحجوزات المكتملة',
        'manual_payment' => 'دفعات تمت معالجتها من قبل المسؤول',
        'commission' => 'خصومات عمولة المنصة',
        'refund' => 'استردادات للحجوزات الملغاة',
        'adjustment' => 'تعديلات رصيد من المسؤول',
        'bonus' => 'مدفوعات مكافآت من المسؤول',
    ],

    'transaction_status' => [
        'pending' => 'معلق',
        'completed' => 'مكتمل',
        'failed' => 'فشل',
        'cancelled' => 'ملغى',
    ],

    'payment_methods' => [
        'iban' => 'تحويل بنكي (آيبان)',
        'cliq' => 'تحويل كليك',
        'cash' => 'دفع نقدي',
        'check' => 'شيك بنكي',
    ],

    'wallet_status' => [
        'active' => 'نشط',
        'inactive' => 'غير نشط',
        'suspended' => 'معلق',
    ],

    'messages' => [
        'insufficient_balance' => 'رصيد المحفظة غير كافي',
        'payment_processed' => 'تم معالجة الدفع بنجاح',
        'earning_added' => 'تم إضافة الإيرادات إلى المحفظة',
        'earning_confirmed' => 'تم تأكيد الإيرادات ونقلها إلى الرصيد',
        'refund_processed' => 'تم معالجة الاسترداد بنجاح',
    ],

    'labels' => [
        'balance' => 'الرصيد الحالي',
        'pending_balance' => 'الرصيد المعلق',
        'total_earned' => 'إجمالي المكاسب',
        'total_paid_out' => 'إجمالي المدفوعات',
        'commission_rate' => 'معدل العمولة',
        'minimum_transfer' => 'الحد الأدنى للتحويل',
        'transfer_frequency' => 'تكرار التحويل (أيام)',
    ],
];