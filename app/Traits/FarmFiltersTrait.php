<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Carbon\Carbon;

trait FarmFiltersTrait
{
    /**
     * Apply all filters to the farm query
     */
    public function applyFarmFilters($query, Request $request)
    {
        $this->applyCityFilter($query, $request);
        $this->applyPriceRangeFilter($query, $request);
        $this->applyAvailableTimeFilter($query, $request);
        $this->applyDateAvailabilityFilter($query, $request);
        
        return $query;
    }

    /**
     * Apply city filter
     */
    private function applyCityFilter($query, Request $request)
    {
        if ($request->has('city_id') && $request->city_id) {
            $query->where('city_id', $request->city_id);
        }
        
        return $query;
    }

    /**
     * Apply price range filter based on offers availability
     */
    private function applyPriceRangeFilter($query, Request $request)
    {
        $minPrice = $request->input('min_price');
        $maxPrice = $request->input('max_price');
        
        if ($minPrice !== null || $maxPrice !== null) {
            $query->where(function ($farmQuery) use ($minPrice, $maxPrice, $request) {
                // Check if we should filter by offer prices or original prices
                $hasOfferFilter = $request->boolean('has_offer', false);
                
                if ($hasOfferFilter) {
                    // Filter by minimum price after offer discount
                    $farmQuery->whereHas('offers', function ($offerQuery) {
                        $offerQuery->where('start_date', '<=', now())
                                ->where('end_date', '>=', now())
                                ->where('is_active', true);
                    });
                    
                    // Get the farm's minimum price after discount and check if it's within range
                    $farmMinPriceAfterOffer = "(
                        SELECT MIN(
                            LEAST(saturday_price, sunday_price, monday_price, tuesday_price, wednesday_price, thursday_price, friday_price) - 
                            (LEAST(saturday_price, sunday_price, monday_price, tuesday_price, wednesday_price, thursday_price, friday_price) * 
                            (SELECT COALESCE(MAX(percentage), 0) FROM farm_offers WHERE farm_id = farm_pricings.farm_id AND start_date <= NOW() AND end_date >= NOW() AND is_active = 1) / 100)
                        ) 
                        FROM farm_pricings 
                        WHERE farm_pricings.farm_id = farms.id
                    )";
                    
                    if ($minPrice !== null) {
                        $farmQuery->whereRaw("$farmMinPriceAfterOffer >= ?", [$minPrice]);
                    }
                    if ($maxPrice !== null) {
                        $farmQuery->whereRaw("$farmMinPriceAfterOffer <= ?", [$maxPrice]);
                    }
                } else {
                    // Filter by farm's minimum price (compare min price with the range)
                    $farmMinPrice = "(
                        SELECT MIN(LEAST(saturday_price, sunday_price, monday_price, tuesday_price, wednesday_price, thursday_price, friday_price)) 
                        FROM farm_pricings 
                        WHERE farm_pricings.farm_id = farms.id
                    )";
                    
                    if ($minPrice !== null) {
                        $farmQuery->whereRaw("$farmMinPrice >= ?", [$minPrice]);
                    }
                    if ($maxPrice !== null) {
                        $farmQuery->whereRaw("$farmMinPrice <= ?", [$maxPrice]);
                    }
                }
            });
        }
        
        return $query;
    }

    /**
     * Apply available time filter (day_use, night, full_day)
     */
    private function applyAvailableTimeFilter($query, Request $request)
    {
        $availableTime = $request->input('available_time');
        
        if ($availableTime && in_array($availableTime, ['day_use', 'night', 'full_day'])) {
            $query->whereHas('pricing', function ($pricingQuery) use ($availableTime) {
                $pricingQuery->where('price_type', $availableTime)
                           ->where(function ($dayQuery) {
                               // Ensure all days have pricing greater than 0
                               $dayQuery->where('saturday_price', '>', 0)
                                       ->where('sunday_price', '>', 0)
                                       ->where('monday_price', '>', 0)
                                       ->where('tuesday_price', '>', 0)
                                       ->where('wednesday_price', '>', 0)
                                       ->where('thursday_price', '>', 0)
                                       ->where('friday_price', '>', 0);
                           });
            });
        }
        
        return $query;
    }

    /**
     * Apply date availability filter
     */
    private function applyDateAvailabilityFilter($query, Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $singleDate = $request->input('date');
        
        if ($singleDate) {
            // Single date filter
            $query->where(function ($dateQuery) use ($singleDate) {
                $dateQuery->whereNull('not_available_dates')
                         ->orWhere('not_available_dates', '[]')
                         ->orWhereRaw("JSON_SEARCH(not_available_dates, 'one', ?) IS NULL", [$singleDate]);
            });
        } elseif ($startDate && $endDate) {
            // Date range filter - return farms if AT LEAST ONE day is available
            $dateRange = $this->getDateRange($startDate, $endDate);
            
            $query->where(function ($dateQuery) use ($dateRange) {
                $dateQuery->whereNull('not_available_dates')
                         ->orWhere('not_available_dates', '[]')
                         ->orWhere(function ($rangeQuery) use ($dateRange) {
                             // At least one date should be available (not in not_available_dates)
                             foreach ($dateRange as $date) {
                                 $rangeQuery->orWhereRaw("JSON_SEARCH(not_available_dates, 'one', ?) IS NULL", [$date]);
                             }
                         });
            });
        } elseif ($startDate) {
            // Only start date provided, treat as single date
            $query->where(function ($dateQuery) use ($startDate) {
                $dateQuery->whereNull('not_available_dates')
                         ->orWhere('not_available_dates', '[]')
                         ->orWhereRaw("JSON_SEARCH(not_available_dates, 'one', ?) IS NULL", [$startDate]);
            });
        }
        
        return $query;
    }

    /**
     * Apply offer filter (farms with or without offers)
     */
    private function applyOfferFilter($query, Request $request)
    {
        $hasOffer = $request->input('has_offer');
        
        if ($hasOffer !== null) {
            if ($request->boolean('has_offer')) {
                // Get farms with valid offers
                $query->whereHas('offers', function ($offerQuery) {
                    $offerQuery->where('start_date', '<=', now())
                              ->where('end_date', '>=', now())
                              ->where('is_active', true);
                });
            } else {
                // Get farms without valid offers
                $query->whereDoesntHave('offers', function ($offerQuery) {
                    $offerQuery->where('start_date', '<=', now())
                              ->where('end_date', '>=', now())
                              ->where('is_active', true);
                });
            }
        }
        
        return $query;
    }

    /**
     * Apply passenger count filter
     */
    private function applyPassengerCountFilter($query, Request $request)
    {
        $passengerCount = $request->input('passenger_count');
        
        if ($passengerCount !== null) {
            $query->where('passengers_count', '>=', $passengerCount);
        }
        
        return $query;
    }

    /**
     * Apply feature filter
     */
    private function applyFeatureFilter($query, Request $request)
    {
        $features = $request->input('features'); // Array of feature IDs
        
        if ($features && is_array($features) && count($features) > 0) {
            $query->whereHas('features', function ($featureQuery) use ($features) {
                $featureQuery->whereIn('features.id', $features);
            }, '=', count($features)); // Ensures farm has ALL specified features
        }
        
        return $query;
    }

    /**
     * Apply sorting
     */
    private function applySorting($query, Request $request)
    {
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        
        $allowedSortFields = ['created_at', 'updated_at', 'name_ar', 'name_en', 'passengers_count'];
        $allowedSortOrders = ['asc', 'desc'];
        
        if (in_array($sortBy, $allowedSortFields) && in_array($sortOrder, $allowedSortOrders)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('created_at', 'desc');
        }
        
        return $query;
    }

    /**
     * Generate array of dates between start and end date
     */
    private function getDateRange($startDate, $endDate): array
    {
        $dates = [];
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        // Limit the date range to prevent performance issues
        if ($start->diffInDays($end) > 365) {
            throw new \InvalidArgumentException('Date range cannot exceed 365 days');
        }
        
        while ($start->lte($end)) {
            $dates[] = $start->format('Y-m-d');
            $start->addDay();
        }
        
        return $dates;
    }

    /**
     * Get all available filter parameters
     */
    public function getAvailableFilters(): array
    {
        return [
            'city_id' => 'integer - Filter by city ID',
            'min_price' => 'numeric - Minimum price filter',
            'max_price' => 'numeric - Maximum price filter',
            'has_offer' => 'boolean - Filter by farms with/without offers',
            'available_time' => 'string - Filter by time type (day_use, night, full_day)',
            'date' => 'date - Single date availability filter (Y-m-d)',
            'start_date' => 'date - Start date for range filter (Y-m-d)',
            'end_date' => 'date - End date for range filter (Y-m-d)',
            'passenger_count' => 'integer - Minimum passenger capacity',
            'features' => 'array - Array of feature IDs (farm must have ALL)',
            'sort_by' => 'string - Sort field (created_at, updated_at, name_ar, name_en, passengers_count)',
            'sort_order' => 'string - Sort order (asc, desc)',
            'per_page' => 'integer - Items per page for pagination'
        ];
    }
}