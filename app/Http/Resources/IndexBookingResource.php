<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IndexBookingResource extends JsonResource
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
            'offer_discount' => $this->discount_amount,
            'coupon_applied' => $this->hasCoupon(),
            'coupon_code' => $this->coupon_code,
            'coupon_discount' => $this->coupon_discount_amount,
            'total_amount' => $this->total_amount,
            'amount_paid' => $this->amount_paid,
            'remaining_amount' => $this->remaining_amount,
            'payment_status' => $this->payment_status,
            'booking_status' => $this->booking_status,
            'can_be_cancelled' => $this->canBeCancelled(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}