<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShowBookingResource extends JsonResource
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
            'time_range' => $this->booking_time_range,
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
                ] : null;
            }),
            'total_amount' => $this->total_amount,
            'deposit_amount' => $this->deposit_amount,
            'remaining_amount' => $this->remaining_amount,
            'payment_option' => $this->payment_option,
            'payment_status' => $this->payment_status,
            'booking_status' => $this->booking_status,
            'customer_name' => $this->customer_name,
            'customer_email' => $this->customer_email,
            'customer_phone' => $this->customer_phone,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'can_be_cancelled' => $this->canBeCancelled(),
            'amount_paid' => $this->amount_paid,
            'duration_in_days' => $this->duration_in_days,
            'is_deposit_payment' => $this->hasDepositPayment(),
            'booking_summary' => $this->booking_summary,
        ];
    }
}