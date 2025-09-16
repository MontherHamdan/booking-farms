<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'wallet_id',
        'booking_id',
        'reference',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'pending_balance_before',
        'pending_balance_after',
        'description',
        'status',
        'metadata',
        'processed_by',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'pending_balance_before' => 'decimal:2',
        'pending_balance_after' => 'decimal:2',
        'metadata' => 'array',
        'processed_at' => 'datetime',
    ];

    // CLEANED UP TRANSACTION TYPES - Remove old unused types
    const TYPE_PENDING_EARNING = 'pending_earning';     // When booking confirmed (→ pending_balance)
    const TYPE_EARNING_CONFIRMED = 'earning_confirmed'; // When booking completed (pending → balance)
    const TYPE_COMMISSION = 'commission';               // Platform commission deduction
    const TYPE_MANUAL_PAYMENT = 'manual_payment';      // Admin payment to farm owner
    const TYPE_REFUND = 'refund';                      // Refund deduction
    const TYPE_ADJUSTMENT = 'adjustment';              // Admin adjustment
    const TYPE_BONUS = 'bonus';                        // Admin bonus

    // Transaction Status
    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Get the wallet that owns this transaction
     */
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(FarmOwnerWallet::class, 'wallet_id');
    }

    /**
     * Get the related booking if applicable
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(FarmBooking::class);
    }

    /**
     * Get the admin who processed this transaction
     */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Check if transaction is any earning type
     */
    public function isEarning(): bool
    {
        return in_array($this->type, [self::TYPE_PENDING_EARNING, self::TYPE_EARNING_CONFIRMED]);
    }

    /**
     * Check if transaction is pending earning
     */
    public function isPendingEarning(): bool
    {
        return $this->type === self::TYPE_PENDING_EARNING;
    }

    /**
     * Check if transaction is confirmed earning
     */
    public function isConfirmedEarning(): bool
    {
        return $this->type === self::TYPE_EARNING_CONFIRMED;
    }

    /**
     * Check if transaction is manual payment (was withdrawal)
     */
    public function isManualPayment(): bool
    {
        return $this->type === self::TYPE_MANUAL_PAYMENT;
    }

    /**
     * Check if transaction is commission deduction
     */
    public function isCommission(): bool
    {
        return $this->type === self::TYPE_COMMISSION;
    }

    /**
     * Check if transaction is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Get transaction type label - UPDATED
     */
    public function getTypeLabel(): string
    {
        return match($this->type) {
            self::TYPE_PENDING_EARNING => 'Pending Earning',
            self::TYPE_EARNING_CONFIRMED => 'Confirmed Earning',
            self::TYPE_COMMISSION => 'Platform Commission',
            self::TYPE_MANUAL_PAYMENT => 'Payment',
            self::TYPE_REFUND => 'Refund Deduction',
            self::TYPE_ADJUSTMENT => 'Admin Adjustment',
            self::TYPE_BONUS => 'Bonus',
            default => ucfirst($this->type),
        };
    }

    /**
     * Get transaction status label
     */
    public function getStatusLabel(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'Pending',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_FAILED => 'Failed',
            self::STATUS_CANCELLED => 'Cancelled',
            default => ucfirst($this->status),
        };
    }

    /**
     * Get formatted amount with sign
     */
    public function getFormattedAmountAttribute(): string
    {
        $sign = $this->amount >= 0 ? '+' : '';
        return $sign . number_format($this->amount, 2) . ' AED';
    }

    /**
     * Check if transaction affects pending balance
     */
    public function affectsPendingBalance(): bool
    {
        return in_array($this->type, [
            self::TYPE_PENDING_EARNING,
            self::TYPE_EARNING_CONFIRMED
        ]);
    }

    /**
     * UPDATED SCOPES - Remove references to old types
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeEarnings($query)
    {
        return $query->whereIn('type', [self::TYPE_PENDING_EARNING, self::TYPE_EARNING_CONFIRMED]);
    }

    public function scopePendingEarnings($query)
    {
        return $query->where('type', self::TYPE_PENDING_EARNING);
    }

    public function scopeConfirmedEarnings($query)
    {
        return $query->where('type', self::TYPE_EARNING_CONFIRMED);
    }

    public function scopeManualPayments($query)
    {
        return $query->where('type', self::TYPE_MANUAL_PAYMENT);
    }

    public function scopeCommissions($query)
    {
        return $query->where('type', self::TYPE_COMMISSION);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
    }

    public function scopeLastMonth($query)
    {
        $lastMonth = now()->subMonth();
        return $query->whereMonth('created_at', $lastMonth->month)
                    ->whereYear('created_at', $lastMonth->year);
    }
}