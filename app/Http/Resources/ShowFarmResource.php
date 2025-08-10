<?php 

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ShowFarmResource extends JsonResource
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

            // Rating information
            'total_ratings' => $this->whenLoaded('ratings', function () {
                return $this->total_ratings;
            }),
            
            'average_rating' => $this->whenLoaded('ratings', function () {
                return $this->display_average_rating; // null if less than 3 ratings
            }),
            
            'latest_ratings' => $this->whenLoaded('ratings', function () {
                return $this->latest_ratings->take(3)->map(function ($rating) {
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
            'farm_owner' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'phone' => $this->user->phone,
                    'email' => $this->user->email,
                    'avatar' => $this->user->avatar,
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
     * Get unavailable dates filtered to only include today and future dates
     * INCLUDES: Manual unavailable dates + Booked dates (by price type)
     */
    private function getFutureUnavailableDates(): ?array
    {
        $notAvailableDates = $this->not_available_dates;
        $today = Carbon::today()->format('Y-m-d');

        // Handle old format (simple array)
        if ($this->isOldFormatUnavailableDates($notAvailableDates)) {
            $manualDates = array_filter($notAvailableDates ?? [], function ($date) use ($today) {
                return $date >= $today;
            });
            
            // Add booked dates (all price types for old format)
            $bookedDates = $this->getBookedDatesAllPriceTypes($today);
            
            $allUnavailable = array_unique(array_merge($manualDates, $bookedDates));
            sort($allUnavailable);
            
            return $allUnavailable;
        }

        // Handle new format (price-type specific)
        $filteredDates = [];
        foreach (['day_use', 'night', 'full_day'] as $priceType) {
            // Get manual unavailable dates for this price type
            $manualDates = $this->getUnavailableDatesForPriceType($priceType);
            $futureManualDates = array_filter($manualDates, function ($date) use ($today) {
                return $date >= $today;
            });
            
            // Get booked dates for this price type
            $bookedDates = $this->getBookedDatesForPriceType($priceType, $today);
            
            // Merge manual + booked dates
            $allUnavailable = array_unique(array_merge($futureManualDates, $bookedDates));
            sort($allUnavailable);
            
            $filteredDates[$priceType] = $allUnavailable;
        }

        return $filteredDates;
    }

    /**
     * Get booked dates for a specific price type (same logic as calculatePrice)
     */
    private function getBookedDatesForPriceType(string $priceType, string $today): array
    {
        $bookedDates = [];

        // Get confirmed bookings for same price type
        $existingBookings = \App\Models\FarmBooking::where('farm_id', $this->id)
            ->where('price_type', $priceType)
            ->where('booking_status', \App\Models\FarmBooking::BOOKING_STATUS_CONFIRMED)
            ->get();

        foreach ($existingBookings as $booking) {
            $bookedDates = array_merge($bookedDates, $booking->booking_dates ?? []);
        }

        // Cross-price-type conflicts
        if (in_array($priceType, ['day_use', 'night'])) {
            // full_day bookings conflict with day_use/night
            $fullDayBookings = \App\Models\FarmBooking::where('farm_id', $this->id)
                ->where('price_type', 'full_day')
                ->where('booking_status', \App\Models\FarmBooking::BOOKING_STATUS_CONFIRMED)
                ->get();
                
            foreach ($fullDayBookings as $booking) {
                $bookedDates = array_merge($bookedDates, $booking->booking_dates ?? []);
            }
        }

        if ($priceType === 'full_day') {
            // day_use + night bookings conflict with full_day
            $dayUseBookings = \App\Models\FarmBooking::where('farm_id', $this->id)
                ->whereIn('price_type', ['day_use', 'night'])
                ->where('booking_status', \App\Models\FarmBooking::BOOKING_STATUS_CONFIRMED)
                ->get();
                
            foreach ($dayUseBookings as $booking) {
                $bookedDates = array_merge($bookedDates, $booking->booking_dates ?? []);
            }
        }

        // Filter for future dates only
        $bookedDates = array_filter(array_unique($bookedDates), function ($date) use ($today) {
            return $date >= $today;
        });

        return array_values($bookedDates);
    }

    /**
     * Get all booked dates for old format (all price types combined)
     */
    private function getBookedDatesAllPriceTypes(string $today): array
    {
        $bookedDates = [];

        $existingBookings = \App\Models\FarmBooking::where('farm_id', $this->id)
            ->where('booking_status', \App\Models\FarmBooking::BOOKING_STATUS_CONFIRMED)
            ->get();

        foreach ($existingBookings as $booking) {
            $bookedDates = array_merge($bookedDates, $booking->booking_dates ?? []);
        }

        // Filter for future dates only
        $bookedDates = array_filter(array_unique($bookedDates), function ($date) use ($today) {
            return $date >= $today;
        });

        return array_values($bookedDates);
    }

    /**
     * Get formatted unavailable dates (includes manual + booked dates)
     */
    private function getFutureFormattedUnavailableDates(): array
    {
        $unavailableDates = $this->getFutureUnavailableDates();
        
        if (!$unavailableDates) {
            return [];
        }

        $today = Carbon::today()->format('Y-m-d');

        // Handle old format (simple array)
        if (!isset($unavailableDates['day_use'])) {
            return array_map(function ($date) {
                return [
                    'date' => $date,
                    'formatted' => Carbon::parse($date)->format('Y-m-d'),
                    'human_readable' => Carbon::parse($date)->format('M d, Y'),
                    'price_types' => ['day_use', 'night', 'full_day'], // All types for old format
                ];
            }, $unavailableDates);
        }

        // Handle new format - create consolidated view with price types
        $allDates = [];
        foreach (['day_use', 'night', 'full_day'] as $priceType) {
            $dates = $unavailableDates[$priceType] ?? [];
            foreach ($dates as $date) {
                if (!isset($allDates[$date])) {
                    $allDates[$date] = [];
                }
                $allDates[$date][] = $priceType;
            }
        }

        $formatted = [];
        foreach ($allDates as $date => $priceTypes) {
            if ($date >= $today) {
                $formatted[] = [
                    'date' => $date,
                    'formatted' => Carbon::parse($date)->format('Y-m-d'),
                    'human_readable' => Carbon::parse($date)->format('M d, Y'),
                    'price_types' => array_unique($priceTypes),
                ];
            }
        }

        // Sort by date
        usort($formatted, function ($a, $b) {
            return strcmp($a['date'], $b['date']);
        });

        return $formatted;
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
     * Get available price types where all days have pricing data.
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

    /**
     * Get localized name for price type
     */
    private function getPriceTypeName(string $priceType, string $locale): string
    {
        // Use the language files to get localized names
        return __("farm.price_types.{$priceType}", [], $locale);
    }
}