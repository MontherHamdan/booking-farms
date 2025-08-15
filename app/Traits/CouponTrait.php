<?php

namespace App\Traits;

use App\Models\Coupon;
use App\Models\Farm;

trait CouponTrait
{
    /**
     * Validate and get coupon by code
     */
    protected function validateCouponCode(string $couponCode, int $userId, ?int $cityId = null, string $platform = 'web'): array
    {
        $coupon = Coupon::where('code', strtoupper($couponCode))->first();

        if (!$coupon) {
            return [
                'valid' => false,
                'coupon' => null,
                'errors' => [__('coupon.not_found')]
            ];
        }

        $validation = $coupon->canBeUsedByUser($userId, $cityId, $platform);

        return [
            'valid' => $validation['valid'],
            'coupon' => $validation['valid'] ? $coupon : null,
            'errors' => $validation['errors']
        ];
    }

    /**
     * Apply coupon discount to pricing data
     */
    protected function applyCouponDiscount(array $pricingData, Coupon $coupon): array
    {
        // Apply coupon discount to the total amount (after offer discount)
        $discountCalculation = $coupon->calculateDiscount($pricingData['total']);
        
        $newTotal = $discountCalculation['final_amount'];
        $couponDiscountAmount = $discountCalculation['discount_amount'];

        // Recalculate deposit and remaining amounts based on new total
        $newDepositAmount = 0;
        $newRemainingAmount = 0;
        $newPaymentAmount = $newTotal;

        if ($pricingData['is_deposit']) {
            // Get farm to calculate deposit percentage
            $farm = $this->farm ?? null;
            if ($farm && $farm->deposit_rate > 0) {
                $newDepositAmount = ($newTotal * $farm->deposit_rate) / 100;
                $newRemainingAmount = $newTotal - $newDepositAmount;
                $newPaymentAmount = $newDepositAmount;
            }
        }

        return array_merge($pricingData, [
            'coupon_discount_amount' => $couponDiscountAmount,
            'total_before_coupon' => $pricingData['total'],
            'total' => $newTotal,
            'payment_amount' => $newPaymentAmount,
            'deposit_amount' => $newDepositAmount,
            'remaining_amount' => $newRemainingAmount,
            'coupon_applied' => true,
            'coupon_details' => [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'name' => $coupon->name,
                'discount_type' => $coupon->discount_type,
                'discount_value' => $coupon->discount_value,
                'discount_description' => $coupon->discount_description,
            ]
        ]);
    }



    /**
     * Remove coupon from pricing data
     */
    protected function removeCouponFromPricing(array $pricingData): array
    {
        if (!isset($pricingData['coupon_applied']) || !$pricingData['coupon_applied']) {
            return $pricingData; // No coupon applied
        }

        // Restore original total before coupon
        $originalTotal = $pricingData['total_before_coupon'] ?? $pricingData['total'];
        
        // Recalculate deposit and remaining amounts based on original total
        $depositAmount = 0;
        $remainingAmount = 0;
        $paymentAmount = $originalTotal;

        if ($pricingData['is_deposit']) {
            $farm = $this->farm ?? null;
            if ($farm && $farm->deposit_rate > 0) {
                $depositAmount = ($originalTotal * $farm->deposit_rate) / 100;
                $remainingAmount = $originalTotal - $depositAmount;
                $paymentAmount = $depositAmount;
            }
        }

        return array_merge($pricingData, [
            'coupon_discount_amount' => 0,
            'total' => $originalTotal,
            'payment_amount' => $paymentAmount,
            'deposit_amount' => $depositAmount,
            'remaining_amount' => $remainingAmount,
            'coupon_applied' => false,
            'coupon_details' => null,
        ]);
    }

    /**
     * Validate coupon for specific farm and dates
     */
    protected function validateCouponForBooking(string $couponCode, Farm $farm, array $dates, int $userId, string $platform = 'web'): array
    {
        // First validate the basic coupon
        $validation = $this->validateCouponCode($couponCode, $userId, $farm->city_id, $platform);
        
        if (!$validation['valid']) {
            return $validation;
        }

        $coupon = $validation['coupon'];

        // Additional validation for booking context could be added here
        // For example: minimum booking amount, specific farm restrictions, etc.

        return $validation;
    }

    /**
     * Get coupon usage statistics for a user
     */
    protected function getUserCouponStats(int $userId): array
    {
        $totalUsed = \App\Models\CouponUsage::where('user_id', $userId)->count();
        $recentUsed = \App\Models\CouponUsage::where('user_id', $userId)
            ->where('used_at', '>=', now()->subDays(30))
            ->count();
        
        $totalSaved = \App\Models\FarmBooking::where('user_id', $userId)
            ->whereNotNull('coupon_discount_amount')
            ->sum('coupon_discount_amount');

        return [
            'total_coupons_used' => $totalUsed,
            'recent_coupons_used' => $recentUsed,
            'total_amount_saved' => round($totalSaved, 2),
        ];
    }

    /**
     * Check if coupon code format is valid
     */
    protected function isValidCouponFormat(string $code): bool
    {
        // Basic format validation - adjust according to your requirements
        return preg_match('/^[A-Z0-9]{3,20}$/', strtoupper($code));
    }

    /**
     * Generate unique coupon code
     */
    protected function generateUniqueCouponCode(string $prefix = '', int $length = 8): string
    {
        $attempts = 0;
        $maxAttempts = 10;

        do {
            $code = $prefix . strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, $length));
            $exists = Coupon::where('code', $code)->exists();
            $attempts++;
        } while ($exists && $attempts < $maxAttempts);

        if ($exists) {
            // Fallback with timestamp
            $code = $prefix . strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, $length - 3)) . now()->format('His');
        }

        return $code;
    }
}