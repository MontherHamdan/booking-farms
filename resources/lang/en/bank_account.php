<?php

return [
    'messages' => [
        'not_found' => 'No bank account found',
        'saved_successfully' => 'Bank account saved successfully',
        'deleted_successfully' => 'Bank account deleted successfully',
        'no_account_to_delete' => 'No bank account found to delete',
    ],

    'account_types' => [
        'iban' => 'Bank Account (IBAN)',
        'cliq' => 'CLIQ Transfer',
    ],

    'labels' => [
        'account_type' => 'Account Type',
        'account_holder_name' => 'Account Holder Name',
        'iban' => 'IBAN Number',
        'cliq_alias' => 'CLIQ Alias',
        'cliq_phone' => 'CLIQ Phone Number',
        'is_active' => 'Account Status',
    ],

    'validation' => [
        'account_type.required' => 'Please select an account type',
        'account_type.in' => 'Account type must be either IBAN or CLIQ',
        
        'account_holder_name.required' => 'Account holder name is required',
        'account_holder_name.string' => 'Account holder name must be text',
        'account_holder_name.max' => 'Account holder name cannot exceed :max characters',
        
        'iban.required_if' => 'IBAN number is required for IBAN transfers',
        'iban.string' => 'IBAN must be text',
        'iban.max' => 'IBAN cannot exceed :max characters',
        'iban.regex' => 'Please enter a valid IBAN format',
        'iban.unique' => 'This IBAN is already registered',
        
        'cliq_alias.string' => 'CLIQ alias must be text',
        'cliq_alias.max' => 'CLIQ alias cannot exceed :max characters',
        
        'cliq_phone.string' => 'CLIQ phone must be text',
        'cliq_phone.max' => 'CLIQ phone cannot exceed :max characters',
        'cliq_phone.regex' => 'Please enter a valid phone number format',
        
        'cliq_details_required' => 'Either CLIQ alias or phone number is required for CLIQ transfers',

        'bank_id' => [
            'required' => 'Bank selection is required.',
            'exists' => 'Selected bank does not exist.',
            'inactive' => 'Selected bank is not active.',
        ],
    ],

    'attributes' => [
        'account_type' => 'Account Type',
        'account_holder_name' => 'Account Holder Name',
        'iban' => 'IBAN Number',
        'cliq_alias' => 'CLIQ Alias',
        'cliq_phone' => 'CLIQ Phone Number',
        'bank_id' => 'Bank',
    ],
];