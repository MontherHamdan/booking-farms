<?php 

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class IndexShowFarmResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Get authenticated user using Sanctum guard
        $user = Auth::guard('sanctum')->user();
        
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

            // Original prices
            'minimum_price' => $this->whenLoaded('pricing', function () {
                return $this->minimum_price;
            }),
            // 'maximum_price' => $this->whenLoaded('pricing', function () {
            //     return $this->maximum_price;
            // }),
            
            // Prices after offer discount
            'minimum_price_after_offer' => $this->whenLoaded('pricing', function () {
                return $this->minimum_price_after_offer;
            }),
            // 'maximum_price_after_offer' => $this->whenLoaded('pricing', function () {
            //     return $this->maximum_price_after_offer;
            // }),
            
            // Available price types (only when all days have pricing)
            'available_price_types' => $this->whenLoaded('pricing', function () {
                return $this->getAvailablePriceTypes();
            }),
            
            // Offer information
            'has_valid_offer' => $this->whenLoaded('offers', function () {
                return $this->hasValidOffer();
            }),

            // Favorite status (only when user is authenticated)
            'is_favorite' => $this->when($user !== null, function () use ($user) {
                return $this->isFavoriteByUser($user->id);
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
        ];
    }

    /**
     * Get available price types where all days have pricing data.
     */
    private function getAvailablePriceTypes(): array
    {
        $availableTypes = [];
        
        foreach ($this->pricing as $pricing) {
            if ($this->isPriceTypeComplete($pricing)) {
                $availableTypes[] = [
                    'type' => $pricing->price_type,
                    'start_time' => $pricing->formatted_start_time,
                    'end_time' => $pricing->formatted_end_time,
                    'time_range' => $pricing->time_range,
                    'duration_hours' => $pricing->duration_in_hours,
                    'min_price' => $pricing->min_price,
                    // 'max_price' => $pricing->max_price,
                ];
            }
        }
        
        return $availableTypes;
    }

    /**
     * Check if a price type has complete pricing data for all days.
     */
    private function isPriceTypeComplete($pricing): bool
    {
        $dayPrices = [
            $pricing->saturday_price,
            $pricing->sunday_price,
            $pricing->monday_price,
            $pricing->tuesday_price,
            $pricing->wednesday_price,
            $pricing->thursday_price,
            $pricing->friday_price,
        ];

        // Check if all days have prices greater than 0
        foreach ($dayPrices as $price) {
            if (!$price || $price <= 0) {
                return false;
            }
        }

        return true;
    }
}