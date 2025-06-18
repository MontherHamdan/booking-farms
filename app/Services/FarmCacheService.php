<?php

namespace App\Services;

use App\Models\City;
use Illuminate\Support\Facades\Cache;

class FarmCacheService
{
    /**
     * Clear city-related caches only
     */
    public static function clearCityCaches(): void
    {
        Cache::forget('cities_list');
        Cache::forget('cities_options_en'); //  needed for filter options
        Cache::forget('cities_options_ar'); //  needed for filter options
    }

    /**
     * Clear area-related caches only
     */
    public static function clearAreaCaches(): void
    {
        // Clear city-specific area caches
        $cityIds = City::pluck('id');
        foreach ($cityIds as $cityId) {
            Cache::forget("areas_by_city_{$cityId}");
        }
    }

    /**
     * Clear feature-related caches only
     */
    public static function clearFeatureCaches(): void
    {
        Cache::forget('features_list'); 
        Cache::forget('features_options_en'); //  needed for filter options  
        Cache::forget('features_options_ar'); //  needed for filter options
    }

    /**
     * Clear all location caches (cities + areas)
     */
    public static function clearAllLocationCaches(): void
    {
        self::clearCityCaches();
        self::clearAreaCaches();
    }

    /**
     * Clear all farm-related caches (locations + features)
     */
    public static function clearAllFarmCaches(): void
    {
        self::clearAllLocationCaches();
        self::clearFeatureCaches();
    }
}