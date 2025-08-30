<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FarmOwnerIndexBookingResource extends JsonResource
{
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
                'main_image' => $this->farm->mainImage ? url($this->farm->mainImage->image_path) : null,
            ],
            'customer' => [
                'name' => $this->customer_name,
                'email' => $this->customer_email,
                'phone' => $this->customer_phone,
            ],
            'price_type' => $this->price_type,
            'price_type_label' => __('farm.price_types.' . $this->price_type),
            'booking_dates' => $this->formatted_booking_dates,
            'start_date' => $this->start_date?->format('Y-m-d'),
            'end_date' => $this->end_date?->format('Y-m-d'),
            'start_time' => $this->start_time?->format('H:i'),
            'end_time' => $this->end_time?->format('H:i'),
            'booking_period' => $this->booking_period,
            'time_range' => $this->booking_time_range,
            'duration_in_days' => $this->duration_in_days,
            'guest_count' => $this->guest_count,
            'subtotal' => $this->subtotal,
            'discount_amount' => $this->discount_amount,
            'coupon_applied' => $this->hasCoupon(),
            'coupon_code' => $this->coupon_code,
            'coupon_discount_amount' => $this->coupon_discount_amount,
            'total_amount' => $this->total_amount,
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
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}