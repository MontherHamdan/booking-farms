<?php

return [
    'transaction_types' => [
        'pending_earning' => 'Pending Earning',
        'earning_confirmed' => 'Confirmed Earning',
        'manual_payment' => 'Manual Payment',
        'commission' => 'Platform Commission',
        'refund' => 'Refund',
        'adjustment' => 'Balance Adjustment',
        'bonus' => 'Bonus Payment',
    ],

    'transaction_descriptions' => [
        'pending_earning' => 'Pending earnings from confirmed bookings',
        'earning_confirmed' => 'Confirmed earnings from completed bookings',
        'manual_payment' => 'Payments processed by admin',
        'commission' => 'Platform commission deductions',
        'refund' => 'Refunds for cancelled bookings',
        'adjustment' => 'Admin balance adjustments',
        'bonus' => 'Admin bonus payments',
    ],

    'transaction_status' => [
        'pending' => 'Pending',
        'completed' => 'Completed',
        'failed' => 'Failed',
        'cancelled' => 'Cancelled',
    ],

    'payment_methods' => [
        'iban' => 'Bank Transfer (IBAN)',
        'cliq' => 'CliQ Transfer',
        'cash' => 'Cash Payment',
        'check' => 'Bank Check',
    ],

    'wallet_status' => [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'suspended' => 'Suspended',
    ],

    'messages' => [
        'insufficient_balance' => 'Insufficient wallet balance',
        'payment_processed' => 'Payment processed successfully',
        'earning_added' => 'Earning added to wallet',
        'earning_confirmed' => 'Earning confirmed and moved to balance',
        'refund_processed' => 'Refund processed successfully',
    ],

    'labels' => [
        'balance' => 'Current Balance',
        'pending_balance' => 'Pending Balance',
        'total_earned' => 'Total Earned',
        'total_paid_out' => 'Total Paid Out',
        'commission_rate' => 'Commission Rate',
        'minimum_transfer' => 'Minimum Transfer Amount',
        'transfer_frequency' => 'Transfer Frequency (Days)',
    ],
];