<?php

namespace App\Observers;

use App\Models\City;
use App\Services\FarmCacheService;

class CityObserver
{
    public function created(City $city): void
    {
        // Only clear city caches, not features!
        FarmCacheService::clearCityCaches();
    }

    public function updated(City $city): void
    {
        // Only clear city caches, not features!
        FarmCacheService::clearCityCaches();
    }

    public function deleted(City $city): void
    {
        // Clear both city and area caches (areas depend on cities)
        FarmCacheService::clearAllLocationCaches();
    }
}