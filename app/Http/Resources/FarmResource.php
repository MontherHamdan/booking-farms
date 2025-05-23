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
            'passengers_count' => $this->passengers_count,
            'not_available_dates' => $this->not_available_dates,
            'formatted_not_available_dates' => $this->formatted_not_available_dates,
            'minimum_price' => $this->whenLoaded('pricing', function () {
                return $this->minimum_price;
            }),
            'maximum_price' => $this->whenLoaded('pricing', function () {
                return $this->maximum_price;
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
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),
            'features' => $this->whenLoaded('features', function () {
                return $this->features->map(function ($feature) {
                    return [
                        'id' => $feature->id,
                        'name_ar' => $feature->name_ar,
                        'name_en' => $feature->name_en,
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
                        'day_prices' => $pricing->day_prices,
                    ];
                });
            }),
        ];
    }
}