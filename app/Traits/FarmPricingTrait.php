<?php

namespace App\Traits;

use App\Models\Farm;
use Illuminate\Http\Request;

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
                $farm->pricing()->create([
                    'price_type' => $priceType,
                    'saturday_price' => $request->input("{$priceType}_pricing.saturday_price", 0),
                    'sunday_price' => $request->input("{$priceType}_pricing.sunday_price", 0),
                    'monday_price' => $request->input("{$priceType}_pricing.monday_price", 0),
                    'tuesday_price' => $request->input("{$priceType}_pricing.tuesday_price", 0),
                    'wednesday_price' => $request->input("{$priceType}_pricing.wednesday_price", 0),
                    'thursday_price' => $request->input("{$priceType}_pricing.thursday_price", 0),
                    'friday_price' => $request->input("{$priceType}_pricing.friday_price", 0),
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
                $farm->pricing()->updateOrCreate(
                    ['price_type' => $priceType],
                    [
                        'saturday_price' => $request->input("{$priceType}_pricing.saturday_price", 0),
                        'sunday_price' => $request->input("{$priceType}_pricing.sunday_price", 0),
                        'monday_price' => $request->input("{$priceType}_pricing.monday_price", 0),
                        'tuesday_price' => $request->input("{$priceType}_pricing.tuesday_price", 0),
                        'wednesday_price' => $request->input("{$priceType}_pricing.wednesday_price", 0),
                        'thursday_price' => $request->input("{$priceType}_pricing.thursday_price", 0),
                        'friday_price' => $request->input("{$priceType}_pricing.friday_price", 0),
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
}