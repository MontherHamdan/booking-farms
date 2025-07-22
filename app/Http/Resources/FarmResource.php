<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FarmResource extends JsonResource
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
            'user_id' => $this->user_id,
            'city_id' => $this->city_id,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'description_ar' => $this->description_ar,
            'description_en' => $this->description_en,
            'guest_count' => $this->guest_count,
            'not_available_dates' => $this->not_available_dates,
            'formatted_not_available_dates' => $this->formatted_not_available_dates,
            
            // Original prices
            'minimum_price' => $this->whenLoaded('pricing', function () {
                return $this->minimum_price;
            }),
            'maximum_price' => $this->whenLoaded('pricing', function () {
                return $this->maximum_price;
            }),
            
            // Prices after offer discount
            'minimum_price_after_offer' => $this->whenLoaded('pricing', function () {
                return $this->minimum_price_after_offer;
            }),
            'maximum_price_after_offer' => $this->whenLoaded('pricing', function () {
                return $this->maximum_price_after_offer;
            }),
            
            // Offer information
            'has_valid_offer' => $this->whenLoaded('offers', function () {
                return $this->hasValidOffer();
            }),
            'current_offer_percentage' => $this->whenLoaded('offers', function () {
                return $this->getCurrentOfferPercentage();
            }),
            
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            'city' => $this->whenLoaded('city', function () {
                return [
                    'id' => $this->city->id,
                    'name_ar' => $this->city->name_ar ?? '',
                    'name_en' => $this->city->name_en ?? '',
                ];
            }),
            'farm owner' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'phone' => $this->user->phone,
                ];
            }),
            'features' => $this->whenLoaded('features', function () {
                return $this->features->map(function ($feature) {
                    return [
                        'id' => $feature->id,
                        'name_ar' => $feature->name_ar,
                        'name_en' => $feature->name_en,
                        'icon' => $feature->icon,
                    ];
                });
            }),
            'images' => $this->whenLoaded('images', function () {
                return $this->images->map(function ($image) {
                    return [
                        'id' => $image->id,
                        'image_path' => $image->image_path,
                        'is_main' => (bool) $image->is_main,
                    ];
                });
            }),
            'pricing' => $this->whenLoaded('pricing', function () {
                return $this->pricing->map(function ($pricing) {
                    return [
                        'id' => $pricing->id,
                        'price_type' => $pricing->price_type,
                        // 'min_price' => $pricing->min_price,
                        // 'max_price' => $pricing->max_price,
                        'start_time' => $pricing->formatted_start_time,
                        'end_time' => $pricing->formatted_end_time,
                        'time_range' => $pricing->time_range,
                        'duration_hours' => $pricing->duration_in_hours,
                        'day_prices' => $pricing->day_prices,
                        // Add prices after offer for each pricing type
                        'day_prices_after_offer' => $this->calculatePricingAfterOffer($pricing),
                    ];
                });
            }),
            'offers' => $this->whenLoaded('offers', function () {
                return $this->offers->map(function ($offer) {
                    return [
                        'id' => $offer->id,
                        'percentage' => $offer->percentage,
                        'start_date' => $offer->start_date->format('Y-m-d'),
                        'end_date' => $offer->end_date->format('Y-m-d'),
                        'is_active' => $offer->is_active,
                        'is_valid' => $offer->isValid(),
                    ];
                });
            }),
            'current_offer' => $this->whenLoaded('offers', function () {
                $currentOffer = $this->currentOffer;
                if ($currentOffer) {
                    return [
                        'id' => $currentOffer->id,
                        'percentage' => $currentOffer->percentage,
                        'start_date' => $currentOffer->start_date->format('Y-m-d'),
                        'end_date' => $currentOffer->end_date->format('Y-m-d'),
                        'is_active' => $currentOffer->is_active,
                        'is_valid' => $currentOffer->isValid(),
                    ];
                }
                return null;
            }),
        ];
    }

    /**
     * Calculate pricing after offer for a specific pricing model
     */
    private function calculatePricingAfterOffer($pricing)
    {
        if (!$this->hasValidOffer()) {
            return $pricing->day_prices;
        }

        $offerPercentage = $this->getCurrentOfferPercentage();
        $pricesAfterOffer = [];

        foreach ($pricing->day_prices as $day => $price) {
            if ($price !== null) {
                $discount = ($price * $offerPercentage) / 100;
                $pricesAfterOffer[$day] = max(0, $price - $discount);
            } else {
                $pricesAfterOffer[$day] = null;
            }
        }

        return $pricesAfterOffer;
    }
}