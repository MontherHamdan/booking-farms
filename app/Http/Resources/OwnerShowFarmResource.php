<?php 

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class OwnerShowFarmResource extends JsonResource
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
            'area_id' => $this->area_id,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'description_ar' => $this->description_ar,
            'description_en' => $this->description_en,
            'guests_count' => $this->guest_count,
            'deposit_rate' => $this->deposit_rate,
            'status' => $this->status,
            'current_step' => $this->current_step,
            
            // Coordinates
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'coordinates' => $this->coordinates, 
            'has_coordinates' => $this->hasCoordinates(),
            
            // UPDATED: Only return today and future unavailable dates
            'not_available_dates' => $this->getFutureUnavailableDates(),
            'formatted_not_available_dates' => $this->getFutureFormattedUnavailableDates(),

            // Original prices
            'minimum_price' => $this->whenLoaded('pricing', function () {
                return $this->minimum_price;
            }),
            
            // Prices after offer discount
            'minimum_price_after_offer' => $this->whenLoaded('pricing', function () {
                return $this->minimum_price_after_offer;
            }),
            
            // UPDATED: Full pricing details for farm owner
            'pricing' => $this->whenLoaded('pricing', function () {
                return $this->getFullPricingDetails();
            }),
            
            // Available price types (only when all days have pricing)
            'available_price_types' => $this->whenLoaded('pricing', function () {
                return $this->getAvailablePriceTypes();
            }),
            
            // Offer information
            'has_valid_offer' => $this->whenLoaded('offers', function () {
                return $this->hasValidOffer();
            }),

            // Current active offers
            'offers' => $this->whenLoaded('offers', function () {
                return $this->offers->map(function ($offer) {
                    return [
                        'id' => $offer->id,
                        'percentage' => $offer->percentage,
                        'start_date' => $offer->start_date,
                        'end_date' => $offer->end_date,
                        'is_active' => $offer->is_active,
                        'is_valid' => $offer->is_valid,
                        'created_at' => $offer->created_at,
                    ];
                });
            }),

            // Rating information
            'total_ratings' => $this->whenLoaded('ratings', function () {
                return $this->total_ratings;
            }),
            
            'average_rating' => $this->whenLoaded('ratings', function () {
                return $this->display_average_rating; // null if less than 3 ratings
            }),
            
            'latest_ratings' => $this->whenLoaded('ratings', function () {
                return $this->latest_ratings->take(5)->map(function ($rating) {
                    return [
                        'id' => $rating->id,
                        'rating' => $rating->rating,
                        'review' => $rating->review,
                        'created_at' => $rating->created_at,
                        'user' => [
                            'id' => $rating->user->id,
                            'name' => $rating->user->name,
                            'avatar' => $rating->user->avatar,
                        ]
                    ];
                });
            }),

            'current_offer_percentage' => $this->whenLoaded('offers', function () {
                return $this->getCurrentOfferPercentage();
            }),

            'city' => $this->whenLoaded('city', function () {
                return [
                    'id' => $this->city->id,
                    'name_ar' => $this->city->name_ar ?? '',
                    'name_en' => $this->city->name_en ?? '',
                    'latitude' => $this->city->latitude,
                    'longitude' => $this->city->longitude,
                    'coordinates' => $this->city->coordinates,
                    'has_coordinates' => $this->city->hasCoordinates(),
                ];
            }),
            'area' => $this->whenLoaded('area', function () {
                return [
                    'id' => $this->area->id,
                    'name_ar' => $this->area->name_ar ?? '',
                    'name_en' => $this->area->name_en ?? '',
                    'latitude' => $this->area->latitude,
                    'longitude' => $this->area->longitude,
                    'coordinates' => $this->area->coordinates,
                    'has_coordinates' => $this->area->hasCoordinates(),
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
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * Get full pricing details for farm owner (complete breakdown)
     */
    private function getFullPricingDetails(): array
    {
        $pricingDetails = [];
        
        foreach ($this->pricing as $pricing) {
            $pricingDetails[] = [
                'id' => $pricing->id,
                'price_type' => $pricing->price_type,
                'name_ar' => $this->getPriceTypeName($pricing->price_type, 'ar'),
                'name_en' => $this->getPriceTypeName($pricing->price_type, 'en'),
                
                // Time information
                'start_time' => $pricing->formatted_start_time,
                'end_time' => $pricing->formatted_end_time,
                'time_range' => $pricing->time_range,
                'duration_hours' => $pricing->duration_in_hours,
                
                // Daily prices (complete breakdown)
                'daily_prices' => [
                    'saturday' => (float) $pricing->saturday_price,
                    'sunday' => (float) $pricing->sunday_price,
                    'monday' => (float) $pricing->monday_price,
                    'tuesday' => (float) $pricing->tuesday_price,
                    'wednesday' => (float) $pricing->wednesday_price,
                    'thursday' => (float) $pricing->thursday_price,
                    'friday' => (float) $pricing->friday_price,
                ],
                
                // Price summary
                'min_price' => $pricing->min_price,
                'max_price' => $pricing->max_price,
                'average_price' => $this->calculateAveragePrice($pricing),
                
                // Completion status
                'is_complete' => $this->isPriceTypeComplete($pricing),
                'missing_days' => $this->getMissingPriceDays($pricing),
                
                // Availability info for this price type
                'unavailable_dates' => $this->getUnavailableDatesForPriceType($pricing->price_type),
                'unavailable_dates_count' => count($this->getUnavailableDatesForPriceType($pricing->price_type)),
                
                'created_at' => $pricing->created_at,
                'updated_at' => $pricing->updated_at,
            ];
        }
        
        return $pricingDetails;
    }

    /**
     * Calculate average price for a pricing type
     */
    private function calculateAveragePrice($pricing): float
    {
        $prices = array_filter([
            $pricing->saturday_price,
            $pricing->sunday_price,
            $pricing->monday_price,
            $pricing->tuesday_price,
            $pricing->wednesday_price,
            $pricing->thursday_price,
            $pricing->friday_price,
        ], function($price) {
            return $price > 0; // Only consider prices greater than 0
        });

        return empty($prices) ? 0 : round(array_sum($prices) / count($prices), 2);
    }

    /**
     * Get missing price days for a pricing type
     */
    private function getMissingPriceDays($pricing): array
    {
        $dayPrices = [
            'saturday' => $pricing->saturday_price,
            'sunday' => $pricing->sunday_price,
            'monday' => $pricing->monday_price,
            'tuesday' => $pricing->tuesday_price,
            'wednesday' => $pricing->wednesday_price,
            'thursday' => $pricing->thursday_price,
            'friday' => $pricing->friday_price,
        ];

        $missingDays = [];
        foreach ($dayPrices as $day => $price) {
            if (!$price || $price <= 0) {
                $missingDays[] = $day;
            }
        }

        return $missingDays;
    }

    /**
     * Get unavailable dates filtered to only include today and future dates
     */
    private function getFutureUnavailableDates(): ?array
    {
        $notAvailableDates = $this->not_available_dates;
        
        if (!$notAvailableDates) {
            return null;
        }

        $today = Carbon::today()->format('Y-m-d');

        // Handle old format (simple array)
        if ($this->isOldFormatUnavailableDates($notAvailableDates)) {
            $futureDates = array_filter($notAvailableDates, function ($date) use ($today) {
                return $date >= $today;
            });
            
            return array_values($futureDates); // Re-index array
        }

        // Handle new format (price-type specific)
        $filteredDates = [];
        foreach (['day_use', 'night', 'full_day'] as $priceType) {
            if (isset($notAvailableDates[$priceType])) {
                $futureDates = array_filter($notAvailableDates[$priceType], function ($date) use ($today) {
                    return $date >= $today;
                });
                $filteredDates[$priceType] = array_values($futureDates); // Re-index array
            } else {
                $filteredDates[$priceType] = [];
            }
        }

        return $filteredDates;
    }

    /**
     * Get formatted unavailable dates filtered to only include today and future dates
     */
    private function getFutureFormattedUnavailableDates(): array
    {
        $formattedDates = $this->formatted_not_available_dates;
        
        if (!$formattedDates) {
            return [];
        }

        $today = Carbon::today()->format('Y-m-d');

        return array_values(array_filter($formattedDates, function ($dateInfo) use ($today) {
            return $dateInfo['date'] >= $today;
        }));
    }

    /**
     * Check if dates format is old (simple array) or new (price-type specific)
     */
    private function isOldFormatUnavailableDates($data): bool
    {
        if (empty($data) || !is_array($data)) {
            return false;
        }

        // If it's a sequential array of dates, it's old format
        return array_keys($data) === range(0, count($data) - 1);
    }

    /**
     * Get available price types where all days have pricing data (summary for quick view)
     */
    private function getAvailablePriceTypes(): array
    {
        $availableTypes = [];
        
        foreach ($this->pricing as $pricing) {
            if ($this->isPriceTypeComplete($pricing)) {
                $availableTypes[] = [
                    'type' => $pricing->price_type,
                    'name_ar' => $this->getPriceTypeName($pricing->price_type, 'ar'),
                    'name_en' => $this->getPriceTypeName($pricing->price_type, 'en'),
                    'start_time' => $pricing->formatted_start_time,
                    'end_time' => $pricing->formatted_end_time,
                    'time_range' => $pricing->time_range,
                    'duration_hours' => $pricing->duration_in_hours,
                    'min_price' => $pricing->min_price,
                    'max_price' => $pricing->max_price,
                    'average_price' => $this->calculateAveragePrice($pricing),
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

    /**
     * Get localized name for price type
     */
    private function getPriceTypeName(string $priceType, string $locale): string
    {
        // Use the language files to get localized names
        return __("farm.price_types.{$priceType}", [], $locale);
    }
}