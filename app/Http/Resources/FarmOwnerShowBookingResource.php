<?php

namespace App\Http\Resources;

use App\Traits\BookingFormatterTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class FarmOwnerShowBookingResource extends JsonResource
{
    use BookingFormatterTrait;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'booking_reference' => $this->booking_reference,
            'farm' => [
                'id' => $this->farm->id,
                'name_en' => $this->farm->name_en,
                'name_ar' => $this->farm->name_ar,
                'description_en' => $this->farm->description_en,
                'description_ar' => $this->farm->description_ar,
                'main_image' => $this->farm->mainImage ? url($this->farm->mainImage->image_path) : null,
                'city' => $this->farm->city ? [
                    'id' => $this->farm->city->id,
                    'name_ar' => $this->farm->city->name_ar,
                    'name_en' => $this->farm->city->name_en,
                ] : null,
                'area' => $this->farm->area ? [
                    'id' => $this->farm->area->id,
                    'name_ar' => $this->farm->area->name_ar,
                    'name_en' => $this->farm->area->name_en,
                ] : null,
            ],
            'customer' => [
                'id' => $this->user->id,
                'name' => $this->customer_name,
                'email' => $this->customer_email,
                'phone' => $this->customer_phone,
                'avatar' => $this->user->avatar ? url($this->user->avatar) : null,
            ],
            'price_type' => $this->price_type,
            'price_type_label' => __('farm.price_types.' . $this->price_type),
            'booking_dates' => $this->formatted_booking_dates,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'start_time' => $this->start_time?->format('H:i'),
            'end_time' => $this->end_time?->format('H:i'),
            'formatted_start_datetime' => $this->formatted_start_datetime,
            'formatted_end_datetime' => $this->formatted_end_datetime,
            'booking_period' => $this->booking_period,
            'time_range' => $this->getLocalizedTimeRange(),
            'duration_hours' => $this->getDurationHours(),
            'duration_in_days' => $this->duration_in_days,
            'guest_count' => $this->guest_count,
            'subtotal' => $this->subtotal,
            'discount_amount' => $this->discount_amount,
            'coupon_applied' => $this->hasCoupon(),
            'coupon_code' => $this->coupon_code,
            'coupon_discount_amount' => $this->coupon_discount_amount,
            'coupon_details' => $this->whenLoaded('coupon', function () {
                return $this->coupon ? [
                    'id' => $this->coupon->id,
                    'code' => $this->coupon->code,
                    'name' => $this->coupon->name,
                    'discount_description' => $this->coupon->discount_description,
                    'discount_amount' => $this->coupon_discount_amount,
                ] : null;
            }),
            'total_amount' => $this->total_amount,
            'amount_paid' => $this->amount_paid,
            'platform_commission_rate' => $this->platform_commission_rate,
            'platform_commission_amount' => $this->platform_commission_amount,
            'farm_owner_earning' => $this->farm_owner_earning,
            'payment_option' => $this->payment_option,
            'payment_option_label' => __('booking.payment_type.' . $this->payment_option),
            'deposit_amount' => $this->deposit_amount,
            'remaining_amount' => $this->remaining_amount,
            'payment_status' => $this->payment_status,
            'payment_status_label' => __('booking.payment_status.' . $this->payment_status),
            'booking_status' => $this->booking_status,
            'booking_status_label' => __('booking.status.' . $this->booking_status),
            'earnings_processed' => $this->earnings_processed,
            'earnings_processed_at' => $this->earnings_processed_at,
            'can_be_cancelled' => $this->canBeCancelled(),
            'can_be_refunded' => method_exists($this, 'canBeRefunded') ? $this->canBeRefunded() : false,
            'notes' => $this->notes,
            'payment_info' => [
                'stripe_payment_intent_id' => $this->stripe_payment_intent_id,
                'expires_at' => $this->expires_at,
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'booking_summary' => method_exists($this, 'booking_summary') ? $this->booking_summary : null,
        ];
    }
}