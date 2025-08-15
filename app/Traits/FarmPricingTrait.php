<?php

namespace App\Traits;

use App\Models\Farm;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Exception;

trait FarmPricingTrait
{
    /**
     * Create pricing data for a farm.
     */
    protected function createFarmPricing(Farm $farm, Request $request): void
    {
        $priceTypes = ['day_use', 'night', 'full_day'];
        
        foreach ($priceTypes as $priceType) {
            if ($this->hasPricingDataForType($request, $priceType)) {
                $pricingData = $request->input("{$priceType}_pricing");
                
                $farm->pricing()->create([
                    'price_type' => $priceType,
                    'start_time' => $pricingData['start_time'] ?? null,
                    'end_time' => $pricingData['end_time'] ?? null,
                    'saturday_price' => $pricingData['saturday_price'] ?? 0,
                    'sunday_price' => $pricingData['sunday_price'] ?? 0,
                    'monday_price' => $pricingData['monday_price'] ?? 0,
                    'tuesday_price' => $pricingData['tuesday_price'] ?? 0,
                    'wednesday_price' => $pricingData['wednesday_price'] ?? 0,
                    'thursday_price' => $pricingData['thursday_price'] ?? 0,
                    'friday_price' => $pricingData['friday_price'] ?? 0,
                ]);
            }
        }
    }

    /**
     * Update pricing data for a farm.
     */
    protected function updateFarmPricing(Farm $farm, Request $request): void
    {
        $priceTypes = ['day_use', 'night', 'full_day'];
        
        foreach ($priceTypes as $priceType) {
            if ($this->hasPricingDataForType($request, $priceType)) {
                $pricingData = $request->input("{$priceType}_pricing");
                
                $farm->pricing()->updateOrCreate(
                    ['price_type' => $priceType],
                    [
                        'start_time' => $pricingData['start_time'] ?? null,
                        'end_time' => $pricingData['end_time'] ?? null,
                        'saturday_price' => $pricingData['saturday_price'] ?? 0,
                        'sunday_price' => $pricingData['sunday_price'] ?? 0,
                        'monday_price' => $pricingData['monday_price'] ?? 0,
                        'tuesday_price' => $pricingData['tuesday_price'] ?? 0,
                        'wednesday_price' => $pricingData['wednesday_price'] ?? 0,
                        'thursday_price' => $pricingData['thursday_price'] ?? 0,
                        'friday_price' => $pricingData['friday_price'] ?? 0,
                    ]
                );
            }
        }
    }

    /**
     * Check if pricing data exists for a specific price type.
     */
    protected function hasPricingDataForType(Request $request, string $priceType): bool
    {
        return $request->has("{$priceType}_pricing") && 
               is_array($request->input("{$priceType}_pricing"));
    }

    /**
     * Get default time ranges for price types.
     */
    protected function getDefaultTimeRanges(): array
    {
        return [
            'day_use' => [
                'start_time' => '08:00',
                'end_time' => '18:00'
            ],
            'night' => [
                'start_time' => '18:00',
                'end_time' => '08:00'
            ],
            'full_day' => [
                'start_time' => '00:00',
                'end_time' => '23:59'
            ]
        ];
    }

    /**
     * Set default time ranges if not provided.
     */
    protected function setDefaultTimeRanges(Request $request): void
    {
        $defaultRanges = $this->getDefaultTimeRanges();
        
        foreach ($defaultRanges as $priceType => $timeRange) {
            if ($request->has("{$priceType}_pricing")) {
                $pricingData = $request->input("{$priceType}_pricing");
                
                if (empty($pricingData['start_time'])) {
                    $pricingData['start_time'] = $timeRange['start_time'];
                }
                
                if (empty($pricingData['end_time'])) {
                    $pricingData['end_time'] = $timeRange['end_time'];
                }
                
                $request->merge(["{$priceType}_pricing" => $pricingData]);
            }
        }
    }

    /**
     * Validate time ranges don't overlap (except for full_day).
     */
    protected function validateTimeRanges(Request $request): array
    {
        $errors = [];
        $timeRanges = [];
        
        // Collect all time ranges
        foreach (['day_use', 'night'] as $priceType) {
            if ($this->hasPricingDataForType($request, $priceType)) {
                $pricingData = $request->input("{$priceType}_pricing");
                
                if (!empty($pricingData['start_time']) && !empty($pricingData['end_time'])) {
                    $timeRanges[$priceType] = [
                        'start' => $pricingData['start_time'],
                        'end' => $pricingData['end_time']
                    ];
                }
            }
        }
        
        // Check for overlaps between day_use and night
        if (count($timeRanges) >= 2) {
            if ($this->timeRangesOverlap($timeRanges['day_use'], $timeRanges['night'])) {
                $errors[] = 'Day use and night pricing time ranges cannot overlap.';
            }
        }
        
        return $errors;
    }

    /**
     * Check if two time ranges overlap.
     */
    private function timeRangesOverlap(array $range1, array $range2): bool
    {
        $start1 = \Carbon\Carbon::createFromFormat('H:i', $range1['start']);
        $end1 = \Carbon\Carbon::createFromFormat('H:i', $range1['end']);
        $start2 = \Carbon\Carbon::createFromFormat('H:i', $range2['start']);
        $end2 = \Carbon\Carbon::createFromFormat('H:i', $range2['end']);
        
        // Handle overnight periods
        if ($end1->lt($start1)) {
            $end1 = $end1->addDay();
        }
        if ($end2->lt($start2)) {
            $end2 = $end2->addDay();
        }
        
        return $start1->lt($end2) && $start2->lt($end1);
    }

    /**
     * Process dates based on price type
     * 
     * @param array $dates
     * @param string $priceType
     * @return array
     */
    private function processDatesByPriceType(array $dates, string $priceType): array
    {
        // For day_use and night, always use the provided dates as-is (should be single date)
        if (in_array($priceType, ['day_use', 'night'])) {
            return $dates;
        }
    
        // For full_day, handle both single date and date range
        if ($priceType === 'full_day') {
            // If only one date provided, return as-is
            if (count($dates) === 1) {
                return $dates;
            }
    
            // If two dates provided, expand the range
            if (count($dates) === 2) {
                $startDate = Carbon::parse($dates[0]);
                $endDate = Carbon::parse($dates[1]);
    
                // Ensure start date is before or equal to end date
                if ($startDate->gt($endDate)) {
                    throw new Exception('Start date must be before or equal to end date');
                }
    
                $dateRange = [];
                $currentDate = $startDate->copy();
    
                while ($currentDate->lte($endDate)) {
                    $dateRange[] = $currentDate->format('Y-m-d');
                    $currentDate->addDay();
                }
    
                return $dateRange;
            }
        }
    
        return $dates;
    }
}