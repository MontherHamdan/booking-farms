<?php

namespace App\Services;

use App\Models\Farm;
use App\Models\FarmBooking;
use App\Models\Coupon;
use App\Traits\FarmPricingTrait;
use App\Traits\CouponTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Stripe\PaymentIntent;

class FarmBookingService
{
    use FarmPricingTrait, CouponTrait;

    /**
     * Check if dates are available for booking
     * Only CONFIRMED bookings block dates
     */
    public function checkAvailability(Farm $farm, array $processedDates, string $priceType): array
    {
        $errors = [];
        
        // Check farm's unavailable dates
        $unavailableDates = $farm->getUnavailableDatesForPriceType($priceType);
        $conflictingUnavailable = array_intersect($processedDates, $unavailableDates);
        if ($conflictingUnavailable) {
            $errors['unavailable'] = $conflictingUnavailable;
        }

        // Check for existing CONFIRMED bookings only
        $bookedDates = $this->getBookedDates($farm->id, $priceType);
        $conflictingBooked = array_intersect($processedDates, $bookedDates);
        if ($conflictingBooked) {
            $errors['booked'] = $conflictingBooked;
        }

        return $errors;
    }

    /**
     * Get all booked dates for a farm and price type
     * Only considers CONFIRMED bookings
     */
    public function getBookedDates(int $farmId, string $priceType): array
    {
        $bookedDates = [];
        
        // Get CONFIRMED bookings for this price type
        $confirmedBookings = FarmBooking::where('farm_id', $farmId)
            ->where('price_type', $priceType)
            ->where('booking_status', FarmBooking::BOOKING_STATUS_CONFIRMED)
            ->get();

        foreach ($confirmedBookings as $booking) {
            $bookedDates = array_merge($bookedDates, $booking->booking_dates ?? []);
        }

        // Handle cross-price-type conflicts
        if (in_array($priceType, ['day_use', 'night'])) {
            // Day use or night conflicts with full day bookings
            $fullDayBookings = FarmBooking::where('farm_id', $farmId)
                ->where('price_type', 'full_day')
                ->where('booking_status', FarmBooking::BOOKING_STATUS_CONFIRMED)
                ->get();
                
            foreach ($fullDayBookings as $booking) {
                $bookedDates = array_merge($bookedDates, $booking->booking_dates ?? []);
            }
        }

        if ($priceType === 'full_day') {
            // Full day conflicts with any partial bookings
            $partialBookings = FarmBooking::where('farm_id', $farmId)
                ->whereIn('price_type', ['day_use', 'night'])
                ->where('booking_status', FarmBooking::BOOKING_STATUS_CONFIRMED)
                ->get();
                
            foreach ($partialBookings as $booking) {
                $bookedDates = array_merge($bookedDates, $booking->booking_dates ?? []);
            }
        }

        return array_unique($bookedDates);
    }

    /**
     * Calculate pricing for booking
     */
    public function calculatePricing(Farm $farm, array $processedDates, string $priceType, string $paymentOption = 'full', ?string $couponCode = null, int $userId = null, string $platform = 'web'): array
    {
        $this->farm = $farm; // Store farm for coupon trait methods
        
        $pricing = $farm->pricing()->where('price_type', $priceType)->first();
        
        // Calculate subtotal
        $subtotal = collect($processedDates)->sum(function ($date) use ($pricing) {
            $day = strtolower(Carbon::parse($date)->format('l'));
            return $pricing->{"{$day}_price"} ?? 0;
        });

        // Apply offer if available
        $offer = $farm->currentOffer;
        $percentage = $offer ? (float) $offer->percentage : 0.0;
        $discountAmount = ($subtotal * $percentage) / 100;
        $total = $subtotal - $discountAmount;

        // Calculate payment amounts
        $depositAmount = 0;
        $remainingAmount = 0;
        $paymentAmount = $total;
        $isDepositPayment = false;

        if ($paymentOption === 'deposit' && $farm->deposit_rate && $farm->deposit_rate > 0) {
            $depositAmount = ($total * $farm->deposit_rate) / 100;
            $remainingAmount = $total - $depositAmount;
            $paymentAmount = $depositAmount;
            $isDepositPayment = true;
        }

        $pricingData = [
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'offer_percentage' => $percentage,
            'total' => $total,
            'payment_amount' => $paymentAmount,
            'deposit_amount' => $depositAmount,
            'remaining_amount' => $remainingAmount,
            'is_deposit' => $isDepositPayment,
            'coupon_discount_amount' => 0,
            'coupon_applied' => false,
            'coupon_details' => null,
            'pricing_details' => [
                'start_time' => $pricing->formatted_start_time,
                'end_time' => $pricing->formatted_end_time,
                'time_range' => $pricing->time_range,
                'duration_hours' => $pricing->duration_in_hours,
            ]
        ];

        // Apply coupon if provided and valid
        if ($couponCode && $userId) {
            $couponValidation = $this->validateCouponForBooking($couponCode, $farm, $processedDates, $userId, $platform);
            
            if ($couponValidation['valid']) {
                $pricingData = $this->applyCouponDiscount($pricingData, $couponValidation['coupon']);
            } else {
                // Return coupon errors
                $pricingData['coupon_errors'] = $couponValidation['errors'];
            }
        }

        return $pricingData;
    }

    /**
     * Validate coupon code for booking
     */
    public function validateCoupon(string $couponCode, Farm $farm, array $processedDates, int $userId, string $platform = 'web'): array
    {
        return $this->validateCouponForBooking($couponCode, $farm, $processedDates, $userId, $platform);
    }



    /**
     * Create booking record
     */
    public function createBooking(array $data): FarmBooking
    {
        $booking = FarmBooking::create($data);
        
        // Set booking times if farm is provided
        if (isset($data['farm'])) {
            $booking->setBookingTimes($data['farm']);
            $booking->save();
        }
        
        return $booking;
    }

    /**
     * Cancel a confirmed booking (user-initiated cancellation)
     */
    public function cancelBooking(FarmBooking $booking, string $reason = null): void
    {
        if (!$booking->canBeCancelled()) {
            throw new \InvalidArgumentException("Only confirmed bookings can be cancelled. Current status: {$booking->booking_status}");
        }

        // Cancel the booking (payment status preserved for refund processing)
        $booking->cancel();

        Log::info('Booking cancelled by user', [
            'booking_id' => $booking->id,
            'booking_reference' => $booking->booking_reference,
            'user_id' => $booking->user_id,
            'farm_id' => $booking->farm_id,
            'payment_status' => $booking->payment_status,
            'coupon_used' => $booking->hasCoupon(),
            'coupon_code' => $booking->coupon_code,
            'reason' => $reason,
        ]);

        // TODO: Process refund if eligible
        // TODO: Send cancellation notification emails
        // TODO: Update farm availability calendar
        // TODO: Handle coupon usage refund if needed
    }

    /**
     * Mark booking as failed (payment failure during checkout)
     */
    public function failBooking(FarmBooking $booking, string $reason = null): void
    {
        if ($booking->booking_status !== FarmBooking::BOOKING_STATUS_PENDING) {
            throw new \InvalidArgumentException("Only pending bookings can be marked as failed. Current status: {$booking->booking_status}");
        }

        $booking->markAsFailed();

        Log::info('Booking marked as failed', [
            'booking_id' => $booking->id,
            'booking_reference' => $booking->booking_reference,
            'user_id' => $booking->user_id,
            'farm_id' => $booking->farm_id,
            'coupon_used' => $booking->hasCoupon(),
            'coupon_code' => $booking->coupon_code,
            'reason' => $reason,
        ]);

        // TODO: Send payment failure notification
    }

    /**
     * Expire pending bookings that have passed their expiration time
     */
    public function expirePendingBookings(int $limit = 100): array
    {
        $expiredBookings = [];
        $failedBookings = [];

        $bookingsToExpire = FarmBooking::shouldBeExpired()
            ->limit($limit)
            ->get();

        foreach ($bookingsToExpire as $booking) {
            try {
                $this->expireBooking($booking);
                $expiredBookings[] = $booking->booking_reference;
            } catch (\Exception $e) {
                $failedBookings[] = [
                    'booking_reference' => $booking->booking_reference,
                    'error' => $e->getMessage()
                ];
                
                Log::error('Failed to expire booking', [
                    'booking_id' => $booking->id,
                    'booking_reference' => $booking->booking_reference,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return [
            'expired' => $expiredBookings,
            'failed' => $failedBookings,
            'total_processed' => count($expiredBookings) + count($failedBookings)
        ];
    }

    /**
     * Expire a single booking
     */
    public function expireBooking(FarmBooking $booking): void
    {
        if ($booking->booking_status !== FarmBooking::BOOKING_STATUS_PENDING) {
            throw new \InvalidArgumentException("Only pending bookings can be expired. Current status: {$booking->booking_status}");
        }

        if (!$booking->isPaymentExpired()) {
            throw new \InvalidArgumentException("Booking has not yet expired. Expires at: {$booking->expires_at}");
        }

        // Mark booking as expired
        $booking->markAsExpired();

        // Cancel Stripe Payment Intent if it exists
        $this->cancelStripePaymentIntent($booking);

        Log::info('Booking expired automatically', [
            'booking_id' => $booking->id,
            'booking_reference' => $booking->booking_reference,
            'user_id' => $booking->user_id,
            'farm_id' => $booking->farm_id,
            'expired_at' => $booking->expires_at,
            'total_amount' => $booking->total_amount,
            'booking_status' => $booking->booking_status,
            'payment_status' => $booking->payment_status,
            'coupon_used' => $booking->hasCoupon(),
            'coupon_code' => $booking->coupon_code,
        ]);

        // TODO: Send expiration notification email to customer
        // TODO: Send notification to farm owner if needed
    }

    /**
     * Cancel Stripe Payment Intent for expired booking
     */
    private function cancelStripePaymentIntent(FarmBooking $booking): void
    {
        if (!$booking->stripe_payment_intent_id) {
            return;
        }

        try {
            $paymentIntent = PaymentIntent::retrieve($booking->stripe_payment_intent_id);
            
            // Only cancel if it's still in a cancellable state
            if (in_array($paymentIntent->status, ['requires_payment_method', 'requires_confirmation', 'requires_action'])) {
                $paymentIntent->cancel();
                Log::info('Cancelled Stripe Payment Intent for expired booking', [
                    'booking_id' => $booking->id,
                    'payment_intent_id' => $booking->stripe_payment_intent_id
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to cancel Stripe Payment Intent for expired booking', [
                'booking_id' => $booking->id,
                'payment_intent_id' => $booking->stripe_payment_intent_id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get booking statistics
     */
    public function getBookingStatistics(): array
    {
        return [
            'pending' => FarmBooking::pending()->count(),
            'confirmed' => FarmBooking::confirmed()->count(),
            'failed_total' => FarmBooking::failed()->count(),
            'failed_payment' => FarmBooking::failed()->where('payment_status', FarmBooking::PAYMENT_STATUS_FAILED)->count(),
            'expired' => FarmBooking::expired()->count(),
            'cancelled' => FarmBooking::cancelled()->count(),
            'completed' => FarmBooking::completed()->count(),
            'should_be_expired' => FarmBooking::shouldBeExpired()->count(),
            'with_coupons' => FarmBooking::withCoupon()->count(),
            'coupon_savings' => FarmBooking::whereNotNull('coupon_discount_amount')->sum('coupon_discount_amount'),
        ];
    }

    /**
     * Clean up old expired bookings (optional housekeeping)
     */
    public function cleanupOldExpiredBookings(int $daysOld = 30): int
    {
        $cutoffDate = Carbon::now()->subDays($daysOld);
        
        $deletedCount = FarmBooking::expired()
            ->where('expires_at', '<', $cutoffDate)
            ->delete();

        Log::info("Cleaned up {$deletedCount} old expired bookings older than {$daysOld} days");

        return $deletedCount;
    }

    /**
     * Get user bookings with proper filtering
     */
    public function getUserBookings(int $userId, array $filters = []): array
    {
        $query = FarmBooking::with(['farm', 'farm.mainImage', 'coupon'])
            ->where('user_id', $userId);

        // Filter out pending bookings by default (incomplete payments)
        $showPending = $filters['show_pending'] ?? false;
        if (!$showPending) {
            $query->where('booking_status', '!=', FarmBooking::BOOKING_STATUS_PENDING);
        }

        // Apply status filter if provided
        if (isset($filters['status'])) {
            if ($filters['status'] === 'expired') {
                // Special handling for expired bookings
                $query->where('booking_status', FarmBooking::BOOKING_STATUS_FAILED)
                      ->where('payment_status', FarmBooking::PAYMENT_STATUS_EXPIRED);
            } else {
                $query->where('booking_status', $filters['status']);
            }
        }

        // Apply date filters
        if (isset($filters['from_date'])) {
            $query->where('start_date', '>=', $filters['from_date']);
        }

        if (isset($filters['to_date'])) {
            $query->where('end_date', '<=', $filters['to_date']);
        }

        // Filter by coupon usage
        if (isset($filters['with_coupon'])) {
            if ($filters['with_coupon']) {
                $query->whereNotNull('coupon_id');
            } else {
                $query->whereNull('coupon_id');
            }
        }

        return $query->orderBy('created_at', 'desc')->get()->toArray();
    }

    /**
     * Process refund for cancelled booking
     */
    public function processRefund(FarmBooking $booking, float $refundAmount = null): array
    {
        if (!$booking->canBeRefunded()) {
            throw new \InvalidArgumentException('Booking is not eligible for refund');
        }

        $refundAmount = $refundAmount ?? $booking->amount_paid;

        // TODO: Implement actual Stripe refund
        // $refund = \Stripe\Refund::create([
        //     'payment_intent' => $booking->stripe_payment_intent_id,
        //     'amount' => (int) ($refundAmount * 100),
        // ]);

        Log::info('Refund processed for cancelled booking', [
            'booking_id' => $booking->id,
            'booking_reference' => $booking->booking_reference,
            'refund_amount' => $refundAmount,
            'coupon_used' => $booking->hasCoupon(),
            'coupon_code' => $booking->coupon_code,
        ]);

        return [
            'refund_amount' => $refundAmount,
            'status' => 'processed',
            'estimated_days' => '5-7 business days'
        ];
    }

    /**
     * Get detailed booking breakdown by status
     */
    public function getDetailedBookingStats(): array
    {
        $stats = $this->getBookingStatistics();
        
        return [
            'pending' => $stats['pending'],
            'confirmed' => $stats['confirmed'],
            'failed' => [
                'total' => $stats['failed_total'],
                'payment_failed' => $stats['failed_payment'],
                'expired' => $stats['expired'],
            ],
            'cancelled' => $stats['cancelled'],
            'completed' => $stats['completed'],
            'maintenance' => [
                'should_be_expired' => $stats['should_be_expired'],
            ],
            'coupons' => [
                'bookings_with_coupons' => $stats['with_coupons'],
                'total_savings' => $stats['coupon_savings'],
            ]
        ];
    }

    /**
     * Get coupon statistics
     */
    public function getCouponStatistics(): array
    {
        $totalCoupons = Coupon::count();
        $activeCoupons = Coupon::active()->count();
        $validCoupons = Coupon::valid()->count();
        $totalUsages = \App\Models\CouponUsage::count();
        $totalSavings = FarmBooking::whereNotNull('coupon_discount_amount')->sum('coupon_discount_amount');

        return [
            'total_coupons' => $totalCoupons,
            'active_coupons' => $activeCoupons,
            'valid_coupons' => $validCoupons,
            'total_usages' => $totalUsages,
            'total_savings' => round($totalSavings, 2),
            'average_discount_per_usage' => $totalUsages > 0 ? round($totalSavings / $totalUsages, 2) : 0,
        ];
    }
}