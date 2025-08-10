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
 * 2b. CANCELLED → Payment fails or user abandons
 *     - markAsFailed() called  
 *     - booking_status = 'cancelled'
 *     - payment_status = 'failed'
 *     - ✅ SHOWN in getUserBookings() (for history)
 * 
 * 3. COMPLETED → Booking dates pass, service delivered
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
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'expires_at' => 'datetime',
    ];

    // Constants
    const PAYMENT_STATUS_PENDING = 'pending';
    const PAYMENT_STATUS_PAID = 'paid';
    const PAYMENT_STATUS_FAILED = 'failed';
    const PAYMENT_STATUS_REFUNDED = 'refunded';
    const PAYMENT_STATUS_PARTIALLY_PAID = 'partially_paid';

    const BOOKING_STATUS_PENDING = 'pending';
    const BOOKING_STATUS_CONFIRMED = 'confirmed';
    const BOOKING_STATUS_CANCELLED = 'cancelled';
    const BOOKING_STATUS_COMPLETED = 'completed';

    const PRICE_TYPE_DAY_USE = 'day_use';
    const PRICE_TYPE_NIGHT = 'night';
    const PRICE_TYPE_FULL_DAY = 'full_day';

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
            
            // Default payment_option if not set
            if (empty($booking->payment_option)) {
                $booking->payment_option = self::PAYMENT_OPTION_FULL;
            }
        });
    }

    /**
     * Get the user that owns the booking
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the farm that was booked
     */
    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    /**
     * Scope for pending bookings
     */
    public function scopePending($query)
    {
        return $query->where('booking_status', self::BOOKING_STATUS_PENDING);
    }

    /**
     * Scope for confirmed bookings
     */
    public function scopeConfirmed($query)
    {
        return $query->where('booking_status', self::BOOKING_STATUS_CONFIRMED);
    }

    /**
     * Scope for paid bookings
     */
    public function scopePaid($query)
    {
        return $query->where('payment_status', self::PAYMENT_STATUS_PAID);
    }

    /**
     * Scope for partially paid bookings (deposit paid)
     */
    public function scopePartiallyPaid($query)
    {
        return $query->where('payment_status', self::PAYMENT_STATUS_PARTIALLY_PAID);
    }

    /**
     * Check if booking is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && Carbon::now()->greaterThan($this->expires_at);
    }

    /**
     * Check if booking can be cancelled
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->booking_status, [
            self::BOOKING_STATUS_PENDING,
            self::BOOKING_STATUS_CONFIRMED
        ]) && !$this->isExpired();
    }

    /**
     * Check if booking has deposit payment
     */
    public function hasDepositPayment(): bool
    {
        return $this->payment_option === self::PAYMENT_OPTION_DEPOSIT && $this->deposit_amount > 0;
    }

    /**
     * Check if booking is fully paid
     */
    public function isFullyPaid(): bool
    {
        return $this->payment_status === self::PAYMENT_STATUS_PAID;
    }

    /**
     * Check if booking is partially paid (deposit only)
     */
    public function isPartiallyPaid(): bool
    {
        return $this->payment_status === self::PAYMENT_STATUS_PARTIALLY_PAID;
    }

    /**
     * Get amount paid so far
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

    /**
     * Get formatted booking dates
     */
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

    /**
     * Get the first booking date
     */
    public function getStartDateAttribute(): ?Carbon
    {
        if (!$this->booking_dates || empty($this->booking_dates)) {
            return null;
        }

        return Carbon::parse(min($this->booking_dates));
    }

    /**
     * Get the last booking date
     */
    public function getEndDateAttribute(): ?Carbon
    {
        if (!$this->booking_dates || empty($this->booking_dates)) {
            return null;
        }

        return Carbon::parse(max($this->booking_dates));
    }

    /**
     * Get booking duration in days
     */
    public function getDurationInDaysAttribute(): int
    {
        return count($this->booking_dates ?? []);
    }

    /**
     * Get payment type label
     */
    public function getPaymentTypeLabel(): string
    {
        return $this->payment_option === self::PAYMENT_OPTION_DEPOSIT 
            ? __('booking.payment_type.deposit') 
            : __('booking.payment_type.full');
    }

    /**
     * Mark booking as paid
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

    /**
     * Mark booking as fully paid (for remaining payment after deposit)
     */
    public function markAsFullyPaid($paymentIntentId = null): void
    {
        $this->update([
            'payment_status' => self::PAYMENT_STATUS_PAID,
            'booking_status' => self::BOOKING_STATUS_CONFIRMED,
            'remaining_amount' => 0,
            'stripe_payment_intent_id' => $paymentIntentId ?: $this->stripe_payment_intent_id,
        ]);
    }

    /**
     * Mark booking as failed
     */
    public function markAsFailed(): void
    {
        $this->update([
            'payment_status' => self::PAYMENT_STATUS_FAILED,
            'booking_status' => self::BOOKING_STATUS_CANCELLED,
        ]);
    }

    /**
     * Cancel booking
     */
    public function cancel(): void
    {
        $this->update([
            'booking_status' => self::BOOKING_STATUS_CANCELLED,
        ]);
    }

    /**
     * Get booking summary for display
     */
    public function getBookingSummaryAttribute(): array
    {
        return [
            'reference' => $this->booking_reference,
            'farm_name' => $this->farm->name_en ?: $this->farm->name_ar,
            'dates' => $this->formatted_booking_dates,
            'guests' => $this->guest_count,
            'total' => $this->total_amount,
            'paid' => $this->amount_paid,
            'remaining' => $this->remaining_amount,
            'payment_type' => $this->getPaymentTypeLabel(),
            'status' => $this->booking_status,
        ];
    }
}