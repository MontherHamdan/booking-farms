<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use App\Models\City;
use App\Models\Area;
use App\Models\Feature;
use App\Observers\CityObserver;
use App\Observers\AreaObserver;
use App\Observers\FeatureObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrap(); 

        // Register observers for automatic cache clearing
        City::observe(CityObserver::class);
        Area::observe(AreaObserver::class);
        Feature::observe(FeatureObserver::class);
    }
}
