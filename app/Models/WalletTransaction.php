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
        'metadata' => 'array',
        'processed_at' => 'datetime',
    ];

    // Transaction Types
    const TYPE_EARNING = 'earning';
    const TYPE_COMMISSION = 'commission';
    const TYPE_WITHDRAWAL = 'withdrawal';
    const TYPE_REFUND = 'refund';
    const TYPE_ADJUSTMENT = 'adjustment';
    const TYPE_BONUS = 'bonus';
    const TYPE_MANUAL_PAYMENT = 'manual_payment';

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
     * Check if transaction is earning type
     */
    public function isEarning(): bool
    {
        return $this->type === self::TYPE_EARNING;
    }

    /**
     * Check if transaction is withdrawal type
     */
    public function isWithdrawal(): bool
    {
        return $this->type === self::TYPE_WITHDRAWAL;
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
     * Check if transaction is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Get transaction type label
     */
    public function getTypeLabel(): string
    {
        return match($this->type) {
            self::TYPE_EARNING => 'Booking Earning',
            self::TYPE_COMMISSION => 'Platform Commission',
            self::TYPE_WITHDRAWAL => 'Withdrawal',
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
     * Check if transaction increases balance
     */
    public function increasesBalance(): bool
    {
        return $this->amount > 0;
    }

    /**
     * Check if transaction decreases balance
     */
    public function decreasesBalance(): bool
    {
        return $this->amount < 0;
    }

    /**
     * Scopes
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
        return $query->where('type', self::TYPE_EARNING);
    }

    public function scopeWithdrawals($query)
    {
        return $query->where('type', self::TYPE_WITHDRAWAL);
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