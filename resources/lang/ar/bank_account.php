
<?php

return [
    'messages' => [
        'not_found' => 'لا يوجد حساب بنكي',
        'saved_successfully' => 'تم حفظ الحساب البنكي بنجاح',
        'deleted_successfully' => 'تم حذف الحساب البنكي بنجاح',
        'no_account_to_delete' => 'لا يوجد حساب بنكي للحذف',
    ],

    'account_types' => [
        'iban' => '(Iban) حساب بنكي ',
        'cliq' => 'تحويل كليك',
    ],

    'labels' => [
        'account_type' => 'نوع الحساب',
        'account_holder_name' => 'اسم صاحب الحساب',
        'iban' => 'رقم الآيبان',
        'bank_name' => 'اسم البنك',
        'cliq_alias' => 'اسم كليك المستعار',
        'cliq_phone' => 'رقم هاتف كليك',
        'is_active' => 'حالة الحساب',
    ],

    'validation' => [
        'account_type.required' => 'يرجى اختيار نوع الحساب',
        'account_type.in' => 'نوع الحساب يجب أن يكون آيبان أو كليك',
        
        'account_holder_name.required' => 'اسم صاحب الحساب مطلوب',
        'account_holder_name.string' => 'اسم صاحب الحساب يجب أن يكون نصاً',
        'account_holder_name.max' => 'اسم صاحب الحساب لا يمكن أن يتجاوز :max حرف',
        
        'iban.required_if' => 'رقم الآيبان مطلوب للتحويلات البنكية',
        'iban.string' => 'الآيبان يجب أن يكون نصاً',
        'iban.max' => 'الآيبان لا يمكن أن يتجاوز :max حرف',
        'iban.regex' => 'يرجى إدخال رقم آيبان صحيح',
        'iban.unique' => 'هذا الآيبان مسجل مسبقاً',
        
        'bank_name.required_if' => 'اسم البنك مطلوب للتحويلات البنكية',
        'bank_name.string' => 'اسم البنك يجب أن يكون نصاً',
        'bank_name.max' => 'اسم البنك لا يمكن أن يتجاوز :max حرف',
        
        'cliq_alias.string' => 'اسم كليك المستعار يجب أن يكون نصاً',
        'cliq_alias.max' => 'اسم كليك المستعار لا يمكن أن يتجاوز :max حرف',
        
        'cliq_phone.string' => 'رقم هاتف كليك يجب أن يكون نصاً',
        'cliq_phone.max' => 'رقم هاتف كليك لا يمكن أن يتجاوز :max حرف',
        'cliq_phone.regex' => 'يرجى إدخال رقم هاتف صحيح',
        
        'cliq_details_required' => 'إما اسم كليك المستعار أو رقم الهاتف مطلوب لتحويلات كليك',
    ],

    'attributes' => [
        'account_type' => 'نوع الحساب',
        'account_holder_name' => 'اسم صاحب الحساب',
        'iban' => 'رقم الآيبان',
        'bank_name' => 'اسم البنك',
        'cliq_alias' => 'اسم كليك المستعار',
        'cliq_phone' => 'رقم هاتف كليك',
    ],
];