<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

/**
 * BOOKING STATUS FLOW:
 * 
 * 1. PENDING → User calls createPaymentIntent()
 *    - booking_status = 'pending'
 *    - payment_status = 'pending' 
 *    - expires_at = now() + 30 minutes
 *    - ❌ NOT shown in getUserBookings() (incomplete payment)
 * 
 * 2a. CONFIRMED → User completes payment successfully
 *     - markAsPaid() called
 *     - booking_status = 'confirmed'
 *     - payment_status = 'paid' OR 'partially_paid' (if deposit)
 *     - ✅ SHOWN in getUserBookings() (real booking)
 * 
 * 2b. FAILED → Payment fails during checkout process
 *     - markAsFailed() called  
 *     - booking_status = 'failed'
 *     - payment_status = 'failed'
 *     - ✅ SHOWN in getUserBookings() (for history)
 * 
 * 2c. EXPIRED → Payment window expires (30 minutes) and still pending
 *     - markAsExpired() called (via scheduled job)
 *     - booking_status = 'failed'
 *     - payment_status = 'expired'
 *     - ✅ SHOWN in getUserBookings() (for history)
 * 
 * 3. CANCELLED → User cancels confirmed booking (after successful payment)
 *    - cancel() called
 *    - booking_status = 'cancelled'
 *    - payment_status = UNCHANGED (remains 'paid' or 'partially_paid')
 *    - ✅ SHOWN in getUserBookings() (for refund processing)
 * 
 * 4. COMPLETED → Booking dates pass, service delivered
 *    - booking_status = 'completed' (probably set by cron job)
 *    - ✅ SHOWN in getUserBookings() (completed service)
 */

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
        'total_amount',
        'deposit_amount',
        'remaining_amount',
        'payment_option',
        'stripe_session_id',
        'stripe_payment_intent_id',
        'payment_status',
        'booking_status',
        'customer_name',
        'customer_email',
        'customer_phone',
        'notes',
        'expires_at',
    ];

    protected $casts = [
        'booking_dates' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'expires_at' => 'datetime',
    ];

    // PAYMENT STATUS CONSTANTS
    const PAYMENT_STATUS_PENDING = 'pending';
    const PAYMENT_STATUS_PAID = 'paid';
    const PAYMENT_STATUS_FAILED = 'failed';
    const PAYMENT_STATUS_EXPIRED = 'expired';      // NEW: Payment window expired
    const PAYMENT_STATUS_REFUNDED = 'refunded';
    const PAYMENT_STATUS_PARTIALLY_PAID = 'partially_paid';

    // BOOKING STATUS CONSTANTS
    const BOOKING_STATUS_PENDING = 'pending';
    const BOOKING_STATUS_CONFIRMED = 'confirmed';
    const BOOKING_STATUS_FAILED = 'failed';       // Payment failed OR expired
    const BOOKING_STATUS_CANCELLED = 'cancelled'; // User cancelled confirmed booking
    const BOOKING_STATUS_COMPLETED = 'completed';

    // PRICE TYPE CONSTANTS
    const PRICE_TYPE_DAY_USE = 'day_use';
    const PRICE_TYPE_NIGHT = 'night';
    const PRICE_TYPE_FULL_DAY = 'full_day';

    // PAYMENT OPTION CONSTANTS
    const PAYMENT_OPTION_FULL = 'full';
    const PAYMENT_OPTION_DEPOSIT = 'deposit';

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

    public function getBookingSummaryAttribute(): array
    {
        return [
            'reference' => $this->booking_reference,
            'farm_name' => $this->farm->name_en ?: $this->farm->name_ar,
            'dates' => $this->formatted_booking_dates,
            'period' => $this->booking_period,
            'time_range' => $this->booking_time_range,
            'guests' => $this->guest_count,
            'total' => $this->total_amount,
            'paid' => $this->amount_paid,
            'remaining' => $this->remaining_amount,
            'payment_type' => $this->getPaymentTypeLabel(),
            'status' => $this->booking_status,
            'payment_status' => $this->payment_status,
        ];
    }

    /**
     * STATUS CHANGE METHODS
     */
    public function markAsPaid($paymentIntentId = null): void
    {
        $newStatus = $this->hasDepositPayment() 
            ? self::PAYMENT_STATUS_PARTIALLY_PAID 
            : self::PAYMENT_STATUS_PAID;

        $this->update([
            'payment_status' => $newStatus,
            'booking_status' => self::BOOKING_STATUS_CONFIRMED,
            'stripe_payment_intent_id' => $paymentIntentId ?: $this->stripe_payment_intent_id,
        ]);
    }

    public function markAsFullyPaid($paymentIntentId = null): void
    {
        $this->update([
            'payment_status' => self::PAYMENT_STATUS_PAID,
            'booking_status' => self::BOOKING_STATUS_CONFIRMED,
            'remaining_amount' => 0,
            'stripe_payment_intent_id' => $paymentIntentId ?: $this->stripe_payment_intent_id,
        ]);
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

        $this->update([
            'booking_status' => self::BOOKING_STATUS_CANCELLED,
            // payment_status remains unchanged for refund processing
        ]);
    }

    /**
     * HELPER METHODS
     */
    public function setBookingTimes(Farm $farm): void
    {
        if (!$this->booking_dates || empty($this->booking_dates)) {
            return;
        }

        $this->start_date = Carbon::parse(min($this->booking_dates));
        $this->end_date = Carbon::parse(max($this->booking_dates));

        $pricing = $farm->pricing()->where('price_type', $this->price_type)->first();
        
        if (!$pricing) {
            return;
        }

        switch ($this->price_type) {
            case self::PRICE_TYPE_DAY_USE:
            case self::PRICE_TYPE_NIGHT:
                $this->start_time = Carbon::parse($pricing->start_time);
                $this->end_time = Carbon::parse($pricing->end_time);
                break;
                
            case self::PRICE_TYPE_FULL_DAY:
                $dayUsePricing = $farm->pricing()->where('price_type', self::PRICE_TYPE_DAY_USE)->first();
                $nightPricing = $farm->pricing()->where('price_type', self::PRICE_TYPE_NIGHT)->first();
                
                if ($dayUsePricing && $nightPricing) {
                    $this->start_time = Carbon::parse($dayUsePricing->start_time);
                    $this->end_time = Carbon::parse($nightPricing->end_time);
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
}