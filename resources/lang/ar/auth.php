<?php

return [
    'failed' => 'بيانات الاعتماد المقدمة غير صحيحة.',
    'inactive_account' => 'حسابك غير نشط. يرجى الاتصال بالدعم.',
    'logged_out_successfully' => 'تم تسجيل الخروج بنجاح',
    'unauthorized' => 'غير مصرح لك بالوصول إلى هذا المورد.',
    
    // OTP related messages
    'otp_sent' => 'تم إرسال رمز التحقق إلى رقم هاتفك.',
    'otp_resent' => 'تم إعادة إرسال رمز التحقق إلى رقم هاتفك.',
    'invalid_otp' => 'رمز التحقق غير صحيح.',
    'otp_expired' => 'انتهت صلاحية رمز التحقق. يرجى طلب رمز جديد.',
    'user_not_found' => 'المستخدم غير موجود أو تم التحقق منه بالفعل.',
    'invalid_verification' => 'رمز التحقق أو الرمز السري غير صحيح.',
    'invalid_token' => 'الرمز السري غير صالح أو مفقود.',
    'registration_complete' => 'تم إكمال التسجيل بنجاح.',
    'registered_successfully' => 'تم تسجيل المستخدم بنجاح.',

    // ────────── Validation Messages ──────────
    'validation' => [
        'name.required'             => 'الاسم مطلوب.',
        'name.string'               => 'الاسم يجب أن يكون نصًا.',
        'name.min'                  => 'يجب أن يكون الاسم على الأقل :min أحرف.',
        'name.max'                  => 'لا يمكن أن يتجاوز الاسم :max أحرف.',

        'email.required'            => 'البريد الإلكتروني مطلوب.',
        'email.email'               => 'يجب أن يكون البريد الإلكتروني عنوانًا صحيحًا.',
        'email.max'                 => 'لا يمكن أن يتجاوز البريد الإلكتروني :max أحرف.',
        'email.unique'              => 'هذا البريد الإلكتروني مستخدم بالفعل.',

        'city_id.required'          => 'المدينة مطلوبة.',
        'city_id.integer'           => 'معرف المدينة يجب أن يكون رقمًا صحيحًا.',
        'city_id.exists'            => 'المدينة المحددة غير موجودة.',

        'phone.required'            => 'رقم الهاتف مطلوب.',
        'phone.string'              => 'رقم الهاتف يجب أن يكون نصًا من الأرقام.',
        'phone.max'                 => 'لا يمكن أن يتجاوز رقم الهاتف :max أحرف.',
        'phone.unique'              => 'رقم الهاتف هذا مستخدم بالفعل.',

        'password.required'         => 'كلمة المرور مطلوبة.',
        'password.min'              => 'يجب أن تكون كلمة المرور على الأقل :min أحرف.',
        'password.confirmed'        => 'تأكيد كلمة المرور لا يطابق.',

        'current_password.required_with' => 'يجب إدخال كلمة المرور الحالية عند تغييرها.',

        'avatar.required' => 'يرجى اختيار صورة للأفاتار.',
        'avatar.image'    => 'الملف المرفوع يجب أن يكون صورة.',
        'avatar.mimes'    => 'يجب أن تكون صيغة الصورة واحدة من: jpg, jpeg, png, gif, webp.',
        'avatar.max'      => 'لا يمكن أن تزيد صورة الأفاتار عن :max كيلوبايت.',
    ],

    // ────────── Human-readable Attribute Names ──────────
    'attributes' => [
        'name'             => 'الاسم',
        'email'            => 'البريد الإلكتروني',
        'city_id'          => 'المدينة',
        'phone'            => 'رقم الهاتف',
        'password'         => 'كلمة المرور',
        'password_confirmation' => 'تأكيد كلمة المرور',
        'current_password' => 'كلمة المرور الحالية',
        'avatar' => 'الأفاتار',
    ],

    // Custom success messages
    'profile_updated_successfully' => 'تم تحديث الملف الشخصي بنجاح.',
    'avatar_updated_successfully' => 'تم تحديث صورة الأفاتار بنجاح.',
    'avatar_deleted_successfully' => 'تم حذف صورة الأفاتار بنجاح.',
    
    // Custom error messages
    'no_avatar_to_delete'         => 'لا توجد صورة للأفاتار للحذف.',
];