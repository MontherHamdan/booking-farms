<?php

return [
    'transaction_types' => [
        'earning' => 'إيرادات',
        'manual_payment' => 'دفع يدوي',
        'commission' => 'عمولة المنصة',
        'refund' => 'استرداد',
        'adjustment' => 'تعديل الرصيد',
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