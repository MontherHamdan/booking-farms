<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;


class FarmBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'farm_id',
        'booking_reference',
        'price_type',
        'booking_dates',
        'start_date',
        'end_date', 
        'start_time',
        'end_time',
        'guest_count',
        'subtotal',
        'discount_amount',
        'coupon_id',
        'coupon_code',
        'coupon_discount_amount',
        'total_amount',
        'deposit_amount',
        'remaining_amount',
        'payment_option',
        'platform', 
        'stripe_session_id',
        'stripe_payment_intent_id',
        'payment_status',
        'booking_status',
        'customer_name',
        'customer_email',
        'customer_phone',
        'notes',
        'expires_at',
        // WALLET-RELATED FIELDS
        'platform_commission_rate',
        'platform_commission_amount', 
        'farm_owner_earning',
        'earnings_processed',
        'earnings_processed_at',
        // PENDING BALANCE FIELDS
        'earnings_confirmed',
        'earnings_confirmed_at',
    ];

    protected $casts = [
        'booking_dates' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'coupon_discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'expires_at' => 'datetime',
        // WALLET-RELATED CASTS
        'platform_commission_rate' => 'decimal:2',
        'platform_commission_amount' => 'decimal:2',
        'farm_owner_earning' => 'decimal:2',
        'earnings_processed' => 'boolean',
        'earnings_processed_at' => 'datetime',
        // PENDING BALANCE CASTS
        'earnings_confirmed' => 'boolean',
        'earnings_confirmed_at' => 'datetime',
    ];

    // PAYMENT STATUS CONSTANTS
    const PAYMENT_STATUS_PENDING = 'pending';
    const PAYMENT_STATUS_PAID = 'paid';
    const PAYMENT_STATUS_FAILED = 'failed';
    const PAYMENT_STATUS_EXPIRED = 'expired';      
    const PAYMENT_STATUS_REFUNDED = 'refunded';
    const PAYMENT_STATUS_PARTIALLY_PAID = 'partially_paid';

    // BOOKING STATUS CONSTANTS
    const BOOKING_STATUS_PENDING = 'pending';
    const BOOKING_STATUS_CONFIRMED = 'confirmed';
    const BOOKING_STATUS_FAILED = 'failed';       
    const BOOKING_STATUS_CANCELLED = 'cancelled'; 
    const BOOKING_STATUS_COMPLETED = 'completed';

    // PRICE TYPE CONSTANTS
    const PRICE_TYPE_DAY_USE = 'day_use';
    const PRICE_TYPE_NIGHT = 'night';
    const PRICE_TYPE_FULL_DAY = 'full_day';

    // PAYMENT OPTION CONSTANTS
    const PAYMENT_OPTION_FULL = 'full';
    const PAYMENT_OPTION_DEPOSIT = 'deposit';

    // PLATFORM CONSTANTS
    const PLATFORM_WEB = 'web';
    const PLATFORM_MOBILE = 'mobile';

    /**
     * Boot method to generate booking reference
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            if (empty($booking->booking_reference)) {
                $booking->booking_reference = 'BK' . strtoupper(uniqid());
            }
            
            if (empty($booking->payment_option)) {
                $booking->payment_option = self::PAYMENT_OPTION_FULL;
            }

            if (empty($booking->platform)) {
                $booking->platform = self::PLATFORM_WEB;
            }
        });
    }

    /**
     * RELATIONSHIPS
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function couponUsage()
    {
        return $this->hasOne(CouponUsage::class, 'booking_id');
    }

    /**
     * SCOPES
     */
    public function scopePending($query)
    {
        return $query->where('booking_status', self::BOOKING_STATUS_PENDING);
    }

    public function scopeConfirmed($query)
    {
        return $query->where('booking_status', self::BOOKING_STATUS_CONFIRMED);
    }

    public function scopeFailed($query)
    {
        return $query->where('booking_status', self::BOOKING_STATUS_FAILED);
    }

    public function scopeCancelled($query)
    {
        return $query->where('booking_status', self::BOOKING_STATUS_CANCELLED);
    }

    public function scopeCompleted($query)
    {
        return $query->where('booking_status', self::BOOKING_STATUS_COMPLETED);
    }

    public function scopeExpired($query)
    {
        return $query->where('booking_status', self::BOOKING_STATUS_FAILED)
                    ->where('payment_status', self::PAYMENT_STATUS_EXPIRED);
    }

    public function scopeShouldBeExpired($query)
    {
        return $query->where('booking_status', self::BOOKING_STATUS_PENDING)
                    ->where('expires_at', '<', now());
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', self::PAYMENT_STATUS_PAID);
    }

    public function scopePartiallyPaid($query)
    {
        return $query->where('payment_status', self::PAYMENT_STATUS_PARTIALLY_PAID);
    }

    public function scopeWithCoupon($query)
    {
        return $query->whereNotNull('coupon_id');
    }

    // NEW: Platform scopes
    public function scopeWeb($query)
    {
        return $query->where('platform', self::PLATFORM_WEB);
    }

    public function scopeMobile($query)
    {
        return $query->where('platform', self::PLATFORM_MOBILE);
    }

    /**
     * Scope for bookings that need earnings processing
     */
    public function scopeNeedsEarningsProcessing($query)
    {
        return $query->where('booking_status', self::BOOKING_STATUS_CONFIRMED)
                    ->where('earnings_processed', false)
                    ->whereIn('payment_status', [
                        self::PAYMENT_STATUS_PAID,
                        self::PAYMENT_STATUS_PARTIALLY_PAID
                    ]);
    }

    /**
     * Scope for bookings that need earnings confirmation
     */
    public function scopeNeedsEarningsConfirmation($query)
    {
        return $query->where('booking_status', self::BOOKING_STATUS_COMPLETED)
                    ->where('earnings_processed', true)
                    ->where('earnings_confirmed', false);
    }

    /**
     * Scope for bookings with processed earnings
     */
    public function scopeWithProcessedEarnings($query)
    {
        return $query->where('earnings_processed', true);
    }

    /**
     * Scope for bookings with confirmed earnings
     */
    public function scopeWithConfirmedEarnings($query)
    {
        return $query->where('earnings_confirmed', true);
    }

    /**
     * BOOLEAN CHECKS
     */
    public function isExpired(): bool
    {
        return $this->booking_status === self::BOOKING_STATUS_FAILED 
            && $this->payment_status === self::PAYMENT_STATUS_EXPIRED;
    }

    public function isPaymentExpired(): bool
    {
        return $this->expires_at && Carbon::now()->greaterThan($this->expires_at);
    }

    public function canBeCancelled(): bool
    {
        return $this->booking_status === self::BOOKING_STATUS_CONFIRMED;
    }

    public function canBeRefunded(): bool
    {
        return $this->booking_status === self::BOOKING_STATUS_CANCELLED 
            && in_array($this->payment_status, [self::PAYMENT_STATUS_PAID, self::PAYMENT_STATUS_PARTIALLY_PAID]);
    }

    public function hasDepositPayment(): bool
    {
        return $this->payment_option === self::PAYMENT_OPTION_DEPOSIT && $this->deposit_amount > 0;
    }

    public function isFullyPaid(): bool
    {
        return $this->payment_status === self::PAYMENT_STATUS_PAID;
    }

    public function isPartiallyPaid(): bool
    {
        return $this->payment_status === self::PAYMENT_STATUS_PARTIALLY_PAID;
    }

    public function hasCoupon(): bool
    {
        return !is_null($this->coupon_id);
    }

    public function hasEnded(): bool
    {
        if (!$this->end_date || !$this->end_time) {
            return false;
        }
    
        $bookingEndDateTime = Carbon::parse($this->end_date->format('Y-m-d') . ' ' . $this->end_time->format('H:i:s'));
        
        return now()->greaterThan($bookingEndDateTime);
    }

    /**
     * Check if booking has processed earnings
     */
    public function hasProcessedEarnings(): bool
    {
        return $this->earnings_processed && $this->farm_owner_earning > 0;
    }

    /**
     * Check if earnings have been confirmed
     */
    public function hasConfirmedEarnings(): bool
    {
        return $this->earnings_confirmed && $this->earnings_confirmed_at;
    }

    // NEW: Platform checks
    public function isWebBooking(): bool
    {
        return $this->platform === self::PLATFORM_WEB;
    }

    public function isMobileBooking(): bool
    {
        return $this->platform === self::PLATFORM_MOBILE;
    }

    /**
     * ATTRIBUTES
     */
    public function getAmountPaidAttribute(): float
    {
        if ($this->isFullyPaid()) {
            return $this->total_amount;
        }
        
        if ($this->isPartiallyPaid() && $this->hasDepositPayment()) {
            return $this->deposit_amount;
        }
        
        return 0;
    }

    public function getTotalDiscountAmountAttribute(): float
    {
        return ($this->discount_amount ?? 0) + ($this->coupon_discount_amount ?? 0);
    }

    public function getSubtotalBeforeDiscountsAttribute(): float
    {
        return $this->subtotal + $this->total_discount_amount;
    }

    public function getFormattedBookingDatesAttribute(): array
    {
        if (!$this->booking_dates) {
            return [];
        }

        return array_map(function ($date) {
            return [
                'date' => $date,
                'formatted' => Carbon::parse($date)->format('Y-m-d'),
                'human_readable' => Carbon::parse($date)->format('M d, Y'),
                'day_name' => Carbon::parse($date)->format('l'),
            ];
        }, $this->booking_dates);
    }

    public function getDurationInDaysAttribute(): int
    {
        return count($this->booking_dates ?? []);
    }

    public function getBookingTimeRangeAttribute(): string
    {
        if (!$this->start_time || !$this->end_time) {
            return '';
        }

        $startTime = $this->start_time->format('g:i A');
        $endTime = $this->end_time->format('g:i A');

        if ($this->start_date && $this->end_date && $this->start_date->equalTo($this->end_date)) {
            return "{$startTime} - {$endTime}";
        }

        return "From {$startTime} to {$endTime}";
    }

    public function getBookingPeriodAttribute(): string
    {
        if (!$this->start_date || !$this->end_date) {
            return '';
        }

        $startDate = $this->start_date->format('d M Y');
        $endDate = $this->end_date->format('d M Y');
        
        if ($this->start_date->equalTo($this->end_date)) {
            return $startDate . ($this->booking_time_range ? ' (' . $this->booking_time_range . ')' : '');
        }
        
        return "{$startDate} - {$endDate}" . ($this->booking_time_range ? ' (' . $this->booking_time_range . ')' : '');
    }

    // NEW: Platform label
    public function getPlatformLabelAttribute(): string
    {
        return match($this->platform) {
            self::PLATFORM_WEB => 'Web',
            self::PLATFORM_MOBILE => 'Mobile',
            default => 'Unknown'
        };
    }

    public function getBookingSummaryAttribute(): array
    {
        return [
            'reference' => $this->booking_reference,
            'farm_name' => $this->farm->name_en ?: $this->farm->name_ar,
            'dates' => $this->formatted_booking_dates,
            'period' => $this->booking_period,
            'time_range' => $this->booking_time_range,
            'guests' => $this->guest_count,
            'platform' => $this->platform_label,
            'subtotal' => $this->subtotal,
            'offer_discount' => $this->discount_amount ?? 0,
            'coupon_discount' => $this->coupon_discount_amount ?? 0,
            'coupon_code' => $this->coupon_code,
            'total_discount' => $this->total_discount_amount,
            'total' => $this->total_amount,
            'paid' => $this->amount_paid,
            'remaining' => $this->remaining_amount,
            'payment_type' => $this->getPaymentTypeLabel(),
            'status' => $this->booking_status,
            'payment_status' => $this->payment_status,
        ];
    }

    public function getFormattedStartDatetimeAttribute(): string
    {
        if (!$this->start_date || !$this->start_time) {
            return '';
        }

        return $this->start_date->format('Y-m-d') . ' ' . $this->start_time->format('H:i');
    }

    public function getFormattedEndDatetimeAttribute(): string
    {
        if (!$this->end_date || !$this->end_time) {
            return '';
        }

        return $this->end_date->format('Y-m-d') . ' ' . $this->end_time->format('H:i');
    }

    /**
     * Get commission percentage for display
     */
    public function getCommissionPercentageAttribute(): string
    {
        return $this->platform_commission_rate ? $this->platform_commission_rate . '%' : 'N/A';
    }

    /**
     * Get net earning percentage (what farm owner gets)
     */
    public function getNetEarningPercentageAttribute(): string
    {
        if (!$this->platform_commission_rate) {
            return 'N/A';
        }
        
        $netPercentage = 100 - $this->platform_commission_rate;
        return $netPercentage . '%';
    }

    /**
     * STATUS CHANGE METHODS
     */
    public function markAsPaid($paymentIntentId = null): void
    {
        $wasAlreadyPaid = $this->isFullyPaid() || $this->isPartiallyPaid();
        
        $newStatus = $this->hasDepositPayment() 
            ? self::PAYMENT_STATUS_PARTIALLY_PAID 
            : self::PAYMENT_STATUS_PAID;
    
        $this->update([
            'payment_status' => $newStatus,
            'booking_status' => self::BOOKING_STATUS_CONFIRMED,
            'stripe_payment_intent_id' => $paymentIntentId ?: $this->stripe_payment_intent_id,
        ]);
    
        // Mark coupon as used if coupon was applied
        if ($this->coupon_id) {
            $this->coupon->markAsUsed($this->user_id, $this->id);
        }
        
        // Process earnings if this is the first time being marked as paid
        if (!$wasAlreadyPaid && $this->shouldProcessEarnings()) {
            try {
                $this->processEarnings();
            } catch (\Exception $e) {
                \Log::error('Failed to auto-process earnings for booking', [
                    'booking_id' => $this->id,
                    'booking_reference' => $this->booking_reference,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }
    }

    public function markAsFullyPaid($paymentIntentId = null): void
    {
        $this->update([
            'payment_status' => self::PAYMENT_STATUS_PAID,
            'booking_status' => self::BOOKING_STATUS_CONFIRMED,
            'remaining_amount' => 0,
            'stripe_payment_intent_id' => $paymentIntentId ?: $this->stripe_payment_intent_id,
        ]);

        // Mark coupon as used if coupon was applied and not already marked
        if ($this->coupon_id && !$this->couponUsage) {
            $this->coupon->markAsUsed($this->user_id, $this->id);
        }
    }

    public function markAsFailed(): void
    {
        $this->update([
            'payment_status' => self::PAYMENT_STATUS_FAILED,
            'booking_status' => self::BOOKING_STATUS_FAILED,
        ]);
    }

    public function markAsExpired(): void
    {
        $this->update([
            'payment_status' => self::PAYMENT_STATUS_EXPIRED,
            'booking_status' => self::BOOKING_STATUS_FAILED,
        ]);
    }

    public function cancel(): void
    {
        if ($this->booking_status !== self::BOOKING_STATUS_CONFIRMED) {
            throw new \InvalidArgumentException('Only confirmed bookings can be cancelled');
        }
        
        $wasEarningsProcessed = $this->earnings_processed;
        $wasEarningsConfirmed = $this->earnings_confirmed;
    
        $this->update([
            'booking_status' => self::BOOKING_STATUS_CANCELLED,
        ]);
        
        if ($wasEarningsProcessed || $wasEarningsConfirmed) {
            try {
                $walletService = app(\App\Services\FarmOwnerWalletService::class);
                $walletService->processBookingRefund($this);
            } catch (\Exception $e) {
                \Log::error('Failed to process refund for cancelled booking', [
                    'booking_id' => $this->id,
                    'booking_reference' => $this->booking_reference,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * HELPER METHODS
     */
    public function setBookingTimes(Farm $farm): void
    {
        if (!$this->booking_dates || empty($this->booking_dates)) {
            return;
        }

        $pricing = $farm->pricing()->where('price_type', $this->price_type)->first();
        
        if (!$pricing) {
            return;
        }

        switch ($this->price_type) {
            case self::PRICE_TYPE_DAY_USE:
                $this->start_date = Carbon::parse(min($this->booking_dates));
                $this->end_date = Carbon::parse(max($this->booking_dates));
                $this->start_time = Carbon::parse($pricing->start_time);
                $this->end_time = Carbon::parse($pricing->end_time);
                break;
                
            case self::PRICE_TYPE_NIGHT:
                $selectedDate = Carbon::parse($this->booking_dates[0]);
                $this->start_date = $selectedDate;
                
                $startTime = Carbon::parse($pricing->start_time);
                $endTime = Carbon::parse($pricing->end_time);
                
                if ($endTime->format('H:i') < $startTime->format('H:i')) {
                    $this->end_date = $selectedDate->copy()->addDay();
                } else {
                    $this->end_date = $selectedDate;
                }
                
                $this->start_time = $startTime;
                $this->end_time = $endTime;
                break;
                
            case self::PRICE_TYPE_FULL_DAY:
                $this->start_date = Carbon::parse(min($this->booking_dates));
                $this->end_date = Carbon::parse(max($this->booking_dates));
                
                $dayUsePricing = $farm->pricing()->where('price_type', self::PRICE_TYPE_DAY_USE)->first();
                $nightPricing = $farm->pricing()->where('price_type', self::PRICE_TYPE_NIGHT)->first();
                
                if ($dayUsePricing && $nightPricing) {
                    $this->start_time = Carbon::parse($dayUsePricing->start_time);
                    $this->end_time = Carbon::parse($nightPricing->end_time);
                    
                    $nightStart = Carbon::parse($nightPricing->start_time);
                    $nightEnd = Carbon::parse($nightPricing->end_time);
                    
                    if ($nightEnd->format('H:i') < $nightStart->format('H:i')) {
                        $this->end_date = $this->end_date->addDay();
                    }
                } else {
                    $this->start_time = Carbon::parse('00:00');
                    $this->end_time = Carbon::parse('23:59');
                }
                break;
        }
    }

    public function getPaymentTypeLabel(): string
    {
        return $this->payment_option === self::PAYMENT_OPTION_DEPOSIT 
            ? __('booking.payment_type.deposit') 
            : __('booking.payment_type.full');
    }

    /**
     * Process earnings when booking is confirmed and paid
     */
    public function processEarnings(): void
    {
        if ($this->earnings_processed) {
            throw new \InvalidArgumentException('Earnings already processed for this booking');
        }

        if ($this->booking_status !== self::BOOKING_STATUS_CONFIRMED) {
            throw new \InvalidArgumentException('Only confirmed bookings can have earnings processed');
        }

        $walletService = app(\App\Services\FarmOwnerWalletService::class);
        $walletService->processBookingEarning($this);
    }

    /**
     * Confirm earnings (move from pending to confirmed balance)
     */
    public function confirmEarnings(): void
    {
        if ($this->earnings_confirmed) {
            throw new \InvalidArgumentException('Earnings already confirmed for this booking');
        }

        if (!$this->earnings_processed) {
            throw new \InvalidArgumentException('Earnings must be processed before they can be confirmed');
        }

        if ($this->booking_status !== self::BOOKING_STATUS_COMPLETED) {
            throw new \InvalidArgumentException('Only completed bookings can have earnings confirmed');
        }

        $walletService = app(\App\Services\FarmOwnerWalletService::class);
        $walletService->confirmBookingEarning($this);
    }

    /**
     * Check if earnings should be processed automatically
     */
    public function shouldProcessEarnings(): bool
    {
        return $this->booking_status === self::BOOKING_STATUS_CONFIRMED 
            && !$this->earnings_processed 
            && in_array($this->payment_status, [
                self::PAYMENT_STATUS_PAID,
                self::PAYMENT_STATUS_PARTIALLY_PAID
            ]);
    }

    /**
     * Check if earnings should be confirmed automatically
     */
    public function shouldConfirmEarnings(): bool
    {
        return $this->booking_status === self::BOOKING_STATUS_COMPLETED 
            && $this->earnings_processed 
            && !$this->earnings_confirmed;
    }

    /**
     * Get farm owner from booking
     */
    public function getFarmOwner(): ?User
    {
        return $this->farm?->user;
    }

    /**
     * Get earning breakdown
     */
    public function getEarningBreakdown(): array
    {
        return [
            'total_amount' => $this->total_amount,
            'commission_rate' => $this->platform_commission_rate,
            'commission_amount' => $this->platform_commission_amount,
            'farm_owner_earning' => $this->farm_owner_earning,
            'earnings_processed' => $this->earnings_processed,
            'earnings_processed_at' => $this->earnings_processed_at,
            'earnings_confirmed' => $this->earnings_confirmed,
            'earnings_confirmed_at' => $this->earnings_confirmed_at,
            'earning_status' => $this->getEarningStatus(),
        ];
    }

    /**
     * Get earning status for display
     */
    public function getEarningStatus(): string
    {
        if (!$this->earnings_processed) {
            return 'not_processed';
        }

        if ($this->earnings_processed && !$this->earnings_confirmed) {
            return 'pending_confirmation';
        }

        if ($this->earnings_confirmed) {
            return 'confirmed';
        }

        return 'unknown';
    }

    /**
     * Get earning status label
     */
    public function getEarningStatusLabel(): string
    {
        return match($this->getEarningStatus()) {
            'not_processed' => 'Not Processed',
            'pending_confirmation' => 'Pending Confirmation',
            'confirmed' => 'Confirmed',
            default => 'Unknown',
        };
    }

    /**
     * Calculate what the earning would be with current commission rate
     */
    public function calculateCurrentEarning(): array
    {
        $wallet = $this->getFarmOwner()?->farmOwnerWallet;
        $currentCommissionRate = $wallet?->platform_commission_rate ?? 15.00;
        
        $commissionAmount = ($this->total_amount * $currentCommissionRate) / 100;
        $farmOwnerEarning = $this->total_amount - $commissionAmount;
        
        return [
            'total_amount' => $this->total_amount,
            'current_commission_rate' => $currentCommissionRate,
            'current_commission_amount' => $commissionAmount,
            'current_farm_owner_earning' => $farmOwnerEarning,
            'difference_from_processed' => $farmOwnerEarning - ($this->farm_owner_earning ?? 0),
        ];
    }
}