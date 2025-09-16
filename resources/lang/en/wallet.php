<?php

return [
    'transaction_types' => [
        'pending_earning' => 'Pending Earning', // NEW
        'earning_confirmed' => 'Confirmed Earning', // NEW
        'manual_payment' => 'Manual Payment',
        'commission' => 'Platform Commission',
        'refund' => 'Refund',
        'adjustment' => 'Balance Adjustment',
        'bonus' => 'Bonus Payment', // NEW
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
        'earning_confirmed' => 'Earning confirmed and moved to balance', // NEW
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