<?php

namespace App\Observers;

use App\Models\Area;
use App\Services\FarmCacheService;

class AreaObserver
{
    public function created(Area $area): void
    {
        // Only clear area caches, not cities or features!
        FarmCacheService::clearAreaCaches();
    }

    public function updated(Area $area): void
    {
        // Only clear area caches, not cities or features!
        FarmCacheService::clearAreaCaches();
    }

    public function deleted(Area $area): void
    {
        // Only clear area caches, not cities or features!
        FarmCacheService::clearAreaCaches();
    }
}