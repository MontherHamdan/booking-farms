<?php

namespace App\Observers;

use App\Models\Feature;
use App\Services\FarmCacheService;

class FeatureObserver
{
    public function created(Feature $feature): void
    {
        // Only clear feature caches, not locations!
        FarmCacheService::clearFeatureCaches();
    }

    public function updated(Feature $feature): void
    {
        // Only clear feature caches, not locations!
        FarmCacheService::clearFeatureCaches();
    }

    public function deleted(Feature $feature): void
    {
        // Only clear feature caches, not locations!
        FarmCacheService::clearFeatureCaches();
    }
}